<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>ERA Health Audit Suite - Login</title>
    
    <!-- Admin Template CSS -->
    <link rel="stylesheet" href="{{ asset('admin/assets/vendors/mdi/css/materialdesignicons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/assets/vendors/css/vendor.bundle.base.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/assets/css/style.css') }}">
    
    <!-- Custom Login Styles -->
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            margin: 0;
            font-family: "Nunito", sans-serif;
        }
        
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            padding: 40px;
            width: 100%;
            max-width: 450px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .login-logo {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #4fd1c7 0%, #2c5282 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }
        
        .login-logo i {
            font-size: 35px;
            color: white;
        }
        
        .login-title {
            color: #2c5282;
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .login-subtitle {
            color: #64748b;
            font-size: 14px;
            margin-bottom: 0;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            color: #2c5282;
            font-weight: 600;
            margin-bottom: 8px;
            display: block;
            font-size: 14px;
        }
        
        .form-control-custom {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 14px;
            transition: all 0.3s ease;
            background: white;
            color: #2d3748;
        }
        
        .form-control-custom:focus {
            outline: none;
            border-color: #4fd1c7;
            box-shadow: 0 0 0 3px rgba(79, 209, 199, 0.1);
        }
        
        .btn-login {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #4fd1c7 0%, #2c5282 100%);
            border: none;
            border-radius: 12px;
            color: white;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s ease;
            margin-top: 10px;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(79, 209, 199, 0.3);
        }
        
        .remember-forgot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 15px;
            margin-bottom: 25px;
        }
        
        .remember-me {
            display: flex;
            align-items: center;
            color: #64748b;
            font-size: 14px;
        }
        
        .remember-me input {
            margin-right: 8px;
        }
        
        .forgot-password {
            color: #4fd1c7;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
        }
        
        .forgot-password:hover {
            color: #2c5282;
            text-decoration: underline;
        }
        
        .alert-custom {
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .alert-success {
            background-color: #d1fae5;
            border: 1px solid #a7f3d0;
            color: #065f46;
        }
        
        .alert-error {
            background-color: #fee2e2;
            border: 1px solid #fecaca;
            color: #991b1b;
        }
        
        .system-info {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
        }
        
        .system-info p {
            color: #64748b;
            font-size: 12px;
            margin-bottom: 5px;
        }
        
        .demo-credentials {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 15px;
            margin-top: 20px;
        }
        
        .demo-credentials h6 {
            color: #2c5282;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .demo-credentials p {
            color: #64748b;
            font-size: 12px;
            margin-bottom: 5px;
        }
        
        .floating-elements {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: -1;
        }
        
        .floating-elements::before,
        .floating-elements::after {
            content: '';
            position: absolute;
            width: 300px;
            height: 300px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
        }
        
        .floating-elements::before {
            top: -150px;
            right: -150px;
            animation: float 6s ease-in-out infinite;
        }
        
        .floating-elements::after {
            bottom: -150px;
            left: -150px;
            animation: float 6s ease-in-out infinite reverse;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        
        .input-group {
            position: relative;
        }
        
        .input-group i {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: #cbd5e0;
            z-index: 2;
        }
        
        .input-group .form-control-custom {
            padding-left: 50px;
        }
    </style>
</head>
<body>
    <!-- Floating Background Elements -->
    <div class="floating-elements"></div>
    
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="login-logo">
                    <i class="mdi mdi-hospital-building"></i>
                </div>
                <h2 class="login-title">ERA Health Audit Suite</h2>
                <p class="login-subtitle">Sign in to access your dashboard</p>
            </div>
            
            <!-- Validation Errors -->
            @if ($errors->any())
                <div class="alert-custom alert-error">
                    <ul style="margin: 0; padding-left: 20px;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            
            <!-- Status Message -->
            @if (session('status'))
                <div class="alert-custom alert-success">
                    {{ session('status') }}
                </div>
            @endif
            
            <form method="POST" action="{{ route('login') }}">
                @csrf
                
                <!-- Email -->
                <div class="form-group">
                    <label for="email" class="form-label">Email Address</label>
                    <div class="input-group">
                        <i class="mdi mdi-email"></i>
                        <input id="email" 
                               type="email" 
                               name="email" 
                               value="{{ old('email') }}" 
                               class="form-control-custom" 
                               placeholder="Enter your email"
                               required 
                               autofocus 
                               autocomplete="username">
                    </div>
                </div>
                
                <!-- Password -->
                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <i class="mdi mdi-lock"></i>
                        <input id="password" 
                               type="password" 
                               name="password" 
                               class="form-control-custom" 
                               placeholder="Enter your password"
                               required 
                               autocomplete="current-password">
                    </div>
                </div>
                
                <!-- Remember Me & Forgot Password -->
                <div class="remember-forgot">
                    <label class="remember-me">
                        <input type="checkbox" id="remember_me" name="remember">
                        Remember me
                    </label>
                    
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="forgot-password">
                            Forgot Password?
                        </a>
                    @endif
                </div>
                
                <!-- Login Button -->
                <button type="submit" class="btn-login">
                    <i class="mdi mdi-login"></i> Sign In
                </button>
            </form>
            
            <!-- Demo Credentials 
            <div class="demo-credentials">
                <h6><i class="mdi mdi-information"></i> Demo Credentials</h6>
                <p><strong>Super Admin:</strong> superadmin@audit.com / SuperAdmin123!</p>
                <p><strong>Admin:</strong> admin@audit.com / Admin123!</p>
                <p><strong>Audit Manager:</strong> manager@audit.com / Manager123!</p>
                <p><strong>Auditor:</strong> auditor@audit.com / Auditor123!</p>
            </div> -->
            
            <!-- System Info -->
            <div class="system-info">
                <p>&copy; {{ date('Y') }} Health Audit System. All rights reserved.</p>
                <p>Secure healthcare auditing platform</p>
            </div>
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="{{ asset('admin/assets/vendors/js/vendor.bundle.base.js') }}"></script>
    <script src="{{ asset('admin/assets/js/misc.js') }}"></script>
</body>
</html>
