@extends('layouts.admin')

@section('title', 'تعديل الدورة: ' . $course->name)

@section('content')
<div class="content-header">
    <div class="page-title">
        <i class="fas fa-edit"></i>
        <h3>تعديل الدورة: {{ $course->name }}</h3>
    </div>
    <a href="{{ route('admin.courses.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> العودة للقائمة
    </a>
</div>

<div class="admin-card">
    <div class="card-header bg-primary text-white">
        <h5>تفاصيل الدورة</h5>
    </div>
    
    <div class="card-body">
        <form method="POST" action="{{ route('admin.courses.update', $course) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label fw-bold">اسم الدورة <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" value="{{ old('name', $course->name) }}" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">وصف الدورة</label>
                        <textarea class="form-control" name="description" rows="3">{{ old('description', $course->description) }}</textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">نوع الدورة <span class="text-danger">*</span></label>
                        <select class="form-select" name="type" required>
                            <option value="online" {{ old('type', $course->type) === 'online' ? 'selected' : '' }}>أونلاين</option>
                            <option value="open" {{ old('type', $course->type) === 'open' ? 'selected' : '' }}>مفتوحة</option>
                            <option value="closed" {{ old('type', $course->type) === 'closed' ? 'selected' : '' }}>مغلقة</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">المسجد</label>
                        <select class="form-select" name="mosque_id">
                            <option value="">اختر مسجدًا (اختياري)</option>
                            @foreach($mosques as $mosque)
                                <option value="{{ $mosque->id }}" 
                                    {{ old('mosque_id', $course->mosque_id) == $mosque->id ? 'selected' : '' }}>
                                    {{ $mosque->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">تاريخ البدء <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="start_date" 
                                   value="{{ old('start_date', $course->start_date->format('Y-m-d')) }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">تاريخ الانتهاء <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="end_date" 
                                   value="{{ old('end_date', $course->end_date->format('Y-m-d')) }}" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">الحد الأقصى للطلاب <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="max_students" 
                                   value="{{ old('max_students', $course->max_students) }}" min="1" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">صورة الدورة</label>
                            <input class="form-control" type="file" name="image">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">متطلبات الدورة</label>
                        <textarea class="form-control" name="requirements" rows="2">{{ old('requirements', $course->requirements) }}</textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">تفاصيل الجدول الزمني</label>
                        <textarea class="form-control" name="schedule_details" rows="2">{{ old('schedule_details', $course->schedule_details) }}</textarea>
                    </div>
                    
                    <input type="hidden" name="is_registration_open" value="0">

<div class="form-check form-switch mb-3">
    <input class="form-check-input" type="checkbox" id="isRegistrationOpen" 
           name="is_registration_open" value="1" 
           {{ old('is_registration_open', $course->is_registration_open) ? 'checked' : '' }}>
    <label class="form-check-label fw-bold" for="isRegistrationOpen">فتح التسجيل</label>
</div>
                    
                    @if($course->image_path)
                    <div class="mb-3">
                        <label class="form-label fw-bold">الصورة الحالية</label>
                        <div class="d-flex align-items-center">
                            <img src="{{ Storage::url($course->image_path) }}" 
                                 alt="{{ $course->name }}" 
                                 class="img-thumbnail me-3" 
                                 style="max-width: 100px">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="removeImage" name="remove_image" value="1">
                                <label class="form-check-label text-danger fw-bold" for="removeImage">
                                    حذف الصورة الحالية
                                </label>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            
            <div class="d-flex justify-content-end mt-4">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="fas fa-save me-1"></i> حفظ التعديلات
                </button>
                <button type="reset" class="btn btn-outline-secondary">
                    <i class="fas fa-redo me-1"></i> إعادة تعيين
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Date validation
        $('input[name="end_date"]').change(function() {
            const startDate = new Date($('input[name="start_date"]').val());
            const endDate = new Date($(this).val());
            
            if (endDate < startDate) {
                alert('تاريخ الانتهاء يجب أن يكون بعد تاريخ البدء');
                $(this).val('{{ $course->end_date->format('Y-m-d') }}');
            }
        });
    });
</script>
@endsection