<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BCP Library - Staff Clearance Gate</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-dark text-white min-vh-100 d-flex align-items-center justify-content-center">

    <div class="card bg-secondary text-white p-4 shadow-lg border-danger text-center" style="max-width: 450px; width: 100%;">
        <div class="mb-3">
            <i class="fas fa-user-shield fa-4x text-danger mb-2"></i>
            <h3 class="fw-bold">BCP Staff & Admin Gate</h3>
            <p class="text-white-50 small">Restricted access portal for authorized personnel only.</p>
        </div>

        <div class="d-grid gap-3">
            <a href="admin_login.php" class="btn btn-danger btn-lg fw-bold py-2">
                <i class="fas fa-sign-in-alt me-2"></i> Admin Login
            </a>
            <a href="admin_register.php" class="btn btn-outline-light btn-lg fw-bold py-2">
                <i class="fas fa-key me-2"></i> Register New Admin (Code Required)
            </a>
        </div>

        <div class="mt-4 border-top border-secondary pt-3">
            <a href="index.php" class="text-white-50 text-decoration-none small">
                <i class="fas fa-arrow-left me-1"></i> Return to Student Portal
            </a>
        </div>
    </div>

</body>
</html>