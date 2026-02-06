@extends('layouts.admin')

@section('title', 'إضافة دورة جديدة')

@section('content')
<div class="content-header">
    <div class="page-title">
        <i class="fas fa-plus-circle"></i>
        <h3>إضافة دورة جديدة</h3>
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
        <form method="POST" action="{{ route('admin.courses.store') }}" enctype="multipart/form-data">
            @csrf
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label fw-bold">اسم الدورة <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">وصف الدورة</label>
                        <textarea class="form-control" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">نوع الدورة <span class="text-danger">*</span></label>
                        <select class="form-select" name="type" required>
                            <option value="online">أونلاين</option>
                            <option value="open">مفتوحة</option>
                            <option value="closed">مغلقة</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">المسجد</label>
                        <select class="form-select" name="mosque_id">
                            <option value="">اختر مسجدًا (اختياري)</option>
                            @foreach($mosques as $mosque)
                                <option value="{{ $mosque->id }}">{{ $mosque->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">تاريخ البدء <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="start_date" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">تاريخ الانتهاء <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="end_date" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">الحد الأقصى للطلاب <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="max_students" min="1" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">صورة الدورة</label>
                            <input class="form-control" type="file" name="image">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">متطلبات الدورة</label>
                        <textarea class="form-control" name="requirements" rows="2"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">تفاصيل الجدول الزمني</label>
                        <textarea class="form-control" name="schedule_details" rows="2"></textarea>
                    </div>
                    
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="isRegistrationOpen" name="is_registration_open" value="1" checked>
                        <label class="form-check-label fw-bold" for="isRegistrationOpen">فتح التسجيل</label>
                    </div>
                </div>
            </div>
            
            <div class="d-flex justify-content-end mt-4">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="fas fa-save me-1"></i> حفظ الدورة
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
                $(this).val('');
            }
        });
    });
</script>
@endsection