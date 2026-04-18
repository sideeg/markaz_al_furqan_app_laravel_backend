@extends('layouts.admin')

@section('title', 'معلومات الطالب: ' . $student->name)

@section('content')
<div class="content-header">
    <div class="page-title">
        <i class="fas fa-user-graduate"></i>
        <h3>معلومات الطالب: {{ $student->name }}</h3>
    </div>
    <div>
        <a href="{{ route('admin.students.edit', $student) }}" class="btn btn-primary me-2">
            <i class="fas fa-edit"></i> تعديل
        </a>
        <a href="{{ route('admin.students.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> العودة للقائمة
        </a>
    </div>
</div>

<div class="row">
    <!-- Student Profile -->
    <div class="col-md-4">
        <div class="admin-card">
            <div class="card-header bg-info text-white">
                <h5>الملف الشخصي</h5>
            </div>
            <div class="card-body text-center">
                @if($student->profile_image)
                <img src="{{ $student->profile_image_url }}" alt="{{ $student->name }}" class="rounded-circle mb-3 border" width="120" height="120" style="object-fit: cover;">
                @else
                <div class="bg-light text-info rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3 border" style="width: 120px; height: 120px;">
                    <span class="fw-bold" style="font-size: 2.5rem;">{{ $student->initials }}</span>
                </div>
                @endif
                
                <h4 class="mb-2">{{ $student->name }}</h4>
                <div class="d-flex justify-content-center mb-3">
                    <span class="badge bg-info text-dark">طالب</span>
                </div>
                
                <form action="{{ route('admin.students.toggle-status', $student) }}" method="POST" class="mb-4">
                    @csrf @method('PATCH')
                    <button type="submit" class="btn btn-sm {{ $student->is_active ? 'btn-success' : 'btn-danger' }}">
                        {{ $student->is_active ? 'حساب نشط' : 'حساب معطل' }}
                    </button>
                </form>
            </div>
        </div>
        
        <div class="admin-card mt-4">
            <div class="card-header bg-info text-white">
                <h5>المعلومات الشخصية والاتصال</h5>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div><i class="fas fa-envelope me-2 text-info"></i>البريد</div>
                        <span dir="ltr">{{ $student->email }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div><i class="fas fa-phone me-2 text-info"></i>الهاتف</div>
                        <span dir="ltr">{{ $student->phone ?? '—' }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div><i class="fas fa-id-card me-2 text-info"></i>الهوية</div>
                        <span>{{ $student->national_id ?? '—' }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div><i class="fas fa-flag me-2 text-info"></i>الجنسية</div>
                        <span>
                            @if($student->nationality)
                                <span class="badge bg-light text-dark border">{{ $student->nationality }}</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div><i class="fas fa-quran me-2 text-info"></i>القراءة</div>
                        <span class="text-info fw-bold">{{ $student->qiraat ?? '—' }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div><i class="fas fa-venus-mars me-2 text-info"></i>الجنس</div>
                        <span>
                            @if($student->gender)
                                <span class="badge {{ $student->gender == 'ذكر' ? 'bg-primary text-white' : 'bg-warning text-dark' }}">{{ $student->gender }}</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Student Data (Right Col) -->
    <div class="col-md-8">
        <div class="admin-card">
            <div class="card-header bg-primary text-white">
                <h5>الدورات المسجل بها</h5>
            </div>
            <div class="card-body p-0">
                @if($student->enrolledCourses->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0 text-center">
                        <thead class="table-light">
                            <tr>
                                <th>اسم الدورة</th>
                                <th>حالة القبول</th>
                                <th>تاريخ التسجيل</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($student->enrolledCourses as $course)
                            <tr>
                                <td class="fw-bold">{{ $course->name }}</td>
                                <td>
                                    <span class="badge {{ $course->pivot->status == 'approved' ? 'bg-success' : 'bg-warning' }}">
                                        {{ $course->pivot->status == 'approved' ? 'مقبول' : 'قيد الانتظار' }}
                                    </span>
                                </td>
                                <!-- Date fix here as well to prevent null errors -->
                                <td>{{ optional($course->pivot->enrolled_at)->format('Y-m-d') ?? '—' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-4 text-muted">
                    <i class="fas fa-book-open fa-2x mb-2 opacity-25"></i>
                    <p class="mb-0">الطالب غير مسجل في أي دورة حالياً</p>
                </div>
                @endif
            </div>
        </div>

        <div class="row mt-4">
            <!-- Hifz Logs -->
            <div class="col-md-6">
                <div class="admin-card h-100">
                    <div class="card-header bg-success text-white">
                        <h5>سجل الحفظ</h5>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            @forelse ($student->hifzLogs->take(5) as $log)
                            <li class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <!-- Date fix here -->
                                    <strong class="text-success">{{ optional($log->date)->format('Y-m-d') ?? 'بدون تاريخ' }}</strong>
                                    <span class="badge bg-light text-dark border">{{ $log->evaluation ?? '—' }}</span>
                                </div>
                                <div class="small text-muted">
                                    سورة {{ $log->from_surah ?? '—' }} <br> من آية {{ $log->start_ayah ?? '-' }} إلى {{ $log->end_ayah ?? '-' }}
                                </div>
                            </li>
                            @empty
                            <li class="list-group-item text-center text-muted py-3">لم يتم تسجيل أي حفظ للطالب</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Review Logs -->
            <div class="col-md-6">
                <div class="admin-card h-100">
                    <div class="card-header bg-secondary text-white">
                        <h5>سجل المراجعة</h5>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            @forelse ($student->reviewLogs->take(5) as $log)
                            <li class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <!-- Date fix here -->
                                    <strong class="text-secondary">{{ optional($log->date)->format('Y-m-d') ?? 'بدون تاريخ' }}</strong>
                                    <span class="badge bg-light text-dark border">{{ $log->evaluation ?? '—' }}</span>
                                </div>
                                <div class="small text-muted">
                                    من سورة {{ $log->from_surah ?? '—' }} <br> إلى سورة {{ $log->to_surah ?? '—' }}
                                </div>
                            </li>
                            @empty
                            <li class="list-group-item text-center text-muted py-3">لم يتم تسجيل أي مراجعة للطالب</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection