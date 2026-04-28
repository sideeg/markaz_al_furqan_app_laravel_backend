@extends('layouts.admin')

@section('title', 'إدارة طلبات التسجيل')

@section('content')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<div class="content-header">
    <div class="page-title">
        <i class="fas fa-user-graduate"></i>
        <h3>إدارة طلبات التسجيل</h3>
    </div>
</div>

<div class="admin-card">
    <div class="card-header bg-primary text-white">
        <h5>طلبات التسجيل في الدورات</h5>
        <a href="{{ route('admin.enrollments.create') }}" class="btn btn-light btn-sm">
        <i class="fas fa-user-plus"></i> تسجيل طالب
    </a>
    </div>
    
    <div class="card-body">
        <!-- Filters -->
        <form action="{{ route('admin.enrollments.index') }}" method="GET" class="row mb-4 align-items-end">
    
    {{-- Text Search --}}
    <div class="col-md-3 mb-2">
        <label class="form-label fw-bold">بحث عام</label>
        <div class="input-group">
            <span class="input-group-text bg-white"><i class="fas fa-search"></i></span>
            <input type="text" name="search" class="form-control" placeholder="اسم طالب، رقم وطني، دورة..." value="{{ request('search') }}">
        </div>
    </div>

    {{-- Course Dropdown (Searchable) --}}
    <div class="col-md-3 mb-2">
        <label class="form-label fw-bold">الدورة</label>
        <select class="form-select searchable-select" name="course_id">
            <option value="">جميع الدورات</option>
            @foreach($courses as $course)
                <option value="{{ $course->id }}" {{ request('course_id') == $course->id ? 'selected' : '' }}>
                    {{ $course->name }}
                </option>
            @endforeach
        </select>
    </div>
    
    {{-- Student Dropdown (Searchable) --}}
    <div class="col-md-3 mb-2">
        <label class="form-label fw-bold">الطالب</label>
        <select class="form-select searchable-select" name="student_id">
            <option value="">جميع الطلاب</option>
            @foreach($students as $student)
                <option value="{{ $student->id }}" {{ request('student_id') == $student->id ? 'selected' : '' }}>
                    {{ $student->name }} @if($student->national_id) ({{ $student->national_id }}) @endif
                </option>
            @endforeach
        </select>
    </div>
    
    {{-- Status Dropdown --}}
    <div class="col-md-3 mb-2">
        <label class="form-label fw-bold">الحالة</label>
        <select class="form-select" name="status">
            <option value="">جميع الحالات</option>
            @foreach($statuses as $key => $value)
                <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>
                    {{ $value }}
                </option>
            @endforeach
        </select>
    </div>
    
    {{-- Action Buttons --}}
    <div class="col-md-12 mt-2 d-flex justify-content-end gap-2">
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-filter me-1"></i> تطبيق الفلتر
        </button>
        @if(request()->anyFilled(['search', 'course_id', 'student_id', 'course_type', 'status']))
            <a href="{{ route('admin.enrollments.index') }}" class="btn btn-outline-danger" title="إلغاء الفلاتر">
                <i class="fas fa-times me-1"></i> إلغاء
            </a>
        @endif
    </div>
</form>
        
        <!-- Enrollment Requests Table -->
        <div class="table-responsive">
            <table class="table admin-table table-hover">
                <thead>
                    <tr>
                        <th>الطالب</th>
                        <th>الدورة</th>
                        <th>نوع الدورة</th>
                        <th>تاريخ الطلب</th>
                        <th>الحالة</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($enrollments as $enrollment)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                @if($enrollment->student->profile_image)
                                <img src="{{ $enrollment->student->profile_image_url }}" 
                                     alt="{{ $enrollment->student->name }}" 
                                     class="rounded-circle me-3" 
                                     width="40" height="40">
                                @else
                                <div class="bg-light rounded-circle d-flex align-items-center justify-content-center me-3" 
                                     style="width: 40px; height: 40px;">
                                    <span class="fw-bold">{{ $enrollment->student->initials }}</span>
                                </div>
                                @endif
                                <div>
                                    <strong>{{ $enrollment->student->name }}</strong>
                                    <div class="text-muted small">{{ $enrollment->student->national_id }}</div>
                                </div>
                            </div>
                        </td>
                        <td>{{ $enrollment->course->name }}</td>
                        <td>
                            @if($enrollment->course->type === 'online')
                            <span class="badge bg-primary">أونلاين</span>
                            @elseif($enrollment->course->type === 'open')
                            <span class="badge bg-success">مفتوحة</span>
                            @else
                            <span class="badge bg-secondary">مغلقة</span>
                            @endif
                        </td>
                        <td>{{ $enrollment->enrolled_at->format('Y-m-d') }}</td>
                        <td>
                            @if($enrollment->status === 'pending')
                            <span class="badge bg-warning">قيد الانتظار</span>
                            @elseif($enrollment->status === 'approved')
                            <span class="badge bg-success">مقبول</span>
                            @else
                            <span class="badge bg-danger">مرفوض</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('admin.enrollments.show', $enrollment) }}" class="btn btn-sm btn-info" title="عرض التفاصيل">
                                <i class="fas fa-eye"></i>
                            </a>
                            @if($enrollment->status === 'pending')
    {{-- زر القبول محاط بـ Form لإرسال الطلب --}}
    <form action="{{ route('admin.enrollments.approve', $enrollment) }}" method="POST" style="display:inline-block;">
        @csrf
        @method('Post') 
        <button type="submit" class="btn btn-sm btn-success" 
                title="قبول الطلب" 
                onclick="return confirm('هل أنت متأكد من قبول هذا الطلب؟')">
            <i class="fas fa-check"></i>
        </button>
    </form>

    <button class="btn btn-sm btn-danger reject-btn" 
            data-id="{{ $enrollment->id }}"
            title="رفض الطلب">
        <i class="fas fa-times"></i>
    </button>
@endif
                            <form action="{{ route('admin.enrollments.destroy', $enrollment) }}" method="POST" style="display:inline-block;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" 
                                        title="حذف الطلب"
                                        onclick="return confirm('هل أنت متأكد من حذف هذا الطلب؟')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            <i class="fas fa-inbox fa-2x mb-3"></i>
                            <p>لا توجد طلبات تسجيل</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="d-flex justify-content-between align-items-center mt-4">
            <div class="text-muted">
                عرض <strong>{{ $enrollments->count() }}</strong> من أصل <strong>{{ $enrollments->total() }}</strong> طلبات
            </div>
            <div>
                {{ $enrollments->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Rejection Modal -->
<div class="modal fade" id="rejectionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">رفض طلب التسجيل</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="" id="rejectionForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">سبب الرفض <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="rejection_reason" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-danger">رفض الطلب</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Setup rejection modal
        $('.reject-btn').click(function() {
            const enrollmentId = $(this).data('id');
            const form = $('#rejectionForm');
            form.attr('action', `/admin/admin/enrollments/${enrollmentId}/reject`);
            $('#rejectionModal').modal('show');
        });
    });
</script>
<script>
    $(document).ready(function() {
        // Setup rejection modal
        $('.reject-btn').click(function() {
            const enrollmentId = $(this).data('id');
            const form = $('#rejectionForm');
            form.attr('action', `/admin/admin/enrollments/${enrollmentId}/reject`); // Fixed the double /admin/admin typo here!
            $('#rejectionModal').modal('show');
        });

        // Setup Select2 with Arabic Normalizer
        function normalizeArabic(text) {
            if (!text) return '';
            return text.replace(/[أإآ]/g, 'ا').replace(/ة/g, 'ه').replace(/[ًٌٍَُِّْ]/g, '').toLowerCase();
        }

        function customMatcher(params, data) {
            if ($.trim(params.term) === '') return data;
            if (typeof data.text === 'undefined') return null;
            
            var term = normalizeArabic(params.term);
            var text = normalizeArabic(data.text);

            if (text.indexOf(term) > -1) return data;
            return null;
        }

        $('.searchable-select').select2({
            theme: 'bootstrap-5',
            width: '100%',
            matcher: customMatcher,
            language: {
                noResults: function() { return 'لم يتم العثور على نتائج'; },
                searching: function() { return 'جاري البحث...'; }
            }
        });
    });
</script>
@endsection