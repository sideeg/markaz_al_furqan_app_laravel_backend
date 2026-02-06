@extends('layouts.admin')

@section('content')
<div class="card">
    <div class="card-header bg-primary text-white">
        <h3 class="card-title">
            <i class="fas fa-mosque"></i> {{ $mosque->name }}
        </h3>
    </div>
    
    <div class="card-body">
        <div class="row">
            <div class="col-md-8">
                <div class="mb-4">
                    <h4>معلومات المسجد</h4>
                    <p>{{ $mosque->description }}</p>
                </div>
                
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title text-muted">العنوان</h5>
                                <p class="card-text">{{ $mosque->address }}, {{ $mosque->city }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title text-muted">معلومات الاتصال</h5>
                                <p class="card-text">
                                    {{ $mosque->phone ?: 'لا يوجد' }}<br>
                                    {{ $mosque->email ?: 'لا يوجد' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                
                @if($mosque->latitude && $mosque->longitude)
                <div class="mb-4">
                    <h4>الموقع الجغرافي</h4>
                    <div id="map" style="height: 300px; border-radius: 8px;"></div>
                </div>
                @endif
                
                <div class="d-flex justify-content-start mt-4">
                    <a href="{{ route('mosques.edit', $mosque) }}" class="btn btn-primary me-2">
                        <i class="fas fa-edit me-1"></i> تعديل
                    </a>
                    <form action="{{ route('mosques.destroy', $mosque) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger" onclick="return confirm('هل أنت متأكد من الحذف؟')">
                            <i class="fas fa-trash me-1"></i> حذف
                        </button>
                    </form>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">صورة المسجد</h5>
                        @if($mosque->image_path)
                            <img src="{{ Storage::url($mosque->image_path) }}" 
                                 alt="{{ $mosque->name }}" 
                                 class="img-fluid rounded mb-3">
                        @else
                            <div class="text-center py-4 bg-light rounded">
                                <i class="fas fa-mosque fa-3x text-muted mb-3"></i>
                                <p class="text-muted">لا توجد صورة</p>
                            </div>
                        @endif
                        
                        <div class="mt-4">
                            <h5 class="card-title">الحالة</h5>
                            <span class="badge {{ $mosque->is_active ? 'bg-success' : 'bg-danger' }}">
                                {{ $mosque->is_active ? 'نشط' : 'معطل' }}
                            </span>
                        </div>
                        
                        <div class="mt-4">
                            <h5 class="card-title">تم الإضافة بواسطة</h5>
                            <p class="card-text">{{ $mosque->creator->name }}</p>
                            <small class="text-muted">
                                {{ $mosque->created_at->format('Y-m-d') }}
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if($mosque->latitude && $mosque->longitude)
<!-- Leaflet.js for maps -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
<script>
    // Initialize map
    const map = L.map('map').setView([{{ $mosque->latitude }}, {{ $mosque->longitude }}], 15);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);
    
    // Add marker
    L.marker([{{ $mosque->latitude }}, {{ $mosque->longitude }}])
        .addTo(map)
        .bindPopup('{{ $mosque->name }}')
        .openPopup();
</script>
@endif
@endsection