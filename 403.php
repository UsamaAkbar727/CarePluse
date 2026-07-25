<?php
require_once 'includes/functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>403 - Access Denied</title>
    <link rel="icon" type="image/png" href="favicon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; height: 100vh; display: flex; align-items: center; justify-content: center; font-family: sans-serif; }
        .error-card { text-align: center; max-width: 500px; padding: 40px; background: white; border-radius: 20px; shadow: 0 10px 30px rgba(0,0,0,0.05); }
        .error-code { font-size: 80px; font-weight: 800; color: #dc3545; line-height: 1; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="error-card shadow">
        <div class="error-code">403</div>
        <h3 class="fw-bold mb-3">Access Denied</h3>
        <p class="text-muted mb-4">Sorry, you don't have permission to access this page. Please contact your administrator if you believe this is an error.</p>
        <a href="index.php" class="btn btn-primary rounded-pill px-4 py-2">Return to Dashboard</a>
    </div>
</body>
</html>
