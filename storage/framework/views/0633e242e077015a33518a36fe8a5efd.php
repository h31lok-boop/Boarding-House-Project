
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Sunshine Boarding House</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    <style>
        :root {
            --primary: #ff7e5f;
            --secondary: #feb47b;
            --dark: #333;
            --light: #f9f9f9;
            --shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .login-container {
            display: flex;
            width: 100%;
            max-width: 1000px;
            min-height: 600px;
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: var(--shadow);
        }

        .login-left {
            flex: 1;
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), 
                        url('https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?ixlib=rb-4.0.3&auto=format&fit=crop&w=1170&q=80');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .login-right {
            flex: 1;
            padding: 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .logo {
            display: flex;
            align-items: center;
            margin-bottom: 30px;
            color: var(--primary);
            font-size: 1.8rem;
            font-weight: 700;
        }

        .logo i {
            margin-right: 10px;
            font-size: 2rem;
        }

        .logo span {
            color: var(--dark);
        }

        .welcome-text h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            color: white;
        }

        .welcome-text p {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .login-form h2 {
            font-size: 2rem;
            margin-bottom: 10px;
            color: var(--dark);
        }

        .login-subtitle {
            color: #666;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark);
        }

        .input-with-icon {
            position: relative;
        }

        .input-with-icon i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
        }

        .form-control {
            width: 100%;
            padding: 15px 15px 15px 45px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(255, 126, 95, 0.1);
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
        }

        .remember-me input {
            margin-right: 8px;
        }

        .forgot-password {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
        }

        .forgot-password:hover {
            text-decoration: underline;
        }

        .btn-login {
            width: 100%;
            padding: 15px;
            background: linear-gradient(to right, var(--primary), var(--secondary));
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-login:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(255, 126, 95, 0.3);
        }

        .btn-login:active {
            transform: translateY(-1px);
        }

        .login-footer {
            margin-top: 30px;
            text-align: center;
            color: #666;
        }

        .login-footer a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
        }

        .login-footer a:hover {
            text-decoration: underline;
        }

        .role-selector {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .role-btn {
            flex: 1;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            background: white;
            cursor: pointer;
            text-align: center;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .role-btn.active {
            border-color: var(--primary);
            background: rgba(255, 126, 95, 0.1);
            color: var(--primary);
        }

        .alert {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-danger {
            background: #fee;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }

        .alert-success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }

        .alert i {
            font-size: 1.2rem;
        }

        .demo-credentials {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            margin-top: 20px;
            font-size: 0.9rem;
        }

        .demo-credentials h4 {
            margin-bottom: 10px;
            color: var(--dark);
        }

        .demo-account {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }

        @media (max-width: 768px) {
            .login-container {
                flex-direction: column;
                max-width: 95%;
                margin: 20px;
            }
            
            .login-left {
                padding: 30px;
            }
            
            .login-right {
                padding: 30px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <!-- Left Side - Branding -->
        <div class="login-left">
            <div class="logo">
                <i class="fas fa-home"></i>
                Sunshine<span>Boarding</span>
            </div>
            <div class="welcome-text">
                <h1>Welcome Back!</h1>
                <p>Access your boarding house account to manage your stay, payments, and community activities.</p>
            </div>
            <div class="features">
                <h3 style="margin-top: 40px; margin-bottom: 15px;">Features:</h3>
                <ul style="list-style: none;">
                    <li style="margin-bottom: 10px;"><i class="fas fa-check" style="margin-right: 10px;"></i> Room Management</li>
                    <li style="margin-bottom: 10px;"><i class="fas fa-check" style="margin-right: 10px;"></i> Payment Tracking</li>
                    <li style="margin-bottom: 10px;"><i class="fas fa-check" style="margin-right: 10px;"></i> Maintenance Requests</li>
                    <li style="margin-bottom: 10px;"><i class="fas fa-check" style="margin-right: 10px;"></i> Community Events</li>
                </ul>
            </div>
        </div>

        <!-- Right Side - Login Form -->
        <div class="login-right">
            <div class="login-form">
                <h2>Sign In</h2>
                <p class="login-subtitle">Enter your credentials to access your account</p>

                <!-- Session Status -->
                <?php if(session('status')): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?php echo e(session('status')); ?>

                    </div>
                <?php endif; ?>

                <!-- Validation Errors -->
                <?php if($errors->any()): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                        <div>
                            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div><?php echo e($error); ?></div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?php echo e(route('login')); ?>">
                    <?php echo csrf_field(); ?>

                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <div class="input-with-icon">
                            <i class="fas fa-envelope"></i>
                            <input id="email" class="form-control" type="email" name="email" 
                                   value="<?php echo e(old('email')); ?>" required autofocus placeholder="Enter your email">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="input-with-icon">
                            <i class="fas fa-lock"></i>
                            <input id="password" class="form-control" type="password" 
                                   name="password" required autocomplete="current-password" placeholder="Enter your password">
                        </div>
                    </div>

                    <div class="remember-forgot">
                        <label class="remember-me">
                            <input type="checkbox" name="remember">
                            <span>Remember me</span>
                        </label>
                        <?php if(Route::has('password.request')): ?>
                            <a class="forgot-password" href="<?php echo e(route('password.request')); ?>">
                                Forgot password?
                            </a>
                        <?php endif; ?>
                    </div>

                    <button type="submit" class="btn-login">
                        <i class="fas fa-sign-in-alt" style="margin-right: 8px;"></i> Sign In
                    </button>
                </form>

                <div class="login-footer">
                    <p>Need an account? <a href="<?php echo e(route('register')); ?>">Register here</a></p>
                    <p style="margin-top: 10px;">
                        <a href="<?php echo e(url('/')); ?>">← Back to Homepage</a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Form submission animation
        document.querySelector('form').addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('.btn-login');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Signing in...';
            submitBtn.disabled = true;
        });
    </script>
</body>
</html>
<?php /**PATH C:\Users\Hazel\Herd\final-project\resources\views/auth/login.blade.php ENDPATH**/ ?>