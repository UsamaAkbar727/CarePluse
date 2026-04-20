<?php
session_start();
require_once 'config.php';
require_once 'includes/functions.php';

$error = '';

if (isset($_POST['login'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token.';
    } elseif (empty(trim($_POST['username'])) || empty($_POST['password'])) {
        $error = 'Please fill all fields.';
    } else {
        $pdo = get_db_pdo();
        $stmt = $pdo->prepare('SELECT id, username, password, role, email, full_name FROM users WHERE username = ? AND is_active = 1');
        $stmt->execute([trim($_POST['username'])]);
        $user = $stmt->fetch();

        if ($user && password_verify($_POST['password'], $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['csrf_token'] = generate_csrf_token();
            session_regenerate_id(true);

            // Update last login
            $stmt = $pdo->prepare('UPDATE users SET last_login = NOW() WHERE id = ?');
            $stmt->execute([$user['id']]);

            // Role-based redirect
            $redirect = match ($user['role']) {
                'admin', 'receptionist' => 'index.php',
                'doctor' => 'appointments.php',
                default => 'index.php'
            };
            header("Location: $redirect");
            exit();
        } else {
            $_SESSION['login_error'] = 'Invalid username or password.';
            header("Location: login.php");
            exit();
        }
    }
}

// Get and clear flash error
$error = $_SESSION['login_error'] ?? '';
unset($_SESSION['login_error']);

$token = generate_csrf_token();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CarePulse | Login</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        :root {
            --bg: #f8fafc;
            --accent: #4f46e5;
            --accent-glow: rgba(79, 70, 229, 0.2);
            --text-dark: #0f172a;
            --text-muted: #64748b;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-dark);
        }

        .login-wrapper {
            width: 100%;
            max-width: 440px;
            padding: 20px;
        }

        .login-card {
            background: #ffffff;
            border-radius: 24px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.04), 0 1px 3px rgba(0, 0, 0, 0.05);
            padding: 48px 40px;
            position: relative;
            overflow: hidden;
        }

        .login-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 6px;
            background: linear-gradient(90deg, #4f46e5, #ec4899);
        }

        .brand-icon {
            width: 64px; height: 64px;
            background: linear-gradient(135deg, var(--accent), #6366f1);
            border-radius: 18px;
            display: flex; align-items: center; justify-content: center;
            color: #ffffff; font-size: 28px;
            box-shadow: 0 8px 25px var(--accent-glow);
            margin: 0 auto 24px;
        }

        .form-control {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 14px 18px;
            border-radius: 12px;
            font-size: 15px;
            transition: all 0.2s;
        }

        .form-control:focus {
            background: #ffffff;
            border-color: var(--accent);
            box-shadow: 0 0 0 4px var(--accent-glow);
        }

        .form-label {
            font-size: 13px;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }

        .btn-login {
            background: var(--accent);
            border: none;
            padding: 14px;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 600;
            color: #ffffff;
            box-shadow: 0 6px 20px var(--accent-glow);
            transition: all 0.2s;
        }

        .btn-login:hover {
            background: #4338ca;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px var(--accent-glow);
        }

        .alert-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #dc2626;
            border-radius: 12px;
            padding: 12px 16px;
            font-size: 14px;
            font-weight: 500;
            display: flex; align-items: center; gap: 10px;
            margin-bottom: 24px;
        }

        .password-field-container {
            position: relative;
            display: flex;
            align-items: center;
        }

        .password-field-container .form-control {
            padding-right: 48px;
            width: 100%;
        }

        .password-toggle-icon {
            position: absolute;
            right: 14px;
            background: none;
            border: none;
            padding: 0;
            color: #94a3b8;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            z-index: 10;
        }

        .password-toggle-icon:hover {
            color: var(--accent);
        }

        .password-toggle-icon:focus {
            outline: none;
        }
    </style>
</head>
<body>

    <div class="login-wrapper">
        <div class="login-card">
            
            <div class="text-center mb-4">
                <div class="brand-icon">
                    <i class="fas fa-heart-pulse"></i>
                </div>
                <h3 class="fw-bolder mb-2" style="letter-spacing: -0.5px;">Welcome Back</h3>
                <p class="text-muted" style="font-size: 15px;">Sign in to CarePulse portal</p>
            </div>

            <?php if ($error): ?>
                <div class="alert-error" id="error-alert">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= esc($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= esc($token) ?>">
                
                <div class="mb-4">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" id="username" class="form-control" placeholder="Enter your username" value="<?= esc($_POST['username'] ?? '') ?>" required autofocus autocomplete="off">
                </div>

                <div class="mb-4">
                    <label class="form-label">Password</label>
                    <div class="password-field-container">
                        <input type="password" name="password" id="password" class="form-control" placeholder="Enter your password" required autocomplete="current-password">
                        <button type="button" class="password-toggle-icon" id="togglePassword" tabindex="-1">
                            <i class="far fa-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="remember" style="cursor: pointer;">
                        <label class="form-check-label text-muted" for="remember" style="font-size: 14px; cursor: pointer; user-select: none;">
                            Remember me
                        </label>
                    </div>
                </div>

                <button type="submit" name="login" class="btn btn-login w-100">
                    Sign In Securely <i class="fas fa-arrow-right ms-2 position-relative" style="top: 1px;"></i>
                </button>
            </form>

        </div>
        
        <div class="text-center mt-4">
            <p style="color: #94a3b8; font-size: 13px;">&copy; <?= date('Y') ?> CarePulse Health Systems</p>
        </div>
    </div>

    <script>
        // Password Visibility Toggle
        const togglePassword = document.querySelector('#togglePassword');
        const passwordInput = document.querySelector('#password');
        const toggleIcon = document.querySelector('#toggleIcon');

        if (togglePassword && passwordInput) {
            togglePassword.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                
                // Toggle icons
                toggleIcon.classList.toggle('fa-eye');
                toggleIcon.classList.toggle('fa-eye-slash');
                
                // Add a subtle scale effect on click
                this.style.transform = 'scale(0.9)';
                setTimeout(() => {
                    this.style.transform = 'scale(1)';
                }, 100);
            });
        }

        // Clear error message when user starts typing
        const inputs = document.querySelectorAll('.form-control');
        const errorAlert = document.getElementById('error-alert');
        
        inputs.forEach(input => {
            input.addEventListener('input', () => {
                if (errorAlert) {
                    errorAlert.style.opacity = '0';
                    setTimeout(() => {
                        errorAlert.style.display = 'none';
                    }, 300);
                }
            });
        });
    </script>

</body>
</html>