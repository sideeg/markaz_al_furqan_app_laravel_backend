@extends('layouts.admin')
@section('title', 'عرض سجل المراجعة')
@section('content')
<div class="content-header mb-3">
    <h3>عرض سجل المراجعة</h3>
    <a href="{{ route('admin.review_logs.index') }}" class="btn btn-secondary">رجوع للسجلات</a>
</div>
<div class="admin-card">
    <div class="card-body">
        <div class="mb-3">
            <label class="form-label fw-bold">التاريخ:</label>
            <div>{{ $log->date }}</div>
        </div>
        <div class="mb-3">
            <label class="form-label fw-bold">الطالب:</label>
            <div>{{ $log->student?->name }}</div>
        </div>
        <div class="mb-3">
            <label class="form-label fw-bold">الشيخ:</label>
            <div>{{ $log->sheikh?->name }}</div>
        </div>
        <div class="mb-3">
            <label class="form-label fw-bold">السورة:</label>
            <div>{{ $log->surah }}</div>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">من آية:</label>
                <div>{{ $log->start_ayah }}</div>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">إلى آية:</label>
                <div>{{ $log->end_ayah }}</div>
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label fw-bold">التقييم:</label>
            <div>{{ ucfirst($log->evaluation) }}</div>
        </div>
        <div class="mb-3">
            <label class="form-label fw-bold">ملاحظات:</label>
            <div>{{ $log->notes ?: 'لا يوجد' }}</div>
        </div>
        <div class="mt-4 d-flex gap-2">
            <a href="{{ route('admin.review_logs.index') }}" class="btn btn-secondary">رجوع</a>
            <form action="{{ route('admin.review_logs.destroy', $log) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذا السجل؟')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">حذف</button>
            </form>
        </div>
    </div>
</div>
@endsection
