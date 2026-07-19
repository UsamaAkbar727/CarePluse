<?php
/**
 * CarePulse Database Schema Updater - Full ERP Upgrade
 * Run: php update_database.php
 */
require_once __DIR__ . '/config.php';

echo "CarePulse Database Schema Updater\n";

try {
    $pdo = get_conn();
    echo "Connected to database successfully.\n";

    // 0. Update User Roles enum
    echo "Updating user roles... ";
    $pdo->exec("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'doctor', 'receptionist', 'pharmacist', 'lab_tech') NOT NULL DEFAULT 'admin'");
    echo "OK\n";

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

    // 6. Create IPD Wards table
    echo "Creating wards table... ";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS wards (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(50) NOT NULL,
            type ENUM('General', 'ICU', 'Private', 'Deluxe') NOT NULL DEFAULT 'General',
            capacity INT NOT NULL DEFAULT 4
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    echo "OK\n";

    // 7. Create Wards Beds table
    echo "Creating beds table... ";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS beds (
            id INT AUTO_INCREMENT PRIMARY KEY,
            ward_id INT NOT NULL,
            bed_number VARCHAR(10) NOT NULL,
            status ENUM('available', 'occupied') NOT NULL DEFAULT 'available',
            FOREIGN KEY (ward_id) REFERENCES wards(id) ON DELETE CASCADE,
            UNIQUE KEY unique_ward_bed (ward_id, bed_number)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    echo "OK\n";

    // 8. Create Inpatient Admissions table
    echo "Creating admissions table... ";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS admissions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            patient_id INT NOT NULL,
            bed_id INT NOT NULL,
            doctor_id INT NOT NULL,
            admission_date DATE NOT NULL,
            discharge_date DATE DEFAULT NULL,
            room_charges DECIMAL(10,2) DEFAULT 50.00,
            status ENUM('admitted', 'discharged') NOT NULL DEFAULT 'admitted',
            FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
            FOREIGN KEY (bed_id) REFERENCES beds(id) ON DELETE CASCADE,
            FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    echo "OK\n";

    // 9. Create Medicines inventory table
    echo "Creating medicines table... ";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS medicines (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            type VARCHAR(30) NOT NULL DEFAULT 'Tablet',
            stock_qty INT NOT NULL DEFAULT 0,
            price_per_unit DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            expiry_date DATE NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    echo "OK\n";

    // 10. Create Prescription Items table (pharmacy connection)
    echo "Creating prescription_items table... ";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS prescription_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            prescription_id INT NOT NULL,
            medicine_id INT NOT NULL,
            quantity INT NOT NULL DEFAULT 1,
            price_charged DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            status ENUM('prescribed', 'dispensed') NOT NULL DEFAULT 'prescribed',
            FOREIGN KEY (prescription_id) REFERENCES prescriptions(id) ON DELETE CASCADE,
            FOREIGN KEY (medicine_id) REFERENCES medicines(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    echo "OK\n";

    // 11. Create Lab Tests catalog table
    echo "Creating lab_tests table... ";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS lab_tests (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            cost DECIMAL(10,2) NOT NULL DEFAULT 0.00
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    echo "OK\n";

    // 12. Create Lab Requests table
    echo "Creating lab_requests table... ";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS lab_requests (
            id INT AUTO_INCREMENT PRIMARY KEY,
            appointment_id INT NOT NULL,
            test_id INT NOT NULL,
            result_details TEXT DEFAULT NULL,
            status ENUM('pending', 'completed') NOT NULL DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE CASCADE,
            FOREIGN KEY (test_id) REFERENCES lab_tests(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    echo "OK\n";

    // Seed defaults
    echo "Seeding default data (Wards, Beds, Medicines, Lab Tests, Accounts)... ";
    
    // Seed Wards
    $pdo->exec("INSERT IGNORE INTO wards (id, name, type, capacity) VALUES 
        (1, 'ICU Ward A', 'ICU', 2),
        (2, 'General Medical Ward B', 'General', 4),
        (3, 'Deluxe Private Suite C', 'Deluxe', 1)
    ");

    // Seed Beds
    $pdo->exec("INSERT IGNORE INTO beds (id, ward_id, bed_number, status) VALUES 
        (1, 1, 'ICU-01', 'available'),
        (2, 1, 'ICU-02', 'available'),
        (3, 2, 'GEN-01', 'available'),
        (4, 2, 'GEN-02', 'available'),
        (5, 2, 'GEN-03', 'available'),
        (6, 2, 'GEN-04', 'available'),
        (7, 3, 'DLX-01', 'available')
    ");

    // Seed Medicines
    $pdo->exec("INSERT IGNORE INTO medicines (id, name, type, stock_qty, price_per_unit, expiry_date) VALUES 
        (1, 'Paracetamol 500mg', 'Tablet', 500, 0.50, '2028-12-31'),
        (2, 'Amoxicillin 250mg', 'Capsule', 200, 1.20, '2027-06-30'),
        (3, 'Ibuprofen 400mg', 'Tablet', 300, 0.75, '2028-09-15'),
        (4, 'Benadryl Cough Syrup', 'Syrup', 80, 5.50, '2027-11-20'),
        (5, 'Insulin Glargine', 'Injection', 40, 25.00, '2027-03-10')
    ");

    // Seed Lab Tests
    $pdo->exec("INSERT IGNORE INTO lab_tests (id, name, cost) VALUES 
        (1, 'Complete Blood Count (CBC)', 15.00),
        (2, 'Lipid Profile Panel', 30.00),
        (3, 'Serum Glucose (Sugar)', 10.00),
        (4, 'Chest X-Ray Digital', 45.00),
        (5, 'Electrocardiogram (ECG)', 25.00)
    ");

    // Seed Staff accounts
    $hashed_pw = password_hash('password', PASSWORD_DEFAULT);
    $pdo->exec("INSERT IGNORE INTO users (username, password, role, email, full_name) VALUES 
        ('pharmacist', '$hashed_pw', 'pharmacist', 'pharmacy@carepulse.com', 'System Pharmacist'),
        ('labtech', '$hashed_pw', 'lab_tech', 'lab@carepulse.com', 'Chief Lab Technician')
    ");

    // Seed doctor availability shifts
    $doctors = $pdo->query("SELECT id FROM doctors")->fetchAll(PDO::FETCH_COLUMN);
    $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
    $stmt = $pdo->prepare("INSERT IGNORE INTO doctor_availability (doctor_id, day_of_week, start_time, end_time) VALUES (?, ?, '09:00:00', '17:00:00')");
    foreach ($doctors as $doc_id) {
        foreach ($days as $day) {
            $stmt->execute([$doc_id, $day]);
        }
    }

    echo "OK\n";
    echo "Database ERP Schema Update Completed Successfully!\n";

} catch (Exception $e) {
    echo "Error during update: " . $e->getMessage() . "\n";
}
?>
