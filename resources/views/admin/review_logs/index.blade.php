@extends('layouts.admin')
@section('title', 'سجلات المراجعة')
@section('content')
<div class="content-header d-flex justify-content-between align-items-center mb-3">
    <h3>سجلات المراجعة</h3>
</div>
<div class="admin-card">
    <div class="card-body">
        <form method="GET" class="row g-3 mb-4">
        <div class="col-md-3">
                <select name="student_id" class="form-select">
                    <option value="">-- اختر الطالب --</option>
                    @foreach($students as $student)
                        <option value="{{ $student->id }}" {{ request('student_id') == $student->id ? 'selected' : '' }}>{{ $student->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="sheikh_id" class="form-select">
                    <option value="">-- اختر الشيخ --</option>
                    @foreach($sheikhs as $sheikh)
                        <option value="{{ $sheikh->id }}" {{ request('sheikh_id') == $sheikh->id ? 'selected' : '' }}>{{ $sheikh->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="course_id" class="form-select">
                    <option value="">-- اختر الدورة --</option>
                    @foreach($courses as $course)
                        <option value="{{ $course->id }}" {{ request('course_id') == $course->id ? 'selected' : '' }}>{{ $course->name }}</option>
                    @endforeach
                </select>
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

        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>التاريخ</th>
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
                        <td>{{ $log->date }}</td>
                        <td>{{ $log->student?->name }}</td>
                        <td>{{ $log->sheikh?->name }}</td>
                        <td>{{ $log->surah }}</td>
                        <td>{{ $log->start_ayah }}</td>
                        <td>{{ $log->end_ayah }}</td>
                        <td>{{ ucfirst($log->evaluation) }}</td>
                        <td>
                            <a href="{{ route('admin.review_logs.show', $log) }}" class="btn btn-sm btn-info">عرض</a>
                            <form action="{{ route('admin.review_logs.destroy', $log) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد؟')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger">حذف</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8">لا توجد سجلات.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            {{ $logs->links() }}
        </div>
    </div>
</div>
@endsection