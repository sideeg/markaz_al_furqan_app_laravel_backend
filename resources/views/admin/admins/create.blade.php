@extends('layouts.admin')

@section('title', 'إضافة مدير جديد')

@section('content')
<div class="content-header">
    <div class="page-title">
        <i class="fas fa-user-plus"></i>
        <h3>إضافة مدير جديد</h3>
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
        <form method="POST" action="{{ route('admin.admins.store') }}" enctype="multipart/form-data">
            @csrf
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label fw-bold">الاسم الكامل <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">البريد الإلكتروني <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" name="email" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">رقم الهاتف <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="phone" required>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label fw-bold">الدور <span class="text-danger">*</span></label>
                        <select class="form-select" name="role" required>
                            @foreach($roles as $role)
                                <option value="{{ $role->name }}">
                                    {{ $role->name === 'super_admin' ? 'مدير عام' : 'مدير' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">كلمة المرور <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" name="password" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">تأكيد كلمة المرور <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" name="password_confirmation" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">صورة الملف الشخصي</label>
                        <input class="form-control" type="file" name="profile_image">
                    </div>
                </div>
            </div>
            
            <div class="d-flex justify-content-end mt-4">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="fas fa-save me-1"></i> حفظ المدير
                </button>
                <button type="reset" class="btn btn-outline-secondary">
                    <i class="fas fa-redo me-1"></i> إعادة تعيين
                </button>
            </div>
        </form>
    </div>
</div>
@endsection