{{-- FIXED: ReviewLog Index with Better Search --}}
{{-- Now searches full names, not just first letters --}}

@extends('layouts.admin')
@section('title', 'سجلات المراجعة')
@section('content')

{{-- Include Select2 CSS --}}
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />

<style>
    .select2-container--bootstrap-5 .select2-selection--single {
        height: 38px;
        display: flex;
        align-items: center;
    }
    
    .select2-container--bootstrap-5.select2-container--open .select2-selection--single {
        border-color: #86b7fe;
    }
</style>

<div class="content-header d-flex justify-content-between align-items-center mb-3">
    <h3>سجلات المراجعة</h3>
</div>

<div class="admin-card">
    <div class="card-body">
        {{-- ENHANCED FILTERS FORM WITH SEARCH --}}
        <form method="GET" class="row g-3 mb-4">
            {{-- Student Filter (Searchable) --}}
            <div class="col-md-3">
                <label class="form-label fw-bold">🧑‍🎓 الطالب</label>
                <select name="student_id" class="form-select searchable-select" id="student_filter">
                    <option value="">-- اختر الطالب --</option>
                    @foreach($students as $student)
                        <option value="{{ $student->id }}" 
                                {{ request('student_id') == $student->id ? 'selected' : '' }}>
                            {{ $student->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Sheikh Filter (Searchable) --}}
            <div class="col-md-3">
                <label class="form-label fw-bold">🧔 الشيخ</label>
                <select name="sheikh_id" class="form-select searchable-select" id="sheikh_filter">
                    <option value="">-- اختر الشيخ --</option>
                    @foreach($sheikhs as $sheikh)
                        <option value="{{ $sheikh->id }}" 
                                {{ request('sheikh_id') == $sheikh->id ? 'selected' : '' }}>
                            {{ $sheikh->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Course Filter (Searchable) --}}
            <div class="col-md-3">
                <label class="form-label fw-bold">📚 الدورة</label>
                <select name="course_id" class="form-select searchable-select" id="course_filter">
                    <option value="">-- اختر الدورة --</option>
                    @foreach($courses as $course)
                        <option value="{{ $course->id }}" 
                                {{ request('course_id') == $course->id ? 'selected' : '' }}>
                            {{ $course->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Surah Filter --}}
            <div class="col-md-3">
                <label class="form-label fw-bold">📖 السورة</label>
                <input type="text" name="surah" value="{{ request('surah') }}" 
                       class="form-control" placeholder="ابحث عن السورة...">
            </div>

            {{-- From Date --}}
            <div class="col-md-3">
                <label class="form-label fw-bold">📅 من تاريخ</label>
                <input type="date" name="from" value="{{ request('from') }}" class="form-control">
            </div>

            {{-- To Date --}}
            <div class="col-md-3">
                <label class="form-label fw-bold">📅 إلى تاريخ</label>
                <input type="date" name="to" value="{{ request('to') }}" class="form-control">
            </div>

            {{-- Buttons --}}
            <div class="col-md-12 d-flex justify-content-end gap-2">
                <button class="btn btn-primary" type="submit">
                    <i class="fas fa-filter"></i> تصفية
                </button>
                <a href="{{ route('admin.review_logs.index') }}" class="btn btn-secondary">
                    <i class="fas fa-redo"></i> إعادة تعيين
                </a>
            </div>
        </form>

        {{-- Success/Error Messages --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- LOGS TABLE --}}
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>التاريخ</th>
                        <th>الطالب</th>
                        <th>الشيخ</th>
                        <th>الدورة</th>
                        <th>السورة</th>
                        <th>من آية</th>
                        <th>إلى آية</th>
                        <th>التقييم</th>
                        <th>إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                    <tr>
                        <td>{{ $log->session_date?->format('Y-m-d') ?? 'غير محدد' }}</td>
                        <td>{{ $log->student?->name ?? 'محذوف' }}</td>
                        <td>{{ $log->sheikh?->name ?? 'محذوف' }}</td>
                        <td>
                            {{ $log->course?->name ?? 'محذوفة' }}
                            @if($log->course?->deleted_at)
                                <span class="badge bg-danger ms-1">محذوفة</span>
                            @endif
                        </td>
                        <td>
                            <small>
                                {{ $log->start_surah }} 
                                @if($log->start_surah !== $log->end_surah) 
                                    - {{ $log->end_surah }}
                                @endif
                            </small>
                        </td>
                        <td>{{ $log->start_ayah }}</td>
                        <td>{{ $log->end_ayah }}</td>
                        <td>
                            <span class="badge bg-info">
                                @switch($log->evaluation)
                                    @case('excellent')
                                        ممتاز
                                        @break
                                    @case('very_good')
                                        جيد جداً
                                        @break
                                    @case('good')
                                        جيد
                                        @break
                                    @case('needs_improvement')
                                        يحتاج تحسين
                                        @break
                                    @case('poor')
                                        ضعيف
                                        @break
                                @endswitch
                            </span>
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="{{ route('admin.review_logs.show', $log) }}" 
                                   class="btn btn-sm btn-info" title="عرض">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.review_logs.edit', $log) }}" 
                                   class="btn btn-sm btn-warning" title="تعديل">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.review_logs.destroy', $log) }}" 
                                      method="POST" class="d-inline" 
                                      onsubmit="return confirm('هل أنت متأكد؟')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" title="حذف">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-4">
                            <p class="text-muted mb-0">لا توجد سجلات</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="mt-3">
            {{ $logs->links() }}
        </div>
    </div>
</div>

{{-- Include Select2 JS --}}
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    $(document).ready(function() {
        // Custom matcher function for better search
        function customMatcher(params, data) {
            // If there are no search terms, return all of the data
            if ($.trim(params.term) === '') {
                return data;
            }

            // Do not display the item if there is no 'text' property
            if (typeof data.text === 'undefined') {
                return null;
            }

            // `params.term` should be the term that is used for searching
            // `data.text` is the text that is displayed for the data object
            var term = params.term.toLowerCase();
            var text = data.text.toLowerCase();

            // Return `null` if the term should not be displayed
            // Return the data object if the term should be displayed
            // Search anywhere in the text, not just at the beginning
            if (text.indexOf(term) > -1) {
                return data;
            }

            return null;
        }

        // Initialize Select2 for all searchable selects
        $('.searchable-select').select2({
            theme: 'bootstrap-5',
            placeholder: 'اكتب للبحث... (مثال: علي أحمد)',
            allowClear: true,
            width: '100%',
            matcher: customMatcher,  // ✅ USE CUSTOM MATCHER
            language: {
                noResults: function() {
                    return 'لم يتم العثور على نتائج';
                },
                searching: function() {
                    return 'جاري البحث...';
                }
            }
        });
    });
</script>

@endsection