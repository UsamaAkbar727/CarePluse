<?php
require_once 'config.php';

try {
    $pdo = get_db_pdo();
    // Test direct match first
    $stmt = $pdo->prepare("SELECT id, username, password, email, role FROM users WHERE username = ? OR email = ?");
    $stmt->execute(['admin', 'admin@carepulse.com']);
    $user = $stmt->fetch();

    echo "<div style='font-family: sans-serif; max-width: 600px; margin: 20px auto; padding: 20px; border: 1px solid #ccc; border-radius: 8px;'>";
    echo "<h2>Admin Connection Diagnostic</h2>";
    
    if ($user) {
        echo "<p style='color: green;'>✅ <strong>User Found:</strong> Account exists in database.</p>";
        echo "<ul>";
        echo "<li><strong>User ID:</strong> " . $user['id'] . "</li>";
        echo "<li><strong>Username:</strong> " . $user['username'] . "</li>";
        echo "<li><strong>Email:</strong> " . $user['email'] . "</li>";
        echo "<li><strong>Role:</strong> " . $user['role'] . "</li>";
        echo "</ul>";

        // Password Test
        $test_pw = 'Admin@123';
        $is_correct = password_verify($test_pw, $user['password']);
        
        echo "<h3>Password Verification</h3>";
        if ($is_correct) {
            echo "<p style='color: green; font-weight: bold;'>✅ SUCCESS: 'Admin@123' matches the password in the database!</p>";
            echo "<p>Try logging in now at <a href='login.php'>login.php</a>.</p>";
        } else {
            echo "<p style='color: red; font-weight: bold;'>❌ FAIL: 'Admin@123' does NOT match the database hash.</p>";
            echo "<p>HASH in DB: <code>" . $user['password'] . "</code></p>";
            echo "<p>Checking if old password 'password' matches...</p>";
            if (password_verify('password', $user['password'])) {
                echo "<p style='color: blue;'>ℹ️ Old password 'password' still matches. The update script didn't overwrite it yet.</p>";
            }
        }
    } else {
        echo "<p style='color: red;'>❌ <strong>User Not Found:</strong> No user with name 'admin' or email 'admin@carepulse.com' was found.</p>";
    }
    echo "</div>";
} catch (Exception $e) {
    echo "<div style='color: red; padding: 20px;'><strong>Error:</strong> " . $e->getMessage() . "</div>";
}
?>
