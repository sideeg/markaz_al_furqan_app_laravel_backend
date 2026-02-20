@extends('layouts.admin')

@section('title', 'تسجيل طالب في دورة')

@section('content')
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

                    {{-- Course --}}
                    <div class="mb-4">
                        <label class="form-label fw-bold">الدورة <span class="text-danger">*</span></label>
                        <select class="form-select" name="course_id" id="courseSelect" required>
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
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Groups (loaded dynamically) --}}
                    <div class="mb-4" id="groupWrapper" style="display:none;">
                        <label class="form-label fw-bold">المجموعة <span class="text-muted small">(اختياري)</span></label>
                        <select class="form-select" name="group_id" id="groupSelect">
                            <option value="">-- بدون مجموعة --</option>
                        </select>
                    </div>

                    {{-- Student --}}
                    <div class="mb-4">
                        <label class="form-label fw-bold">الطالب <span class="text-danger">*</span></label>
                        <select class="form-select" name="student_id" required>
                            <option value="">-- اختر الطالب --</option>
                            @foreach($students as $student)
                                <option value="{{ $student->id }}" {{ old('student_id') == $student->id ? 'selected' : '' }}>
                                    {{ $student->name }}
                                    @if($student->national_id) — {{ $student->national_id }} @endif
                                </option>
                            @endforeach
                        </select>
                        @error('student_id')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Notes --}}
                    <div class="mb-4">
                        <label class="form-label fw-bold">ملاحظات <span class="text-muted small">(اختياري)</span></label>
                        <textarea class="form-control" name="notes" rows="2">{{ old('notes') }}</textarea>
                    </div>

                    <div class="alert alert-info small">
                        <i class="fas fa-info-circle me-1"></i>
                        التسجيل عبر الإدارة يتم قبوله تلقائياً دون الحاجة لمراجعة.
                    </div>

                    <div class="d-flex justify-content-end gap-2">
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
<script>
document.getElementById('courseSelect').addEventListener('change', function () {
    const courseId = this.value;
    const groupWrapper = document.getElementById('groupWrapper');
    const groupSelect = document.getElementById('groupSelect');

    if (!courseId) {
        groupWrapper.style.display = 'none';
        return;
    }

    // Fetch groups for selected course
    fetch(`/admin/courses/${courseId}/groups`)
        .then(r => r.json())
        .then(groups => {
            groupSelect.innerHTML = '<option value="">-- بدون مجموعة --</option>';
            if (groups.length > 0) {
                groups.forEach(g => {
                    groupSelect.innerHTML += `<option value="${g.id}">${g.name} (${g.available_slots} مكان)</option>`;
                });
                groupWrapper.style.display = 'block';
            } else {
                groupWrapper.style.display = 'none';
            }
        });
});
</script>
@endsection