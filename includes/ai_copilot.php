<?php
/**
 * CarePulse Enterprise AI Clinical Copilot & Decision Engine
 */
require_once __DIR__ . '/../config.php';

class CarePulseAI {
    
    /**
     * Analyze symptoms and patient vitals to generate clinical decision support recommendations.
     */
    public static function analyzeSymptoms($symptoms, $vitals = []) {
        $symptomsLower = strtolower($symptoms);
        $recommendations = [];
        $suggestedDiagnosis = [];
        $recommendedTests = [];
        $riskScore = 'Low';

        // Symptom Rule Engine & ML Simulation
        if (strpos($symptomsLower, 'chest pain') !== false || strpos($symptomsLower, 'shortness of breath') !== false) {
            $riskScore = 'Critical';
            $suggestedDiagnosis[] = 'Possible Acute Coronary Syndrome / Angina';
            $suggestedDiagnosis[] = 'Pulmonary Embolism Evaluation';
            $recommendedTests[] = 'Electrocardiogram (ECG)';
            $recommendedTests[] = 'Troponin I & Cardiac Enzymes';
            $recommendedTests[] = 'Chest X-Ray Digital';
        } elseif (strpos($symptomsLower, 'fever') !== false && strpos($symptomsLower, 'cough') !== false) {
            $riskScore = 'Moderate';
            $suggestedDiagnosis[] = 'Acute Respiratory Tract Infection';
            $suggestedDiagnosis[] = 'Community-Acquired Pneumonia';
            $recommendedTests[] = 'Complete Blood Count (CBC)';
            $recommendedTests[] = 'Chest X-Ray Digital';
        } elseif (strpos($symptomsLower, 'headache') !== false || strpos($symptomsLower, 'dizziness') !== false) {
            $riskScore = 'Moderate';
            $suggestedDiagnosis[] = 'Hypertensive Cephalea / Tension Headache';
            $suggestedDiagnosis[] = 'Neurological Evaluation';
            $recommendedTests[] = 'Serum Glucose (Sugar)';
            $recommendedTests[] = 'BP Monitoring Panel';
        } else {
            $suggestedDiagnosis[] = 'General Clinical Assessment';
            $recommendedTests[] = 'Complete Blood Count (CBC)';
        }

        // Vitals evaluation
        if (!empty($vitals['blood_pressure'])) {
            $bpParts = explode('/', $vitals['blood_pressure']);
            if (count($bpParts) == 2) {
                $systolic = (int)$bpParts[0];
                if ($systolic >= 140) {
                    $recommendations[] = '⚠️ High Systolic Pressure Detected (' . $systolic . ' mmHg). Consider Antihypertensive therapy assessment.';
                    if ($riskScore !== 'Critical') $riskScore = 'High';
                }
            }
        }

        if (!empty($vitals['heart_rate'])) {
            $hr = (int)$vitals['heart_rate'];
            if ($hr > 100) {
                $recommendations[] = '⚠️ Tachycardia observed (HR: ' . $hr . ' bpm). Recommend resting ECG.';
            } elseif ($hr < 60) {
                $recommendations[] = '⚠️ Bradycardia observed (HR: ' . $hr . ' bpm). Check patient medication history.';
            }
        }

        $summary = "Based on symptom pattern analysis [" . htmlspecialchars($symptoms) . "] and vitals telemetry, patient shows a " . strtoupper($riskScore) . " clinical risk profile. Primary differential diagnosis: " . implode(', ', $suggestedDiagnosis) . ".";

        return [
            'risk_level' => $riskScore,
            'suggested_diagnosis' => implode(' | ', $suggestedDiagnosis),
            'recommended_tests' => implode(' | ', $recommendedTests),
            'clinical_alerts' => $recommendations,
            'ai_summary' => $summary
        ];
    }

    /**
     * Save AI Clinical Analysis to DB
     */
    public static function saveAnalysis($patientId, $doctorId, $symptoms, $analysisData) {
        $pdo = get_conn();
        $stmt = $pdo->prepare("
            INSERT INTO ai_clinical_notes 
            (patient_id, doctor_id, symptoms, suggested_diagnosis, recommended_tests, risk_level, ai_summary)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $patientId,
            $doctorId,
            $symptoms,
            $analysisData['suggested_diagnosis'],
            $analysisData['recommended_tests'],
            $analysisData['risk_level'],
            $analysisData['ai_summary']
        ]);
        return $pdo->lastInsertId();
    }

    /**
     * Predictive Hospital Occupancy & Revenue Analytics
     */
    public static function getPredictiveAnalytics() {
        $pdo = get_conn();
        
        // Bed Occupancy
        $totalBeds = $pdo->query("SELECT COUNT(*) FROM beds")->fetchColumn() ?: 1;
        $occupiedBeds = $pdo->query("SELECT COUNT(*) FROM beds WHERE status='occupied'")->fetchColumn() ?: 0;
        $occupancyRate = round(($occupiedBeds / $totalBeds) * 100, 1);
        
        // Projected bed surge based on trend
        $projectedSurge = min(100, round($occupancyRate * 1.15, 1));

        // Revenue projections
        $currentMonthRev = $pdo->query("SELECT COALESCE(SUM(net_amount), 0) FROM invoices WHERE status='paid'")->fetchColumn();
        $projectedRev = round($currentMonthRev * 1.22, 2);

        return [
            'current_occupancy' => $occupancyRate,
            'projected_occupancy_peak' => $projectedSurge,
            'total_beds' => $totalBeds,
            'occupied_beds' => $occupiedBeds,
            'current_revenue' => $currentMonthRev,
            'projected_revenue' => $projectedRev,
            'ai_insight' => ($occupancyRate > 75) 
                ? "ALERT: High bed utilization detected ($occupancyRate%). Recommend preparing General Ward expansion B." 
                : "STABLE: System operating within normal operational capacity limits ($occupancyRate%)."
        ];
    }
}

// AJAX endpoint handler
if (basename($_SERVER['PHP_SELF']) === 'ai_copilot.php' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (isset($input['action']) && $input['action'] === 'analyze') {
        $symptoms = $input['symptoms'] ?? '';
        $vitals = $input['vitals'] ?? [];
        $result = CarePulseAI::analyzeSymptoms($symptoms, $vitals);
        echo json_encode(['status' => 'success', 'data' => $result]);
        exit;
    }
}
