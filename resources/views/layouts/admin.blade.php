<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') | مركز الفرقان</title>
    
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
            --sidebar-width: 250px;
            --topbar-height: 70px;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Cairo', sans-serif;
            background-color: #f8f9fa;
            min-height: 100vh;
            overflow-x: hidden;
            background-image: linear-gradient(to bottom, rgba(216, 243, 220, 0.1), rgba(216, 243, 220, 0.3)), url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100"><rect width="100" height="100" fill="white"/><path d="M20,20 Q50,5 80,20 T100,50 T80,80 T50,100 T20,80 T0,50 T20,20 Z" fill="none" stroke="%23d8f3dc" stroke-width="0.5"/></svg>');
        }
        
        /* Admin Layout Structure */
        .admin-layout {
            display: flex;
            min-height: 100vh;
            position: relative;
        }
        
        /* Sidebar Styles */
        .admin-sidebar {
            width: var(--sidebar-width);
            background: linear-gradient(to bottom, var(--dark), var(--primary));
            color: white;
            position: fixed;
            right: 0;
            top: 0;
            height: 100vh;
            transition: all 0.3s ease;
            z-index: 1040;
            box-shadow: -3px 0 10px rgba(0, 0, 0, 0.1);
            overflow-y: auto;
        }
        
        .sidebar-logo {
            height: var(--topbar-height);
            display: flex;
            align-items: center;
            justify-content: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            position: sticky;
            top: 0;
            background: var(--dark);
            z-index: 10;
        }
        
        .sidebar-logo h3 {
            font-weight: 700;
            font-size: 1.5rem;
            margin: 0;
            color: white;
        }
        
        .sidebar-logo span {
            color: var(--gold);
        }
        
        .sidebar-nav {
            padding: 20px 0;
        }
        
        .nav-item {
            margin-bottom: 5px;
        }
        
        .nav-link {
            color: rgba(255, 255, 255, 0.85);
            padding: 12px 20px;
            display: flex;
            align-items: center;
            transition: all 0.3s ease;
            border-right: 3px solid transparent;
            text-decoration: none;
        }
        
        .nav-link:hover, .nav-link.active {
            color: white;
            background: rgba(255, 255, 255, 0.1);
            border-right: 3px solid var(--gold);
        }
        
        .nav-link i {
            margin-left: 10px;
            font-size: 1.2rem;
            width: 25px;
            text-align: center;
        }
        
        /* Main Content Area */
        .admin-main {
            flex: 1;
            margin-right: var(--sidebar-width);
            padding-top: var(--topbar-height);
            width: 100%;
            transition: margin-right 0.3s ease;
        }
        
        /* Topbar Styles */
        .admin-topbar {
            height: var(--topbar-height);
            background-color: white;
            position: fixed;
            top: 0;
            right: var(--sidebar-width);
            left: 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 25px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            z-index: 1030;
            transition: all 0.3s ease;
        }
        
        .topbar-left {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .toggle-sidebar {
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--dark);
            cursor: pointer;
            padding: 8px 12px;
            border-radius: 5px;
            transition: all 0.3s ease;
            display: none;
        }
        
        .toggle-sidebar:hover {
            background: var(--light);
        }
        
        .topbar-title {
            margin: 0;
            font-weight: 600;
            color: var(--dark);
            font-size: 1.3rem;
        }
        
        .topbar-right {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .user-dropdown {
            display: flex;
            align-items: center;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            margin-left: 10px;
        }
        
        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            width: 20px;
            height: 20px;
            background: #e74c3c;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            font-weight: bold;
        }
        
        /* Sidebar Overlay for Mobile */
        .sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1035;
            display: none;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .sidebar-overlay.active {
            display: block;
            opacity: 1;
        }
        
        /* Content Area */
        .admin-content {
            padding: 25px;
            min-height: calc(100vh - var(--topbar-height));
        }
        
        .content-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--light);
        }
        
        .page-title {
            font-weight: 700;
            color: var(--dark);
            font-size: 1.8rem;
            display: flex;
            align-items: center;
        }
        
        .page-title i {
            margin-left: 10px;
            background: var(--light);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
        }
        
        .breadcrumb {
            background: transparent;
            padding: 0;
            margin: 0;
        }
        
        .breadcrumb-item a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }
        
        .breadcrumb-item.active {
            color: var(--dark);
        }
        
        /* Card Styles */
        .admin-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            margin-bottom: 25px;
            overflow: hidden;
            background-color: white;
            border-top: 4px solid var(--primary);
        }
        
        .admin-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.12);
        }
        
        .card-header {
            background-color: white;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            padding: 15px 20px;
            font-weight: 700;
            color: var(--dark);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .card-header i {
            color: var(--primary);
        }
        
        .card-body {
            padding: 20px;
        }
        
        /* Footer */
        .admin-footer {
            background-color: var(--dark);
            color: white;
            padding: 15px 25px;
            text-align: center;
        }
        
        /* Buttons */
        .btn-primary {
            background-color: var(--primary);
            border-color: var(--primary);
        }
        
        .btn-primary:hover {
            background-color: var(--dark);
            border-color: var(--dark);
        }
        
        .btn-outline-primary {
            color: var(--primary);
            border-color: var(--primary);
        }
        
        .btn-outline-primary:hover {
            background-color: var(--primary);
            border-color: var(--primary);
            color: white;
        }
        
        /* Table Styles */
        .admin-table {
            width: 100%;
        }
        
        .admin-table th {
            background-color: var(--primary);
            color: white;
            font-weight: 600;
            border: none;
        }
        
        .admin-table td, .admin-table th {
            padding: 12px 15px;
            text-align: right;
        }
        
        /* Form Styles */
        .form-label {
            font-weight: 600;
            color: var(--dark);
        }
        
        /* Utility Classes */
        .badge-success {
            background-color: #2a9d8f;
        }
        
        .badge-warning {
            background-color: #e9c46a;
        }
        
        .badge-danger {
            background-color: #e76f51;
        }
        
        /* ============================================
           RESPONSIVE MOBILE STYLES
        ============================================ */
        
        /* Tablet Breakpoint */
        @media (max-width: 1024px) {
            .admin-content {
                padding: 20px;
            }
            
            .topbar-title {
                font-size: 1.1rem;
            }
        }
        
        /* Mobile Breakpoint */
        @media (max-width: 992px) {
            :root {
                --sidebar-width: 280px;
            }
            
            /* Hide sidebar by default on mobile */
            .admin-sidebar {
                transform: translateX(100%);
            }
            
            /* Show sidebar when active */
            .admin-sidebar.active {
                transform: translateX(0);
            }
            
            /* Remove margin from main content */
            .admin-main {
                margin-right: 0;
            }
            
            /* Adjust topbar */
            .admin-topbar {
                right: 0;
                left: 0;
                padding: 0 15px;
            }
            
            /* Show toggle button */
            .toggle-sidebar {
                display: block;
            }
            
            /* Adjust content padding */
            .admin-content {
                padding: 15px;
            }
            
            /* Stack breadcrumb items */
            .breadcrumb {
                flex-wrap: wrap;
            }
        }
        
        /* Small Mobile Breakpoint */
        @media (max-width: 576px) {
            :root {
                --sidebar-width: 85vw;
                --topbar-height: 60px;
            }
            
            .sidebar-logo h3 {
                font-size: 1.2rem;
            }
            
            .topbar-title {
                font-size: 0.95rem;
                max-width: 150px;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }
            
            .user-dropdown .d-block {
                display: none !important;
            }
            
            .user-avatar {
                margin-left: 0;
            }
            
            .admin-content {
                padding: 10px;
            }
            
            .page-title {
                font-size: 1.3rem;
            }
            
            .breadcrumb {
                font-size: 0.85rem;
            }
            
            .nav-link {
                padding: 10px 15px;
                font-size: 0.95rem;
            }
            
            .card-body {
                padding: 15px;
            }
            
            /* Hide notification badge number on very small screens */
            .notification-badge {
                font-size: 0.6rem;
                width: 18px;
                height: 18px;
            }
            
            /* Make tables responsive */
            .admin-table {
                font-size: 0.85rem;
            }
            
            .admin-table td, .admin-table th {
                padding: 8px 10px;
            }
        }
        
        /* Extra Small Devices */
        @media (max-width: 375px) {
            .topbar-title {
                font-size: 0.85rem;
                max-width: 120px;
            }
            
            .toggle-sidebar {
                font-size: 1.3rem;
                padding: 5px 8px;
            }
            
            .nav-link {
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    
    <div class="admin-layout">
        <!-- Sidebar -->
        <aside class="admin-sidebar" id="adminSidebar">
            <div class="sidebar-logo">
                <h3>مركز <span>الفرقان</span></h3>
            </div>
            
            <nav class="sidebar-nav">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a href="{{ route('admin.dashboard') }}" class="nav-link">
                            <i class="fas fa-home"></i>
                            <span>الرئيسية</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('mosques.index') }}"
                         class="nav-link {{ request()->routeIs('mosques.*') ? 'active' : '' }}">
                            <i class="fas fa-mosque"></i>
                            <span>إدارة المساجد</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.courses.index') }}"
                         class="nav-link {{ request()->routeIs('admin.courses.*') ? 'active' : '' }}">
                            <i class="fas fa-book"></i>
                            <span>الدورات التعليمية</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.enrollments.index') }}"
                         class="nav-link {{ request()->routeIs('admin.enrollments.*') ? 'active' : '' }}">
                            <i class="fas fa-user-check"></i>
                            <span>إدارة طلبات التسجيل</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.admins.index') }}"
                         class="nav-link {{ request()->routeIs('admin.admins.*') ? 'active' : '' }}">
                            <i class="fas fa-user-shield"></i>
                            <span>إدارة مشرفي المنصة</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.students.index') }}"
                         class="nav-link {{ request()->routeIs('admin.students.*') ? 'active' : '' }}">
                            <i class="fas fa-users"></i>
                            <span>إدارة الطلاب</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.sheikhs.index') }}"
                         class="nav-link {{ request()->routeIs('admin.sheikhs.*') ? 'active' : '' }}">
                            <i class="fas fa-user-tie"></i>
                            <span>إدارة المشايخ</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.hifz_logs.index') }}"
                         class="nav-link {{ request()->routeIs('admin.hifz_logs.*') ? 'active' : '' }}">
                            <i class="fas fa-quran"></i>
                            <span>سجل الحفظ</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.review_logs.index') }}" 
                         class="nav-link {{ request()->routeIs('admin.review_logs.*') ? 'active' : '' }}">
                            <i class="fas fa-book-reader"></i>
                            <span>سجل المراجعة</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.notifications.index') }}" 
                         class="nav-link {{ request()->routeIs('admin.notifications.*') ? 'active' : '' }}">
                            <i class="fas fa-bell"></i>
                            <span>الإشعارات</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="nav-link">
                            <i class="fas fa-chart-bar"></i>
                            <span>التقارير والإحصائيات</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="nav-link">
                            <i class="fas fa-cog"></i>
                            <span>إعدادات النظام</span>
                        </a>
                    </li>
                    <li class="nav-item mt-4">
                        <a href="{{ route('logout') }}" class="nav-link">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>تسجيل الخروج</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>
        
        <!-- Main Content -->
        <main class="admin-main">
            <!-- Topbar -->
            <header class="admin-topbar">
                <div class="topbar-left">
                    <button class="toggle-sidebar" id="toggleSidebar" aria-label="Toggle Sidebar">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h2 class="topbar-title">@yield('title')</h2>
                </div>
                
                <div class="topbar-right">
                    <div class="dropdown me-3 position-relative">
                        <a href="#" class="text-dark fs-4" aria-label="Notifications">
                            <i class="fas fa-bell"></i>
                            <span class="notification-badge">3</span>
                        </a>
                    </div>
                    
                    <div class="dropdown user-dropdown">
                        <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="user-avatar">م</div>
                            <div class="d-none d-md-block">
                                <span class="d-block fw-bold">مدير النظام</span>
                                <small class="text-muted">الإدارة العامة</small>
                            </div>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i> الملف الشخصي</a></li>
                            <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i> الإعدادات</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="{{ route('logout') }}"><i class="fas fa-sign-out-alt me-2"></i> تسجيل الخروج</a></li>
                        </ul>
                    </div>
                </div>
            </header>
            
            <!-- Content -->
            <div class="admin-content">
                <!-- Breadcrumb -->
                <nav aria-label="breadcrumb" class="mb-4">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i> الرئيسية</a></li>
                        @yield('breadcrumb')
                        <li class="breadcrumb-item active" aria-current="page">@yield('title')</li>
                    </ol>
                </nav>
                
                <!-- Page Content -->
                @yield('content')
            </div>
            
            <!-- Footer -->
            <footer class="admin-footer">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-12">
                            <p class="mb-0">© 2024 مركز الفرقان للقرآن الكريم. جميع الحقوق محفوظة.</p>
                        </div>
                    </div>
                </div>
            </footer>
        </main>
    </div>
    
    <!-- Bootstrap & jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        $(document).ready(function() {
            const sidebar = $('#adminSidebar');
            const overlay = $('#sidebarOverlay');
            const toggleBtn = $('#toggleSidebar');
            
            // Toggle sidebar
            function toggleSidebar() {
                sidebar.toggleClass('active');
                overlay.toggleClass('active');
                $('body').toggleClass('overflow-hidden');
            }
            
            // Toggle button click
            toggleBtn.on('click', function() {
                toggleSidebar();
            });
            
            // Overlay click to close
            overlay.on('click', function() {
                toggleSidebar();
            });
            
            // Close sidebar when clicking a link on mobile
            if (window.innerWidth <= 992) {
                $('.nav-link').on('click', function() {
                    setTimeout(function() {
                        sidebar.removeClass('active');
                        overlay.removeClass('active');
                        $('body').removeClass('overflow-hidden');
                    }, 200);
                });
            }
            
            // Handle window resize
            $(window).on('resize', function() {
                if (window.innerWidth > 992) {
                    sidebar.removeClass('active');
                    overlay.removeClass('active');
                    $('body').removeClass('overflow-hidden');
                }
            });
            
            // Set current Arabic date
            const event = new Date();
            const options = { 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric'
            };
            const arabicDate = event.toLocaleDateString('ar-SA', options);
            $('#current-date').text(arabicDate);
        });
    </script>
    
    @yield('scripts')
</body>
</html>