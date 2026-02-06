@extends('layouts.admin')

@section('title', 'تعديل بيانات الطالب')

@section('content')
<div class="card">
    <div class="card-header bg-primary text-white">
        <h3 class="card-title">
            <i class="fas fa-user-edit"></i> تعديل بيانات الطالب
        </h3>
    </div>

    <div class="card-body">
        @if ($errors->getBag('student')->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.students.update', $student) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            @include('admin.students.form', ['student' => $student])

            <div class="d-flex justify-content-between mt-4">
                <div>
                    <a href="{{ route('admin.students.index') }}" class="btn btn-secondary me-2">
                        <i class="fas fa-arrow-left me-1"></i> العودة للقائمة
                    </a>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i> حفظ التعديلات
                </button>
            </div>
        </form>

        <form action="{{ route('admin.students.destroy', $student) }}" method="POST" style="display:inline-block; margin-top: 10px;">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger" onclick="return confirm('هل أنت متأكد من حذف هذا الطالب؟')">
                <i class="fas fa-trash me-1"></i> حذف الطالب
            </button>
        </form>
    </div>
</div>
@endsection
