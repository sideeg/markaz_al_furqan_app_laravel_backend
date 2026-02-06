@extends('layouts.admin')

@section('title', 'معلومات المدير: ' . $admin->name)

@section('content')
<div class="content-header">
    <div class="page-title">
        <i class="fas fa-user-shield"></i>
        <h3>معلومات المدير: {{ $admin->name }}</h3>
    </div>
    <div>
        <a href="{{ route('admin.admins.edit', $admin) }}" class="btn btn-primary me-2">
            <i class="fas fa-edit"></i> تعديل
        </a>
        <a href="{{ route('admin.admins.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> العودة للقائمة
        </a>
    </div>
</div>

<div class="row">
    <!-- Admin Info -->
    <div class="col-md-4">
        <div class="admin-card">
            <div class="card-header bg-primary text-white">
                <h5>الملف الشخصي</h5>
            </div>
            <div class="card-body text-center">
                @if($admin->profile_image)
                <img src="{{ $admin->profile_image_url }}" 
                     alt="{{ $admin->name }}" 
                     class="rounded-circle mb-3" 
                     width="120" height="120">
                @else
                <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" 
                     style="width: 120px; height: 120px;">
                    <span class="fw-bold" style="font-size: 2rem;">{{ $admin->initials }}</span>
                </div>
                @endif
                
                <h4 class="mb-2">{{ $admin->name }}</h4>
                <p class="text-muted mb-3">{{ $admin->phone }}</p>
                
                <div class="d-flex justify-content-center mb-3">
                    <span class="badge bg-{{ $admin->isSuperAdmin() ? 'danger' : 'primary' }}">
                        {{ $admin->roles->first()->name === 'super_admin' ? 'مدير عام' : 'مدير' }}
                    </span>
                </div>
                
                <div class="d-flex justify-content-center mb-3">
                    <form action="{{ route('admin.admins.toggle-status', $admin) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-sm {{ $admin->is_active ? 'btn-success' : 'btn-danger' }}">
                            {{ $admin->is_active ? 'نشط' : 'معطل' }}
                        </button>
                    </form>
                </div>
                
                <div class="row text-center">
                    <div class="col-6">
                        <div class="p-2 bg-light rounded">
                            <h5 class="mb-0">{{ $admin->createdCourses->count() }}</h5>
                            <small class="text-muted">الدورات المنشأة</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-2 bg-light rounded">
                            <h5 class="mb-0">{{ $admin->sentNotifications->count() }}</h5>
                            <small class="text-muted">الإشعارات المرسلة</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="admin-card mt-4">
            <div class="card-header bg-primary text-white">
                <h5>معلومات الاتصال</h5>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-envelope me-2 text-primary"></i>
                            البريد الإلكتروني
                        </div>
                        <span>{{ $admin->email }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-phone me-2 text-primary"></i>
                            الهاتف
                        </div>
                        <span>{{ $admin->phone }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-calendar me-2 text-primary"></i>
                            تاريخ التسجيل
                        </div>
                        <span>{{ $admin->created_at->format('Y-m-d') }}</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    
    <!-- Admin Activities -->
    <div class="col-md-8">
        <div class="admin-card">
            <div class="card-header bg-primary text-white">
                <h5>آخر الأنشطة</h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-end mb-3">
                    <a href="{{ route('admin.admins.activity', $admin) }}" class="btn btn-sm btn-primary">
                        <i class="fas fa-history me-1"></i> عرض سجل النشاط الكامل
                    </a>
                </div>
                
                <ul class="timeline">
                    <li class="timeline-item">
                        <div class="timeline-badge bg-primary">
                            <i class="fas fa-book"></i>
                        </div>
                        <div class="timeline-panel">
                            <div class="timeline-heading">
                                <h5 class="timeline-title">أنشأ دورة جديدة</h5>
                                <p class="text-muted"><small>منذ 2 ساعة</small></p>
                            </div>
                            <div class="timeline-body">
                                <p>دورة الفرقان المتقدمة في التجويد</p>
                            </div>
                        </div>
                    </li>
                    <li class="timeline-item">
                        <div class="timeline-badge bg-success">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <div class="timeline-panel">
                            <div class="timeline-heading">
                                <h5 class="timeline-title">أضاف مدير جديد</h5>
                                <p class="text-muted"><small>منذ 1 يوم</small></p>
                            </div>
                            <div class="timeline-body">
                                <p>تمت إضافة المدير أحمد محمد</p>
                            </div>
                        </div>
                    </li>
                    <li class="timeline-item">
                        <div class="timeline-badge bg-info">
                            <i class="fas fa-bell"></i>
                        </div>
                        <div class="timeline-panel">
                            <div class="timeline-heading">
                                <h5 class="timeline-title">أرسل إشعار</h5>
                                <p class="text-muted"><small>منذ 3 أيام</small></p>
                            </div>
                            <div class="timeline-body">
                                <p>إشعار ببدء التسجيل في الدورة الصيفية</p>
                            </div>
                        </div>
                    </li>
                    <li class="timeline-item">
                        <div class="timeline-badge bg-warning">
                            <i class="fas fa-mosque"></i>
                        </div>
                        <div class="timeline-panel">
                            <div class="timeline-heading">
                                <h5 class="timeline-title">قام بتحديث مسجد</h5>
                                <p class="text-muted"><small>منذ 4 أيام</small></p>
                            </div>
                            <div class="timeline-body">
                                <p>تحديث معلومات مسجد الرحمة</p>
                            </div>
                        </div>
                    </li>
                    <li class="timeline-item">
                        <div class="timeline-badge bg-danger">
                            <i class="fas fa-file-export"></i>
                        </div>
                        <div class="timeline-panel">
                            <div class="timeline-heading">
                                <h5 class="timeline-title">صدر تقرير</h5>
                                <p class="text-muted"><small>منذ 5 أيام</small></p>
                            </div>
                            <div class="timeline-body">
                                <p>تقرير الحفظ الشهري لشهر يونيو</p>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
        
        <div class="admin-card mt-4">
            <div class="card-header bg-primary text-white">
                <h5>الدورات المنشأة</h5>
            </div>
            <div class="card-body">
                @if($admin->createdCourses->count() > 0)
                <div class="table-responsive">
                    <table class="table admin-table">
                        <thead>
                            <tr>
                                <th>اسم الدورة</th>
                                <th>تاريخ البدء</th>
                                <th>عدد الطلاب</th>
                                <th>الحالة</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($admin->createdCourses as $course)
                            <tr>
                                <td>{{ $course->name }}</td>
                                <td>{{ $course->start_date->format('Y-m-d') }}</td>
                                <td>{{ $course->current_students }} / {{ $course->max_students }}</td>
                                <td>
                                    <span class="badge {{ $course->is_active ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $course->is_active ? 'نشطة' : 'منتهية' }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="alert alert-info text-center">
                    لم ينشئ هذا المدير أي دورات
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

<style>
    .timeline {
        list-style: none;
        padding: 0;
        position: relative;
    }
    
    .timeline:before {
        content: '';
        position: absolute;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #e9ecef;
        left: 25px;
        margin-left: -1.5px;
    }
    
    .timeline-item {
        margin-bottom: 20px;
        position: relative;
    }
    
    .timeline-badge {
        position: absolute;
        width: 50px;
        height: 50px;
        left: 0;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        z-index: 100;
    }
    
    .timeline-panel {
        margin-left: 70px;
        background: white;
        border-radius: 8px;
        padding: 15px;
        position: relative;
        box-shadow: 0 1px 6px rgba(0,0,0,0.1);
    }
    
    .timeline-panel:before {
        content: '';
        position: absolute;
        top: 15px;
        left: -15px;
        border-style: solid;
        border-width: 15px 15px 15px 0;
        border-color: transparent white transparent transparent;
    }
</style>