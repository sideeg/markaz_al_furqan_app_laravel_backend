@extends('layouts.admin')

@section('title', 'إدارة المشايخ')

@section('content')
<div class="content-header">
    <div class="page-title">
        <i class="fas fa-user-tie"></i>
        <h3>إدارة المشايخ</h3>
    </div>
    <button type="button" data-bs-toggle="modal" data-bs-target="#addSheikhModal" class="btn btn-primary">
        <i class="fas fa-plus"></i> إضافة شيخ جديد
    </button>
</div>

<div class="admin-card">
    <div class="card-header">
        <h5>قائمة المشايخ</h5>
        <div class="d-flex">
            <div class="form-check form-switch me-3">
                <input class="form-check-input" type="checkbox" id="activeFilter" checked>
                <label class="form-check-label" for="activeFilter">النشطين فقط</label>
            </div>
            <div class="input-group" style="width: 300px;">
                <input type="text" class="form-control" placeholder="بحث عن شيخ...">
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
                        <th>الشيخ</th>
                        <th>البريد الإلكتروني</th>
                        <th>الهاتف</th>
                        <th>القراءات</th>
                        <th>الدورات</th>
                        <th>الحالة</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sheikhs as $sheikh)
                    <tr>
                    <td>
    <div class="d-flex align-items-center">
        @if($sheikh->profile_image)
            <img src="{{ $sheikh->profile_image_url }}" 
                 alt="{{ $sheikh->name }}" 
                 class="rounded-circle me-3" 
                 width="40" height="40">
        @else
            <div class="bg-light rounded-circle d-flex align-items-center justify-content-center me-3" 
                 style="width: 40px; height: 40px;">
                <span class="fw-bold">{{ $sheikh->initials }}</span>
            </div>
        @endif

        <div>
            <strong>{{ $sheikh->name }}</strong>
            <div class="text-muted small">{{ $sheikh->national_id }}</div>
        </div>
    </div>
</td>
                        <td>{{ $sheikh->email }}</td>
                        <td>{{ $sheikh->phone }}</td>
                        <td>{{ $sheikh->qiraat }}</td>
                        <td>
                            <span class="badge bg-primary">
                                {{ $sheikh->teachingCourses->count() }}
                            </span>
                        </td>
                        <td>
                            <form action="{{ route('admin.sheikhs.toggle-status', $sheikh) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-sm {{ $sheikh->is_active ? 'btn-success' : 'btn-danger' }}">
                                    {{ $sheikh->is_active ? 'نشط' : 'معطل' }}
                                </button>
                            </form>
                        </td>
                        <td>
                            <a href="{{ route('admin.sheikhs.schedule', $sheikh) }}" class="btn btn-sm btn-info" title="الجدول">
                                <i class="fas fa-calendar-alt"></i>
                            </a>
                            <a href="{{ route('admin.sheikhs.edit', $sheikh) }}" class="btn btn-sm btn-primary" title="تعديل">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.sheikhs.destroy', $sheikh) }}" method="POST" style="display:inline-block;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" 
                                        title="حذف"
                                        onclick="return confirm('هل أنت متأكد من حذف هذا الشيخ؟')">
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
                عرض <strong>{{ $sheikhs->count() }}</strong> من أصل <strong>{{ $sheikhs->total() }}</strong> مشايخ
            </div>
            <div>
                {{ $sheikhs->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

 <!-- Sheikh Modal -->
 <div class="modal fade" id="addSheikhModal" tabindex="-1" aria-labelledby="addSheikhModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" action="{{ route('admin.sheikhs.store') }}" enctype="multipart/form-data">
      @csrf
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">إضافة شيخ جديد</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <input type="text" name="name"
                 class="form-control mb-2 @error('name') is-invalid @enderror"
                 placeholder="اسم الشيخ" value="{{ old('name') }}" required>
          @error('name', 'sheikh')
            <div class="invalid-feedback d-block">{{ $message }}</div>
          @enderror

          <input type="email" name="email"
                 class="form-control mb-2 @error('email') is-invalid @enderror"
                 placeholder="البريد الإلكتروني" value="{{ old('email') }}" required>
          @error('email', 'sheikh')
            <div class="invalid-feedback d-block">{{ $message }}</div>
          @enderror

          <input type="text" name="phone"
                 class="form-control mb-2 @error('phone') is-invalid @enderror"
                 placeholder="رقم الهاتف" value="{{ old('phone') }}">
          @error('phone', 'sheikh')
            <div class="invalid-feedback d-block">{{ $message }}</div>
          @enderror

          <input type="text" name="national_id"
                 class="form-control mb-2 @error('national_id') is-invalid @enderror"
                 placeholder="الرقم الوطني" value="{{ old('national_id') }}">
          @error('national_id', 'sheikh')
            <div class="invalid-feedback d-block">{{ $message }}</div>
          @enderror

          <select name="qiraat" class="form-control mb-2 @error('qiraat') is-invalid @enderror">
    <option value="">اختر القراءة</option>
    @foreach(config('qiraat.types') as $qiraat)
        <option value="{{ $qiraat }}" {{ old('qiraat', $user->qiraat ?? '') == $qiraat ? 'selected' : '' }}>
            {{ $qiraat }}
        </option>
    @endforeach
</select>
@error('qiraat')
    <div class="invalid-feedback">{{ $message }}</div>
@enderror

          <input type="file" name="profile_image"
                 class="form-control mb-2 @error('profile_image') is-invalid @enderror">
          @error('profile_image', 'sheikh')
            <div class="invalid-feedback d-block">{{ $message }}</div>
          @enderror

          <input type="password" name="password"
                 class="form-control mb-2 @error('password') is-invalid @enderror"
                 placeholder="كلمة المرور" required>
          @error('password', 'sheikh')
            <div class="invalid-feedback d-block">{{ $message }}</div>
          @enderror

          <input type="password" name="password_confirmation"
                 class="form-control mb-2" placeholder="تأكيد كلمة المرور" required>
        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-success">حفظ</button>
        </div>
      </div>
    </form>
  </div>
</div>
@if ($errors->any())
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var modal = new bootstrap.Modal(document.getElementById('addSheikhModal'));
        modal.show();
    });
</script>
@endif




