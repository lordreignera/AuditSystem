<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Health Audit System - Register</title>
    
    <!-- Admin Template CSS -->
    <link rel="stylesheet" href="{{ asset('admin/assets/vendors/mdi/css/materialdesignicons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/assets/vendors/css/vendor.bundle.base.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/assets/css/style.css') }}">
    
    <!-- Custom Register Styles -->
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            margin: 0;
            font-family: "Nunito", sans-serif;
        }
        
        .register-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .register-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            padding: 40px;
            width: 100%;
            max-width: 500px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .register-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .register-logo {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #4fd1c7 0%, #2c5282 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }
        
        .register-logo i {
            font-size: 30px;
            color: white;
        }
        
        .register-title {
            color: #2c5282;
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .register-subtitle {
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
            padding: 12px 15px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
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
        
        .btn-register {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #4fd1c7 0%, #2c5282 100%);
            border: none;
            border-radius: 10px;
            color: white;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s ease;
            margin-top: 10px;
        }
        
        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(79, 209, 199, 0.3);
        }
        
        .alert-custom {
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .alert-error {
            background-color: #fee2e2;
            border: 1px solid #fecaca;
            color: #991b1b;
        }
        
        .login-link {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
        }
        
        .login-link a {
            color: #4fd1c7;
            text-decoration: none;
            font-weight: 500;
        }
        
        .login-link a:hover {
            color: #2c5282;
            text-decoration: underline;
        }
        
        .terms-checkbox {
            display: flex;
            align-items: center;
            margin-top: 15px;
            margin-bottom: 20px;
        }
        
        .terms-checkbox input {
            margin-right: 10px;
        }
        
        .terms-checkbox label {
            color: #64748b;
            font-size: 14px;
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
    </style>
</head>
<body>
    <!-- Floating Background Elements -->
    <div class="floating-elements"></div>
    
    <div class="register-container">
        <div class="register-card">
            <div class="register-header">
                <div class="register-logo">
                    <i class="mdi mdi-account-plus"></i>
                </div>
                <h2 class="register-title">Create Account</h2>
                <p class="register-subtitle">Join the Health Audit System</p>
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
            
            <form method="POST" action="{{ route('register') }}">
                @csrf
                
                <!-- Name -->
                <div class="form-group">
                    <label for="name" class="form-label">Full Name</label>
                    <input id="name" 
                           type="text" 
                           name="name" 
                           value="{{ old('name') }}" 
                           class="form-control-custom" 
                           placeholder="Enter your full name"
                           required 
                           autofocus 
                           autocomplete="name">
                </div>
                
                <!-- Email -->
                <div class="form-group">
                    <label for="email" class="form-label">Email Address</label>
                    <input id="email" 
                           type="email" 
                           name="email" 
                           value="{{ old('email') }}" 
                           class="form-control-custom" 
                           placeholder="Enter your email"
                           required 
                           autocomplete="username">
                </div>
                
                <!-- Password -->
                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <input id="password" 
                           type="password" 
                           name="password" 
                           class="form-control-custom" 
                           placeholder="Enter your password"
                           required 
                           autocomplete="new-password">
                </div>
                
                <!-- Confirm Password -->
                <div class="form-group">
                    <label for="password_confirmation" class="form-label">Confirm Password</label>
                    <input id="password_confirmation" 
                           type="password" 
                           name="password_confirmation" 
                           class="form-control-custom" 
                           placeholder="Confirm your password"
                           required 
                           autocomplete="new-password">
                </div>
                
                @if (Laravel\Jetstream\Jetstream::hasTermsAndPrivacyPolicyFeature())
                    <div class="terms-checkbox">
                        <input type="checkbox" name="terms" id="terms" required>
                        <label for="terms">
                            I agree to the <a href="{{ route('terms.show') }}" target="_blank">Terms of Service</a> and <a href="{{ route('policy.show') }}" target="_blank">Privacy Policy</a>
                        </label>
                    </div>
                @endif
                
                <!-- Register Button -->
                <button type="submit" class="btn-register">
                    <i class="mdi mdi-account-plus"></i> Create Account
                </button>
            </form>
            
            <!-- Login Link -->
            <div class="login-link">
                <p style="color: #64748b; margin-bottom: 5px;">Already have an account?</p>
                <a href="{{ route('login') }}">Sign in to your account</a>
            </div>
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="{{ asset('admin/assets/vendors/js/vendor.bundle.base.js') }}"></script>
    <script src="{{ asset('admin/assets/js/misc.js') }}"></script>
</body>
</html>

            @if (Laravel\Jetstream\Jetstream::hasTermsAndPrivacyPolicyFeature())
                <div class="mt-4">
                    <x-label for="terms">
                        <div class="flex items-center">
                            <x-checkbox name="terms" id="terms" required />

                            <div class="ms-2">
                                {!! __('I agree to the :terms_of_service and :privacy_policy', [
                                        'terms_of_service' => '<a target="_blank" href="'.route('terms.show').'" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">'.__('Terms of Service').'</a>',
                                        'privacy_policy' => '<a target="_blank" href="'.route('policy.show').'" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">'.__('Privacy Policy').'</a>',
                                ]) !!}
                            </div>
                        </div>
                    </x-label>
                </div>
            @endif

            <div class="flex items-center justify-end mt-4">
                <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
                    {{ __('Already registered?') }}
                </a>

                <x-button class="ms-4">
                    {{ __('Register') }}
                </x-button>
            </div>
        </form>
    </x-authentication-card>
</x-guest-layout>
