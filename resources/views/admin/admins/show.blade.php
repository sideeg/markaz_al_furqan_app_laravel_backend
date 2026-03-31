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
                    <span class="badge bg-{{ $admin->isAdmin() ? 'danger' : 'primary' }}">
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
                            <i class="fas fa-venus-mars me-2 text-primary"></i>
                            الجنس
                        </div>
                        <span>
                            @if($admin->gender)
                                <span class="badge {{ $admin->gender == 'ذكر' ? 'bg-info text-dark' : 'bg-warning text-dark' }}">
                                    {{ $admin->gender }}
                                </span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </span>
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
    @forelse($admin->activities as $activity)
        @php
            // Map actions to icons and colors
            $config = match($activity->action) {
                'create' => ['icon' => 'fa-plus', 'color' => 'bg-success'],
                'update' => ['icon' => 'fa-edit', 'color' => 'bg-info'],
                'delete' => ['icon' => 'fa-trash', 'color' => 'bg-danger'],
                'send_notification' => ['icon' => 'fa-bell', 'color' => 'bg-primary'],
                'approve_enrollment' => ['icon' => 'fa-check-circle', 'color' => 'bg-success'],
                'reject_enrollment' => ['icon' => 'fa-times-circle', 'color' => 'bg-warning'],
                'assign_sheikh' => ['icon' => 'fa-user-tie', 'color' => 'bg-secondary'],
                default => ['icon' => 'fa-tasks', 'color' => 'bg-dark'],
            };
        @endphp

        <li class="timeline-item">
            <div class="timeline-badge {{ $config['color'] }}">
                <i class="fas {{ $config['icon'] }}"></i>
            </div>
            <div class="timeline-panel">
                <div class="timeline-heading">
                    <h5 class="timeline-title">{{ $activity->action_label }}</h5>
                    <p class="text-muted">
                        <small><i class="fas fa-clock me-1"></i> {{ $activity->created_at->diffForHumans() }}</small>
                    </p>
                </div>
                <div class="timeline-body">
                    <p>{{ $activity->description }}</p>
                </div>
            </div>
        </li>
    @empty
        <div class="text-center py-4 text-muted">
            <i class="fas fa-history fa-3x mb-3 opacity-20"></i>
            <p>لا يوجد نشاط مسجل لهذا المدير حتى الآن</p>
        </div>
    @endforelse
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
        right: 25px; /* Changed from left to right for RTL */
        margin-right: -1px;
    }
    
    .timeline-item {
        margin-bottom: 30px;
        position: relative;
    }
    
    .timeline-badge {
        position: absolute;
        width: 50px;
        height: 50px;
        right: 0; /* Changed from left to right */
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        z-index: 10;
        box-shadow: 0 0 0 4px #fff;
    }
    
    .timeline-panel {
        margin-right: 70px; /* Changed from margin-left */
        margin-left: 0;
        background: #f8f9fa;
        border-radius: 8px;
        padding: 15px;
        position: relative;
    }

    .timeline-panel:before {
        content: '';
        position: absolute;
        top: 15px;
        right: -10px; /* Pointer on the right side */
        border-style: solid;
        border-width: 10px 0 10px 10px;
        border-color: transparent transparent transparent #f8f9fa;
    }

    .timeline-title {
        margin-top: 0;
        color: inherit;
        font-size: 1rem;
        font-weight: 700;
    }
</style>