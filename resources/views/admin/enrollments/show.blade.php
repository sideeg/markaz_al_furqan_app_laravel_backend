@extends('layouts.admin')

@section('title', 'تفاصيل طلب التسجيل')

@section('content')
<div class="content-header">
    <div class="page-title">
        <i class="fas fa-file-alt"></i>
        <h3>تفاصيل طلب التسجيل</h3>
    </div>
    <a href="{{ route('admin.enrollments.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> العودة للقائمة
    </a>
</div>

<div class="row">
    <!-- Enrollment Details -->
    <div class="col-md-5">
        <div class="admin-card">
            <div class="card-header bg-primary text-white">
                <h5>معلومات الطلب</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">تاريخ الطلب</label>
                            <p>{{ $enrollment->enrolled_at->format('Y-m-d H:i') }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">الحالة</label>
                            <p>
                                @if($enrollment->status === 'pending')
                                <span class="badge bg-warning">قيد الانتظار</span>
                                @elseif($enrollment->status === 'approved')
                                <span class="badge bg-success">مقبول</span>
                                @else
                                <span class="badge bg-danger">مرفوض</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">الدورة</label>
                    <div class="d-flex align-items-center bg-light p-3 rounded">
                        @if($enrollment->course->image_path)
                        <img src="{{ Storage::url($enrollment->course->image_path) }}" 
                             alt="{{ $enrollment->course->name }}" 
                             class="rounded me-3" 
                             width="60" height="60">
                        @else
                        <div class="bg-light rounded d-flex align-items-center justify-content-center me-3" 
                             style="width: 60px; height: 60px;">
                            <i class="fas fa-book text-muted"></i>
                        </div>
                        @endif
                        <div>
                            <h5 class="mb-0">{{ $enrollment->course->name }}</h5>
                            <p class="mb-0 text-muted">
                                {{ $enrollment->course->type === 'online' ? 'أونلاين' : 'حضوري' }}
                                | {{ $enrollment->course->start_date->format('Y-m-d') }}
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">الطالب</label>
                    <div class="d-flex align-items-center bg-light p-3 rounded">
                        @if($enrollment->student->profile_image)
                        <img src="{{ $enrollment->student->profile_image_url }}" 
                             alt="{{ $enrollment->student->name }}" 
                             class="rounded-circle me-3" 
                             width="60" height="60">
                        @else
                        <div class="bg-light rounded-circle d-flex align-items-center justify-content-center me-3" 
                             style="width: 60px; height: 60px;">
                            <span class="fw-bold" style="font-size: 1.5rem;">{{ $enrollment->student->initials }}</span>
                        </div>
                        @endif
                        <div>
                            <h5 class="mb-0">{{ $enrollment->student->name }}</h5>
                            <p class="mb-0 text-muted">
                                {{ $enrollment->student->phone }} | {{ $enrollment->student->email }}
                            </p>
                        </div>
                    </div>
                </div>
                
                @if($enrollment->status === 'rejected' || $enrollment->notes)
                <div class="mb-3">
                    <label class="form-label fw-bold">ملاحظات</label>
                    <div class="bg-light p-3 rounded">
                        {{ $enrollment->notes ?? 'لا توجد ملاحظات' }}
                    </div>
                </div>
                @endif
                
                @if($enrollment->status === 'pending')
                <div class="d-flex justify-content-between mt-4">
                    <form method="POST" action="{{ route('admin.enrollments.approve', $enrollment) }}">
                        @csrf
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check me-1"></i> قبول الطلب
                        </button>
                    </form>
                    <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectionModal">
                        <i class="fas fa-times me-1"></i> رفض الطلب
                    </button>
                </div>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Student History -->
    <div class="col-md-7">
        <div class="admin-card">
            <div class="card-header bg-primary text-white">
                <h5>سجل الطالب</h5>
            </div>
            <div class="card-body">
                <ul class="nav nav-tabs" id="studentHistoryTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="courses-tab" data-bs-toggle="tab" data-bs-target="#courses" type="button" role="tab">
                            الدورات السابقة
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="hifz-tab" data-bs-toggle="tab" data-bs-target="#hifz" type="button" role="tab">
                            سجل الحفظ
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="review-tab" data-bs-toggle="tab" data-bs-target="#review" type="button" role="tab">
                            سجل المراجعة
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="evaluation-tab" data-bs-toggle="tab" data-bs-target="#evaluation" type="button" role="tab">
                            التقييم العام
                        </button>
                    </li>
                </ul>
                
                <div class="tab-content mt-3" id="studentHistoryContent">
                    <!-- Courses Tab -->
                    <div class="tab-pane fade show active" id="courses" role="tabpanel">
                        @if($enrollment->student->enrolledCourses->count() > 0)
                        <div class="table-responsive">
                            <table class="table admin-table">
                                <thead>
                                    <tr>
                                        <th>اسم الدورة</th>
                                        <th>تاريخ البدء</th>
                                        <th>تاريخ الانتهاء</th>
                                        <th>الحالة</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($enrollment->student->enrolledCourses as $course)
                                    <tr>
                                        <td>{{ $course->name }}</td>
                                        <td>{{ $course->start_date->format('Y-m-d') }}</td>
                                        <td>{{ $course->end_date->format('Y-m-d') }}</td>
                                        <td>
                                            @if($course->pivot->status === 'approved')
                                            <span class="badge bg-success">مكتملة</span>
                                            @else
                                            <span class="badge bg-warning">قيد الدراسة</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <div class="alert alert-info text-center py-4">
                            <i class="fas fa-book fa-2x mb-3"></i>
                            <p>لا توجد دورات سابقة لهذا الطالب</p>
                        </div>
                        @endif
                    </div>
                    
                    <!-- Hifz Tab -->
                    <div class="tab-pane fade" id="hifz" role="tabpanel">
                        @if($enrollment->student->hifzLogs->count() > 0)
                        <div class="table-responsive">
                            <table class="table admin-table">
                                <thead>
                                    <tr>
                                        <th>التاريخ</th>
                                        <th>السورة</th>
                                        <th>الآيات</th>
                                        <th>التقييم</th>
                                        <th>الشيخ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($enrollment->student->hifzLogs as $log)
                                    <tr>
                                        
                                        <td>{{ $log->date }}</td>
                                        <td>{{ $log->start_sura }}</td>
                                        <td>{{ $log->start_ayah }} - {{ $log->end_ayah }}</td>
                                        <td>
                                            <span class="badge bg-info">{{ $log->evaluation }}</span>
                                        </td>
                                        <td>{{ $log->sheikh->name ?? 'غير معروف' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <div class="alert alert-info text-center py-4">
                            <i class="fas fa-quran fa-2x mb-3"></i>
                            <p>لا توجد سجلات حفظ لهذا الطالب</p>
                        </div>
                        @endif
                    </div>
                    
                    <!-- Review Tab -->
                    <div class="tab-pane fade" id="review" role="tabpanel">
                        @if($enrollment->student->reviewLogs->count() > 0)
                        <div class="table-responsive">
                            <table class="table admin-table">
                                <thead>
                                    <tr>
                                        <th>التاريخ</th>
                                        <th>السورة</th>
                                        <th>الآيات</th>
                                        <th>التقييم</th>
                                        <th>الشيخ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($enrollment->student->reviewLogs as $log)
                                    <tr>
                                        <td>{{ $log->date }}</td>
                                        <td>{{ $log->start_sura }}</td>
                                        <td>{{ $log->start_ayah }} - {{ $log->end_ayah }}</td>
                                        <td>
                                            <span class="badge bg-info">{{ $log->evaluation }}</span>
                                        </td>
                                        <td>{{ $log->sheikh->name ?? 'غير معروف' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <div class="alert alert-info text-center py-4">
                            <i class="fas fa-sync-alt fa-2x mb-3"></i>
                            <p>لا توجد سجلات مراجعة لهذا الطالب</p>
                        </div>
                        @endif
                    </div>
                    
                    <!-- Evaluation Tab -->
                    <div class="tab-pane fade" id="evaluation" role="tabpanel">
                        <div class="row text-center mb-4">
                            <div class="col-md-4">
                                <div class="bg-light p-3 rounded">
                                    <h3 class="text-primary">{{ $enrollment->student->total_memorized_ayahs }}</h3>
                                    <p class="mb-0">عدد الآيات المحفوظة</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="bg-light p-3 rounded">
                                    <h3 class="text-primary">{{ $enrollment->student->enrolledCourses->count() }}</h3>
                                    <p class="mb-0">عدد الدورات المسجلة</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="bg-light p-3 rounded">
                                    <h3 class="text-primary">{{ number_format($enrollment->student->average_evaluation, 1) }}/5</h3>
                                    <p class="mb-0">متوسط التقييم</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <h5>تقييم الأداء</h5>
                            <div class="progress" style="height: 25px;">
                                <div class="progress-bar bg-success" 
                                     role="progressbar" 
                                     style="width: {{ $enrollment->student->average_evaluation * 20 }}%">
                                    {{ number_format($enrollment->student->average_evaluation, 1) }}
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <h5>آخر تقييمات الشيوخ</h5>
                            @if($enrollment->student->hifzLogs->count() > 0 || $enrollment->student->reviewLogs->count() > 0)
                            <ul class="list-group">
                                @foreach($enrollment->student->hifzLogs->take(3) as $log)
                                <li class="list-group-item">
                                    <div class="d-flex justify-content-between">
                                        <strong>الحفظ: {{ $log->start_sura }}</strong>
                                        <span>{{ $log->date }}</span>
                                    </div>
                                    <div class="text-muted small">
                                        الشيخ: {{ $log->sheikh->name ?? 'غير معروف' }}
                                    </div>
                                    <div class="mt-1">
                                        <span class="badge bg-info">{{ $log->evaluation }}</span>
                                        <small class="text-muted">{{ $log->notes }}</small>
                                    </div>
                                </li>
                                @endforeach
                                @foreach($enrollment->student->reviewLogs->take(3) as $log)
                                <li class="list-group-item">
                                    <div class="d-flex justify-content-between">
                                        <strong>المراجعة: {{ $log->start_sura }}</strong>
                                        <span>{{ $log->date }}</span>
                                    </div>
                                    <div class="text-muted small">
                                        الشيخ: {{ $log->sheikh->name ?? 'غير معروف' }}
                                    </div>
                                    <div class="mt-1">
                                        <span class="badge bg-info">{{ $log->evaluation }}</span>
                                        <small class="text-muted">{{ $log->notes }}</small>
                                    </div>
                                </li>
                                @endforeach
                            </ul>
                            @else
                            <div class="alert alert-info text-center">
                                لا توجد تقييمات متاحة
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Rejection Modal -->
<div class="modal fade" id="rejectionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">رفض طلب التسجيل</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('admin.enrollments.reject', $enrollment) }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">سبب الرفض <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="rejection_reason" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-danger">رفض الطلب</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    .nav-tabs .nav-link {
        color: #495057;
        font-weight: 600;
        border: none;
        border-bottom: 3px solid transparent;
        padding: 10px 15px;
    }
    
    .nav-tabs .nav-link.active {
        color: var(--primary);
        background: none;
        border-bottom: 3px solid var(--primary);
    }
    
    .progress-bar {
        font-weight: 600;
        font-size: 1rem;
    }
</style>
@endsection