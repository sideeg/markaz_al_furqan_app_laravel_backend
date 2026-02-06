@extends('layouts.admin')
@section('title', 'بيانات الطالب')
@section('content')
<div class="card">
    <div class="card-header">
        <h3>الطالب: {{ $student->name }}</h3>
    </div>
    <div class="card-body row">
        <div class="col-md-4">
            @if($student->profile_image_url)
                <img src="{{ $student->profile_image_url }}" class="img-thumbnail" width="150">
            @endif
            <ul class="list-group mt-3">
                <li class="list-group-item">البريد: {{ $student->email }}</li>
                <li class="list-group-item">الهاتف: {{ $student->phone }}</li>
                <li class="list-group-item">الهوية: {{ $student->national_id }}</li>
                <li class="list-group-item">القراءة: {{ $student->qiraat }}</li>
                <li class="list-group-item">الحالة: {{ $student->is_active ? 'نشط' : 'غير نشط' }}</li>
            </ul>
        </div>

        <div class="col-md-8">
            <h5>الدورات المسجل بها</h5>
            <ul>
                @foreach ($student->enrolledCourses as $course)
                    <li>{{ $course->title }} (تاريخ التسجيل: {{ $course->pivot->enrolled_at }})</li>
                @endforeach
            </ul>

            <h5 class="mt-4">سجل الحفظ</h5>
            <ul>
                @foreach ($student->hifzLogs as $log)
                    <li>{{ $log->date }}: {{ $log->from_surah }} {{ $log->start_ayah }} - {{ $log->end_ayah }} ({{ $log->evaluation }})</li>
                @endforeach
            </ul>

            <h5 class="mt-4">سجل المراجعة</h5>
            <ul>
                @foreach ($student->reviewLogs as $log)
                    <li>{{ $log->date }}: {{ $log->from_surah }} - {{ $log->to_surah }} ({{ $log->evaluation }})</li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
@endsection