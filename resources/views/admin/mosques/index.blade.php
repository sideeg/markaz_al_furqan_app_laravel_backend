@extends('layouts.admin')

@section('content')
<div class="card">
    <div class="card-header bg-primary text-white">
        <h3 class="card-title">
            <i class="fas fa-mosque"></i> إدارة المساجد
        </h3>
        
        <div class="card-tools">
            <a href="{{ route('mosques.create') }}" class="btn btn-light">
                <i class="fas fa-plus"></i> إضافة مسجد جديد
            </a>
        </div>
    </div>
    
    <div class="card-body">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>اسم المسجد</th>
                    <th>المدينة</th>
                    <th>العنوان</th>
                    <th>الحالة</th>
                    <th>تاريخ الإضافة</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($mosques as $mosque)
                <tr>
                    <td>{{ $mosque->name }}</td>
                    <td>{{ $mosque->city }}</td>
                    <td>{{ $mosque->address }}</td>
                    <td>
                        <form action="{{ route('mosques.toggle-status', $mosque) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-sm {{ $mosque->is_active ? 'btn-success' : 'btn-danger' }}">
                                {{ $mosque->is_active ? 'نشط' : 'معطل' }}
                            </button>
                        </form>
                    </td>
                    <td>{{ $mosque->created_at ? $mosque->created_at->format('Y-m-d') : '-' }}</td>
                    <td>
                        <a href="{{ route('mosques.show', $mosque) }}" class="btn btn-sm btn-info">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="{{ route('mosques.edit', $mosque) }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('mosques.destroy', $mosque) }}" method="POST" style="display:inline-block;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('هل أنت متأكد من الحذف؟')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">لا توجد مساجد مسجلة</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        
        <div class="d-flex justify-content-center">
            {{ $mosques->links() }}
        </div>
    </div>
</div>
@endsection