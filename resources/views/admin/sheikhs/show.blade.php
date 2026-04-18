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
    <!-- Sheikh Profile -->
    <div class="col-md-4">
        <div class="admin-card">
            <div class="card-header bg-success text-white">
                <h5>الملف الشخصي</h5>
            </div>
            <div class="card-body text-center">
                @if($sheikh->profile_image)
                <img src="{{ $sheikh->profile_image_url }}" alt="{{ $sheikh->name }}" class="rounded-circle mb-3 border" width="120" height="120" style="object-fit: cover;">
                @else
                <div class="bg-light text-success rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3 border" style="width: 120px; height: 120px;">
                    <span class="fw-bold" style="font-size: 2.5rem;">{{ $sheikh->initials }}</span>
                </div>
                @endif
                
                <h4 class="mb-2">{{ $sheikh->name }}</h4>
                <div class="d-flex justify-content-center mb-3">
                    <span class="badge bg-success">شيخ / معلم</span>
                </div>
                
                <form action="{{ route('admin.sheikhs.toggle-status', $sheikh) }}" method="POST" class="mb-4">
                    @csrf @method('PATCH')
                    <button type="submit" class="btn btn-sm {{ $sheikh->is_active ? 'btn-success' : 'btn-danger' }}">
                        {{ $sheikh->is_active ? 'حساب نشط' : 'حساب معطل' }}
                    </button>
                </form>
            </div>
        </div>
        
        <div class="admin-card mt-4">
            <div class="card-header bg-success text-white">
                <h5>المعلومات الشخصية والاتصال</h5>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div><i class="fas fa-envelope me-2 text-success"></i>البريد</div>
                        <span dir="ltr">{{ $sheikh->email }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div><i class="fas fa-phone me-2 text-success"></i>الهاتف</div>
                        <span dir="ltr">{{ $sheikh->phone ?? '—' }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div><i class="fas fa-id-card me-2 text-success"></i>الهوية</div>
                        <span>{{ $sheikh->national_id ?? '—' }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div><i class="fas fa-flag me-2 text-success"></i>الجنسية</div>
                        <span>
                            @if($sheikh->nationality)
                                <span class="badge bg-light text-dark border">{{ $sheikh->nationality }}</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div><i class="fas fa-quran me-2 text-success"></i>القراءة</div>
                        <span class="text-success fw-bold">{{ $sheikh->qiraat ?? '—' }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div><i class="fas fa-venus-mars me-2 text-success"></i>الجنس</div>
                        <span>
                            @if($sheikh->gender)
                                <span class="badge {{ $sheikh->gender == 'ذكر' ? 'bg-info text-dark' : 'bg-warning text-dark' }}">{{ $sheikh->gender }}</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Sheikh Data (Right Col) -->
    <div class="col-md-8">
        <div class="admin-card">
            <div class="card-header bg-success text-white">
                <h5>الدورات التي يُدرّسها</h5>
            </div>
            <div class="card-body p-0">
                @if($sheikh->teachingCourses->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0 text-center">
                        <thead class="table-light">
                            <tr>
                                <th>اسم الدورة</th>
                                <th>النوع</th>
                                <th>تاريخ البدء</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($sheikh->teachingCourses as $course)
                            <tr>
                                <td class="fw-bold">{{ $course->name }}</td>
                                <td><span class="badge bg-light text-dark border">{{ $course->type_display_name }}</span></td>
                                <td>{{ optional($course->start_date)->format('Y-m-d') ?? '—' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-4 text-muted">
                    <i class="fas fa-book fa-2x mb-2 opacity-25"></i>
                    <p class="mb-0">لا توجد دورات مسندة لهذا الشيخ</p>
                </div>
                @endif
            </div>
        </div>

        <div class="row mt-4">
            <!-- Hifz Logs -->
            <div class="col-md-6">
                <div class="admin-card h-100">
                    <div class="card-header bg-info text-white">
                        <h5>أحدث سجلات الحفظ</h5>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            @forelse ($sheikh->createdHifzLogs as $log)
                            <li class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <strong class="text-primary">{{ $log->student->name ?? 'طالب محذوف' }}</strong>
                                    <!-- Here is the Date fix -->
                                    <small class="text-muted">{{ optional($log->date)->format('Y-m-d') ?? 'بدون تاريخ' }}</small>
                                </div>
                                <div class="small">
                                    سورة {{ $log->from_surah ?? '—' }} ({{ $log->start_ayah ?? '-' }} إلى {{ $log->end_ayah ?? '-' }})
                                </div>
                                <div class="mt-1">
                                    <span class="badge bg-success">{{ $log->evaluation ?? 'تم التسميع' }}</span>
                                </div>
                            </li>
                            @empty
                            <li class="list-group-item text-center text-muted py-3">لا توجد سجلات حفظ</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Review Logs -->
            <div class="col-md-6">
                <div class="admin-card h-100">
                    <div class="card-header bg-secondary text-white">
                        <h5>أحدث سجلات المراجعة</h5>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            @forelse ($sheikh->createdReviewLogs as $log)
                            <li class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <strong class="text-primary">{{ $log->student->name ?? 'طالب محذوف' }}</strong>
                                    <!-- Here is the Date fix -->
                                    <small class="text-muted">{{ optional($log->date)->format('Y-m-d') ?? 'بدون تاريخ' }}</small>
                                </div>
                                <div class="small">
                                    من سورة {{ $log->from_surah ?? '—' }} إلى {{ $log->to_surah ?? '—' }}
                                </div>
                                <div class="mt-1">
                                    <span class="badge bg-secondary">{{ $log->evaluation ?? 'تمت المراجعة' }}</span>
                                </div>
                            </li>
                            @empty
                            <li class="list-group-item text-center text-muted py-3">لا توجد سجلات مراجعة</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection