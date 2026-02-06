@extends('layouts.admin')

@section('title', 'جدول الشيخ: ' . $sheikh->name)

@section('content')
<div class="content-header">
    <div class="page-title">
        <i class="fas fa-calendar-alt"></i>
        <h3>جدول الشيخ: {{ $sheikh->name }}</h3>
    </div>
    <a href="{{ route('admin.sheikhs.show', $sheikh) }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> العودة للملف الشخصي
    </a>
</div>

<div class="admin-card">
    <div class="card-header bg-primary text-white">
        <h5>الجدول الأسبوعي</h5>
    </div>
    
    <div class="card-body">
        @if(count($schedule) > 0)
        <div class="table-responsive">
            <table class="table admin-table">
                <thead>
                    <tr>
                        <th>اليوم</th>
                        <th>المجموعة</th>
                        <th>الدورة</th>
                        <th>الوقت</th>
                        <th>عدد الطلاب</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach(['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'] as $day)
                    @if(isset($schedule[$day]))
                    @foreach($schedule[$day] as $group)
                    <tr>
                        <td>
                            @php
                                $days = [
                                    'sunday' => 'الأحد',
                                    'monday' => 'الإثنين',
                                    'tuesday' => 'الثلاثاء',
                                    'wednesday' => 'الأربعاء',
                                    'thursday' => 'الخميس',
                                    'friday' => 'الجمعة',
                                    'saturday' => 'السبت',
                                ];
                            @endphp
                            <strong>{{ $days[$day] }}</strong>
                        </td>
                        <td>
                            <a href="#">
                                {{ $group->name }}
                            </a>
                        </td>
                        <td>
                            <a href="{{ route('admin.courses.show', $group->course) }}">
                                {{ $group->course->name }}
                            </a>
                        </td>
                        <td>
                            {{ $group->start_time }} - {{ $group->end_time }}
                        </td>
                        <td>
                            <span class="badge bg-primary">
                                {{ $group->students->count() }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                    @endif
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="alert alert-info text-center">
            لا توجد مجموعات مسندة لهذا الشيخ
        </div>
        @endif
    </div>
</div>
@endsection