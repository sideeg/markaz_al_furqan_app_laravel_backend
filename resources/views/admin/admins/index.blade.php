@extends('layouts.admin')

@section('title', 'إدارة المديرين')

@section('content')
<div class="content-header">
    <div class="page-title">
        <i class="fas fa-user-shield"></i>
        <h3>إدارة المديرين</h3>
    </div>
    <a href="{{ route('admin.admins.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> إضافة مدير جديد
    </a>
</div>

<div class="admin-card">
    <div class="card-header">
        <h5>قائمة المديرين</h5>
        <div class="d-flex">
            <div class="form-check form-switch me-3">
                <input class="form-check-input" type="checkbox" id="activeFilter" checked>
                <label class="form-check-label" for="activeFilter">النشطين فقط</label>
            </div>
            <div class="input-group" style="width: 300px;">
                <input type="text" class="form-control" placeholder="بحث عن مدير...">
                <button class="btn btn-outline-primary" type="button">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </div>
    </div>
    
    <div class="card-body">
        <div class="table-responsive">
            <table class="table admin-table table-hover">
                <thead>
                    <tr>
                        <th>المدير</th>
                        <th>البريد الإلكتروني</th>
                        <th>الدور</th>
                        <th>الحالة</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($admins as $admin)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                @if($admin->profile_image)
                                <img src="{{ $admin->profile_image_url }}" 
                                     alt="{{ $admin->name }}" 
                                     class="rounded-circle me-3" 
                                     width="40" height="40">
                                @else
                                <div class="bg-light rounded-circle d-flex align-items-center justify-content-center me-3" 
                                     style="width: 40px; height: 40px;">
                                    <span class="fw-bold">{{ $admin->initials }}</span>
                                </div>
                                @endif
                                <div>
                                    <strong>{{ $admin->name }}</strong>
                                    <div class="text-muted small">{{ $admin->phone }}</div>
                                </div>
                            </div>
                        </td>
                        <td>{{ $admin->email }}</td>
                        <td>
                            <span class="badge bg-{{ $admin->isSuperAdmin() ? 'danger' : 'primary' }}">
                                {{ $admin->roles->first()->name === 'super_admin' ? 'مدير عام' : 'مدير' }}
                            </span>
                        </td>
                        <td>
                            <form action="{{ route('admin.admins.toggle-status', $admin) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-sm {{ $admin->is_active ? 'btn-success' : 'btn-danger' }}">
                                    {{ $admin->is_active ? 'نشط' : 'معطل' }}
                                </button>
                            </form>
                        </td>
                        <td>
                            <a href="{{ route('admin.admins.activity', $admin) }}" class="btn btn-sm btn-info" title="سجل النشاط">
                                <i class="fas fa-history"></i>
                            </a>
                            <a href="{{ route('admin.admins.edit', $admin) }}" class="btn btn-sm btn-primary" title="تعديل">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.admins.destroy', $admin) }}" method="POST" style="display:inline-block;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" 
                                        title="حذف"
                                        onclick="return confirm('هل أنت متأكد من حذف هذا المدير؟')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <div class="d-flex justify-content-between align-items-center mt-4">
            <div class="text-muted">
                عرض <strong>{{ $admins->count() }}</strong> من أصل <strong>{{ $admins->total() }}</strong> مديرين
            </div>
            <div>
                {{ $admins->links() }}
            </div>
        </div>
    </div>
</div>
@endsection