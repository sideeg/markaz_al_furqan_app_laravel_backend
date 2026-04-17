@extends('layouts.admin')

@section('title', 'تفاصيل الإشعار')

@section('content')
<div class="container-fluid py-4" dir="rtl">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1 fw-bold">تفاصيل الإشعار</h1>
            <p class="text-muted mb-0">
                عرض كامل لمحتوى الإشعار وبياناته الوصفية.
            </p>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('admin.notifications.index') }}" class="btn btn-outline-secondary">
                العودة
            </a>

            <form action="{{ route('admin.notifications.destroy', $notification) }}" method="POST" onsubmit="return confirm('هل تريد حذف هذا الإشعار؟')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger">
                    حذف
                </button>
            </form>
        </div>
    </div>

    @php
        $data = is_array($notification->data) ? $notification->data : (json_decode($notification->data ?? '[]', true) ?: []);
        $course = $notification->course ?? null;
    @endphp

    <div class="row g-4">
        <div class="col-12 col-lg-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body p-4">
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                        <span class="badge text-bg-primary">{{ $notification->target_label }}</span>
                        <span class="badge text-bg-secondary">{{ $notification->type_label }}</span>
                        @if ($notification->sent_at)
                            <span class="badge text-bg-success">مرسل</span>
                        @else
                            <span class="badge text-bg-warning">مسودة</span>
                        @endif
                    </div>

                    <h2 class="h4 fw-bold mb-3">{{ $notification->title }}</h2>

                    <div class="border rounded-3 bg-light p-3">
                        <div class="text-muted small mb-2">نص الإشعار</div>
                        <div class="fs-6 lh-lg">
                            {!! nl2br(e($notification->message)) !!}
                        </div>
                    </div>
                </div>
            </div>

            @if (!empty($data))
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white border-0 py-3">
                        <h3 class="h6 mb-0">البيانات الإضافية</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm align-middle">
                                <tbody>
                                    @foreach ($data as $key => $value)
                                        <tr>
                                            <th class="text-muted" style="width: 220px;">
                                                {{ $key }}
                                            </th>
                                            <td>
                                                @if (is_array($value) || is_object($value))
                                                    <pre class="mb-0 bg-light p-2 rounded small">{{ json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                                @else
                                                    {{ $value }}
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            @if ($course)
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white border-0 py-3">
                        <h3 class="h6 mb-0">الدورة المرتبطة</h3>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <div class="border rounded-3 p-3 h-100">
                                    <div class="text-muted small">اسم الدورة</div>
                                    <div class="fw-semibold">{{ $course->name ?? '—' }}</div>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="border rounded-3 p-3 h-100">
                                    <div class="text-muted small">رقم الدورة</div>
                                    <div class="fw-semibold">{{ $course->id ?? '—' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <div class="col-12 col-lg-4">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h3 class="h6 mb-0">معلومات سريعة</h3>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="text-muted small">تاريخ الإرسال</div>
                        <div class="fw-semibold">
                            {{ $notification->sent_at ? $notification->sent_at->format('Y-m-d H:i') : '—' }}
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="text-muted small">تاريخ الإنشاء</div>
                        <div class="fw-semibold">
                            {{ $notification->created_at ? $notification->created_at->format('Y-m-d H:i') : '—' }}
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="text-muted small">النوع</div>
                        <div class="fw-semibold">{{ $notification->type_label }}</div>
                    </div>

                    <div class="mb-3">
                        <div class="text-muted small">الجهة المستهدفة</div>
                        <div class="fw-semibold">{{ $notification->target_label }}</div>
                    </div>

                    <div class="mb-3">
                        <div class="text-muted small">المعرّف</div>
                        <div class="fw-semibold">#{{ $notification->id }}</div>
                    </div>

                    @if ($notification->creator)
                        <div class="mb-3">
                            <div class="text-muted small">أنشأه</div>
                            <div class="fw-semibold">{{ $notification->creator->name }}</div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0 py-3">
                    <h3 class="h6 mb-0">إجراءات سريعة</h3>
                </div>
                <div class="card-body d-grid gap-2">
                    <a href="{{ route('admin.notifications.create') }}" class="btn btn-primary">
                        إرسال إشعار جديد
                    </a>
                    <a href="{{ route('admin.notifications.index') }}" class="btn btn-outline-secondary">
                        العودة إلى القائمة
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection