<?php
session_start();
require_once 'config.php';
require_once 'includes/functions.php';

require_login();

$id = $_GET['id'] ?? 0;
$pdo = get_db_pdo();

// Fetch prescription details with patient and doctor details
$stmt = $pdo->prepare('
    SELECT 
        pr.*, 
        p.name as p_name, p.phone as p_phone, p.gender as p_gender, p.date_of_birth,
        d.name as d_name, d.specialization as d_specialization, d.email as d_email
    FROM prescriptions pr
    JOIN patients p ON pr.patient_id = p.id
    JOIN doctors d ON pr.doctor_id = d.id
    WHERE pr.id = ?
');
$stmt->execute([$id]);
$pres = $stmt->fetch();

if (!$pres) {
    die('Prescription not found.');
}

// Security check: doctors can only print their own prescriptions
if ($_SESSION['role'] === 'doctor') {
    $doctor_id = get_doctor_id($pdo);
    if ($pres['doctor_id'] != $doctor_id) {
        die('Access denied.');
    }
}

// Calculate age from date of birth
$age = 'N/A';
if (!empty($pres['date_of_birth'])) {
    $dob = new DateTime($pres['date_of_birth']);
    $today = new DateTime();
    $age = $today->diff($dob)->y . ' Years';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prescription_#<?= $pres['id'] ?></title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
            color: #1e293b;
            background: #ffffff;
            margin: 0;
            padding: 40px;
        }

        .prescription-container {
            max-width: 800px;
            margin: 0 auto;
            border: 1px solid #e2e8f0;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.02);
            position: relative;
        }

        /* Medical header */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #4f46e5;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .brand-icon {
            width: 44px;
            height: 44px;
            background: #4f46e5;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            font-size: 20px;
        }

        .brand-name {
            font-size: 22px;
            font-weight: 800;
            letter-spacing: -0.5px;
        }

        .doctor-info {
            text-align: right;
        }

        .doctor-name {
            font-weight: 700;
            font-size: 16px;
            margin-bottom: 2px;
        }

        .doctor-spec {
            color: #64748b;
            font-size: 13px;
            margin-bottom: 2px;
        }

        .doctor-contact {
            color: #64748b;
            font-size: 12px;
        }

        /* Patient Info Table */
        .patient-meta {
            background: #f8fafc;
            border-radius: 8px;
            padding: 18px;
            margin-bottom: 30px;
            font-size: 14px;
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            border: 1px solid #e2e8f0;
        }

        .meta-item {
            display: flex;
            flex-direction: column;
        }

        .meta-label {
            color: #64748b;
            font-weight: 600;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }

        .meta-val {
            font-weight: 600;
            color: #0f172a;
        }

        /* RX Section */
        .rx-section {
            min-height: 380px;
            position: relative;
        }

        .rx-symbol {
            font-size: 40px;
            font-weight: 700;
            color: #4f46e5;
            font-family: serif;
            margin-bottom: 20px;
        }

        .section-title {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
            margin-bottom: 10px;
            border-bottom: 1px solid #f1f5f9;
            padding-bottom: 6px;
        }

        .rx-details {
            margin-bottom: 25px;
        }

        .rx-details p {
            margin: 0;
            font-size: 14px;
            line-height: 1.6;
        }

        .medications-list {
            font-family: monospace;
            font-size: 14px;
            white-space: pre-wrap;
            background: #fafafa;
            border: 1px dashed #e2e8f0;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            line-height: 1.8;
            color: #334155;
        }

        /* Footer / Sign off */
        .footer {
            border-top: 1px solid #e2e8f0;
            padding-top: 30px;
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }

        .hospital-footer {
            font-size: 12px;
            color: #94a3b8;
        }

        .signature-area {
            text-align: center;
            width: 200px;
        }

        .signature-line {
            border-top: 1.5px solid #cbd5e1;
            margin-bottom: 8px;
        }

        .signature-title {
            font-size: 12px;
            font-weight: 600;
            color: #64748b;
        }

        /* Print Media Style overrides */
        @media print {
            body {
                padding: 0;
                background: #ffffff;
            }
            
            .prescription-container {
                border: none;
                box-shadow: none;
                padding: 0;
            }

            .print-btn {
                display: none !important;
            }
        }

        .print-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #4f46e5;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
        }

        .print-btn:hover {
            background: #4338ca;
            transform: translateY(-1px);
        }
    </style>
