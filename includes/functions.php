<?php
/**
 * CarePulse Core Functions
 * Security, helpers, audit logging
 */

function load_env($path = '.env')
{
    $env_path = __DIR__ . '/../' . $path;
    if (!file_exists($env_path))
        return false;

    $lines = file($env_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0)
            continue;
        $parts = explode('=', $line, 2);
        if (count($parts) === 2) {
            $name = trim($parts[0]);
            $value = trim($parts[1], '"\' ');
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
            if (function_exists('putenv')) {
                putenv("$name=$value");
            }
        }
    }
    return true;
}

function get_db_pdo()
{
    static $pdo = null;
    if ($pdo)
        return $pdo;

    load_env('.env');
    $dsn = 'mysql:host=' . ($_ENV['DB_HOST'] ?? 'localhost') .
        ';dbname=' . ($_ENV['DB_NAME'] ?? 'carepulse_db') .
        ';charset=' . ($_ENV['DB_CHARSET'] ?? 'utf8mb4');

    try {
        $pdo = new PDO($dsn, $_ENV['DB_USER'] ?? 'root', $_ENV['DB_PASS'] ?? '', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        
        // Ensure default admin & enterprise tables exist
        ensure_admin_exists($pdo);
        ensure_enterprise_tables_exist($pdo);
        
        return $pdo;
    } catch (PDOException $e) {
        die('DB Connection failed: ' . $e->getMessage());
    }
}

/**
 * Automatically creates all enterprise ERP & HMS tables if they do not exist yet.
 */
function ensure_enterprise_tables_exist($pdo)
{
    static $checked = false;
    if ($checked) return;
    $checked = true;

    try {
        // 1. Shift handoffs
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS shift_handoffs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                shift_name VARCHAR(50) NOT NULL,
                handover_to VARCHAR(100) NOT NULL,
                high_risk_patients TEXT,
                pending_tasks TEXT,
                notes TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        // 2. Patient documents
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS patient_documents (
                id INT AUTO_INCREMENT PRIMARY KEY,
                patient_id INT NOT NULL,
                title VARCHAR(150) NOT NULL,
                document_type ENUM('X-Ray', 'CT Scan', 'MRI', 'Lab Report', 'General') DEFAULT 'General',
                file_path VARCHAR(255) NOT NULL,
                notes TEXT,
                uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        // 3. Telehealth sessions
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS telehealth_sessions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                appointment_id INT NOT NULL,
                doctor_id INT NOT NULL,
                patient_id INT NOT NULL,
                room_code VARCHAR(64) UNIQUE NOT NULL,
                status ENUM('scheduled', 'live', 'completed', 'cancelled') DEFAULT 'scheduled',
                vitals_snapshot JSON DEFAULT NULL,
                consultation_notes TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE CASCADE,
                FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE,
                FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        // 4. Drug contraindications
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS drug_contraindications (
                id INT AUTO_INCREMENT PRIMARY KEY,
                medicine_a_id INT NOT NULL,
                medicine_b_id INT NOT NULL,
                severity ENUM('Minor', 'Moderate', 'Major', 'Contraindicated') DEFAULT 'Moderate',
                description TEXT NOT NULL,
                FOREIGN KEY (medicine_a_id) REFERENCES medicines(id) ON DELETE CASCADE,
                FOREIGN KEY (medicine_b_id) REFERENCES medicines(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        // 5. AI Clinical notes
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS ai_clinical_notes (
                id INT AUTO_INCREMENT PRIMARY KEY,
                patient_id INT NOT NULL,
                doctor_id INT NOT NULL,
                symptoms TEXT,
                suggested_diagnosis TEXT,
                recommended_tests TEXT,
                risk_level ENUM('Low', 'Moderate', 'High', 'Critical') DEFAULT 'Low',
                ai_summary TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
                FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
    } catch (Exception $e) {
        // Silently handle if table creation succeeds or foreign keys are built
    }
}

/**
 * Automatically creates the default admin user if it doesn't exist
 */
function ensure_admin_exists($pdo)
{
    $email = 'admin@carepulse.com';
    $password = 'Admin@123';
    $hashed = password_hash($password, PASSWORD_DEFAULT);
    
    try {
        // Fix schema if necessary (hashes need space!)
        $pdo->exec("ALTER TABLE users MODIFY COLUMN password VARCHAR(255) NOT NULL");
        
        // Look for the admin by email
        $stmt = $pdo->prepare('SELECT id, password FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user) {
            // 1. Create if missing
            $stmt = $pdo->prepare('INSERT INTO users (username, password, role, email, full_name) VALUES ("admin", ?, "admin", ?, "System Admin")');
            $stmt->execute([$hashed, $email]);
        } else {
            // 2. Update existing to ensure the password is Admin@123 as requested
            // We only do this if it's the system admin email to avoid overwriting other accounts
            if (!password_verify($password, $user['password'])) {
                $stmt = $pdo->prepare('UPDATE users SET password = ?, username = "admin" WHERE id = ?');
                $stmt->execute([$hashed, $user['id']]);
            }
        }
    } catch (Exception $e) {
        // Silently fail during initial DB setup
    }
}

function require_login()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit();
    }
}

function require_role($roles)
{
    require_login();
    if (!in_array($_SESSION['role'] ?? '', (array) $roles)) {
        http_response_code(403);
        include __DIR__ . '/../403.php'; // Fallback to a 403 page if exists
        die('Access denied. Insufficient permissions.');
    }
}

function generate_csrf_token()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token)
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    return hash_equals($_SESSION['csrf_token'] ?? '', (string)$token);
}

function audit_log($pdo, $action, $table, $record_id = null, $old_values = null, $new_values = null)
{
    $stmt = $pdo->prepare('INSERT INTO audit_logs (user_id, action, table_name, record_id, old_values, new_values, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute([
        $_SESSION['user_id'] ?? null,
        strtoupper($action),
        $table,
        $record_id,
        $old_values ? json_encode($old_values) : null,
        $new_values ? json_encode($new_values) : null,
        $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
        $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
    ]);
}

function validate_date($date, $format = 'Y-m-d')
{
    $d = DateTime::createFromFormat($format, $date);
    if (!$d || $d->format($format) !== $date) return false;
    
    $year = (int)$d->format('Y');
    return $year >= 2024 && $year <= 2030;
}

/**
 * Get doctor_id linked to current logged in user
 */
function get_doctor_id($pdo)
{
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'doctor') {
        return 0;
    }

    if (isset($_SESSION['doctor_id']) && $_SESSION['doctor_id'] !== 0) {
        return $_SESSION['doctor_id'];
    }

    $user_id = $_SESSION['user_id'];
    $stmt = $pdo->prepare('SELECT email, full_name, username FROM users WHERE id = ?');
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if (!$user) return 0;

    $email = strtolower(trim($user['email'] ?? ''));
    $name = trim($user['full_name'] ?? '');
    $username = trim($user['username'] ?? '');

    // 1. Try exact email match (case-insensitive)
    if ($email) {
        $stmt = $pdo->prepare('SELECT id FROM doctors WHERE LOWER(TRIM(email)) = ?');
        $stmt->execute([$email]);
        $id = $stmt->fetchColumn();
        if ($id) return $_SESSION['doctor_id'] = (int)$id;
    }

    // 2. Try exact name match
    if ($name) {
        $stmt = $pdo->prepare('SELECT id FROM doctors WHERE TRIM(name) = ?');
        $stmt->execute([$name]);
        $id = $stmt->fetchColumn();
        if ($id) return $_SESSION['doctor_id'] = (int)$id;
    }

    // 3. Try name match with/without "Dr." prefix
    $name_no_dr = trim(preg_replace('/^(Dr\.|Dr|Doctor)\s+/i', '', $name));
    $stmt = $pdo->prepare('SELECT id FROM doctors WHERE name LIKE ? OR TRIM(name) = ?');
    $stmt->execute(["%$name_no_dr%", $name_no_dr]);
    $id = $stmt->fetchColumn();
    if ($id) return $_SESSION['doctor_id'] = (int)$id;

    // 4. Final attempt: Username match in email field (common for test accounts)
    $stmt = $pdo->prepare('SELECT id FROM doctors WHERE email LIKE ? OR name LIKE ?');
    $stmt->execute(["%$username%", "%$username%"]);
    $id = $stmt->fetchColumn();

    return $_SESSION['doctor_id'] = (int)($id ?: 0);
}

function get_pending_count($pdo)
{
    if (!isset($_SESSION['user_id'])) return 0;

    if ($_SESSION['role'] === 'doctor') {
        $doctor_id = get_doctor_id($pdo);
        if (!$doctor_id) return 0;
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM appointments WHERE status = "pending" AND doctor_id = ?');
        $stmt->execute([$doctor_id]);
        return $stmt->fetchColumn();
    }

    return $pdo->query('SELECT COUNT(*) FROM appointments WHERE status = "pending"')->fetchColumn();
}

// Sanitize output
function esc($str)
{
    return htmlspecialchars((string)($str ?? ''), ENT_QUOTES, 'UTF-8');
}

/**
 * Flash messages
 */
function set_flash($message, $type = 'success') {
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
}

function display_flash() {
    if (isset($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        
        $type = $f['type'];
        // Map bootstrap warning/danger to SweetAlert2 icon types
        $icon = match ($type) {
            'danger' => 'error',
            'warning' => 'warning',
            'info' => 'info',
            default => 'success'
        };
        
        $message = addslashes(htmlspecialchars_decode((string)$f['message'], ENT_QUOTES));
        
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 4000,
                    timerProgressBar: true,
                    didOpen: (toast) => {
                        toast.addEventListener('mouseenter', Swal.stopTimer)
                        toast.addEventListener('mouseleave', Swal.resumeTimer)
                    }
                });
                Toast.fire({
                    icon: '{$icon}',
                    title: '{$message}'
                });
            });
        </script>";
    }
}

function flash_has_errors() {
    return isset($_SESSION['flash']) && $_SESSION['flash']['type'] === 'danger';
}

/**
 * Format a doctor name to ensure it has exactly one "Dr." prefix
 */
function format_doctor_name($name) {
    if (empty($name)) return '';
    // Strip leading "Dr." or "Dr " (case-insensitive)
    $clean_name = preg_replace('/^(Dr\.\s*|Dr\s+)/i', '', trim($name));
    return 'Dr. ' . $clean_name;
}


