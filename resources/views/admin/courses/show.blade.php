@extends('layouts.admin')

@section('title', 'تفاصيل الدورة: ' . $course->name)

@section('content')
<div class="content-header">
    <div class="page-title">
        <i class="fas fa-eye"></i>
        <h3>تفاصيل الدورة: {{ $course->name }}</h3>
    </div>
    <a href="{{ route('admin.courses.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> العودة للقائمة
    </a>
</div>

<div class="admin-card">
    <div class="card-header bg-info text-white">
        <h5>معلومات الدورة</h5>
    </div>
    
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3">
                <strong>اسم الدورة:</strong>
                <p>{{ $course->name }}</p>
            </div>

            <div class="col-md-6 mb-3">
                <strong>نوع الدورة:</strong>
                <p>
                    @if($course->type === 'online')
                        <span class="badge bg-primary">أونلاين</span>
                    @elseif($course->type === 'open')
                        <span class="badge bg-success">مفتوحة</span>
                    @else
                        <span class="badge bg-secondary">مغلقة</span>
                    @endif
                </p>
            </div>

            <div class="col-md-6 mb-3">
                <strong>المسجد:</strong>
                <p>{{ $course->mosque?->name ?? 'بدون مسجد' }}</p>
            </div>

            <div class="col-md-6 mb-3">
                <strong>التسجيل:</strong>
                <p>
                    @if($course->is_registration_open)
                        <span class="badge bg-success">مفتوح</span>
                    @else
                        <span class="badge bg-danger">مغلق</span>
                    @endif
                </p>
            </div>

            <div class="col-md-6 mb-3">
                <strong>تاريخ البدء:</strong>
                <p>{{ $course->start_date->format('Y-m-d') }}</p>
            </div>

            <div class="col-md-6 mb-3">
                <strong>تاريخ الانتهاء:</strong>
                <p>{{ $course->end_date->format('Y-m-d') }}</p>
            </div>

            <div class="col-md-6 mb-3">
                <strong>عدد الطلاب:</strong>
                <p>{{ $course->current_students }} / {{ $course->max_students }}</p>
            </div>

            <div class="col-md-6 mb-3">
                <strong>الحالة:</strong>
                <p>
                    @if($course->is_active)
                        <span class="badge bg-success">نشطة</span>
                    @else
                        <span class="badge bg-danger">معطلة</span>
                    @endif
                </p>
            </div>

            <div class="col-md-12 mb-3">
                <strong>وصف الدورة:</strong>
                <p>{{ $course->description ?? 'لا يوجد وصف' }}</p>
            </div>

            <div class="col-md-12 mb-3">
                <strong>متطلبات الدورة:</strong>
                <p>{{ $course->requirements ?? 'لا توجد' }}</p>
            </div>

            <div class="col-md-12 mb-3">
                <strong>تفاصيل الجدول الزمني:</strong>
                <p>{{ $course->schedule_details ?? 'لا توجد' }}</p>
            </div>

            @if($course->image_url)
            <div class="col-md-12 mb-3">
                <strong>صورة الدورة:</strong>
                <div>
                    <img src="{{ $course->image_url }}" class="img-fluid rounded" style="max-width: 300px" alt="صورة الدورة">
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
