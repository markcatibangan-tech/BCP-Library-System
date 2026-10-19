<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BCP Library System Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</>
<body class="bg-light d-flex justify-content-center align-items-center vh-100">

    <div class="card bcp-card p-5 shadow-lg text-center" style="max-width: 450px; width: 100%;">
        <div class="mb-4">
            <i class="fas fa-university fa-3x mb-2" style="color: var(--bcp-blue);"></i>
            <h3 class="fw-bold text-dark">BCP Library Portal</h3>
            <p class="text-muted small">Bestlink College of the Philippines</p>
        </div>

        <div class="d-grid gap-3">
            <a href="login.php" class="btn btn-primary py-3 fw-bold">
                <i class="fas fa-user-graduate me-2"></i> Student Portal (Login / Register)
            </a>
                <i class="fas fa-user-shield me-2"></i> Admin & Librarian Portal
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>