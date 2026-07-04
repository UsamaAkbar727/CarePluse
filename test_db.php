<?php
/**
 * Test DB Connection & Schema
 * Run: http://localhost/Carepulse/test_db.php
 */
session_start();
require 'config.php';

echo '<h2>🔍 CarePulse DB Test</h2>';

try {
    $pdo = get_conn();
    echo '<p style="color:green;">✅ PDO Connected successfully</p>';

    // Check tables
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo '<p>📋 Tables (' . count($tables) . '): ' . implode(', ', $tables) . '</p>';

    if (in_array('appointments', $tables)) {
        $stmt = $pdo->query('SELECT COUNT(*) as cnt FROM appointments');
        echo '<p>📊 Appointments: ' . $stmt->fetch()['cnt'] . ' records</p>';
    }

    if (in_array('users', $tables)) {
        $stmt = $pdo->query('SELECT id, username, email, role, password, is_active FROM users');
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo '<h3>👥 Users Table Content:</h3>';
        echo '<table border="1" cellpadding="5" style="border-collapse: collapse;">';
        echo '<tr><th>ID</th><th>Username</th><th>Email</th><th>Role</th><th>Active</th><th>Hash</th></tr>';
        foreach ($users as $u) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($u['id']) . '</td>';
            echo '<td>' . htmlspecialchars($u['username']) . '</td>';
            echo '<td>' . htmlspecialchars($u['email']) . '</td>';
            echo '<td>' . htmlspecialchars($u['role']) . '</td>';
            echo '<td>' . htmlspecialchars($u['is_active']) . '</td>';
            echo '<td><code>' . htmlspecialchars($u['password']) . '</code></td>';
            echo '</tr>';
        }
        echo '</table>';
    }

    echo '<hr><p><a href="test_db.php?run_import=1">🔄 Re-run database.sql import?</a> (backup first!)</p>';

    if (isset($_GET['run_import']) && $_GET['run_import'] == 1) {
        echo '<pre>';
        system('mysql -u root -p"" carepulse_db < database.sql 2>&1');
        echo '</pre>';
    }

} catch (Exception $e) {
    echo '<p style="color:red;">❌ Error: ' . $e->getMessage() . '</p>';
    echo '<p>💡 Steps:<br>1. Import database.sql (phpMyAdmin or CLI)<br>2. Copy .env.example → .env</p>';
}
?>