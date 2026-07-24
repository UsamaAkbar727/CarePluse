<?php
/**
 * CarePulse EHR Timeline Engine
 */
require_once __DIR__ . '/../config.php';

class EHRTimelineEngine {

    public static function getPatientTimeline($patientId) {
        $pdo = get_conn();
        $timeline = [];

        // 1. Appointments
        $stmt = $pdo->prepare("
            SELECT a.id, a.app_date, a.app_time, a.status, a.notes, d.name as doctor_name, d.specialization
            FROM appointments a
            JOIN doctors d ON a.doctor_id = d.id
            WHERE a.patient_id = ?
            ORDER BY a.app_date DESC, a.app_time DESC
        ");
        $stmt->execute([$patientId]);
        $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($appointments as $app) {
            $timeline[] = [
                'type' => 'appointment',
                'timestamp' => $app['app_date'] . ' ' . ($app['app_time'] ?? '00:00:00'),
                'title' => 'Consultation with ' . $app['doctor_name'] . ' (' . $app['specialization'] . ')',
                'status' => ucfirst($app['status']),
                'details' => $app['notes'] ?: 'No notes attached',
                'icon' => 'calendar',
                'badge_class' => ($app['status'] === 'completed') ? 'bg-success' : 'bg-primary'
            ];
        }

        // 2. Prescriptions
        $stmt = $pdo->prepare("
            SELECT p.*, d.name as doctor_name
            FROM prescriptions p
            JOIN doctors d ON p.doctor_id = d.id
            WHERE p.patient_id = ?
            ORDER BY p.created_at DESC
        ");
        $stmt->execute([$patientId]);
        $prescriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($prescriptions as $p) {
            $vitals = [];
            if ($p['blood_pressure']) $vitals[] = "BP: " . $p['blood_pressure'];
            if ($p['heart_rate']) $vitals[] = "HR: " . $p['heart_rate'] . " bpm";
            if ($p['temperature']) $vitals[] = "Temp: " . $p['temperature'] . "°F";

            $timeline[] = [
                'type' => 'prescription',
                'timestamp' => $p['created_at'],
                'title' => 'Prescription & Medical Diagnosis',
                'status' => 'Rx Recorded',
                'details' => 'Diagnosis: ' . ($p['diagnosis'] ?: 'N/A') . ' | Vitals: [' . implode(', ', $vitals) . ']',
                'icon' => 'pill',
                'badge_class' => 'bg-info'
            ];
        }

        // 3. Admissions
        $stmt = $pdo->prepare("
            SELECT adm.*, w.name as ward_name, b.bed_number, d.name as doctor_name
            FROM admissions adm
            JOIN beds b ON adm.bed_id = b.id
            JOIN wards w ON b.ward_id = w.id
            JOIN doctors d ON adm.doctor_id = d.id
            WHERE adm.patient_id = ?
            ORDER BY adm.admission_date DESC
        ");
        $stmt->execute([$patientId]);
        $admissions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($admissions as $adm) {
            $timeline[] = [
                'type' => 'admission',
                'timestamp' => $adm['admission_date'] . ' 08:00:00',
                'title' => 'Ward Bed Admission: ' . $adm['ward_name'] . ' (Bed ' . $adm['bed_number'] . ')',
                'status' => ucfirst($adm['status']),
                'details' => 'Attending Physician: ' . $adm['doctor_name'] . ' | Rate: $' . number_format($adm['room_charges'], 2) . '/day',
                'icon' => 'bed',
                'badge_class' => ($adm['status'] === 'admitted') ? 'bg-warning text-dark' : 'bg-secondary'
            ];
        }

        // 4. Lab Requests
        $stmt = $pdo->prepare("
            SELECT lr.*, lt.name as test_name, a.app_date
            FROM lab_requests lr
            JOIN lab_tests lt ON lr.test_id = lt.id
            JOIN appointments a ON lr.appointment_id = a.id
            WHERE a.patient_id = ?
            ORDER BY lr.created_at DESC
        ");
        $stmt->execute([$patientId]);
        $labRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($labRequests as $lr) {
            $timeline[] = [
                'type' => 'lab',
                'timestamp' => $lr['created_at'],
                'title' => 'Lab Test: ' . $lr['test_name'],
                'status' => ucfirst($lr['status']),
                'details' => 'Results: ' . ($lr['result_details'] ?: 'Pending laboratory processing'),
                'icon' => 'activity',
                'badge_class' => ($lr['status'] === 'completed') ? 'bg-success' : 'bg-warning text-dark'
            ];
        }

        // Sort all chronologically descending
        usort($timeline, function($a, $b) {
            return strtotime($b['timestamp']) - strtotime($a['timestamp']);
        });

        return $timeline;
    }
}
