<?php
/**
 * CarePulse Drug Safety & Contraindication Checking Service
 */
require_once __DIR__ . '/../config.php';

class DrugSafetyEngine {
    
    /**
     * Check array of medicine IDs for mutual contraindications
     */
    public static function checkInteractions(array $medicineIds) {
        if (count($medicineIds) < 2) {
            return ['has_warnings' => false, 'warnings' => []];
        }

        $pdo = get_conn();
        $placeholders = implode(',', array_fill(0, count($medicineIds), '?'));
        
        $sql = "
            SELECT dc.*, m1.name as medicine_a_name, m2.name as medicine_b_name 
            FROM drug_contraindications dc
            JOIN medicines m1 ON dc.medicine_a_id = m1.id
            JOIN medicines m2 ON dc.medicine_b_id = m2.id
            WHERE (dc.medicine_a_id IN ($placeholders) AND dc.medicine_b_id IN ($placeholders))
               OR (dc.medicine_b_id IN ($placeholders) AND dc.medicine_a_id IN ($placeholders))
        ";

        $params = array_merge($medicineIds, $medicineIds, $medicineIds, $medicineIds);
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $contraindications = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $warnings = [];
        foreach ($contraindications as $c) {
            $warnings[] = [
                'severity' => $c['severity'],
                'drug_a' => $c['medicine_a_name'],
                'drug_b' => $c['medicine_b_name'],
                'message' => "Potential " . $c['severity'] . " interaction between " . $c['medicine_a_name'] . " & " . $c['medicine_b_name'] . ": " . $c['description']
            ];
        }

        return [
            'has_warnings' => count($warnings) > 0,
            'warnings' => $warnings
        ];
    }
}

// AJAX API Endpoint
if (basename($_SERVER['PHP_SELF']) === 'drug_safety.php' && isset($_GET['action']) && $_GET['action'] === 'check') {
    header('Content-Type: application/json');
    $ids = isset($_GET['medicine_ids']) ? explode(',', $_GET['medicine_ids']) : [];
    $medicineIds = array_map('intval', array_filter($ids));
    
    $result = DrugSafetyEngine::checkInteractions($medicineIds);
    echo json_encode($result);
    exit;
}
