<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Noriks Call Center</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            min-height: 100vh;
            display: flex;
            background: linear-gradient(135deg, #1e3a5f 0%, #0f172a 100%);
        }
        
        .login-container {
            display: flex;
            width: 100%;
            min-height: 100vh;
        }
        
        .login-left {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 40px;
            color: white;
        }
        
        .login-branding {
            text-align: center;
            margin-bottom: 60px;
        }
        
        .login-logo {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            font-weight: 700;
            margin: 0 auto 24px;
            box-shadow: 0 20px 40px rgba(59, 130, 246, 0.3);
        }
        
        .login-title {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 12px;
        }
        
        .login-subtitle {
            font-size: 16px;
            opacity: 0.7;
        }
        
        .login-features {
            display: flex;
            flex-direction: column;
            gap: 20px;
            max-width: 400px;
        }
        
        .login-feature {
            display: flex;
            align-items: flex-start;
            gap: 16px;
        }
        
        .login-feature-icon {
            width: 48px;
            height: 48px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
        }
        
        .login-feature h3 {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 4px;
        }
        
        .login-feature p {
            font-size: 14px;
            opacity: 0.7;
            line-height: 1.5;
        }
        
        .login-right {
            width: 480px;
            background: #fff;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 60px;
        }
        
        .login-form-header {
            margin-bottom: 40px;
        }
        
        .login-form-header h2 {
            font-size: 28px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 8px;
        }
        
        .login-form-header p {
            color: #64748b;
            font-size: 15px;
        }
        
        .form-group {
            margin-bottom: 24px;
        }
        
        .form-label {
            display: block;
            font-size: 14px;
            font-weight: 500;
            color: #1e293b;
            margin-bottom: 8px;
        }
        
        .form-input-wrapper {
            position: relative;
        }
        
        .form-input-wrapper i {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 16px;
        }
        
        .form-input {
            width: 100%;
            padding: 14px 16px 14px 48px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 15px;
            transition: all 0.2s;
            background: #f8fafc;
        }
        
        .form-input:focus {
            outline: none;
            border-color: #3b82f6;
            background: #fff;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
        }
        
        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 32px;
        }
        
        .remember-me {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }
        
        .remember-me input {
            width: 18px;
            height: 18px;
            accent-color: #3b82f6;
        }
        
        .remember-me span {
            font-size: 14px;
            color: #64748b;
        }
        
        .forgot-link {
            font-size: 14px;
            color: #3b82f6;
            text-decoration: none;
            font-weight: 500;
        }
        
        .forgot-link:hover {
            text-decoration: underline;
        }
        
        .login-btn {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(59, 130, 246, 0.3);
        }
        
        .login-btn:active {
            transform: translateY(0);
        }
        
        .error-message {
            background: #fee2e2;
            color: #dc2626;
            padding: 12px 16px;
            border-radius: 10px;
            font-size: 14px;
            margin-bottom: 24px;
            display: none;
        }
        
        .error-message.show {
            display: block;
        }
        
        @media (max-width: 992px) {
            .login-left {
                display: none;
            }
            
            .login-right {
                width: 100%;
                max-width: 480px;
                margin: 0 auto;
            }
        }
        
        @media (max-width: 576px) {
            .login-right {
                padding: 40px 24px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-left">
            <div class="login-branding">
                <div class="login-logo">N</div>
                <h1 class="login-title">Noriks Call Center</h1>
                <p class="login-subtitle">Manage your customer calls efficiently</p>
            </div>
            
            <div class="login-features">
                <div class="login-feature">
                    <div class="login-feature-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div>
                        <h3>Abandoned Carts</h3>
                        <p>Track and recover abandoned shopping carts from all stores</p>
                    </div>
                </div>
                <div class="login-feature">
                    <div class="login-feature-icon">
                        <i class="fas fa-user-slash"></i>
                    </div>
                    <div>
                        <h3>Suppressed Profiles</h3>
                        <p>Re-engage customers who unsubscribed from emails</p>
                    </div>
                </div>
                <div class="login-feature">
                    <div class="login-feature-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div>
                        <h3>Pending Orders</h3>
                        <p>Handle failed and pending orders efficiently</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="login-right">
            <div class="login-form-header">
                <h2>Welcome Back</h2>
                <p>Please enter your credentials to continue</p>
            </div>
            
            <div class="error-message" id="errorMessage">
                <i class="fas fa-exclamation-circle"></i>
                Invalid username or password
            </div>
            
            <form id="loginForm">
                <div class="form-group">
                    <label class="form-label">Username</label>
                    <div class="form-input-wrapper">
                        <i class="fas fa-user"></i>
                        <input type="text" class="form-input" id="username" placeholder="Enter your username" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <div class="form-input-wrapper">
                        <i class="fas fa-lock"></i>
                        <input type="password" class="form-input" id="password" placeholder="Enter your password" required>
                    </div>
                </div>
                
                <div class="form-options">
                    <label class="remember-me">
                        <input type="checkbox" id="remember">
                        <span>Remember me</span>
                    </label>
                </div>
                
                <button type="submit" class="login-btn">
                    <i class="fas fa-sign-in-alt"></i>
                    Sign In
                </button>
            </form>
        </div>
    </div>
    
    <script>
        // Check if already logged in
        if (localStorage.getItem('callcenter_user')) {
            window.location.href = 'index.php';
        }
        
        document.getElementById('loginForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;
            const errorEl = document.getElementById('errorMessage');
            
            try {
                const response = await fetch('api.php?action=login', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ username, password })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    localStorage.setItem('callcenter_user', JSON.stringify(data.user));
                    window.location.href = 'index.php';
                } else {
                    errorEl.classList.add('show');
                    setTimeout(() => errorEl.classList.remove('show'), 3000);
                }
            } catch (error) {
                errorEl.textContent = 'Connection error. Please try again.';
                errorEl.classList.add('show');
                setTimeout(() => errorEl.classList.remove('show'), 3000);
            }
        });
    </script>
</body>
</html>
