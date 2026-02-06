@extends('layouts.admin')

@section('content')

<div class="card">
    <div class="card-header bg-primary text-white">
        <h3 class="card-title">
            <i class="fas fa-edit"></i> تعديل مسجد: {{ $mosque->name }}
        </h3>
    </div>
    
    <div class="card-body">
        @if ($errors->mosque->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->mosque->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('mosques.update', $mosque) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <div class="row">
                <div class="col-md-6">
                    <!-- الاسم -->
                    <div class="mb-3">
                        <label for="mosqueName" class="form-label fw-bold">اسم المسجد <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name', 'mosque') is-invalid @enderror"
                               id="mosqueName" name="name" 
                               value="{{ old('name', $mosque->name) }}" required>
                        @error('name', 'mosque')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- الوصف -->
                    <div class="mb-3">
                        <label for="mosqueDescription" class="form-label fw-bold">وصف المسجد</label>
                        <textarea class="form-control @error('description', 'mosque') is-invalid @enderror" 
                                  id="mosqueDescription" name="description" rows="3">{{ old('description', $mosque->description) }}</textarea>
                        @error('description', 'mosque')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- الهاتف -->
                    <div class="mb-3">
                        <label for="mosquePhone" class="form-label fw-bold">رقم الهاتف</label>
                        <input type="text" class="form-control @error('phone', 'mosque') is-invalid @enderror" 
                               id="mosquePhone" name="phone" 
                               value="{{ old('phone', $mosque->phone) }}">
                        @error('phone', 'mosque')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- البريد الإلكتروني -->
                    <div class="mb-3">
                        <label for="mosqueEmail" class="form-label fw-bold">البريد الإلكتروني</label>
                        <input type="email" class="form-control @error('email', 'mosque') is-invalid @enderror" 
                               id="mosqueEmail" name="email" 
                               value="{{ old('email', $mosque->email) }}">
                        @error('email', 'mosque')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <!-- العنوان -->
                    <div class="mb-3">
                        <label for="mosqueAddress" class="form-label fw-bold">العنوان <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('address', 'mosque') is-invalid @enderror" 
                               id="mosqueAddress" name="address" 
                               value="{{ old('address', $mosque->address) }}" required>
                        @error('address', 'mosque')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- المدينة -->
                    <div class="mb-3">
                        <label for="mosqueCity" class="form-label fw-bold">المدينة <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('city', 'mosque') is-invalid @enderror" 
                               id="mosqueCity" name="city" 
                               value="{{ old('city', $mosque->city) }}" required>
                        @error('city', 'mosque')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- خط العرض والطول -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="mosqueLatitude" class="form-label fw-bold">خط العرض</label>
                            <input type="number" step="any" class="form-control @error('latitude', 'mosque') is-invalid @enderror" 
                                   id="mosqueLatitude" name="latitude" 
                                   value="{{ old('latitude', $mosque->latitude) }}">
                            @error('latitude', 'mosque')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="mosqueLongitude" class="form-label fw-bold">خط الطول</label>
                            <input type="number" step="any" class="form-control @error('longitude', 'mosque') is-invalid @enderror" 
                                   id="mosqueLongitude" name="longitude" 
                                   value="{{ old('longitude', $mosque->longitude) }}">
                            @error('longitude', 'mosque')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- الصورة -->
                    <div class="mb-3">
                        <label for="mosqueImage" class="form-label fw-bold">صورة المسجد</label>
                        <input class="form-control @error('image_path', 'mosque') is-invalid @enderror" 
                               type="file" id="mosqueImage" name="image_path" accept="image/*">
                        <small class="text-muted">الصيغ المقبولة: JPG, PNG, GIF - الحجم الأقصى: 2MB</small>
                        @error('image_path', 'mosque')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror

                        @if($mosque->image_path)
                            <div class="mt-2">
                                <img src="{{ Storage::url($mosque->image_path) }}" 
                                     alt="{{ $mosque->name }}" 
                                     class="img-thumbnail" 
                                     style="max-height: 100px">
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" id="removeImage" name="remove_image" value="1">
                                    <label class="form-check-label text-danger" for="removeImage">
                                        حذف الصورة الحالية
                                    </label>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- الحالة -->
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="mosqueActive" name="is_active" 
                               value="1" {{ old('is_active', $mosque->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label fw-bold" for="mosqueActive">الحالة النشطة</label>
                        @error('is_active', 'mosque')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end mt-4">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="fas fa-save me-1"></i> حفظ التعديلات
                </button>
                <a href="{{ route('mosques.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times me-1"></i> إلغاء
                </a>
            </div>
        </form>
    </div>
</div>

@endsection
