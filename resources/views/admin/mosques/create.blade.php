@extends('layouts.admin')

@section('content')
<div class="card">
    <div class="card-header bg-primary text-white">
        <h3 class="card-title">
            <i class="fas fa-mosque"></i> إضافة مسجد جديد
        </h3>
    </div>
    
    <div class="card-body">
        <form method="POST" action="{{ route('mosques.store') }}" enctype="multipart/form-data">
            @csrf
            <!-- Use the same form structure as the modal -->
            <div class="row">
                <!-- Basic Information Column -->
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="mosqueName" class="form-label fw-bold">اسم المسجد <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="mosqueName" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="mosqueDescription" class="form-label fw-bold">وصف المسجد</label>
                        <textarea class="form-control" id="mosqueDescription" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="mosquePhone" class="form-label fw-bold">رقم الهاتف</label>
                        <input type="text" class="form-control" id="mosquePhone" name="phone">
                    </div>
                    
                    <div class="mb-3">
                        <label for="mosqueEmail" class="form-label fw-bold">البريد الإلكتروني</label>
                        <input type="email" class="form-control" id="mosqueEmail" name="email">
                    </div>
                </div>
                
                <!-- Location & Image Column -->
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="mosqueAddress" class="form-label fw-bold">العنوان <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="mosqueAddress" name="address" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="mosqueCity" class="form-label fw-bold">المدينة <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="mosqueCity" name="city" required>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="mosqueLatitude" class="form-label fw-bold">خط العرض</label>
                            <input type="number" step="any" class="form-control" id="mosqueLatitude" name="latitude">
                        </div>
                        <div class="col-md-6">
                            <label for="mosqueLongitude" class="form-label fw-bold">خط الطول</label>
                            <input type="number" step="any" class="form-control" id="mosqueLongitude" name="longitude">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="mosqueImage" class="form-label fw-bold">صورة المسجد</label>
                        <input class="form-control" type="file" id="mosqueImage" name="image_path" accept="image/*">
                        <small class="text-muted">الصيغ المقبولة: JPG, PNG, GIF - الحجم الأقصى: 2MB</small>
                    </div>
                    
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="mosqueActive" name="is_active" value="1" checked>
                        <label class="form-check-label fw-bold" for="mosqueActive">الحالة النشطة</label>
                    </div>
                </div>
            </div>
            
            <div class="d-flex justify-content-end mt-4">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="fas fa-save me-1"></i> حفظ المسجد
                </button>
                <a href="{{ route('mosques.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times me-1"></i> إلغاء
                </a>
            </div>
        </form>
    </div>
</div>
@endsection