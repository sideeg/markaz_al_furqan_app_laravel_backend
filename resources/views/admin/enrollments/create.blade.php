@extends('layouts.admin')

@section('title', 'تسجيل طالب في دورة')

@section('content')

{{-- Include Select2 CSS --}}
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />

<style>
    /* Styling adjustments for Select2 inside Bootstrap 5 */
    .select2-container--bootstrap-5 .select2-selection--single {
        height: 38px;
        display: flex;
        align-items: center;
    }
    
    .select2-container--bootstrap-5.select2-container--open .select2-selection--single {
        border-color: #86b7fe;
    }
</style>

<div class="content-header">
    <div class="page-title">
        <i class="fas fa-user-plus"></i>
        <h3>تسجيل طالب في دورة</h3>
    </div>
    <a href="{{ route('admin.enrollments.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> العودة
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="admin-card">
            <div class="card-header bg-primary text-white">
                <h5><i class="fas fa-user-graduate me-2"></i>بيانات التسجيل</h5>
            </div>
            <div class="card-body">

                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <form method="POST" action="{{ route('admin.enrollments.store') }}" id="enrollForm">
                    @csrf

                    {{-- Course (Now Searchable) --}}
                    <div class="mb-4">
                        <label class="form-label fw-bold">الدورة <span class="text-danger">*</span></label>
                        <select class="form-select searchable-select" name="course_id" id="courseSelect" required>
                            <option value="">-- اختر الدورة --</option>
                            @foreach($courses as $course)
                                <option value="{{ $course->id }}"
                                    data-slots="{{ $course->available_slots }}"
                                    {{ old('course_id') == $course->id ? 'selected' : '' }}>
                                    {{ $course->name }}
                                    ({{ $course->available_slots }} مكان متاح)
                                </option>
                            @endforeach
                        </select>
                        @error('course_id')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Groups (loaded dynamically) --}}
                    <div class="mb-4" id="groupWrapper" style="display:none;">
                        <label class="form-label fw-bold">المجموعة <span class="text-muted small">(اختياري)</span></label>
                        <select class="form-select" name="group_id" id="groupSelect">
                            <option value="">-- بدون مجموعة --</option>
                        </select>
                    </div>

                    {{-- Student (Now Searchable) --}}
                    <div class="mb-4">
                        <label class="form-label fw-bold">الطالب <span class="text-danger">*</span></label>
                        <select class="form-select searchable-select" name="student_id" required>
                            <option value="">-- اكتب اسم الطالب أو الرقم الوطني للبحث --</option>
                            @foreach($students as $student)
                                <option value="{{ $student->id }}" {{ old('student_id') == $student->id ? 'selected' : '' }}>
                                    {{ $student->name }}
                                    @if($student->national_id) — ({{ $student->national_id }}) @endif
                                </option>
                            @endforeach
                        </select>
                        @error('student_id')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Notes --}}
                    <div class="mb-4">
                        <label class="form-label fw-bold">ملاحظات <span class="text-muted small">(اختياري)</span></label>
                        <textarea class="form-control" name="notes" rows="2" placeholder="أضف أي ملاحظات إضافية حول هذا التسجيل...">{{ old('notes') }}</textarea>
                    </div>

                    <div class="alert alert-info small">
                        <i class="fas fa-info-circle me-1"></i>
                        التسجيل عبر الإدارة يتم قبوله تلقائياً دون الحاجة لمراجعة.
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="{{ route('admin.enrollments.index') }}" class="btn btn-secondary">إلغاء</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-user-plus me-1"></i> تسجيل الطالب
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
{{-- Include Select2 JS --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function() {
    // 1. Arabic Text Normalizer for better searching
    function normalizeArabic(text) {
        if (!text) return '';
        return text
            .replace(/[أإآ]/g, 'ا')
            .replace(/ة/g, 'ه')
            .replace(/[ًٌٍَُِّْ]/g, '')
            .toLowerCase();
    }

    // 2. Custom Matcher for Select2
    function customMatcher(params, data) {
        if ($.trim(params.term) === '') {
            return data;
        }
        if (typeof data.text === 'undefined') {
            return null;
        }
        
        var term = normalizeArabic(params.term);
        var text = normalizeArabic(data.text);

        if (text.indexOf(term) > -1) {
            return data;
        }
        return null;
    }

    // 3. Initialize Select2 on both dropdowns
    $('.searchable-select').select2({
        theme: 'bootstrap-5',
        width: '100%',
        matcher: customMatcher,
        language: {
            noResults: function() { return 'لم يتم العثور على نتائج'; },
            searching: function() { return 'جاري البحث...'; }
        }
    });

    // 4. Dynamic Group Loading (Using jQuery since we loaded it for Select2)
    $('#courseSelect').on('change', function () {
        const courseId = $(this).val();
        const groupWrapper = $('#groupWrapper');
        const groupSelect = $('#groupSelect');

        if (!courseId) {
            groupWrapper.hide();
            groupSelect.html('<option value="">-- بدون مجموعة --</option>');
            return;
        }

        // Fetch groups for selected course
        $.ajax({
            url: `/admin/courses/${courseId}/groups`,
            method: 'GET',
            success: function(groups) {
                groupSelect.html('<option value="">-- بدون مجموعة --</option>');
                
                if (groups && groups.length > 0) {
                    groups.forEach(g => {
                        groupSelect.append(`<option value="${g.id}">${g.name} (${g.available_slots} مكان متاح)</option>`);
                    });
                    groupWrapper.fadeIn();
                } else {
                    groupWrapper.hide();
                }
            },
            error: function() {
                console.error("Failed to load groups.");
            }
        });
    });

    // If a course is pre-selected (e.g., after validation error), trigger the group fetch
    if ($('#courseSelect').val()) {
        $('#courseSelect').trigger('change');
    }
});
</script>
@endsection