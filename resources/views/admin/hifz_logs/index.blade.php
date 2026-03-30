@extends('layouts.admin')

@section('title', 'سجلات الحفظ')

@section('content')
<div class="content-header d-flex justify-content-between align-items-center mb-3">
    <h3>سجلات الحفظ</h3>
</div>

<div class="admin-card">
    <div class="card-header">
        <form method="GET" action="{{ route('admin.hifz_logs.index') }}" class="row g-2">
            <div class="col-md-3">
                <select name="course_id" class="form-select">
                    <option value="">-- اختر الدورة --</option>
                    @foreach($courses as $course)
                        <option value="{{ $course->id }}" {{ request('course_id') == $course->id ? 'selected' : '' }}>
                            {{ $course->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="sheikh_id" class="form-select">
                    <option value="">-- اختر الشيخ --</option>
                    @foreach($sheikhs as $sheikh)
                        <option value="{{ $sheikh->id }}" {{ request('sheikh_id') == $sheikh->id ? 'selected' : '' }}>
                            {{ $sheikh->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="student_id" class="form-select">
                    <option value="">-- اختر الطالب --</option>
                    @foreach($students as $student)
                        <option value="{{ $student->id }}" {{ request('student_id') == $student->id ? 'selected' : '' }}>
                            {{ $student->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <input type="text" name="surah" class="form-control" placeholder="اسم السورة" value="{{ request('surah') }}">
            </div>
            <div class="col-md-3">
                <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
            </div>
            <div class="col-md-3">
                <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100">بحث</button>
            </div>
            <div class="col-md-3">
                <a href="{{ route('admin.hifz_logs.index') }}" class="btn btn-secondary w-100">إعادة تعيين</a>
            </div>
        </form>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>التاريخ</th>
                        <th>الطالب</th>
                        <th>الشيخ</th>
                        <th>الدورة</th>
                        <th>من</th>
                        <th>إلى</th>
                        <th>التقييم</th>
                        <th>ملاحظات</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr>
                        {{-- ✅ FIXED: Changed to session_date --}}
                        <td>{{ $log->session_date?->format('Y-m-d') ?? 'غير محدد' }}</td>
                        
                        <td>{{ $log->student?->name ?? 'محذوف' }}</td>
                        <td>{{ $log->sheikh?->name ?? 'محذوف' }}</td>
                        
                        {{-- ✅ NOW SHOWS COURSE (with soft-deleted courses) --}}
                        <td>
                            {{ $log->course?->name ?? 'محذوفة من النظام' }}
                            @if($log->course?->deleted_at)
                                <span class="badge bg-warning ms-2">محذوفة</span>
                            @endif
                        </td>
                        
                        <td>{{ $log->start_surah }}:{{ $log->start_ayah }}</td>
                        <td>{{ $log->end_surah }}:{{ $log->end_ayah }}</td>
                        <td>
                            <span class="badge bg-info">
                                @switch($log->evaluation)
                                    @case('excellent')
                                        ممتاز
                                        @break
                                    @case('very_good')
                                        جيد جداً
                                        @break
                                    @case('good')
                                        جيد
                                        @break
                                    @case('needs_improvement')
                                        يحتاج تحسين
                                        @break
                                    @case('poor')
                                        ضعيف
                                        @break
                                @endswitch
                            </span>
                        </td>
                        
                        {{-- ✅ FIXED: Changed to notes --}}
                        <td>{{ $log->notes ?? '-' }}</td>
                        
                        <td class="d-flex gap-1">
                            <a href="{{ route('admin.hifz_logs.show', $log) }}" class="btn btn-sm btn-info">عرض</a>
                            <form action="{{ route('admin.hifz_logs.destroy', $log) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذا السجل؟')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">حذف</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center">لا توجد سجلات مطابقة.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $logs->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection