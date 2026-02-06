@extends('layouts.admin')

@section('title', 'مجموعة ' . $group->name . ' - ' . $course->name)

@section('content')
<div class="content-header">
    <div class="page-title">
        <i class="fas fa-users"></i>
        <h3>مجموعة {{ $group->name }} - {{ $course->name }}</h3>
    </div>
    <div>
        <a href="{{ route('admin.courses.groups.edit', [$course, $group]) }}" class="btn btn-primary me-2">
            <i class="fas fa-edit"></i> تعديل
        </a>
        <a href="{{ route('admin.courses.groups.index', $course) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> العودة للمجموعات
        </a>
    </div>
</div>

<div class="row">
    <!-- Group Details -->
    <div class="col-md-4">
        <div class="admin-card">
            <div class="card-header bg-primary text-white">
                <h5>معلومات المجموعة</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">الاسم</label>
                    <p>{{ $group->name }}</p>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">الوصف</label>
                    <p>{{ $group->description ?? 'لا يوجد وصف' }}</p>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">الجدول الزمني</label>
                    <p>{{ $group->schedule_details }}</p>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">الشيخ المسؤول</label>
                    <p>{{ $group->sheikh->name ?? 'غير محدد' }}</p>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">عدد الطلاب</label>
                        <p>{{ $group->students->count() }} / {{ $group->max_students }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">الحالة</label>
                        <form action="{{ route('admin.courses.groups.toggle-status', [$course, $group]) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-sm {{ $group->is_active ? 'btn-success' : 'btn-danger' }}">
                                {{ $group->is_active ? 'نشطة' : 'معطلة' }}
                            </button>
                        </form>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">تم الإنشاء بواسطة</label>
                    <p>{{ $group->creator->name }}</p>
                </div>
            </div>
        </div>
        
        <div class="admin-card mt-4">
            <div class="card-header bg-primary text-white">
                <h5>إضافة طلاب</h5>
            </div>
            <div class="card-body">
                @if($availableStudents->count() > 0)
                <form method="POST" action="{{ route('admin.courses.groups.add-student', [$course, $group]) }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-bold">اختر طالبًا</label>
                        <select class="form-select" name="student_id" required>
                            @foreach($availableStudents as $student)
                                <option value="{{ $student->id }}">
                                    {{ $student->name }} ({{ $student->national_id }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-user-plus me-1"></i> إضافة إلى المجموعة
                    </button>
                </form>
                @else
                <div class="alert alert-info text-center py-4">
                    <i class="fas fa-check-circle fa-2x mb-3"></i>
                    <p>جميع الطلاب معينون في مجموعات</p>
                </div>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Group Students -->
    <div class="col-md-8">
        <div class="admin-card">
            <div class="card-header bg-primary text-white">
                <h5>طلاب المجموعة</h5>
                <span class="badge bg-info">{{ $group->students->count() }}</span>
            </div>
            <div class="card-body">
                @if($group->students->count() > 0)
                <div class="table-responsive">
                    <table class="table admin-table">
                        <thead>
                            <tr>
                                <th>اسم الطالب</th>
                                <th>الرقم الوطني</th>
                                <th>تاريخ الإضافة</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($group->students as $student)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($student->profile_image)
                                        <img src="{{ $student->profile_image_url }}" 
                                             alt="{{ $student->name }}" 
                                             class="rounded-circle me-3" 
                                             width="40" height="40">
                                        @else
                                        <div class="bg-light rounded-circle d-flex align-items-center justify-content-center me-3" 
                                             style="width: 40px; height: 40px;">
                                            <span class="fw-bold">{{ $student->initials }}</span>
                                        </div>
                                        @endif
                                        <div>
                                            <strong>{{ $student->name }}</strong>
                                            <div class="text-muted small">{{ $student->phone }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $student->national_id }}</td>
                                <td>{{ $student->pivot->assigned_at }}</td>
                                <td>
                                    <a href="{{ route('admin.students.show', $student) }}" class="btn btn-sm btn-info" title="الملف الشخصي">
                                        <i class="fas fa-user"></i>
                                    </a>
                                    <form action="{{ route('admin.courses.groups.remove-student', [$course, $group, $student]) }}" method="POST" style="display:inline-block;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" 
                                                title="إزالة من المجموعة"
                                                onclick="return confirm('هل أنت متأكد من إزالة هذا الطالب؟')">
                                            <i class="fas fa-user-minus"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-5">
                    <i class="fas fa-users-slash fa-3x text-muted mb-3"></i>
                    <p class="text-muted">لا يوجد طلاب في هذه المجموعة</p>
                    <p class="text-muted">استخدم القائمة الجانبية لإضافة طلاب</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection