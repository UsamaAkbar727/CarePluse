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
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

        $lockout_stmt = $pdo->prepare('SELECT COUNT(*) FROM login_attempts WHERE ip_address = ? AND attempt_time > DATE_SUB(NOW(), INTERVAL 15 MINUTE)');
        $lockout_stmt->execute([$ip]);
        $attempts = $lockout_stmt->fetchColumn();

        if ($attempts >= 5) {
            $_SESSION['login_error'] = 'Too many failed login attempts. This IP has been locked out for 15 minutes.';
            header("Location: login.php");
            exit();
        }

        $stmt = $pdo->prepare('SELECT id, username, password, role, email, full_name, is_active FROM users WHERE username = ? OR email = ?');
        $stmt->execute([trim($_POST['username']), trim($_POST['username'])]);
        $user = $stmt->fetch();

        if (!$user) {
            // Log failed attempt
            $log_stmt = $pdo->prepare('INSERT INTO login_attempts (ip_address, username) VALUES (?, ?)');
            $log_stmt->execute([$ip, trim($_POST['username'])]);

            $_SESSION['login_error'] = 'Incorrect username or password. Attempts left: ' . (5 - ($attempts + 1));
            header("Location: login.php");
            exit();
        } elseif ($user['is_active'] == 0) {
            $_SESSION['login_error'] = 'This account is currently inactive.';
            header("Location: login.php");
            exit();
        } elseif (!password_verify(trim($_POST['password']), $user['password'])) {
            // Log failed attempt
            $log_stmt = $pdo->prepare('INSERT INTO login_attempts (ip_address, username) VALUES (?, ?)');
            $log_stmt->execute([$ip, trim($_POST['username'])]);

            $_SESSION['login_error'] = 'Incorrect username or password. Attempts left: ' . (5 - ($attempts + 1));
            header("Location: login.php");
            exit();
        } else {
            // Success - Clear failed attempts for this IP
            $clear_stmt = $pdo->prepare('DELETE FROM login_attempts WHERE ip_address = ?');
            $clear_stmt->execute([$ip]);

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['avatar'] = $user['avatar'] ?? null;
            $_SESSION['csrf_token'] = generate_csrf_token();
            session_regenerate_id(true);

            $stmt = $pdo->prepare('UPDATE users SET last_login = NOW() WHERE id = ?');
            $stmt->execute([$user['id']]);

            // Smart role-based workspace redirect
            $redirect = match ($user['role']) {
                'pharmacist' => 'pharmacy.php',
                'lab_tech' => 'lab_portal.php',
                'doctor' => 'admin_dashboard.php',
                'receptionist' => 'patients.php',
                default => 'admin_dashboard.php'
            };
            header("Location: $redirect");
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
    <link rel="icon" type="image/png" href="favicon.png">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        :root {
            --bg: #f8f6f2; /* Warm background from landing page */
            --primary: #0b2b3c;
            --secondary: #1c7e6f;
            --secondary-dark: #115a4f;
            --accent: #c97d4b;
            --accent-glow: rgba(28, 126, 111, 0.15);
            --text-dark: #0b2b3c;
            --text-muted: #64748b;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: 
                radial-gradient(circle 500px at 10% 15%, rgba(28, 126, 111, 0.28), transparent 75%),
                radial-gradient(circle 500px at 90% 85%, rgba(201, 125, 75, 0.24), transparent 75%),
                linear-gradient(135deg, #f2edd8 0%, #ffffff 50%, #e2dcc5 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-dark);
            position: relative;
            overflow: hidden;
            margin: 0;
            padding: 0;
        }

        body::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image: radial-gradient(circle, rgba(28, 126, 111, 0.18) 1.5px, transparent 1.5px);
            background-size: 20px 20px;
            z-index: -1;
            pointer-events: none;
            opacity: 1;
        }

        .login-wrapper {
            width: 100%;
            max-width: 390px; /* Reduced width */
            padding: 16px;
        }

        .login-card {
            background: #ffffff;
            border-radius: 20px; /* Reduced border radius */
            box-shadow: 0 10px 30px rgba(11, 43, 60, 0.06), 0 1px 3px rgba(11, 43, 60, 0.02);
            padding: 32px 24px; /* Reduced padding from 48px 40px */
            position: relative;
            overflow: hidden;
        }

        .login-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 6px;
            background: linear-gradient(90deg, var(--secondary), var(--accent), var(--primary));
        }

        .btn-back-home {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--secondary);
            font-size: 13px; /* Slightly smaller text */
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s;
            padding: 8px 14px; /* Reduced padding */
            border-radius: 10px;
            background: #ffffff;
            box-shadow: 0 4px 15px rgba(11, 43, 60, 0.03);
            border: 1px solid #e2e8f0;
            margin-bottom: 16px;
        }

        .btn-back-home:hover {
            color: var(--primary);
            background: #ffffff;
            border-color: var(--secondary);
            transform: translateX(-3px);
            box-shadow: 0 4px 20px rgba(28, 126, 111, 0.12);
        }

        .brand-icon {
            width: 36px; height: 36px;
            background: linear-gradient(135deg, var(--secondary), var(--primary));
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            color: #ffffff; font-size: 16px;
            box-shadow: 0 4px 12px rgba(28, 126, 111, 0.2);
            margin: 0;
        }

        .form-control {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 11px 15px; /* Reduced vertical padding */
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.2s;
        }

        .form-control:focus {
            background: #ffffff;
            border-color: var(--secondary);
            box-shadow: 0 0 0 4px var(--accent-glow);
        }

        .form-label {
            font-size: 11px; /* Reduced label text size */
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px; /* Reduced space below label */
        }

        .btn-login {
            background: linear-gradient(135deg, var(--secondary), var(--secondary-dark));
            border: none;
            padding: 11px; /* Reduced button padding */
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            color: #ffffff;
            box-shadow: 0 6px 20px rgba(28, 126, 111, 0.2);
            transition: all 0.2s;
        }

        .btn-login:hover {
            background: var(--secondary-dark);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(28, 126, 111, 0.3);
            color: #ffffff;
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
            color: var(--secondary);
        }

        .password-toggle-icon:focus {
            outline: none;
        }

        /* Demo credentials styling */
        .demo-cred-btn {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            color: #475569;
            padding: 5px 10px; /* Compact padding */
            border-radius: 6px;
            font-size: 11px; /* Slightly smaller text */
            font-weight: 500;
            transition: all 0.2s;
            cursor: pointer;
        }
            font-weight: 500;
            transition: all 0.2s;
            cursor: pointer;
        }

        .demo-cred-btn:hover {
            background: var(--accent-glow);
            border-color: var(--secondary);
            color: var(--secondary);
            transform: translateY(-1px);
        }

        .text-indigo {
            color: var(--secondary);
        }
    </style>
</head>
<body>

    <div class="login-wrapper">
        <!-- Back to Home Button -->
        <div class="text-start">
            <a href="index.php" class="btn-back-home">
                <i class="fas fa-arrow-left"></i> Back to Home
            </a>
        </div>

        <div class="login-card">
            
            <div class="text-center mb-3">
                <div class="d-inline-flex align-items-center justify-content-center gap-2 mb-1">
                    <div class="brand-icon">
                        <i class="fas fa-heart-pulse"></i>
                    </div>
                    <span class="fs-4 fw-black tracking-tight" style="color: var(--primary); font-family: 'Outfit', sans-serif;">Care<span style="color: var(--secondary);">Pulse</span></span>
                </div>
                <p class="text-muted mb-0" style="font-size: 13px;">Sign in to Portal</p>
            </div>

            <?php if ($error): ?>
                <div class="alert-error" id="error-alert">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= esc($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= esc($token) ?>">
                
                <div class="mb-3">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" id="username" class="form-control" placeholder="Enter username" value="<?= esc($_POST['username'] ?? '') ?>" required autofocus autocomplete="off">
                </div>

                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <div class="password-field-container">
                        <input type="password" name="password" id="password" class="form-control" placeholder="Enter password" required autocomplete="current-password">
                        <button type="button" class="password-toggle-icon" id="togglePassword" tabindex="-1">
                            <i class="far fa-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="remember" style="cursor: pointer;">
                        <label class="form-check-label text-muted" for="remember" style="font-size: 13px; cursor: pointer; user-select: none;">
                            Remember me
                        </label>
                    </div>
                </div>

                <button type="submit" name="login" class="btn btn-login w-100">
                    Sign In Securely <i class="fas fa-arrow-right ms-2 position-relative" style="top: 1px;"></i>
                </button>
            </form>

            <div class="mt-3 pt-2 border-top text-center">
                <p class="text-center text-muted mb-1.5" style="font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">Quick Demo Accounts</p>
                <div class="d-flex flex-wrap gap-1.5 justify-content-center">
                    <button type="button" class="btn btn-sm btn-outline-primary demo-cred-btn" data-username="admin" data-password="Admin@123">
                        <i class="fas fa-user-shield me-1"></i> Admin
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-info demo-cred-btn" data-username="doctor1" data-password="password">
                        <i class="fas fa-user-md me-1"></i> Doctor
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-success demo-cred-btn" data-username="recep1" data-password="password">
                        <i class="fas fa-user-tag me-1"></i> Receptionist
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-warning demo-cred-btn" data-username="pharmacist" data-password="password">
                        <i class="fas fa-pills me-1"></i> Pharmacist
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger demo-cred-btn" data-username="labtech" data-password="password">
                        <i class="fas fa-flask me-1"></i> Lab Tech
                    </button>
                </div>
            </div>

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

        // Auto-fill demo credentials
        const demoBtns = document.querySelectorAll('.demo-cred-btn');
        const usernameInput = document.querySelector('#username');
        
        demoBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const username = this.getAttribute('data-username');
                const password = this.getAttribute('data-password');
                
                if (usernameInput) {
                    usernameInput.value = username;
                    usernameInput.dispatchEvent(new Event('input'));
                }
                if (passwordInput) {
                    passwordInput.value = password;
                    passwordInput.dispatchEvent(new Event('input'));
                }

                this.style.transform = 'scale(0.95)';
                setTimeout(() => {
                    this.style.transform = 'none';
                }, 150);
            });
        });
    </script>

</body>
</html>