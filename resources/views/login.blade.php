<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>تسجيل الدخول - مركز الفرقان</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Cairo', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 40px;
            width: 100%;
            max-width: 450px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .logo-section {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        }

        .logo i {
            font-size: 35px;
            color: white;
        }

        .app-title {
            font-size: 28px;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 5px;
        }

        .app-subtitle {
            color: #718096;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #4a5568;
            font-weight: 600;
            font-size: 14px;
        }

        .input-wrapper {
            position: relative;
        }

        .form-control {
            width: 100%;
            padding: 15px 50px 15px 20px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: #f8fafc;
            color: #2d3748;
        }

        .form-control:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .input-icon {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: #a0aec0;
            font-size: 18px;
        }

        .password-toggle {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #a0aec0;
            cursor: pointer;
            font-size: 18px;
            transition: color 0.3s ease;
        }

        .password-toggle:hover {
            color: #667eea;
        }

        .remember-forgot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .remember-me input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: #667eea;
        }

        .forgot-link {
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .forgot-link:hover {
            color: #764ba2;
        }

        .login-btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
        }

        .login-btn:active {
            transform: translateY(0);
        }

        .login-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 2px solid transparent;
            border-top: 2px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-right: 10px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .alert {
            padding: 12px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            display: none;
        }

        .alert-error {
            background-color: #fed7d7;
            color: #c53030;
            border: 1px solid #feb2b2;
        }

        .alert-success {
            background-color: #c6f6d5;
            color: #2f855a;
            border: 1px solid #9ae6b4;
        }

        .role-selector {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 25px;
        }

        .role-option {
            padding: 15px 10px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: #f8fafc;
        }

        .role-option:hover {
            border-color: #667eea;
            background: white;
        }

        .role-option.active {
            border-color: #667eea;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
            color: #667eea;
        }

        .role-option i {
            font-size: 20px;
            margin-bottom: 5px;
            display: block;
        }

        .role-option span {
            font-size: 12px;
            font-weight: 600;
        }

        @media (max-width: 480px) {
            .login-container {
                padding: 30px 20px;
                margin: 10px;
            }
            
            .role-selector {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo-section">
            <div class="logo">
                <i class="fas fa-quran-open"></i>
            </div>
            <h1 class="app-title">مركز الفرقان</h1>
            <p class="app-subtitle">لتحفيظ القرآن الكريم</p>
        </div>

        <div class="alert alert-error" id="error-alert"></div>
        <div class="alert alert-success" id="success-alert"></div>

        <form id="loginForm" method="POST">
            @csrf
            
            <div class="form-group">
                <label>نوع المستخدم</label>
                <div class="role-selector">
                    <div class="role-option active" data-role="student">
                        <i class="fas fa-user-graduate"></i>
                        <span>طالب</span>
                    </div>
                    <div class="role-option" data-role="sheikh">
                        <i class="fas fa-chalkboard-teacher"></i>
                        <span>شيخ</span>
                    </div>
                    <div class="role-option" data-role="admin">
                        <i class="fas fa-user-cog"></i>
                        <span>مدير</span>
                    </div>
                    <div class="role-option" data-role="supervisor">
                        <i class="fas fa-user-shield"></i>
                        <span>مشرف</span>
                    </div>
                </div>
                <input type="hidden" name="role" id="selected-role" value="student">
            </div>

            <div class="form-group">
                <label for="email">البريد الإلكتروني أو رقم الهاتف</label>
                <div class="input-wrapper">
                    <input type="text" class="form-control" id="email" name="email" required autocomplete="username">
                    <i class="fas fa-envelope input-icon"></i>
                </div>
            </div>

            <div class="form-group">
                <label for="password">كلمة المرور</label>
                <div class="input-wrapper">
                    <input type="password" class="form-control" id="password" name="password" required autocomplete="current-password">
                    <button type="button" class="password-toggle" onclick="togglePassword()">
                        <i class="fas fa-eye" id="password-icon"></i>
                    </button>
                </div>
            </div>

            <div class="remember-forgot">
                <div class="remember-me">
                    <input type="checkbox" id="remember" name="remember">
                    <label for="remember">تذكرني</label>
                </div>
                <a href="#" class="forgot-link">نسيت كلمة المرور؟</a>
            </div>

            <button type="submit" class="login-btn" id="login-btn">
                <span class="spinner" id="spinner"></span>
                <span id="btn-text">تسجيل الدخول</span>
            </button>
        </form>
    </div>

    <script>
        // Role selection
        document.querySelectorAll('.role-option').forEach(option => {
            option.addEventListener('click', function() {
                document.querySelectorAll('.role-option').forEach(opt => opt.classList.remove('active'));
                this.classList.add('active');
                document.getElementById('selected-role').value = this.dataset.role;
            });
        });

        // Password toggle
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const passwordIcon = document.getElementById('password-icon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                passwordIcon.classList.remove('fa-eye');
                passwordIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                passwordIcon.classList.remove('fa-eye-slash');
                passwordIcon.classList.add('fa-eye');
            }
        }

        // Form submission
        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const btn = document.getElementById('login-btn');
            const spinner = document.getElementById('spinner');
            const btnText = document.getElementById('btn-text');
            const errorAlert = document.getElementById('error-alert');
            const successAlert = document.getElementById('success-alert');
            
            // Hide alerts
            errorAlert.style.display = 'none';
            successAlert.style.display = 'none';
            
            // Show loading
            btn.disabled = true;
            spinner.style.display = 'inline-block';
            btnText.textContent = 'جاري تسجيل الدخول...';
            
            try {
                const formData = new FormData(this);
                const response = await fetch('{{ route("login.post") }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    successAlert.textContent = 'تم تسجيل الدخول بنجاح! جاري التحويل...';
                    successAlert.style.display = 'block';
                    
                    setTimeout(() => {
                        window.location.href = data.redirect || '/dashboard';
                    }, 1500);
                } else {
                    throw new Error(data.message || 'حدث خطأ في تسجيل الدخول');
                }
            } catch (error) {
                errorAlert.textContent = error.message || 'حدث خطأ في تسجيل الدخول. يرجى المحاولة مرة أخرى.';
                errorAlert.style.display = 'block';
            } finally {
                // Hide loading
                btn.disabled = false;
                spinner.style.display = 'none';
                btnText.textContent = 'تسجيل الدخول';
            }
        });

        // Auto-detect input type (email or phone)
        document.getElementById('email').addEventListener('input', function() {
            const value = this.value;
            const icon = this.parentElement.querySelector('.input-icon');
            
            if (/^\d+$/.test(value)) {
                icon.className = 'fas fa-phone input-icon';
            } else {
                icon.className = 'fas fa-envelope input-icon';
            }
        });
    </script>
</body>
</html>