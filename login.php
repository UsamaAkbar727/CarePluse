<?php
session_start();
include('config.php');

if (isset($_POST['login'])) {
    $user = mysqli_real_escape_string($conn, $_POST['username']);
    $pass = mysqli_real_escape_string($conn, $_POST['password']);

    // Note: In a real production app, you should use password_verify() with hashed passwords
    $query = "SELECT * FROM users WHERE username='$user' AND password='$pass'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        $_SESSION['admin_user'] = $user;
        header("Location: index.php");
        exit();
    } else {
        $error = "Invalid Username or Password!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CarePulse | Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        :root {
            --bg-dark: #0b1437;
            --card-bg: #ffffff;
            --accent: #4318FF;
            --accent-gradient: linear-gradient(135deg, #4318FF 0%, #7551FF 100%);
            --accent-light: rgba(67, 24, 255, 0.1);
            --text-main: #2b3674;
            --text-muted: #a3aed0;
            --shadow-lg: 0 15px 35px rgba(0, 0, 0, 0.3);
        }

        body {
            background-color: var(--bg-dark);
            background-image:
                radial-gradient(circle at 15% 20%, rgba(67, 24, 255, 0.08) 0%, transparent 50%),
                radial-gradient(circle at 85% 80%, rgba(117, 81, 255, 0.06) 0%, transparent 50%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            margin: 0;
            padding: 20px;
        }

        .login-card {
            background: var(--card-bg);
            padding: 40px 35px;
            border-radius: 20px;
            width: 100%;
            max-width: 420px;
            box-shadow: var(--shadow-lg);
            position: relative;
            border: 1px solid rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(8px);
            animation: slideUp 0.5s cubic-bezier(0.2, 0.8, 0.2, 1);
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--accent-gradient);
            border-radius: 20px 20px 0 0;
        }

        .brand-section {
            text-align: center;
            margin-bottom: 30px;
        }

        .brand-logo {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 56px;
            height: 56px;
            background: var(--accent-gradient);
            border-radius: 16px;
            margin-bottom: 16px;
            box-shadow: 0 8px 20px rgba(67, 24, 255, 0.25);
        }

        .brand-logo i {
            color: white;
            font-size: 24px;
        }

        .brand-text {
            color: var(--accent);
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            opacity: 0.9;
        }

        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-header h3 {
            color: var(--text-main);
            font-weight: 700;
            font-size: 24px;
            margin-bottom: 8px;
            letter-spacing: -0.5px;
        }

        .login-header p {
            color: var(--text-muted);
            font-size: 14px;
            line-height: 1.5;
        }

        .login-form {
            position: relative;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            font-weight: 600;
            color: var(--text-main);
            font-size: 13px;
            margin-bottom: 8px;
            display: block;
        }

        .input-group {
            position: relative;
        }

        .form-control {
            border: 1.5px solid #e8edf9;
            border-radius: 12px;
            padding: 12px 16px;
            font-size: 14px;
            transition: all 0.25s ease;
            background: #fcfdff;
            width: 100%;
        }

        .form-control:hover {
            border-color: #d0d9f0;
        }

        .form-control:focus {
            border-color: var(--accent);
            background: white;
            box-shadow: 0 0 0 3px rgba(67, 24, 255, 0.1);
            outline: none;
            transform: translateY(-1px);
        }

        .input-icon {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 16px;
            pointer-events: none;
        }

        .password-toggle {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            font-size: 16px;
            padding: 4px;
            transition: color 0.2s ease;
        }

        .password-toggle:hover {
            color: var(--accent);
        }

        .btn-login {
            background: var(--accent-gradient);
            border: none;
            border-radius: 12px;
            padding: 14px;
            font-weight: 600;
            font-size: 14px;
            color: white;
            margin-top: 10px;
            transition: all 0.3s ease;
            box-shadow: 0 10px 20px rgba(67, 24, 255, 0.2);
            width: 100%;
            cursor: pointer;
            letter-spacing: 0.3px;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 24px rgba(67, 24, 255, 0.25);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .alert-custom {
            background: rgba(255, 90, 95, 0.05);
            border: 1px solid rgba(255, 90, 95, 0.15);
            color: #ff5a5f;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 500;
            padding: 12px 16px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        .alert-custom i {
            font-size: 16px;
            flex-shrink: 0;
        }

        .form-footer {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid rgba(0, 0, 0, 0.05);
        }

        .forgot-password a {
            color: var(--accent);
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            transition: color 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .forgot-password a:hover {
            color: #3311cc;
        }

        /* Responsive */
        @media (max-width: 480px) {
            body {
                padding: 15px;
            }

            .login-card {
                padding: 30px 25px;
                border-radius: 18px;
            }

            .login-header h3 {
                font-size: 22px;
            }

            .brand-logo {
                width: 50px;
                height: 50px;
                border-radius: 14px;
            }
        }

        /* Modern input focus effect */
        .form-control:focus~.input-icon {
            color: var(--accent);
        }

        /* Loading state */
        .btn-login.loading {
            position: relative;
            color: transparent;
        }

        .btn-login.loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 18px;
            height: 18px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            transform: translate(-50%, -50%);
        }

        @keyframes spin {
            to {
                transform: translate(-50%, -50%) rotate(360deg);
            }
        }

        /* Success state */
        .form-control.success {
            border-color: #05cd99;
            background: rgba(5, 205, 153, 0.02);
        }

        /* Additional modern touch */
        .form-control::placeholder {
            color: #a3aed0;
            font-size: 14px;
        }

        /* Checkbox styling for "Remember me" */
        .remember-me {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 20px;
            font-size: 13px;
            color: var(--text-muted);
        }

        .remember-me input[type="checkbox"] {
            width: 16px;
            height: 16px;
            border-radius: 4px;
            border: 1.5px solid #e0e5f2;
            cursor: pointer;
        }

        .remember-me input[type="checkbox"]:checked {
            background-color: var(--accent);
            border-color: var(--accent);
        }
    </style>
</head>

<body>
    <div class="login-card">
        <div class="brand-logo">
            <i class="fa-solid fa-heart-pulse"></i>
        </div>
        <div class="login-header text-center">
            <h3>CarePulse Admin</h3>
            <p>Enter your credentials to access the portal</p>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert-custom">
                <i class="fa-solid fa-circle-exclamation"></i>
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" placeholder="admin" required>
            </div>
            <div class="mb-4">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>
            <button type="submit" name="login" class="btn btn-primary btn-login w-100 text-white">
                Sign In
            </button>
        </form>

        <div class="text-center mt-4">
            <small style="color: #a3aed0;">CarePulse Health Systems &copy; 2026</small>
        </div>
    </div>
</body>

</html>