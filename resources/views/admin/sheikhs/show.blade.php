@extends('layouts.admin')

@section('title', 'معلومات الشيخ: ' . $sheikh->name)

@section('content')
<div class="content-header">
    <div class="page-title">
        <i class="fas fa-user-tie"></i>
        <h3>معلومات الشيخ: {{ $sheikh->name }}</h3>
    </div>
    <div>
        <a href="{{ route('admin.sheikhs.edit', $sheikh) }}" class="btn btn-primary me-2">
            <i class="fas fa-edit"></i> تعديل
        </a>
        <a href="{{ route('admin.sheikhs.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> العودة للقائمة
        </a>
    </div>
</div>

<div class="row">
    <!-- Sheikh Info -->
    <div class="col-md-4">
        <div class="admin-card">
            <div class="card-header bg-primary text-white">
                <h5>الملف الشخصي</h5>
            </div>
            <div class="card-body text-center">
                @if($sheikh->profile_image)
                <img src="{{ $sheikh->profile_image_url }}" 
                     alt="{{ $sheikh->name }}" 
                     class="rounded-circle mb-3" 
                     width="120" height="120">
                @else
                <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" 
                     style="width: 120px; height: 120px;">
                    <span class="fw-bold" style="font-size: 2rem;">{{ $sheikh->initials }}</span>
                </div>
                @endif
                
                <h4 class="mb-2">{{ $sheikh->name }}</h4>
                <p class="text-muted mb-1">{{ $sheikh->qiraat }}</p>
                <p class="text-muted mb-3">{{ $sheikh->national_id }}</p>
                
                <div class="d-flex justify-content-center mb-3">
                    <form action="{{ route('admin.sheikhs.toggle-status', $sheikh) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-sm {{ $sheikh->is_active ? 'btn-success' : 'btn-danger' }}">
                            {{ $sheikh->is_active ? 'نشط' : 'معطل' }}
                        </button>
                    </form>
                </div>
                
                <div class="row text-center">
                    <div class="col-6">
                        <div class="p-2 bg-light rounded">
                            <h5 class="mb-0">{{ $sheikh->teachingCourses->count() }}</h5>
                            <small class="text-muted">الدورات</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-2 bg-light rounded">
                            <h5 class="mb-0">{{ $sheikh->teachingGroups->count() }}</h5>
                            <small class="text-muted">المجموعات</small>
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
                        <span>{{ $sheikh->email }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-phone me-2 text-primary"></i>
                            الهاتف
                        </div>
                        <span>{{ $sheikh->phone }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-id-card me-2 text-primary"></i>
                            الرقم الوطني
                        </div>
                        <span>{{ $sheikh->national_id }}</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    
    <!-- Sheikh Activities -->
    <div class="col-md-8">
        <div class="admin-card">
            <div class="card-header bg-primary text-white">
                <h5>الدورات والمجموعات</h5>
            </div>
            <div class="card-body">
                @if($sheikh->teachingCourses->count() > 0)
                <h6 class="mb-3">الدورات التعليمية</h6>
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
                            @foreach($sheikh->teachingCourses as $course)
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
                    لا توجد دورات مسندة لهذا الشيخ
                </div>
                @endif
                
                @if($sheikh->teachingGroups->count() > 0)
                <hr>
                <h6 class="mb-3">المجموعات الدراسية</h6>
                <div class="table-responsive">
                    <table class="table admin-table">
                        <thead>
                            <tr>
                                <th>اسم المجموعة</th>
                                <th>المسجد</th>
                                <th>عدد الطلاب</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sheikh->teachingGroups as $group)
                            <tr>
                                <td>{{ $group->name }}</td>
                                <td>
                                    @if($group->mosque)
                                    {{ $group->mosque->name }}
                                    @else
                                    <span class="text-muted">بدون مسجد</span>
                                    @endif
                                </td>
                                <td>{{ $group->students->count() }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>
        
        <div class="admin-card mt-4">
            <div class="card-header bg-primary text-white">
                <h5>آخر أنشطة الحفظ والمراجعة</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="mb-3">سجل الحفظ</h6>
                        @if($sheikh->createdHifzLogs->count() > 0)
                        <ul class="list-group">
                            @foreach($sheikh->createdHifzLogs as $log)
                            <li class="list-group-item">
                                <div class="d-flex justify-content-between">
                                    <strong>{{ $log->student->name }}</strong>
                                    <span>{{ $log->date->format('Y-m-d') }}</span>
                                </div>
                                <div class="text-muted small">
                                    {{ $log->surah->name }}: الآيات {{ $log->start_ayah }} - {{ $log->end_ayah }}
                                </div>
                                <div class="mt-1">
                                    <span class="badge bg-info">{{ $log->evaluation }}</span>
                                </div>
                            </li>
                            @endforeach
                        </ul>
                        @else
                        <div class="alert alert-info text-center">
                            لا توجد سجلات حفظ
                        </div>
                        @endif
                    </div>
                    
                    <div class="col-md-6">
                        <h6 class="mb-3">سجل المراجعة</h6>
                        @if($sheikh->createdReviewLogs->count() > 0)
                        <ul class="list-group">
                            @foreach($sheikh->createdReviewLogs as $log)
                            <li class="list-group-item">
                                <div class="d-flex justify-content-between">
                                    <strong>{{ $log->student->name }}</strong>
                                    <span>{{ $log->date->format('Y-m-d') }}</span>
                                </div>
                                <div class="text-muted small">
                                    {{ $log->surah->name }}: الآيات {{ $log->start_ayah }} - {{ $log->end_ayah }}
                                </div>
                                <div class="mt-1">
                                    <span class="badge bg-info">{{ $log->evaluation }}</span>
                                </div>
                            </li>
                            @endforeach
                        </ul>
                        @else
                        <div class="alert alert-info text-center">
                            لا توجد سجلات مراجعة
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection