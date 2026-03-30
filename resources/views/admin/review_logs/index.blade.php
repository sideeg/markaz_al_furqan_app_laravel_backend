{{-- FIXED: index.blade.php for Review Logs --}}
{{-- Issues Fixed:
    1. Changed $log->date to $log->session_date ✅
    2. Changed $log->surah to $log->start_surah & $log->end_surah ✅
    3. Added soft delete support (show trashed logs) ✅
    4. Better error handling ✅
--}}

@extends('layouts.admin')
@section('title', 'سجلات المراجعة')
@section('content')
<div class="content-header d-flex justify-content-between align-items-center mb-3">
    <h3>سجلات المراجعة</h3>
</div>

<div class="admin-card">
    <div class="card-body">
        {{-- Filters Form --}}
        <form method="GET" class="row g-3 mb-4">
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
                <input type="text" name="surah" value="{{ request('surah') }}" class="form-control" placeholder="السورة">
            </div>

            <div class="col-md-3">
                <input type="date" name="from" value="{{ request('from') }}" class="form-control" placeholder="من تاريخ">
            </div>

            <div class="col-md-3">
                <input type="date" name="to" value="{{ request('to') }}" class="form-control" placeholder="إلى تاريخ">
            </div>

            <div class="col-md-12 d-flex justify-content-end">
                <button class="btn btn-primary me-2" type="submit">تصفية</button>
                <a href="{{ route('admin.review_logs.index') }}" class="btn btn-secondary">إعادة تعيين</a>
            </div>
        </form>

        {{-- Success/Error Messages --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Logs Table --}}
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>التاريخ</th>
                        <th>الوقت</th>
                        <th>الطالب</th>
                        <th>الشيخ</th>
                        <th>السورة</th>
                        <th>من آية</th>
                        <th>إلى آية</th>
                        <th>التقييم</th>
                        <th>إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                    <tr>
                        {{-- Changed from $log->date to $log->session_date ✅ --}}
                        <td>{{ $log->session_date?->format('Y-m-d') ?? 'غير محدد' }}</td>
                        
                        {{-- Session time (nullable) --}}
                        <td>{{ $log->session_time ?? '-' }}</td>
                        
                        <td>{{ $log->student?->name ?? 'محذوف' }}</td>
                        <td>{{ $log->sheikh?->name ?? 'محذوف' }}</td>
                        
                        {{-- Changed from $log->surah to start_surah & end_surah ✅ --}}
                        <td>
                            <small>
                                {{ $log->start_surah }} 
                                @if($log->start_surah !== $log->end_surah) 
                                    - {{ $log->end_surah }}
                                @endif
                            </small>
                        </td>
                        
                        <td>{{ $log->start_ayah }}</td>
                        <td>{{ $log->end_ayah }}</td>
                        
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
                        
                        <td>
                            <div class="btn-group" role="group">
                                {{-- View --}}
                                <a href="{{ route('admin.review_logs.show', $log) }}" 
                                   class="btn btn-sm btn-info" 
                                   title="عرض">
                                    <i class="fas fa-eye"></i>
                                </a>
                                
                                {{-- Edit (NEW: allows changing session date) ✅ --}}
                                <a href="{{ route('admin.review_logs.edit', $log) }}" 
                                   class="btn btn-sm btn-warning" 
                                   title="تعديل">
                                    <i class="fas fa-edit"></i>
                                </a>
                                
                                {{-- Delete --}}
                                <form action="{{ route('admin.review_logs.destroy', $log) }}" 
                                      method="POST" 
                                      class="d-inline" 
                                      onsubmit="return confirm('هل أنت متأكد من حذف هذا السجل؟')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="btn btn-sm btn-danger" 
                                            title="حذف">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-4">
                            <p class="text-muted mb-0">لا توجد سجلات</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="mt-3">
            {{ $logs->links() }}
        </div>
    </div>
</div>
@endsection