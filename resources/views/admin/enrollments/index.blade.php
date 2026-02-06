@extends('layouts.admin')

@section('title', 'إدارة طلبات التسجيل')

@section('content')
<div class="content-header">
    <div class="page-title">
        <i class="fas fa-user-graduate"></i>
        <h3>إدارة طلبات التسجيل</h3>
    </div>
</div>

<div class="admin-card">
    <div class="card-header bg-primary text-white">
        <h5>طلبات التسجيل في الدورات</h5>
    </div>
    
    <div class="card-body">
        <!-- Filters -->
        <form method="GET" class="row mb-4">
            <div class="col-md-3 mb-2">
                <label class="form-label">الدورة</label>
                <select class="form-select" name="course_id">
                    <option value="">جميع الدورات</option>
                    @foreach($courses as $course)
                        <option value="{{ $course->id }}" {{ request('course_id') == $course->id ? 'selected' : '' }}>
                            {{ $course->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="col-md-3 mb-2">
                <label class="form-label">الطالب</label>
                <select class="form-select" name="student_id">
                    <option value="">جميع الطلاب</option>
                    @foreach($students as $student)
                        <option value="{{ $student->id }}" {{ request('student_id') == $student->id ? 'selected' : '' }}>
                            {{ $student->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="col-md-2 mb-2">
                <label class="form-label">نوع الدورة</label>
                <select class="form-select" name="course_type">
                    <option value="">الكل</option>
                    <option value="online" {{ request('course_type') == 'online' ? 'selected' : '' }}>أونلاين</option>
                    <option value="open" {{ request('course_type') == 'open' ? 'selected' : '' }}>مفتوحة</option>
                    <option value="closed" {{ request('course_type') == 'closed' ? 'selected' : '' }}>مغلقة</option>
                </select>
            </div>
            
            <div class="col-md-2 mb-2">
                <label class="form-label">الحالة</label>
                <select class="form-select" name="status">
                    @foreach($statuses as $key => $value)
                        <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>
                            {{ $value }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="col-md-2 d-flex align-items-end mb-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-filter"></i> تطبيق الفلتر
                </button>
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
                            <button class="btn btn-sm btn-success approve-btn" 
                                    data-id="{{ $enrollment->id }}"
                                    title="قبول الطلب">
                                <i class="fas fa-check"></i>
                            </button>
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
@endsection