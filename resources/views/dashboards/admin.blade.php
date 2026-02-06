<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة تحكم الإدارة - مركز الفرقان</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Cairo Font -->
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #2c6e49;
            --secondary: #4c956c;
            --accent: #fefee3;
            --light: #d8f3dc;
            --dark: #1b4332;
            --gold: #c9a227;
            --dark-gold: #8d6e1f;
        }
        
        body {
            font-family: 'Cairo', sans-serif;
            background-color: #f8f9fa;
            background-image: linear-gradient(to bottom, rgba(216, 243, 220, 0.1), rgba(216, 243, 220, 0.3)), url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100"><rect width="100" height="100" fill="white"/><path d="M20,20 Q50,5 80,20 T100,50 T80,80 T50,100 T20,80 T0,50 T20,20 Z" fill="none" stroke="%23d8f3dc" stroke-width="0.5"/></svg>');
            direction: rtl;
            min-height: 100vh;
        }
        
        .dashboard-container {
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            margin-bottom: 25px;
            overflow: hidden;
            background-color: white;
            border-top: 4px solid var(--primary);
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.12);
        }
        
        .card-icon {
            font-size: 2.5rem;
            opacity: 0.2;
            position: absolute;
            right: 20px;
            bottom: 20px;
            color: var(--primary);
        }
        
        .stats-card {
            border-right: 4px solid var(--primary);
        }
        
        .stats-card.students {
            border-right-color: #2a9d8f;
        }
        
        .stats-card.courses {
            border-right-color: #e9c46a;
        }
        
        .stats-card.sheikhs {
            border-right-color: #f4a261;
        }
        
        .stats-card.active {
            border-right-color: #e76f51;
        }
        
        .dashboard-header {
            background: linear-gradient(120deg, var(--primary), var(--dark));
            color: white;
            padding: 25px 0;
            margin-bottom: 30px;
            border-radius: 0 0 20px 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            position: relative;
            overflow: hidden;
        }
        
        .dashboard-header::before {
            content: "﷽";
            position: absolute;
            font-size: 15rem;
            opacity: 0.05;
            font-family: "Times New Roman", serif;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 0;
        }
        
        .quick-actions .btn {
            width: 100%;
            margin-bottom: 15px;
            text-align: right;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 20px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .quick-actions .btn:hover {
            transform: translateX(-5px);
        }
        
        .quick-actions .btn i {
            margin-left: 10px;
            font-size: 1.2rem;
        }
        
        .table th {
            background-color: var(--primary);
            color: white;
            font-weight: 600;
            border: none;
        }
        
        .badge-online {
            background-color: #2a9d8f;
        }
        
        .badge-open {
            background-color: #e9c46a;
        }
        
        .badge-closed {
            background-color: #e76f51;
        }
        
        .star-rating {
            color: var(--gold);
        }
        
        .section-title {
            border-right: 4px solid var(--primary);
            padding-right: 15px;
            margin-bottom: 20px;
            color: var(--dark);
            font-weight: 700;
            display: flex;
            align-items: center;
        }
        
        .section-title i {
            margin-left: 10px;
            background-color: var(--light);
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
        }
        
        .notification-item {
            border-right: 3px solid #e9ecef;
            padding: 12px 15px;
            margin-bottom: 12px;
            transition: all 0.3s ease;
            border-radius: 8px;
            background-color: #f8f9fa;
            position: relative;
        }
        
        .notification-item:hover {
            background-color: white;
            border-right-color: var(--primary);
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        
        .notification-item::after {
            content: "";
            position: absolute;
            right: -3px;
            top: 50%;
            transform: translateY(-50%);
            width: 8px;
            height: 8px;
            background-color: var(--primary);
            border-radius: 50%;
        }
        
        .notification-item.new::after {
            background-color: #e74c3c;
        }
        
        .stats-number {
            font-size: 2.5rem;
            font-weight: 700;
            margin: 10px 0;
            color: var(--dark);
        }
        
        .stats-label {
            color: #6c757d;
            font-weight: 600;
        }
        
        .stats-change {
            font-weight: 600;
        }
        
        .action-btn {
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 0.9rem;
        }
        
        .logo-container {
            background-color: white;
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 10px rgba(0,0,0,0.15);
            margin-left: 20px;
        }
        
        .logo-text {
            font-weight: 800;
            font-size: 1.8rem;
            color: var(--primary);
        }
        
        .header-content {
            display: flex;
            align-items: center;
        }
        
        .welcome-text {
            color: rgba(255,255,255,0.85);
        }
        
        .date-badge {
            background-color: rgba(255,255,255,0.15);
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: 600;
        }
        
        .navigation-buttons {
            display: flex;
            gap: 15px;
            margin-top: 15px;
        }
        
        .nav-btn {
            display: flex;
            align-items: center;
            padding: 10px 20px;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(5px);
            color: white;
            font-weight: 600;
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .nav-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-3px);
            text-decoration: none;
        }
        
        .nav-btn i {
            margin-left: 8px;
            font-size: 1.2rem;
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .view-all-btn {
            display: flex;
            align-items: center;
            color: var(--primary);
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .view-all-btn:hover {
            color: var(--dark);
            text-decoration: none;
            transform: translateX(-5px);
        }
        
        .view-all-btn i {
            margin-right: 5px;
        }
    </style>
</head>
<body>
    <!-- Dashboard Layout -->
    <div class="dashboard-container">
        <!-- Header -->
        <header class="dashboard-header">
            <div class="container">
                <div class="header-content">
                    <div class="logo-container">
                        <div class="logo-text">ق</div>
                    </div>
                    <div class="flex-grow-1">
                        <h1 class="mb-2"><i class="fas fa-mosque me-2"></i>لوحة تحكم الإدارة - مركز الفرقان</h1>
                        <p class="welcome-text mb-0">مرحباً بك، <strong>مدير النظام</strong> | <span class="date-badge" id="current-date"></span></p>
                        
                        <!-- Navigation Buttons -->
                        <div class="navigation-buttons">
                            <a href="{{ route('admin.courses.index') }}" class="nav-btn">
                                <i class="fas fa-book"></i> جميع الدورات
                            </a>
                            <a href="{{ route('admin.students.index') }}" class="nav-btn">
                                <i class="fas fa-users"></i> جميع الطلاب
                            </a>
                            <a href="{{ route('admin.sheikhs.index') }}" class="nav-btn">
                                <i class="fas fa-user-tie"></i> جميع المشايخ
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <div class="container pb-5">
            <!-- Stats Cards with View All Buttons -->
            <div class="row mb-4">
                <div class="col-md-3 col-sm-6">
                    <div class="card stats-card students">
                        <div class="card-body position-relative">
                            <p class="stats-label">إجمالي الطلاب</p>
                            <p class="stats-number">{{ $stats['studentsCount']['count'] ?? 0 }}</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <p class="stats-change text-success mb-0"><i class="fas fa-arrow-up"></i> {{ $stats['studentsCount']['growth'] ?? 0 }} زيادة</p>
                                <a href="admin.students.index" class="view-all-btn">عرض الكل <i class="fas fa-arrow-left"></i></a>
                            </div>
                            <i class="fas fa-users card-icon"></i>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3 col-sm-6">
                    <div class="card stats-card courses">
                        <div class="card-body position-relative">
                            <p class="stats-label">إجمالي الدورات</p>
                            <p class="stats-number">{{ $stats['coursesCount']['count'] ?? 0 }}</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <p class="stats-change text-success mb-0"><i class="fas fa-arrow-up"></i>{{ $stats['coursesCount']['growth'] ?? 0 }} زيادة</p>
                                <a href={{ route('admin.courses.index') }} target="" class="view-all-btn">عرض الكل <i class="fas fa-arrow-left"></i></a>
                            </div>
                            <i class="fas fa-book card-icon"></i>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3 col-sm-6">
                    <div class="card stats-card sheikhs">
                        <div class="card-body position-relative">
                            <p class="stats-label">إجمالي المشايخ</p>
                            <p class="stats-number">{{ $stats['sheikhsCount']['count'] ?? 0 }}</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <p class="stats-change text-success mb-0"><i class="fas fa-arrow-up"></i> {{$stats['sheikhsCount']['growth']}} زيادة</p>
                                <a href="{{ route('admin.sheikhs.index') }}" class="view-all-btn">عرض الكل <i class="fas fa-arrow-left"></i></a>
                            </div>
                            <i class="fas fa-user-tie card-icon"></i>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3 col-sm-6">
                    <div class="card stats-card active">
                        <div class="card-body position-relative">
                            <p class="stats-label">الدورات النشطة</p>
                            <p class="stats-number">{{ $stats['activeCoursesCount']['count'] ?? 0 }}</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <p class="stats-change text-danger mb-0"><i class="fas fa-arrow-down"></i> {{ $stats['activeCoursesCount']['growth'] ?? 0 }}% انخفاض</p>
                                <a href="#" class="view-all-btn">عرض الكل <i class="fas fa-arrow-left"></i></a>
                            </div>
                            <i class="fas fa-book-open card-icon"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Recent Courses & Recent Activity -->
            <div class="row mb-4">
                <!-- Recent Courses -->
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-body">
                            <div class="section-header">
                                <h3 class="section-title"><i class="fas fa-book"></i> الدورات الحديثة</h3>
                                <a href="{{ route('admin.courses.index') }}" class="view-all-btn">عرض جميع الدورات <i class="fas fa-arrow-left"></i></a>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>اسم الدورة</th>
                                            <th>النوع</th>
                                            <th>حالة التسجيل</th>
                                            <th>تاريخ البدء</th>
                                            <th>الإجراءات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($recentCourses ?? [] as $course)
                                        <tr>
                                            <td>{{ $course['name'] ?? 'اسم الدورة' }}</td>
                                            <td>
                                                @if(($course['type'] ?? 'online') === 'online')
                                                <span class="badge badge-online bg-success">أونلاين</span>
                                                @elseif(($course['type'] ?? 'open') === 'open')
                                                <span class="badge badge-open bg-warning">مفتوح</span>
                                                @else
                                                <span class="badge badge-closed bg-danger">مغلق</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if(($course['status'] ?? 'open') === 'open')
                                                <span class="badge bg-success">مفتوح</span>
                                                @elseif(($course['status'] ?? 'limited') === 'limited')
                                                <span class="badge bg-warning">محدود</span>
                                                @else
                                                <span class="badge bg-danger">مغلق</span>
                                                @endif
                                            </td>
                                            <td>{{ $course['start_date'] ?? '01/01/2023' }}</td>
                                            <td>
                                                <a class="btn btn-sm btn-outline-primary action-btn" href="{{ route('admin.courses.show', $course['id']) }}"><i class="fas fa-eye"></i> عرض</a>
                                                <a class="btn btn-sm btn-outline-secondary action-btn" href="{{ route('admin.courses.edit', $course['id']) }}"><i class="fas fa-edit"></i> تعديل</a>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">لا توجد دورات متاحة</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Recent Hifz Activity -->
                    <div class="card mt-4">
                        <div class="card-body">
                            <div class="section-header">
                                <h3 class="section-title"><i class="fas fa-quran"></i> نشاط الحفظ الحديث</h3>
                                <a href="#" class="view-all-btn">عرض جميع الأنشطة <i class="fas fa-arrow-left"></i></a>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>اسم الطالب</th>
                                            <th>اسم الشيخ</th>
                                            <th>التاريخ</th>
                                            <th>التقييم</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($recentHifzLog ?? [] as $activity)
                                        <tr>
                                            <td>{{ $activity['student_name'] ?? 'طالب' }}</td>
                                            <td>{{ $activity['sheikh_name'] ?? 'شيخ' }}</td>
                                            <td>{{ $activity['date'] ?? '01/01/2023' }}</td>
                                            <td>
                                                @php
                                                    $rating = $activity['rating'] ?? 3;
                                                    $stars = str_repeat('★', $rating) . str_repeat('☆', 5 - $rating);
                                                @endphp
                                                <span class="star-rating">{{ $stars }}</span> ({{ $rating }}/5)
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-4">لا توجد أنشطة حفظ حديثة</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Actions & Notifications -->
                <div class="col-lg-4">
                    <!-- Quick Actions -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <h3 class="section-title"><i class="fas fa-bolt"></i> إجراءات سريعة</h3>
                            <div class="quick-actions">
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCourseModal">
                                <i class="fas fa-plus"></i> إضافة دورة جديدة
                            </button>
                            <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#addMosqueModal">
                                <i class="fas fa-mosque"></i> إضافة مسجد
                            </button>
                            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addSheikhModal">
                                <i class="fas fa-user-plus"></i> إضافة شيخ جديد
                            </button>
                            <button class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#addAdminModal">
                                <i class="fas fa-user-shield"></i> إضافة مدير جديد
                            </button>
                            <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#sendNotificationModal">
                                <i class="fas fa-bell"></i> إرسال إشعار
                            </button>
                                <button class="btn btn-warning">
                                    <i class="fas fa-file-export"></i> تصدير التقارير
                                </button>
                                <button class="btn btn-dark">
                                    <i class="fas fa-cog"></i> إعدادات النظام
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Notifications Log -->
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h3 class="section-title mb-0"><i class="fas fa-bell"></i> سجل الإشعارات</h3>
                                <span class="badge bg-primary position-relative">
                                    {{ count($notifications ?? []) }} جديد
                                    <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle">
                                        <span class="visually-hidden">إشعارات جديدة</span>
                                    </span>
                                </span>
                            </div>
                            
                            <div class="notification-list">
                                @forelse($notifications ?? [] as $notification)
                                <div class="notification-item {{ $notification['created_at'] ? 'new' : '' }}">
                                    <div class="d-flex justify-content-between">
                                        <strong>{{ $notification['title'] ?? 'عنوان الإشعار' }}</strong>
                                        <small class="text-muted">{{ $notification['time'] ?? 'منذ ساعة' }}</small>
                                    </div>
                                    <p class="mb-1">{{ $notification['message'] ?? 'محتوى الإشعار هنا' }}</p>
                                    <span class="badge 
                                        @if(($notification['target'] ?? 'all') === 'all') bg-info
                                        @elseif(($notification['target'] ?? 'students') === 'students') bg-primary
                                        @else bg-success
                                        @endif">
                                        {{ $notification['target'] ?? 'الجميع' }}
                                    </span>
                                </div>
                                @empty
                                <div class="text-center text-muted py-3">
                                    <i class="fas fa-bell-slash fa-2x mb-2"></i>
                                    <p>لا توجد إشعارات</p>
                                </div>
                                @endforelse
                            </div>
                            
                            <div class="text-center mt-3">
                                <a href="#" class="btn btn-outline-primary">عرض جميع الإشعارات</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap & jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Set current date in Arabic
        const event = new Date();
        const options = { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        };
        const arabicDate = event.toLocaleDateString('ar-SA', options);
        document.getElementById('current-date').textContent = arabicDate;
        
        // Simple script to handle active states
        $(document).ready(function() {
            // Mark notifications as read
            $('.notification-item').on('click', function() {
                $(this).removeClass('new');
            });
        });
    </script>

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
          @error('name')
            <div class="invalid-feedback d-block">{{ $message }}</div>
          @enderror

          <input type="email" name="email"
                 class="form-control mb-2 @error('email') is-invalid @enderror"
                 placeholder="البريد الإلكتروني" value="{{ old('email') }}" required>
          @error('email')
            <div class="invalid-feedback d-block">{{ $message }}</div>
          @enderror

          <input type="text" name="phone"
                 class="form-control mb-2 @error('phone') is-invalid @enderror"
                 placeholder="رقم الهاتف" value="{{ old('phone') }}">
          @error('phone')
            <div class="invalid-feedback d-block">{{ $message }}</div>
          @enderror

          <input type="text" name="national_id"
                 class="form-control mb-2 @error('national_id') is-invalid @enderror"
                 placeholder="الرقم الوطني" value="{{ old('national_id') }}">
          @error('national_id')
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
          @error('profile_image')
            <div class="invalid-feedback d-block">{{ $message }}</div>
          @enderror

          <input type="password" name="password"
                 class="form-control mb-2 @error('password') is-invalid @enderror"
                 placeholder="كلمة المرور" required>
          @error('password')
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

<div class="modal fade" id="addCourseModal" tabindex="-1" aria-labelledby="addCourseModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" action="{{ route('admin.courses.store') }}" enctype="multipart/form-data">
      @csrf
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">إضافة دورة جديدة</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="text" name="name" class="form-control mb-2 @error('name', 'course') is-invalid @enderror" placeholder="اسم الدورة" value="{{ old('name') }}">
          @error('name', 'course') <div class="invalid-feedback">{{ $message }}</div> @enderror

          <select name="type" class="form-control mb-2 @error('type', 'course') is-invalid @enderror">
            <option value="online" {{ old('type') === 'online' ? 'selected' : '' }}>أونلاين</option>
            <option value="open" {{ old('type') === 'open' ? 'selected' : '' }}>مفتوحة</option>
            <option value="closed" {{ old('type') === 'closed' ? 'selected' : '' }}>مغلقة</option>
          </select>
          @error('type', 'course') <div class="invalid-feedback">{{ $message }}</div> @enderror

          <input type="date" name="start_date" class="form-control mb-2 @error('start_date', 'course') is-invalid @enderror" value="{{ old('start_date') }}">
          @error('start_date', 'course') <div class="invalid-feedback">{{ $message }}</div> @enderror

          <input type="date" name="end_date" class="form-control mb-2 @error('end_date', 'course') is-invalid @enderror" value="{{ old('end_date') }}">
          @error('end_date', 'course') <div class="invalid-feedback">{{ $message }}</div> @enderror

          <select name="mosque_id" class="form-control mb-2 @error('mosque_id', 'course') is-invalid @enderror">
            <option value="">اختر مسجد للدورة</option>
            @foreach($mosques as $mosque)
              <option value="{{ $mosque->id }}" {{ old('mosque_id') == $mosque->id ? 'selected' : '' }}>{{ $mosque->name }}</option>
            @endforeach
          </select>
          @error('mosque_id', 'course') <div class="invalid-feedback">{{ $message }}</div> @enderror

          <input type="number" name="max_students" class="form-control mb-2 @error('max_students', 'course') is-invalid @enderror" placeholder="اقصي عدد للطلاب " value="{{ old('max_students') }}">
          @error('max_students', 'course') <div class="invalid-feedback">{{ $message }}</div> @enderror

          <input type="file" name="image" class="form-control mb-2 @error('image', 'course') is-invalid @enderror">
          @error('image', 'course') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">إنشاء</button>
        </div>
      </div>
    </form>
  </div>
</div>


<!-- Admin Modal -->
<div class="modal fade @if($errors->hasBag('admin')) show d-block @endif" id="addAdminModal" tabindex="-1" aria-labelledby="addAdminModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" action="{{ route('admin.admins.store') }}">
      @csrf
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">إضافة مدير جديد</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          @if($errors->admin->any())
            <div class="alert alert-danger">
              <ul>
                @foreach ($errors->admin->all() as $error)
                  <li>{{ $error }}</li>
                @endforeach
              </ul>
            </div>
          @endif
          <input type="text" name="name" class="form-control mb-2" placeholder="الاسم الكامل" value="{{ old('name') }}" required>
          <input type="email" name="email" class="form-control mb-2" placeholder="البريد الإلكتروني" value="{{ old('email') }}" required>
          <input type="password" name="password" class="form-control mb-2" placeholder="كلمة المرور" required>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-dark">إضافة</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Notification Modal -->
<div class="modal fade @if($errors->hasBag('notification')) show d-block @endif" id="sendNotificationModal" tabindex="-1" aria-labelledby="sendNotificationModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" action="{{ route('admin.notifications.store') }}">
      @csrf
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">إرسال إشعار</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          @if($errors->notification->any())
            <div class="alert alert-danger">
              <ul>
                @foreach ($errors->notification->all() as $error)
                  <li>{{ $error }}</li>
                @endforeach
              </ul>
            </div>
          @endif
          <input type="text" name="title" class="form-control mb-2" placeholder="عنوان الإشعار" value="{{ old('title') }}" required>
          <textarea name="body" class="form-control mb-2" placeholder="محتوى الإشعار" required>{{ old('body') }}</textarea>
          <select name="target" class="form-control mb-2">
            <option value="students">الطلاب</option>
            <option value="sheikhs">المشايخ</option>
            <option value="all">الجميع</option>
          </select>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-info">إرسال</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Mosque Modal -->
<div class="modal fade" id="addMosqueModal" tabindex="-1" aria-labelledby="addMosqueModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="addMosqueModalLabel">
                    <i class="fas fa-mosque me-2"></i> إضافة مسجد جديد
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('mosques.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <!-- Basic Information Column -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="mosqueName" class="form-label fw-bold">اسم المسجد <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="mosqueName" name="name" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="mosqueDescription" class="form-label fw-bold">وصف المسجد</label>
                                <textarea class="form-control" id="mosqueDescription" name="description" rows="3"></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="mosquePhone" class="form-label fw-bold">رقم الهاتف</label>
                                <input type="text" class="form-control" id="mosquePhone" name="phone">
                            </div>
                            
                            <div class="mb-3">
                                <label for="mosqueEmail" class="form-label fw-bold">البريد الإلكتروني</label>
                                <input type="email" class="form-control" id="mosqueEmail" name="email">
                            </div>
                        </div>
                        
                        <!-- Location & Image Column -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="mosqueAddress" class="form-label fw-bold">العنوان <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="mosqueAddress" name="address" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="mosqueCity" class="form-label fw-bold">المدينة <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="mosqueCity" name="city" required>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="mosqueLatitude" class="form-label fw-bold">خط العرض</label>
                                    <input type="number" step="any" class="form-control" id="mosqueLatitude" name="latitude">
                                </div>
                                <div class="col-md-6">
                                    <label for="mosqueLongitude" class="form-label fw-bold">خط الطول</label>
                                    <input type="number" step="any" class="form-control" id="mosqueLongitude" name="longitude">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="mosqueImage" class="form-label fw-bold">صورة المسجد</label>
                                <input class="form-control" type="file" id="mosqueImage" name="image_path" accept="image/*">
                                <small class="text-muted">الصيغ المقبولة: JPG, PNG, GIF - الحجم الأقصى: 2MB</small>
                            </div>
                            
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="mosqueActive" name="is_active" value="1" checked>
                                <label class="form-check-label fw-bold" for="mosqueActive">الحالة النشطة</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> حفظ المسجد
                    </button>
                </div>

                
            <script>
// AJAX form submission for adding a mosque
$(document).ready(function() {
    $('#mosqueForm').on('submit', function(e) {
        e.preventDefault();
        
        var formData = new FormData(this);
        
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                $('#addMosqueModal').modal('hide');
                // Show success message
                alert('تم إضافة المسجد بنجاح');
                // Reset form
                $('#mosqueForm')[0].reset();
            },
            error: function(xhr) {
                // Show validation errors
                var errors = xhr.responseJSON.errors;
                $.each(errors, function(key, value) {
                    alert(value[0]);
                });
            }
        });
    });
});
            </script>
            </form>
        </div>
    </div>
</div>


<script>
    @if ($errors->hasBag('sheikh'))
  new bootstrap.Modal(document.getElementById('addSheikhModal')).show();
@endif

  @if ($errors->hasBag('course'))
    new bootstrap.Modal(document.getElementById('addCourseModal')).show();
  @endif

  @if ($errors->hasBag('admin'))
    new bootstrap.Modal(document.getElementById('addAdminModal')).show();
  @endif

  @if ($errors->hasBag('notification'))
    new bootstrap.Modal(document.getElementById('sendNotificationModal')).show();
  @endif
</script>

</body>
</html>