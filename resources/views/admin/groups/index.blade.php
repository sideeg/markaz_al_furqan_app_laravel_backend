@extends('layouts.admin')

@section('title', 'مجموعات الدورة: ' . $course->name)

@section('content')
<div class="content-header">
    <div class="page-title">
        <i class="fas fa-users"></i>
        <h3>مجموعات الدورة: {{ $course->name }}</h3>
    </div>
    <div>
        <a href="{{ route('admin.courses.groups.create', $course) }}" class="btn btn-primary me-2">
            <i class="fas fa-plus"></i> إنشاء مجموعة جديدة
        </a>
        <a href="{{ route('admin.courses.show', $course) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> العودة للدورة
        </a>
    </div>
</div>

<div class="row">
    <!-- Groups List -->
    <div class="col-md-8">
        <div class="admin-card">
            <div class="card-header bg-primary text-white">
                <h5>قائمة المجموعات</h5>
            </div>
            <div class="card-body">
                @if($groups->count() > 0)
                <div class="row">
                    @foreach($groups as $group)
                    <div class="col-md-6 mb-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">{{ $group->name }}</h5>
                                <span class="badge bg-info">{{ $group->sheikh->name ?? 'غير محدد' }}</span>
                                <form action="{{ route('admin.courses.groups.toggle-status', [$course, $group]) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm {{ $group->is_active ? 'btn-success' : 'btn-danger' }}">
                                        {{ $group->is_active ? 'نشطة' : 'معطلة' }}
                                    </button>
                                </form>
                            </div>
                            <div class="card-body">
                                <p class="text-muted">{{ $group->description }}</p>
                                <div class="d-flex justify-content-between mb-3">
                                    <div>
                                        <i class="fas fa-users me-1"></i>
                                        <span>{{ $group->students_count }} / {{ $group->max_students }}</span>
                                    </div>
                                    <div>
                                        <i class="fas fa-user me-1"></i>
                                        <span>{{ $group->creator->name }}</span>
                                    </div>
                                </div>
                                <div class="progress mb-3" style="height: 8px;">
                                    <div class="progress-bar bg-success" 
                                         role="progressbar" 
                                         style="width: {{ ($group->students_count / $group->max_students) * 100 }}%">
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('admin.courses.groups.show', [$course, $group]) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye"></i> عرض التفاصيل
                                    </a>
                                    <div>
                                        <a href="{{ route('admin.courses.groups.edit', [$course, $group]) }}" class="btn btn-sm btn-outline-secondary me-1">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.courses.groups.destroy', [$course, $group]) }}" method="POST" style="display:inline-block;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                    onclick="return confirm('هل أنت متأكد من حذف هذه المجموعة؟')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-5">
                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                    <p class="text-muted">لا توجد مجموعات لهذه الدورة</p>
                    <a href="{{ route('admin.courses.groups.create', $course) }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> إنشاء أول مجموعة
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Unassigned Students -->
    <div class="col-md-4">
        <div class="admin-card">
            <div class="card-header bg-primary text-white">
                <h5>الطلاب غير المعينين</h5>
                <span class="badge bg-warning">{{ $unassignedStudents->count() }}</span>
            </div>
            <div class="card-body">
                @if($unassignedStudents->count() > 0)
                <div class="list-group">
                    @foreach($unassignedStudents as $student)
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <strong>{{ $student->name }}</strong>
                            <div class="text-muted small">{{ $student->national_id }}</div>
                        </div>
                        <div>
                            <a href="{{ route('admin.students.show', $student) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-user"></i> الملف
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-4">
                    <i class="fas fa-check-circle fa-2x text-success mb-3"></i>
                    <p>جميع الطلاب معينون في مجموعات</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection