{{-- UPDATED ADMIN ACTIVITY LOG VIEW --}}
{{-- File: resources/views/admin/admins/activity.blade.php --}}
{{-- Shows REAL activity data instead of fake data --}}

@extends('layouts.admin')
@section('title', 'سجل نشاط المدير: ' . $admin->name)
@section('content')

<style>
    .activity-badge {
        padding: 6px 12px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 600;
    }

    .badge-create {
        background-color: #d4edda;
        color: #155724;
    }

    .badge-update {
        background-color: #cce5ff;
        color: #004085;
    }

    .badge-delete {
        background-color: #f8d7da;
        color: #721c24;
    }

    .badge-notification {
        background-color: #fff3cd;
        color: #856404;
    }

    .badge-approval {
        background-color: #d1ecf1;
        color: #0c5460;
    }

    .time-ago {
        color: #6c757d;
        font-size: 12px;
    }

    .activity-details {
        font-size: 13px;
        color: #495057;
        margin-top: 4px;
    }

    .no-data {
        text-align: center;
        padding: 40px;
        color: #6c757d;
    }

    .search-box {
        max-width: 300px;
    }
</style>

<div class="content-header">
    <div class="page-title">
        <i class="fas fa-history"></i>
        <h3>سجل نشاط المدير: {{ $admin->name }}</h3>
    </div>
    <a href="{{ route('admin.admins.show', $admin) }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> العودة للملف الشخصي
    </a>
</div>

<div class="admin-card">
    <div class="card-header bg-primary text-white">
        <h5>📊 سجل النشاط</h5>
    </div>

    <div class="card-body">
        {{-- Search and Filter Bar --}}
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
            {{-- Search Box --}}
            <form method="GET" action="{{ route('admin.admins.activity', $admin) }}" class="d-flex search-box">
                <input type="text" 
                       name="q" 
                       class="form-control" 
                       placeholder="بحث في السجل..."
                       value="{{ request('q') }}">
                <button class="btn btn-outline-primary" type="submit">
                    <i class="fas fa-search"></i>
                </button>
            </form>

            {{-- Action Buttons --}}
            <div class="d-flex gap-2">
                {{-- Filter dropdown --}}
                <div class="dropdown">
                    <button class="btn btn-outline-primary dropdown-toggle" 
                            type="button" 
                            data-bs-toggle="dropdown">
                        <i class="fas fa-filter"></i> تصفية
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="{{ route('admin.admins.activity', $admin) }}">جميع النشاطات</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="?action=create">✅ إنشاء</a></li>
                        <li><a class="dropdown-item" href="?action=update">✏️ تحديث</a></li>
                        <li><a class="dropdown-item" href="?action=delete">❌ حذف</a></li>
                        <li><a class="dropdown-item" href="?action=send_notification">📢 إرسال إشعار</a></li>
                        <li><a class="dropdown-item" href="?action=approve_enrollment">✔️ قبول</a></li>
                    </ul>
                </div>

                {{-- Export Button --}}
                <a href="{{ route('admin.admins.activity.export', $admin) }}" 
                   class="btn btn-primary">
                    <i class="fas fa-download"></i> تصدير
                </a>
            </div>
        </div>

        {{-- Activity Table --}}
        @if($activities->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>النشاط</th>
                        <th>الهدف</th>
                        <th>النوع</th>
                        <th>الوقت</th>
                        <th>التفاصيل</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($activities as $activity)
                    <tr>
                        {{-- Action Badge --}}
                        <td>
                            <span class="activity-badge badge-{{ $activity->action }}">
                                {{ $activity->action_label }}
                            </span>
                        </td>

                        {{-- Target/Model Name --}}
                        <td>
                            <strong>{{ $activity->model_name }}</strong>
                            <div class="activity-details">
                                {{ Str::limit($activity->description, 50) }}
                            </div>
                        </td>

                        {{-- Model Type --}}
                        <td>
                            <small class="badge bg-secondary">
                                {{ $activity->model_type_label }}
                            </small>
                        </td>

                        {{-- Time --}}
                        <td>
                            <div>{{ $activity->created_at->format('Y-m-d') }}</div>
                            <div>{{ $activity->created_at->format('H:i:s') }}</div>
                            <div class="time-ago">{{ $activity->time_ago }}</div>
                        </td>

                        {{-- Details Button --}}
                        <td>
                            @if($activity->old_data || $activity->new_data)
                            <button class="btn btn-sm btn-outline-primary" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#detailsModal{{ $activity->id }}">
                                <i class="fas fa-eye"></i> عرض
                            </button>
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>

                    {{-- Details Modal --}}
                    @if($activity->old_data || $activity->new_data)
                    <div class="modal fade" id="detailsModal{{ $activity->id }}" tabindex="-1">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">تفاصيل النشاط</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <p><strong>الوصف:</strong> {{ $activity->description }}</p>
                                    <p><strong>الوقت:</strong> {{ $activity->created_at->format('Y-m-d H:i:s') }}</p>
                                    <p><strong>عنوان IP:</strong> {{ $activity->ip_address ?? 'N/A' }}</p>

                                    @if($activity->old_data)
                                    <div class="mt-3">
                                        <h6>القيم القديمة:</h6>
                                        <pre class="bg-light p-2 rounded">{{ json_encode($activity->old_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                    </div>
                                    @endif

                                    @if($activity->new_data)
                                    <div class="mt-3">
                                        <h6>القيم الجديدة:</h6>
                                        <pre class="bg-light p-2 rounded">{{ json_encode($activity->new_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                    </div>
                                    @endif

                                    @if($activity->action === 'update' && $activity->old_data && $activity->new_data)
                                    <div class="mt-3">
                                        <h6>التغييرات:</h6>
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>الحقل</th>
                                                    <th>القيمة القديمة</th>
                                                    <th>القيمة الجديدة</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($activity->getChanges() as $field => $change)
                                                <tr>
                                                    <td><strong>{{ $field }}</strong></td>
                                                    <td><small>{{ $change['old'] ?? '-' }}</small></td>
                                                    <td><small>{{ $change['new'] ?? '-' }}</small></td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    @endif
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                        إغلاق
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <nav class="mt-4">
            {{ $activities->links() }}
        </nav>

        @else
        {{-- No Activities Message --}}
        <div class="no-data">
            <i class="fas fa-inbox" style="font-size: 48px; color: #ccc; margin-bottom: 20px;"></i>
            <p>لا توجد نشاطات مسجلة لهذا المدير</p>
            <small class="text-muted">سيتم تسجيل النشاطات هنا عند إجراء أي عمليات</small>
        </div>
        @endif
    </div>
</div>

@endsection