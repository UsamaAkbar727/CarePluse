<?php
session_start();
require_once 'includes/functions.php';

// Audit logout
$pdo = get_db_pdo();
audit_log($pdo, 'LOGOUT', 'users', $_SESSION['user_id'] ?? null);

session_unset();
session_destroy();
session_write_close();
setcookie(session_name(), '', 0, '/');
header("Location: login.php");
exit();

?>