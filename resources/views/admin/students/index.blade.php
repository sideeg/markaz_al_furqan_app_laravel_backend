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
    <div class="card-header bg-light">
        <h5 class="mb-0"><i class="fas fa-filter me-2"></i> تصفية وبحث</h5>
    </div>
    
    <div class="card-body border-bottom bg-light bg-opacity-50">
        {{-- SEARCH AND FILTER FORM --}}
        <form action="{{ route('admin.students.index') }}" method="GET" class="row g-3 align-items-end">
            
            {{-- Search Bar --}}
            <div class="col-md-3">
                <label class="form-label small fw-bold">بحث عام</label>
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="fas fa-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="الاسم، الإيميل، الهاتف..." value="{{ request('search') }}">
                </div>
            </div>

            {{-- Gender Filter --}}
            <div class="col-md-2">
                <label class="form-label small fw-bold">الجنس</label>
                <select name="gender" class="form-select">
                    <option value="">الكل</option>
                    <option value="ذكر" {{ request('gender') == 'ذكر' ? 'selected' : '' }}>ذكر</option>
                    <option value="أنثي" {{ request('gender') == 'أنثي' ? 'selected' : '' }}>أنثى</option>
                </select>
            </div>

            {{-- Qiraat Filter --}}
            <div class="col-md-2">
                <label class="form-label small fw-bold">القراءة</label>
                <select name="qiraat" class="form-select">
                    <option value="">الكل</option>
                    @foreach(config('qiraat.types') as $qiraat)
                        <option value="{{ $qiraat }}" {{ request('qiraat') == $qiraat ? 'selected' : '' }}>
                            {{ $qiraat }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Status Filter --}}
            <div class="col-md-2">
                <label class="form-label small fw-bold">الحالة</label>
                <select name="status" class="form-select">
                    <option value="">الكل</option>
                    <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>نشط</option>
                    <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>معطل</option>
                </select>
            </div>

            {{-- Action Buttons --}}
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1">
                    <i class="fas fa-filter me-1"></i> تصفية
                </button>
                @if(request()->anyFilled(['search', 'gender', 'qiraat', 'status']))
                    <a href="{{ route('admin.students.index') }}" class="btn btn-outline-danger" title="إلغاء الفلاتر">
                        <i class="fas fa-times"></i>
                    </a>
                @endif
            </div>
        </form>
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
                        <th scope="col">الجنس</th>
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
                        <td>
                            <strong>{{ $student->name }}</strong>
                            @if($student->national_id)
                                <div class="text-muted small">{{ $student->national_id }}</div>
                            @endif
                        </td>
                        <td>{{ $student->email }}</td>
                        <td>{{ $student->phone ?? '—' }}</td>
                        <td>
                            @if($student->gender)
                                <span class="badge {{ $student->gender == 'ذكر' ? 'bg-info text-dark' : 'bg-warning text-dark' }}">
                                    {{ $student->gender }}
                                </span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>{{ $student->qiraat ?? '—' }}</td>
                        <td>
                            <span class="badge bg-primary rounded-pill">{{ $student->enrolledCourses->count() }}</span>
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
                        <td colspan="9" class="text-center text-muted py-5">
                            <i class="fas fa-search fa-3x mb-3 text-light"></i>
                            <h5>لا يوجد طلاب لعرضهم</h5>
                            <p>جرب تغيير إعدادات التصفية أو أضف طالباً جديداً.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="d-flex justify-content-between align-items-center mt-4">
            <div class="text-muted small">
                عرض <strong>{{ $students->count() }}</strong> من أصل <strong>{{ $students->total() }}</strong> طلاب
            </div>
            <div>
                {{ $students->links() }}
            </div>
        </div>
    </div>
</div>
@endsection