<?php
session_start();
require_once 'config.php';
require_once 'includes/functions.php';

require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'], $_POST['status'])) {
    $pdo = get_db_pdo();
    $id = (int)$_POST['id'];
    $status = $_POST['status'];
    $allowed_statuses = ['pending', 'confirmed', 'completed', 'cancelled'];

    if (!in_array($status, $allowed_statuses)) {
        set_flash('Invalid status selected.', 'danger');
        header("Location: appointment_details.php?id=$id");
        exit();
    }

    try {
        // Fetch old values for audit log
        $stmt = $pdo->prepare('SELECT status FROM appointments WHERE id = ?');
        $stmt->execute([$id]);
        $old_status = $stmt->fetchColumn();

        if ($old_status === false) {
            set_flash('Appointment not found.', 'danger');
            header('Location: appointments.php');
            exit();
        }

        // Update status
        $stmt = $pdo->prepare('UPDATE appointments SET status = ?, updated_by = ?, updated_at = NOW() WHERE id = ?');
        if ($stmt->execute([$status, $_SESSION['user_id'], $id])) {
            
            // Audit Log
            audit_log($pdo, 'UPDATE_STATUS', 'appointments', $id, ['status' => $old_status], ['status' => $status]);
            
            // Auto-generate invoice if status is 'completed'
            if ($status === 'completed') {
                $chk_stmt = $pdo->prepare('SELECT id FROM invoices WHERE appointment_id = ?');
                $chk_stmt->execute([$id]);
                if (!$chk_stmt->fetchColumn()) {
                    $appt_stmt = $pdo->prepare('SELECT patient_id FROM appointments WHERE id = ?');
                    $appt_stmt->execute([$id]);
                    $patient_id = $appt_stmt->fetchColumn();

                    if ($patient_id) {
                        // Default consultation fee: $50.00, tax 5%: $2.50, net: $52.50
                        $inv_stmt = $pdo->prepare('INSERT INTO invoices (appointment_id, patient_id, total_amount, tax, net_amount, status) VALUES (?, ?, 50.00, 2.50, 52.50, "unpaid")');
                        $inv_stmt->execute([$id, $patient_id]);
                        $invoice_id = $pdo->lastInsertId();

                        $item_stmt = $pdo->prepare('INSERT INTO invoice_items (invoice_id, item_description, quantity, unit_price, total_price) VALUES (?, "Doctor Consultation Fee", 1, 50.00, 50.00)');
                        $item_stmt->execute([$invoice_id]);
                    }
                }
            }

            set_flash("Appointment #$id status updated to " . ucfirst($status) . ".", "success");
        } else {
            set_flash("Failed to update status.", "danger");
        }
    } catch (PDOException $e) {
        set_flash("Error: " . $e->getMessage(), "danger");
    }
}

header("Location: appointment_details.php?id=$id");
exit();
