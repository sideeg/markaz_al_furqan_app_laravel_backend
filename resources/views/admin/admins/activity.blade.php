@extends('layouts.admin')

@section('title', 'سجل نشاط المدير: ' . $admin->name)

@section('content')
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
        <h5>سجل النشاط</h5>
    </div>
    
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="input-group" style="width: 300px;">
                <input type="text" class="form-control" placeholder="بحث في السجل...">
                <button class="btn btn-outline-primary" type="button">
                    <i class="fas fa-search"></i>
                </button>
            </div>
            <div>
                <button class="btn btn-outline-primary me-2">
                    <i class="fas fa-filter"></i> تصفية
                </button>
                <button class="btn btn-primary">
                    <i class="fas fa-download"></i> تصدير
                </button>
            </div>
        </div>
        
        <div class="table-responsive">
            <table class="table admin-table">
                <thead>
                    <tr>
                        <th>النشاط</th>
                        <th>الهدف</th>
                        <th>الوقت</th>
                        <th>تفاصيل</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($activities as $activity)
                    <tr>
                        <td>{{ $activity['action'] }}</td>
                        <td>{{ $activity['target'] }}</td>
                        <td>{{ $activity['time'] }}</td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye"></i> عرض التفاصيل
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <nav aria-label="Page navigation" class="mt-4">
            <ul class="pagination justify-content-center">
                <li class="page-item disabled">
                    <a class="page-link" href="#" tabindex="-1">السابق</a>
                </li>
                <li class="page-item active"><a class="page-link" href="#">1</a></li>
                <li class="page-item"><a class="page-link" href="#">2</a></li>
                <li class="page-item"><a class="page-link" href="#">3</a></li>
                <li class="page-item">
                    <a class="page-link" href="#">التالي</a>
                </li>
            </ul>
        </nav>
    </div>
</div>
@endsection