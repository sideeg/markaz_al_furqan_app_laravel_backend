@extends('layouts.admin')

@section('title', 'قائمة الطلاب')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="mb-0"><i class="fas fa-user-graduate me-2"></i> الطلاب</h3>
    <a href="{{ route('admin.students.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> إضافة طالب جديد
    </a>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-light d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-list-ul me-2"></i> جميع الطلاب</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th scope="col" class="text-center" style="width: 60px;">الصورة</th>
                        <th scope="col">الاسم</th>
                        <th scope="col">البريد الإلكتروني</th>
                        <th scope="col">الهاتف</th>
                        <th scope="col">القراءة</th>
                        <th scope="col">الدورات</th>
                        <th scope="col">الحالة</th>
                        <th scope="col" class="text-center" style="min-width: 160px;">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($students as $student)
                    <tr>
                        <td class="text-center">
                            @if ($student->profile_image_url)
                                <img src="{{ $student->profile_image_url }}" width="40" height="40" class="rounded-circle border" alt="صورة الطالب">
                            @else
                                <div class="bg-secondary text-white rounded-circle d-inline-flex justify-content-center align-items-center" style="width:40px;height:40px;">
                                    <span class="fw-bold">{{ $student->initials }}</span>
                                </div>
                            @endif
                        </td>
                        <td>{{ $student->name }}</td>
                        <td>{{ $student->email }}</td>
                        <td>{{ $student->phone }}</td>
                        <td>{{ $student->qiraat }}</td>
                        <td>
                            <span class="badge bg-info text-dark">{{ $student->enrolledCourses->count() }}</span>
                        </td>
                        <td>
                            <form action="{{ route('admin.students.toggle-status', $student) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-sm {{ $student->is_active ? 'btn-success' : 'btn-warning' }}">
                                    {{ $student->is_active ? 'نشط' : 'معطل' }}
                                </button>
                            </form>
                        </td>
                        <td class="text-center">
                            <div class="btn-group" role="group" aria-label="Student Actions">
                                <a href="{{ route('admin.students.show', $student) }}" class="btn btn-sm btn-outline-info" title="عرض"><i class="fas fa-eye"></i></a>
                                <a href="{{ route('admin.students.edit', $student) }}" class="btn btn-sm btn-outline-primary" title="تعديل"><i class="fas fa-edit"></i></a>
                                <form action="{{ route('admin.students.destroy', $student) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذا الطالب؟')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="حذف"><i class="fas fa-trash-alt"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">لا يوجد طلاب لعرضهم.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            {{ $students->links() }}
        </div>
    </div>
</div>
@endsection
