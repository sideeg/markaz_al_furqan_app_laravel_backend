@extends('layouts.admin')

@section('title', 'الدورات التعليمية')

@section('content')
<div class="content-header d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div class="page-title">
        <i class="fas fa-book"></i>
        <h3>إدارة الدورات التعليمية</h3>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCourseModal">
        <i class="fas fa-plus"></i> إضافة دورة جديدة
    </button>
</div>

<div class="admin-card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
        <h5>قائمة الدورات</h5>
        <div class="d-flex gap-3 flex-wrap">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="activeFilter" checked>
                <label class="form-check-label" for="activeFilter">النشطة فقط</label>
            </div>
            <div class="input-group" style="width: 250px;">
                <input type="text" class="form-control" placeholder="بحث عن دورة...">
                <button class="btn btn-outline-primary" type="button">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </div>
    </div>
    
    <div class="card-body" style="overflow-x: auto;">
    <div class="table-responsive" style="min-width: 1200px;">
        <table class="table table-hover align-middle text-center admin-table">
            <thead class="table-light">
                <tr>
                    <th>اسم الدورة</th>
                    <th>النوع</th>
                    <th>المسجد</th>
                    <th>تاريخ البدء</th>
                    <th>تاريخ الإنتهاء</th>
                    <th>الطلاب</th>
                    <th>التسجيل</th>
                    <th>الحالة</th>
                    <th>تاريخ الإنشاء</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($courses as $course)
                <tr>
                    <td>
                        <strong>{{ $course->name }}</strong><br>
                        <small class="text-muted">{{ Str::limit($course->description, 30) }}</small>
                    </td>
                    <td>
                        <span class="badge 
                            {{ $course->type === 'online' ? 'bg-primary' : 
                               ($course->type === 'open' ? 'bg-success' : 'bg-secondary') }}">
                            {{ $course->type_display_name }}
                        </span>
                    </td>
                    <td>
                        {{ $course->mosque?->name ?? '—' }}
                    </td>
                    <td>{{ $course->start_date->format('Y-m-d') }}</td>
                    <td>{{ $course->end_date->format('Y-m-d') }}</td>
                    <td>
                        <span class="badge bg-info">
                            {{ $course->current_students }} / {{ $course->max_students }}
                        </span>
                    </td>
                    <td>
                        <span class="badge {{ $course->is_registration_open ? 'bg-success' : 'bg-danger' }}">
                            {{ $course->is_registration_open ? 'مفتوح' : 'مغلق' }}
                        </span>
                    </td>
                    <td>
                        <span class="badge {{ $course->is_active ? 'bg-success' : 'bg-secondary' }}">
                            {{ $course->is_active ? 'نشطة' : 'معطلة' }}
                        </span>
                    </td>
                    <td>{{ $course->created_at->format('Y-m-d') }}</td>
                    <td>
                        <div class="d-flex justify-content-center gap-1">
                            <a href="{{ route('admin.courses.show', $course) }}" class="btn btn-sm btn-info" title="عرض التفاصيل">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('admin.courses.edit', $course) }}" class="btn btn-sm btn-primary" title="تعديل">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.courses.groups.index', $course) }}" method="GET"  >
                                @csrf
                                @method('GET')
                                <button class="btn btn-sm btn-secondary" title="إدارة المجموعات">
                                    <i class="fas fa-users"></i>
                                </button>
                            </form>
                            <form action="{{ route('admin.courses.destroy', $course) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذه الدورة؟')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger" title="حذف">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="text-center text-muted">لا توجد دورات حالياً.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-4">
            <div class="text-muted">
                عرض <strong>{{ $courses->count() }}</strong> من أصل <strong>{{ $courses->total() }}</strong> دورات
            </div>
            <div>
                {{ $courses->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $('#activeFilter').change(function() {
            const showActive = $(this).is(':checked');
            // AJAX filtering if needed
        });
    });
</script>
@endsection

<!-- Course Modal -->
<div class="modal fade" id="addCourseModal" tabindex="-1" aria-labelledby="addCourseModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" action="{{ route('admin.courses.store') }}" enctype="multipart/form-data">
      @csrf
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">إضافة دورة جديدة</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <input type="text" name="name" class="form-control mb-2" placeholder="اسم الدورة" required>
          <select name="type" class="form-control mb-2">
            <option value="online">أونلاين</option>
            <option value="open">مفتوحة</option>
            <option value="closed">مغلقة</option>
          </select>
          <input type="date" name="start_date" class="form-control mb-2" required>
          <input type="date" name="end_date" class="form-control mb-2" required>
          <select name="mosque_id" class="form-control mb-2">
          <option value="{{ null }}">اختر مسجد للدورة</option>

            @foreach( $mosques  as  $mosque)
              <option value="{{ $mosque->id }}">{{ $mosque->name }}</option>
            @endforeach
          </select>
          <input type="text" name="max_students" class="form-control mb-2" placeholder="اقصي عدد للطلاب " required>

          <input type="file" name="image" class="form-control mb-2">
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">إنشاء</button>
        </div>
      </div>
    </form>
  </div>
</div>