</head>
<body>

    <button onclick="window.print()" class="print-btn">
        <i class="fas fa-print"></i> Print Prescription
    </button>

    <div class="prescription-container">
        <!-- Logo and header -->
        <div class="header">
            <div class="brand">
                <div class="brand-icon">
                    <i class="fas fa-heart-pulse"></i>
                </div>
                <div>
                    <div class="brand-name">CarePulse</div>
                    <div style="font-size: 11px; color:#64748b; letter-spacing: 0.5px; text-transform: uppercase;">Health Systems</div>
                </div>
            </div>
            
            <div class="doctor-info">
                <div class="doctor-name"><?= esc(format_doctor_name($pres['d_name'])) ?></div>
                <div class="doctor-spec"><?= esc($pres['d_specialization']) ?> Specialist</div>
                <div class="doctor-contact"><i class="far fa-envelope me-1"></i> <?= esc($pres['d_email']) ?></div>
            </div>
        </div>

        <!-- Patient Metadata Box -->
        <div class="patient-meta">
            <div class="meta-item">
                <span class="meta-label">Patient Name</span>
                <span class="meta-val"><?= esc($pres['p_name']) ?></span>
            </div>
            <div class="meta-item">
                <span class="meta-label">Age / Gender</span>
                <span class="meta-val"><?= $age ?> / <?= $pres['p_gender'] ?></span>
            </div>
            <div class="meta-item">
                <span class="meta-label">Phone Contact</span>
                <span class="meta-val"><?= esc($pres['p_phone'] ?: 'N/A') ?></span>
            </div>
            <div class="meta-item">
                <span class="meta-label">Prescription Date</span>
                <span class="meta-val"><?= date('M j, Y', strtotime($pres['created_at'])) ?></span>
            </div>
        </div>

        <!-- Patient Vitals Section -->
        <?php if (!empty($pres['blood_pressure']) || !empty($pres['heart_rate']) || !empty($pres['temperature']) || !empty($pres['weight'])): ?>
        <div style="background: #fdfdfd; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 18px; margin-bottom: 25px; font-size: 13px; display: flex; justify-content: space-around; align-items: center; border-left: 4px solid #4f46e5;">
            <?php if (!empty($pres['blood_pressure'])): ?>
                <div><strong>Blood Pressure:</strong> <?= esc($pres['blood_pressure']) ?></div>
            <?php endif; ?>
            <?php if (!empty($pres['heart_rate'])): ?>
                <div><strong>Heart Rate:</strong> <?= esc($pres['heart_rate']) ?> bpm</div>
            <?php endif; ?>
            <?php if (!empty($pres['temperature'])): ?>
                <div><strong>Temperature:</strong> <?= esc($pres['temperature']) ?> °C</div>
            <?php endif; ?>
            <?php if (!empty($pres['weight'])): ?>
                <div><strong>Weight:</strong> <?= esc($pres['weight']) ?> kg</div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Rx Body -->
        <div class="rx-section">
            <div class="rx-symbol">Rₓ</div>
            
            <div class="rx-details">
                <div class="section-title">Symptoms & Clinical Notes</div>
                <p><?= nl2br(esc($pres['symptoms'])) ?></p>
            </div>

            <div class="rx-details">
                <div class="section-title">Diagnosis</div>
                <p><strong><?= nl2br(esc($pres['diagnosis'])) ?></strong></p>
            </div>

            <div class="rx-details">
                <div class="section-title">Medications (Rx Formulation)</div>
                <div class="medications-list"><?= esc($pres['medications']) ?></div>
            </div>

            <?php if (!empty($pres['instructions'])): ?>
                <div class="rx-details">
                    <div class="section-title">Instructions & Remarks</div>
                    <p><?= nl2br(esc($pres['instructions'])) ?></p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Print Footer Signature Area -->
        <div class="footer">
            <div class="hospital-footer">
                <p style="margin: 0 0 4px 0;">CarePulse Health Portal &copy; <?= date('Y') ?></p>
                <span style="font-size: 10px; color:#cbd5e1;">Generated electronically on behalf of attending doctor.</span>
            </div>
            
            <div class="signature-area">
                <div class="signature-line"></div>
                <div class="signature-title">Authorized Signature</div>
                <span style="font-size: 11px; color: #94a3b8;"><?= esc(format_doctor_name($pres['d_name'])) ?></span>
            </div>
        </div>
    </div>

    <script>
        // Trigger automated printing after page load
        window.addEventListener('load', function() {
            setTimeout(function() {
                window.print();
            }, 500);
        });
    </script>
</body>
</html>
