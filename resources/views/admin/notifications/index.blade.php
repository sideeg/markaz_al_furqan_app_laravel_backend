@extends('layouts.admin')

@section('title', 'الإشعارات')

@section('content')
<div class="container-fluid py-4" dir="rtl">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1 fw-bold">الإشعارات</h1>
            <p class="text-muted mb-0">
                إدارة الإشعارات اليدوية والاطلاع على سجل الإرسال.
            </p>
        </div>

        <a href="{{ route('admin.notifications.create') }}" class="btn btn-primary">
            + إرسال إشعار جديد
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
        </div>
    @endif

    @php
        $items = $notifications->getCollection();
        $count = $items->count();
        $studentsCount = $items->where('target', 'students')->count();
        $teachersCount = $items->where('target', 'teachers')->count();
        $bothCount = $items->where('target', 'both')->count();
    @endphp

    <div class="row g-3 mb-4">
        <div class="col-12 col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">إجمالي الصفحة الحالية</div>
                    <div class="h4 mb-0 fw-bold">{{ $count }}</div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">للطلاب</div>
                    <div class="h4 mb-0 fw-bold">{{ $studentsCount }}</div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">للمشايخ / المعلمين</div>
                    <div class="h4 mb-0 fw-bold">{{ $teachersCount }}</div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">للجميع</div>
                    <div class="h4 mb-0 fw-bold">{{ $bothCount }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-0 py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h2 class="h5 mb-0">سجل الإشعارات</h2>
            <span class="text-muted small">مرتب من الأحدث إلى الأقدم</span>
        </div>

        <div class="card-body p-0">
            @if ($notifications->count())
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="py-3">العنوان</th>
                                <th class="py-3">النوع</th>
                                <th class="py-3">الجهة المستهدفة</th>
                                <th class="py-3">تاريخ الإرسال</th>
                                <th class="py-3">الحالة</th>
                                <th class="py-3 text-center">إجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($notifications as $notification)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">
                                            {{ $notification->title }}
                                        </div>
                                        <div class="text-muted small">
                                            {{ \Illuminate\Support\Str::limit($notification->message, 90) }}
                                        </div>
                                    </td>

                                    <td>
                                        <span class="badge text-bg-secondary">
                                            {{ $notification->type_label }}
                                        </span>
                                    </td>

                                    <td>
                                        <span class="badge text-bg-primary">
                                            {{ $notification->target_label }}
                                        </span>
                                    </td>

                                    <td>
                                        <div class="small">
                                            {{ $notification->sent_at ? $notification->sent_at->format('Y-m-d H:i') : '—' }}
                                        </div>
                                        <div class="text-muted small">
                                            {{ $notification->created_at ? $notification->created_at->diffForHumans() : '' }}
                                        </div>
                                    </td>

                                    <td>
                                        @if ($notification->sent_at)
                                            <span class="badge text-bg-success">مرسل</span>
                                        @else
                                            <span class="badge text-bg-warning">مسودة</span>
                                        @endif
                                    </td>

                                    <td class="text-center">
                                        <div class="d-inline-flex gap-2">
                                            <a href="{{ route('admin.notifications.show', $notification) }}" class="btn btn-sm btn-outline-primary">
                                                عرض
                                            </a>

                                            <form action="{{ route('admin.notifications.destroy', $notification) }}" method="POST" onsubmit="return confirm('هل تريد حذف هذا الإشعار؟')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    حذف
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="px-3 py-3 border-top">
                    {{ $notifications->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <div class="mb-3">
                        <div class="display-6">🛈</div>
                    </div>
                    <h3 class="h5">لا توجد إشعارات بعد</h3>
                    <p class="text-muted mb-4">
                        ابدأ بإرسال أول إشعار إلى الطلاب أو المشايخ أو الجميع.
                    </p>
                    <a href="{{ route('admin.notifications.create') }}" class="btn btn-primary">
                        إرسال إشعار جديد
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection