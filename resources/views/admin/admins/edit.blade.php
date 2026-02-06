@extends('layouts.admin')

@section('title', 'تعديل المدير: ' . $admin->name)

@section('content')
<div class="content-header">
    <div class="page-title">
        <i class="fas fa-edit"></i>
        <h3>تعديل المدير: {{ $admin->name }}</h3>
    </div>
    <a href="{{ route('admin.admins.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> العودة للقائمة
    </a>
</div>

<div class="admin-card">
    <div class="card-header bg-primary text-white">
        <h5>معلومات المدير</h5>
    </div>
    
    <div class="card-body">
        <form method="POST" action="{{ route('admin.admins.update', $admin) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label fw-bold">الاسم الكامل <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" 
                               value="{{ old('name', $admin->name) }}" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">البريد الإلكتروني <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" name="email" 
                               value="{{ old('email', $admin->email) }}" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">رقم الهاتف <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="phone" 
                               value="{{ old('phone', $admin->phone) }}" required>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label fw-bold">الدور <span class="text-danger">*</span></label>
                        <select class="form-select" name="role" required>
                            @foreach($roles as $role)
                                <option value="{{ $role->name }}" 
                                    {{ $admin->hasRole($role->name) ? 'selected' : '' }}>
                                    {{ $role->name === 'super_admin' ? 'مدير عام' : 'مدير' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">كلمة المرور الجديدة</label>
                        <input type="password" class="form-control" name="password">
                        <small class="text-muted">اتركه فارغًا إذا لم ترغب في تغيير كلمة المرور</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">تأكيد كلمة المرور الجديدة</label>
                        <input type="password" class="form-control" name="password_confirmation">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">صورة الملف الشخصي</label>
                        <input class="form-control" type="file" name="profile_image">
                    </div>
                    
                    @if($admin->profile_image)
                    <div class="mb-3">
                        <label class="form-label fw-bold">الصورة الحالية</label>
                        <div class="d-flex align-items-center">
                            <img src="{{ $admin->profile_image_url }}" 
                                 alt="{{ $admin->name }}" 
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