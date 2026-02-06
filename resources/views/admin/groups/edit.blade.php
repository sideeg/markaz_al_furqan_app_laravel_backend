@extends('layouts.admin')

@section('title', 'تعديل مجموعة ' . $group->name . ' - ' . $course->name)

@section('content')
<div class="content-header">
    <div class="page-title">
        <i class="fas fa-edit"></i>
        <h3>تعديل مجموعة {{ $group->name }} - {{ $course->name }}</h3>
    </div>
    <a href="{{ route('admin.courses.groups.index', $course) }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> العودة للمجموعات
    </a>
</div>

<div class="admin-card">
    <div class="card-header bg-primary text-white">
        <h5>تفاصيل المجموعة</h5>
    </div>
    
    <div class="card-body">
        <form method="POST" action="{{ route('admin.courses.groups.update', [$course, $group]) }}">
            @csrf
            @method('PUT')
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label fw-bold">اسم المجموعة <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" value="{{ old('name', $group->name) }}" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">وصف المجموعة</label>
                        <textarea class="form-control" name="description" rows="3">{{ old('description', $group->description) }}</textarea>
                    </div>
                </div>

               
                
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label fw-bold">الحد الأقصى للطلاب <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="max_students" 
                               value="{{ old('max_students', $group->max_students) }}" min="1" required>
                        <small class="text-muted">العدد الحالي: {{ $group->students->count() }} طالب</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">تفاصيل الجدول الزمني <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="schedule_details" rows="3" required>{{ old('schedule_details', $group->schedule_details) }}</textarea>
                    </div>
                     
                    <div class="mb-2">
                        <label class="form-label fw-bold">الشيخ المسؤول <span class="text-danger">*</span></label>
                        <select class="form-select" name="sheikh_id" required>  
                            <option value="">اختر الشيخ</option>
                            @foreach($shiekhs as $sheikh)
                                <option value="{{ $sheikh->id }}" {{ $group->sheikh_id == $sheikh->id ? 'selected' : '' }}>
                                    {{ $sheikh->name }}
                                </option>
                            @endforeach
                        </select>
                </div>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="isActive" 
                               name="is_active" value="1" {{ $group->is_active ? 'checked' : '' }}>
                        <label class="form-check-label fw-bold" for="isActive">الحالة النشطة</label>
                    </div>
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