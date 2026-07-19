<?php
/**
 * CarePulse Database Schema Updater
 * Run: php update_database.php
 */
require_once __DIR__ . '/config.php';

echo "CarePulse Database Schema Updater\n";

try {
    $pdo = get_conn();
    echo "Connected to database successfully.\n";

    // 1. Create doctor_availability table
    echo "Creating doctor_availability table... ";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS doctor_availability (
            id INT AUTO_INCREMENT PRIMARY KEY,
            doctor_id INT NOT NULL,
            day_of_week VARCHAR(15) NOT NULL,
            start_time TIME NOT NULL,
            end_time TIME NOT NULL,
            FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE,
            UNIQUE KEY unique_doc_day (doctor_id, day_of_week)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    echo "OK\n";

    // 2. Create invoices table
    echo "Creating invoices table... ";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS invoices (
            id INT AUTO_INCREMENT PRIMARY KEY,
            appointment_id INT NOT NULL,
            patient_id INT NOT NULL,
            total_amount DECIMAL(10,2) DEFAULT 0.00,
            discount DECIMAL(10,2) DEFAULT 0.00,
            tax DECIMAL(10,2) DEFAULT 0.00,
            net_amount DECIMAL(10,2) DEFAULT 0.00,
            status ENUM('unpaid', 'paid', 'partially_paid') DEFAULT 'unpaid',
            payment_method ENUM('Cash', 'Card', 'Insurance', 'Other') DEFAULT 'Cash',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE CASCADE,
            FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    echo "OK\n";

    // 3. Create invoice_items table
    echo "Creating invoice_items table... ";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS invoice_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            invoice_id INT NOT NULL,
            item_description VARCHAR(255) NOT NULL,
            quantity INT DEFAULT 1,
            unit_price DECIMAL(10,2) NOT NULL,
            total_price DECIMAL(10,2) NOT NULL,
            FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    echo "OK\n";

    // 4. Create login_attempts table
    echo "Creating login_attempts table... ";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS login_attempts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            ip_address VARCHAR(45) NOT NULL,
            username VARCHAR(100) NOT NULL,
            attempt_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    echo "OK\n";

    // 5. Alter prescriptions table to add vitals columns
    echo "Checking prescriptions table columns... ";
    // Check if prescriptions table exists first (auto-created by appointment_details.php normally, but we ensure it here)
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS prescriptions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            appointment_id INT UNIQUE NOT NULL,
            patient_id INT NOT NULL,
            doctor_id INT NOT NULL,
            symptoms TEXT,
            diagnosis TEXT,
            medications TEXT,
            instructions TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    $columnsToAdd = [
        'blood_pressure' => 'VARCHAR(15) DEFAULT NULL',
        'heart_rate' => 'VARCHAR(10) DEFAULT NULL',
        'temperature' => 'VARCHAR(10) DEFAULT NULL',
        'weight' => 'VARCHAR(10) DEFAULT NULL'
    ];

    foreach ($columnsToAdd as $col => $definition) {
        $check = $pdo->query("SHOW COLUMNS FROM prescriptions LIKE '$col'");
        if ($check->rowCount() == 0) {
            $pdo->exec("ALTER TABLE prescriptions ADD COLUMN $col $definition");
            echo "Added column '$col'... ";
        }
    }
    echo "OK\n";

    // Add some default doctor schedule data to make demoing easier
    echo "Seeding default doctor schedules... ";
    $doctors = $pdo->query("SELECT id FROM doctors")->fetchAll(PDO::FETCH_COLUMN);
    $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
    
    $stmt = $pdo->prepare("INSERT IGNORE INTO doctor_availability (doctor_id, day_of_week, start_time, end_time) VALUES (?, ?, '09:00:00', '17:00:00')");
    foreach ($doctors as $doc_id) {
        foreach ($days as $day) {
            $stmt->execute([$doc_id, $day]);
        }
    }
    echo "OK\n";

    echo "Database Schema Update Completed Successfully!\n";

} catch (Exception $e) {
    echo "Error during update: " . $e->getMessage() . "\n";
}
?>
