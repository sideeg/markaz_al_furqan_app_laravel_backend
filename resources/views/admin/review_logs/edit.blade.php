{{-- FIXED: show.blade.php for Review Logs --}}
{{-- Issues Fixed:
    1. Changed $log->date to $log->session_date ✅
    2. Changed $log->surah to $log->start_surah & $log->end_surah ✅
    3. Changed $log->comment to $log->notes ✅
    4. Added edit button (for requirement #2: change session date) ✅
    5. Better formatting with translations ✅
--}}

@extends('layouts.admin')
@section('title', 'عرض سجل المراجعة')
@section('content')
<div class="content-header mb-3">
    <div class="d-flex justify-content-between align-items-center">
        <h3>عرض سجل المراجعة</h3>
        <div>
            {{-- Edit button (allows changing session date) ✅ --}}
            <a href="{{ route('admin.review_logs.edit', $log) }}" class="btn btn-warning me-2">
                <i class="fas fa-edit"></i> تعديل
            </a>
            <a href="{{ route('admin.review_logs.index') }}" class="btn btn-secondary">رجوع للسجلات</a>
        </div>
    </div>
</div>

<div class="admin-card">
    <div class="card-body">
        
        {{-- Session Date --}}
        <div class="mb-4 pb-3 border-bottom">
            <label class="form-label fw-bold">📅 التاريخ:</label>
            <div class="fs-5">
                {{ $log->session_date?->format('Y-m-d') ?? 'غير محدد' }}
                @if($log->session_time)
                    <span class="text-muted">@ {{ $log->session_time }}</span>
                @endif
            </div>
        </div>

        {{-- Student --}}
        <div class="mb-4 pb-3 border-bottom">
            <label class="form-label fw-bold">👨‍🎓 الطالب:</label>
            <div class="fs-5">
                {{ $log->student?->name ?? 'محذوف من النظام' }}
                @if($log->student)
                    <small class="text-muted d-block">({{ $log->student->email }})</small>
                @endif
            </div>
        </div>

        {{-- Sheikh --}}
        <div class="mb-4 pb-3 border-bottom">
            <label class="form-label fw-bold">🧔 الشيخ:</label>
            <div class="fs-5">
                {{ $log->sheikh?->name ?? 'محذوف من النظام' }}
                @if($log->sheikh)
                    <small class="text-muted d-block">({{ $log->sheikh->email }})</small>
                @endif
            </div>
        </div>

        {{-- Course --}}
        <div class="mb-4 pb-3 border-bottom">
            <label class="form-label fw-bold">📚 الدورة:</label>
            <div class="fs-5">
                {{ $log->course?->name ?? 'محذوفة من النظام' }}
            </div>
        </div>

        {{-- Group (if applicable) --}}
        @if($log->group)
        <div class="mb-4 pb-3 border-bottom">
            <label class="form-label fw-bold">👥 المجموعة:</label>
            <div class="fs-5">
                {{ $log->group->name }}
            </div>
        </div>
        @endif

        {{-- Surahs (Changed from $log->surah to start_surah & end_surah) ✅ --}}
        <div class="mb-4 pb-3 border-bottom">
            <label class="form-label fw-bold">📖 السور:</label>
            <div class="fs-5">
                من: <strong>{{ $log->start_surah }}</strong>
                @if($log->start_surah !== $log->end_surah)
                    إلى: <strong>{{ $log->end_surah }}</strong>
                @endif
            </div>
        </div>

        {{-- Ayahs --}}
        <div class="row mb-4 pb-3 border-bottom">
            <div class="col-md-6">
                <label class="form-label fw-bold">آية البداية:</label>
                <div class="fs-5">
                    <badge class="badge bg-primary">{{ $log->start_ayah }}</badge>
                </div>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-bold">آية النهاية:</label>
                <div class="fs-5">
                    <badge class="badge bg-primary">{{ $log->end_ayah }}</badge>
                </div>
            </div>
        </div>

        {{-- Evaluation --}}
        <div class="mb-4 pb-3 border-bottom">
            <label class="form-label fw-bold">⭐ التقييم:</label>
            <div class="fs-5">
                <span class="badge bg-info fs-6">
                    @switch($log->evaluation)
                        @case('excellent')
                            ممتاز 🌟
                            @break
                        @case('very_good')
                            جيد جداً 👍
                            @break
                        @case('good')
                            جيد ✓
                            @break
                        @case('needs_improvement')
                            يحتاج تحسين 📈
                            @break
                        @case('poor')
                            ضعيف ⚠️
                            @break
                        @default
                            غير محدد
                    @endswitch
                </span>
            </div>
        </div>

        {{-- Notes (Changed from $log->comment to $log->notes) ✅ --}}
        <div class="mb-4">
            <label class="form-label fw-bold">📝 الملاحظات:</label>
            <div class="p-3 bg-light rounded">
                {{ $log->notes ?: '- لا توجد ملاحظات -' }}
            </div>
        </div>

        {{-- Actions --}}
        <div class="mt-5 d-flex gap-2 justify-content-end">
            <a href="{{ route('admin.review_logs.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> رجوع
            </a>

            {{-- Edit button --}}
            <a href="{{ route('admin.review_logs.edit', $log) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> تعديل
            </a>

            {{-- Delete button --}}
            <form action="{{ route('admin.review_logs.destroy', $log) }}" 
                  method="POST" 
                  onsubmit="return confirm('هل أنت متأكد من حذف هذا السجل؟ سيتم نقله إلى المحذوفات.');" 
                  class="d-inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-trash"></i> حذف
                </button>
            </form>
        </div>
    </div>
</div>

@endsection