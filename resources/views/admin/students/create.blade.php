@extends('layouts.admin')

@section('title', 'إضافة طالب جديد')

@section('content')
<div class="card">
    <div class="card-header bg-success text-white">
        <h3 class="card-title">
            <i class="fas fa-user-plus"></i> إضافة طالب جديد
        </h3>
    </div>

    <div class="card-body">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.students.store') }}" enctype="multipart/form-data">
            @csrf
            @include('admin.students.form', ['student' => null])

            <div class="d-flex justify-content-end mt-4">
                <button type="submit" class="btn btn-success me-2">
                    <i class="fas fa-save me-1"></i> حفظ
                </button>
                <a href="{{ route('admin.students.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times me-1"></i> إلغاء
                </a>
            </div>
        </form>
    </div>
</div>
@endsection