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
