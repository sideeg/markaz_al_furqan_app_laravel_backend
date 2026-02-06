@extends('layouts.admin')

@section('title', 'عرض سجل الحفظ')

@section('content')
<div class="content-header mb-3">
    <h3>عرض سجل الحفظ</h3>
    <a href="{{ route('admin.hifz_logs.index') }}" class="btn btn-secondary">رجوع للسجلات</a>
</div>

<div class="admin-card">
    <div class="card-body">
        <div class="mb-3">
            <label class="form-label fw-bold">التاريخ:</label>
            <div>{{ $hifzLog->date }}</div>
        </div>
        <div class="mb-3">
            <label class="form-label fw-bold">الطالب:</label>
            <div>{{ $hifzLog->student?->name }}</div>
        </div>
        <div class="mb-3">
            <label class="form-label fw-bold">الشيخ:</label>
            <div>{{ $hifzLog->sheikh?->name }}</div>
        </div>
        <div class="mb-3">
            <label class="form-label fw-bold">الدورة:</label>
            <div>{{ $hifzLog->course?->name }}</div>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">من:</label>
                <div>{{ $hifzLog->start_surah }} : {{ $hifzLog->start_ayah }}</div>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">إلى:</label>
                <div>{{ $hifzLog->end_surah }} : {{ $hifzLog->end_ayah }}</div>
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label fw-bold">التقييم:</label>
            <div>{{ ucfirst($hifzLog->evaluation) }}</div>
        </div>
        <div class="mb-3">
            <label class="form-label fw-bold">ملاحظات:</label>
            <div>{{ $hifzLog->comment ?: 'لا يوجد' }}</div>
        </div>

        <div class="mt-4 d-flex gap-2">
            <a href="{{ route('admin.hifz_logs.index') }}" class="btn btn-secondary">رجوع</a>
            <form action="{{ route('admin.hifz_logs.destroy', $hifzLog) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذا السجل؟')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">حذف</button>
            </form>
        </div>
    </div>
</div>
@endsection
