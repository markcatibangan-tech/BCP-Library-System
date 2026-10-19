<?php
session_start();
include 'config.php';

$error = "";

if (isset($_POST['admin_login'])) {
    // Sinisigurado natin na sasalo nito ang 'username' o 'admin_id' mula sa form
    $admin_id = trim($_POST['username'] ?? $_POST['admin_id'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!empty($admin_id) && !empty($password)) {
        // I-check sa database kung ang student_id o email ay Admin ang role
        $sql = "SELECT * FROM users WHERE (student_id = '$admin_id' OR email = '$admin_id') AND role = 'Admin'";
        $result = $conn->query($sql);

        if ($result && $result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            // I-verify ang hashed password
            if (password_verify($password, $user['password'])) {
                $_SESSION['admin_id'] = $user['student_id'];
                $_SESSION['admin_name'] = $user['fullname'];
                $_SESSION['role'] = 'Admin';
                
                // Diretso na sa admin dashboard
                header("Location: admin.php");
                exit();
            } else {
                $error = "Incorrect password. Please try again.";
            }
        } else {
            $error = "Admin ID not found, or this account does not have 'Admin' privileges in the database.";
        }
    } else {
        $error = "Please fill in all required fields.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Portal - BCP Library</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-light d-flex flex-column min-vh-100 justify-content-center align-items-center">

    <div class="card p-4 shadow-lg border-0 rounded-4" style="max-width: 420px; width: 100%;">
        <div class="text-center mb-4">
            <div class="text-primary mb-2">
                <i class="fas fa-user-shield fa-3x" style="color: #6f42c1;"></i>
            </div>
            <h3 class="fw-bold text-dark">Admin Portal</h3>
            <p class="text-muted small">Bestlink College of the Philippines</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger py-2 small shadow-sm" role="alert">
                <i class="fas fa-exclamation-circle me-1"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-3">
                <label class="form-label fw-semibold small text-secondary">Username</label>
                <input type="text" name="username" class="form-control" placeholder="Enter admin username" required>
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold small text-secondary">Password</label>
                <div class="input-group">
                    <input type="password" id="adminPass" name="password" class="form-control" placeholder="Enter admin password" required>
                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword()">
                        <i class="fas fa-eye" id="toggleIcon"></i>
                    </button>
                </div>
            </div>

            <button type="submit" name="admin_login" class="btn text-white w-100 py-2 fw-bold shadow-sm" style="background-color: #6f42c1;">
                Login to Portal
            </button>
        </form>

        <div class="text-center mt-3 border-top pt-3">
            <a href="admin_portal.php" class="text-decoration-none small text-muted">
                <i class="fas fa-arrow-left me-1"></i> Back to Student View / Portal Gate
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function togglePassword() {
            const input = document.getElementById('adminPass');
            const icon = document.getElementById('toggleIcon');
            if (input.type === "password") {
                input.type = "text";
                icon.classList.remove("fa-eye");
                icon.classList.add("fa-eye-slash");
            } else {
                input.type = "password";
                icon.classList.remove("fa-eye-slash");
                icon.classList.add("fa-eye");
            }
        }
    </script>
</body>
</html>