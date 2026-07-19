<?php
$page_title = 'My Profile';
require_once 'includes/header.php';

$pdo = get_db_pdo();
$user_id = $_SESSION['user_id'];

// Auto-add avatar column if it doesn't exist
try {
    $pdo->query("SELECT avatar FROM users LIMIT 1");
} catch (Exception $e) {
    $pdo->exec("ALTER TABLE users ADD COLUMN avatar VARCHAR(255) DEFAULT NULL");
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Handle Profile Update
if (isset($_POST['update_profile'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash('Invalid security token.', 'danger');
    } else {
        $full_name = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $avatar_path = $user['avatar'] ?? null;

        // Handle Avatar File Upload
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['avatar']['tmp_name'];
            $file_name = $_FILES['avatar']['name'];
            $file_size = $_FILES['avatar']['size'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            $allowed_exts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (!in_array($file_ext, $allowed_exts)) {
                set_flash('Invalid avatar file type. Allowed: JPG, PNG, GIF, WebP.', 'danger');
                header('Location: profile.php');
                exit();
            }

            if ($file_size > 2 * 1024 * 1024) { // 2MB
                set_flash('Avatar image must be smaller than 2MB.', 'danger');
                header('Location: profile.php');
                exit();
            }

            // Generate unique path
            $upload_dir = 'uploads/avatars/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            // Delete old avatar file if it exists
            if ($avatar_path && file_exists($avatar_path)) {
                unlink($avatar_path);
            }

            $avatar_path = $upload_dir . uniqid('avatar_', true) . '.' . $file_ext;
            move_uploaded_file($file_tmp, $avatar_path);
        }

        if ($new_password && $new_password !== $confirm_password) {
            set_flash('Passwords do not match!', 'danger');
        } elseif ($new_password && !preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#])[A-Za-z\d@$!%*?&#]{8,}$/', $new_password)) {
            set_flash('Password must be at least 8 characters long, contain at least one uppercase letter, one lowercase letter, one number, and one special character.', 'danger');
        } else {
            $sql = "UPDATE users SET full_name = ?, email = ?, avatar = ?";
            $params = [$full_name, $email, $avatar_path];

            if ($new_password) {
                $sql .= ", password = ?";
                $params[] = password_hash($new_password, PASSWORD_DEFAULT);
            }

            $sql .= " WHERE id = ?";
            $params[] = $user_id;

            $stmt = $pdo->prepare($sql);
            if ($stmt->execute($params)) {
                $_SESSION['full_name'] = $full_name;
                $_SESSION['email'] = $email;
                $_SESSION['avatar'] = $avatar_path;
                unset($_SESSION['doctor_id']);
                set_flash('Profile updated successfully!');
            }
        }
    }
    header('Location: profile.php');
    exit();
}
?>

<div class="row justify-content-center mt-3">
    <div class="col-lg-6">
        <div class="card border-0 mb-4" style="border-radius: 20px !important; box-shadow: 0 10px 40px rgba(0,0,0,0.04); overflow: hidden;">
            <div class="p-5" style="background: linear-gradient(135deg, var(--accent), var(--accent-light));">
                <div class="d-flex align-items-center gap-4">
                    <?php
                    $profile_avatar_url = !empty($user['avatar']) && file_exists($user['avatar'])
                        ? $user['avatar']
                        : "https://ui-avatars.com/api/?name=" . urlencode($user['username']) . "&size=80&background=ffffff&color=4f46e5";
                    ?>
                    <img src="<?= $profile_avatar_url ?>" class="rounded-circle border border-white border-4 shadow-sm" width="80" height="80" style="object-fit: cover;" alt="Avatar">
                    <div class="text-white">
                        <h3 class="mb-1 fw-bold" style="letter-spacing: -0.5px;"><?= esc($user['full_name'] ?: $user['username']) ?></h3>
                        <p class="mb-0" style="opacity: 0.85; font-size: 14px; font-weight: 500; letter-spacing: 0.5px; text-transform: uppercase;">
                            <i class="fas fa-shield-alt me-1"></i><?= ucfirst($user['role']) ?>
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="card-body p-4 p-md-5 bg-white">
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                    
                    <div class="mb-4">
                        <label class="form-label" style="font-size: 12px; color: var(--muted); font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px;">Profile Picture</label>
                        <input type="file" name="avatar" class="form-control" accept="image/*" style="padding: 10px 16px;">
                        <div class="form-text mt-2 ms-1" style="font-size: 12px;">Supported formats: JPG, PNG, GIF, WebP. Max size: 2MB.</div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label" style="font-size: 12px; color: var(--muted); font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px;">Username</label>
                        <input type="text" class="form-control" value="<?= esc($user['username']) ?>" disabled style="background-color: #f8fafc; color: #94a3b8; border-color: #f1f5f9; padding: 12px 16px;">
                        <div class="form-text mt-2 ms-1" style="font-size: 12px;"><i class="fas fa-lock me-1 text-muted"></i>Username cannot be changed.</div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label" style="font-size: 12px; color: var(--muted); font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px;">Full Name</label>
                        <input type="text" name="full_name" class="form-control" value="<?= esc($user['full_name']) ?>" required style="padding: 12px 16px;">
                    </div>

                    <div class="mb-5">
                        <label class="form-label" style="font-size: 12px; color: var(--muted); font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px;">Email Address</label>
                        <input type="email" name="email" class="form-control" value="<?= esc($user['email']) ?>" required style="padding: 12px 16px;">
                    </div>

                    <div class="d-flex align-items-center mb-4">
                        <hr class="flex-grow-1" style="border-color: #e2e8f0; opacity: 1;">
                        <span class="px-3" style="font-size: 12px; font-weight: 600; color: var(--muted); text-transform: uppercase; letter-spacing: 0.5px;">Security Options</span>
                        <hr class="flex-grow-1" style="border-color: #e2e8f0; opacity: 1;">
                    </div>

                    <p class="text-muted small mb-4 text-center">Leave these fields blank if you don't want to change your password.</p>

                    <div class="row g-3">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" style="font-size: 12px; color: var(--muted); font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px;">New Password</label>
                            <div class="password-field-container">
                                <input type="password" name="new_password" class="form-control" minlength="8" placeholder="••••••••" style="padding: 12px 16px;">
                                <button type="button" class="password-toggle-icon" tabindex="-1">
                                    <i class="far fa-eye"></i>
                                </button>
                            </div>
                            <div id="password-strength-container" class="mt-2 d-none">
                                <div class="progress" style="height: 6px; border-radius: 3px;">
                                    <div id="strength-bar" class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <div id="strength-text" class="form-text mt-1 text-muted" style="font-size: 11px; font-weight: 500;">Strength: Weak</div>
                            </div>
                        </div>

                        <div class="col-md-6 mb-4">
                            <label class="form-label" style="font-size: 12px; color: var(--muted); font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px;">Confirm Password</label>
                            <div class="password-field-container">
                                <input type="password" name="confirm_password" class="form-control" minlength="8" placeholder="••••••••" style="padding: 12px 16px;">
                                <button type="button" class="password-toggle-icon" tabindex="-1">
                                    <i class="far fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="pt-3">
                        <button type="submit" name="update_profile" class="btn btn-primary w-100" style="padding: 14px; font-size: 15px; border-radius: 12px; font-weight: 600; box-shadow: 0 4px 15px var(--accent-glow);">
                             Save Changes
                        </button>
                    </div>
                </form>

                <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const newPasswordInput = document.querySelector('input[name="new_password"]');
                    const strengthContainer = document.getElementById('password-strength-container');
                    const strengthBar = document.getElementById('strength-bar');
                    const strengthText = document.getElementById('strength-text');
                    
                    if (newPasswordInput) {
                        newPasswordInput.addEventListener('input', function() {
                            const val = newPasswordInput.value;
                            if (!val) {
                                strengthContainer.classList.add('d-none');
                                return;
                            }
                            
                            strengthContainer.classList.remove('d-none');
                            
                            let score = 0;
                            if (val.length >= 8) score++;
                            if (/[A-Z]/.test(val)) score++;
                            if (/[a-z]/.test(val)) score++;
                            if (/[0-9]/.test(val)) score++;
                            if (/[@$!%*?&#]/.test(val)) score++;
                            
                            let percent = (score / 5) * 100;
                            let color = 'bg-danger';
                            let text = 'Too Weak';
                            
                            if (score === 5) {
                                color = 'bg-success';
                                text = 'Strong & Secure';
                            } else if (score >= 3) {
                                color = 'bg-warning';
                                text = 'Medium';
                            } else if (score >= 2) {
                                color = 'bg-danger';
                                text = 'Weak';
                            }
                            
                            strengthBar.className = 'progress-bar ' + color;
                            strengthBar.style.width = percent + '%';
                            strengthText.innerText = 'Strength: ' + text;
                        });
                    }
                });
                </script>
            </div>
        </div>

        <div class="text-center mt-3">
            <p class="text-muted" style="font-size: 12px;">Account created on <span class="fw-medium text-dark"><?= date('F j, Y', strtotime($user['created_at'])) ?></span></p>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
