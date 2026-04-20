<?php
$page_title = 'My Profile';
require_once 'includes/header.php';

$pdo = get_db_pdo();
$user_id = $_SESSION['user_id'];

// Handle Password Update
if (isset($_POST['update_profile'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash('Invalid security token.', 'danger');
    } else {
        $full_name = trim($_POST['full_name']);
        $email = trim($_POST['email']);
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        if ($new_password && $new_password !== $confirm_password) {
            set_flash('Passwords do not match!', 'danger');
        } else {
            $sql = "UPDATE users SET full_name = ?, email = ?";
            $params = [$full_name, $email];

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
                unset($_SESSION['doctor_id']);
                set_flash('Profile updated successfully!');
            }
        }
    }
    header('Location: profile.php');
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
?>

<div class="row justify-content-center mt-3">
    <div class="col-lg-6">
        <div class="card border-0 mb-4" style="border-radius: 20px !important; box-shadow: 0 10px 40px rgba(0,0,0,0.04); overflow: hidden;">
            <div class="p-5" style="background: linear-gradient(135deg, var(--accent), var(--accent-light));">
                <div class="d-flex align-items-center gap-4">
                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($user['username']) ?>&size=80&background=ffffff&color=4f46e5" class="rounded-circle border border-white border-4 shadow-sm" width="80" alt="Avatar">
                    <div class="text-white">
                        <h3 class="mb-1 fw-bold" style="letter-spacing: -0.5px;"><?= esc($user['full_name'] ?: $user['username']) ?></h3>
                        <p class="mb-0" style="opacity: 0.85; font-size: 14px; font-weight: 500; letter-spacing: 0.5px; text-transform: uppercase;">
                            <i class="fas fa-shield-alt me-1"></i><?= ucfirst($user['role']) ?>
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="card-body p-4 p-md-5 bg-white">
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                    
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
            </div>
        </div>

        <div class="text-center mt-3">
            <p class="text-muted" style="font-size: 12px;">Account created on <span class="fw-medium text-dark"><?= date('F j, Y', strtotime($user['created_at'])) ?></span></p>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
