<?php
session_start();
include 'config.php';

// Language Setup
if (!isset($_SESSION['lang'])) { $_SESSION['lang'] = 'en'; }
$lang = $_SESSION['lang'];

$message = "";
$msg_type = "";

if (isset($_POST['register_admin'])) {
    $admin_id = trim($_POST['admin_id']);
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $secret_code = trim($_POST['secret_code']);
    $role = 'Admin';

    // 1. I-verify kung valid at hindi pa nagagamit ang Secret Code mula sa nakakataas
    $code_check = "SELECT * FROM admin_codes WHERE access_code = '$secret_code' AND is_used = 0";
    $code_result = $conn->query($code_check);

    if ($code_result && $code_result->num_rows > 0) {
        // 2. I-check kung existing na ang Admin ID
        $check_query = "SELECT * FROM users WHERE student_id = '$admin_id'";
        $check_result = $conn->query($check_query);

        if ($check_result && $check_result->num_rows > 0) {
            $message = "Admin ID is already registered!";
            $msg_type = "danger";
        } else {
            // 3. I-save ang bagong Admin account
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (student_id, fullname, email, password, role) VALUES ('$admin_id', '$fullname', '$email', '$hashed_password', '$role')";
            
            if ($conn->query($sql) === TRUE) {
                // I-mark ang code bilang nagamit na para hindi na magamit ulit
                $conn->query("UPDATE admin_codes SET is_used = 1 WHERE access_code = '$secret_code'");
                
                $message = "Admin account registered successfully! You can now log in.";
                $msg_type = "success";
            } else {
                $message = "Database Error: " . $conn->error;
                $msg_type = "danger";
            }
        }
    } else {
        $message = "Invalid or already used Admin Access Code! Ask higher-ups for a valid code.";
        $msg_type = "danger";
    }
}
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Registration - BCP Library</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body class="d-flex flex-column min-vh-100 bg-light">

    <header class="bcp-header py-3 shadow-sm" style="background-color: #8B0000;">
        <div class="container d-flex justify-content-between align-items-center">
            <div>
                <div class="bcp-logo-title fs-4 text-white"><i class="fas fa-shield-alt me-2"></i>BCP LIBRARY ADMIN PORTAL</div>
                <div class="bcp-tagline text-white-50">Secure Staff Registration</div>
            </div>
            <a href="admin_login.php" class="btn btn-outline-light btn-sm"><i class="fas fa-sign-in-alt me-1"></i> Admin Login</a>
        </div>
    </header>

    <div class="container d-flex justify-content-center align-items-center my-auto py-4">
        <div class="card bcp-card p-4 shadow-lg border-danger" style="max-width: 450px; width: 100%;">
            <div class="text-center mb-4">
                <i class="fas fa-user-shield fa-3x mb-2 text-danger"></i>
                <h4 class="fw-bold text-dark">Register New Admin</h4>
                <p class="text-muted small">Requires authorized clearance code from management</p>
            </div>

            <?php if (!empty($message)): ?>
                <div class="alert alert-<?php echo $msg_type; ?> py-2 small shadow-sm" role="alert">
                    <i class="fas fa-info-circle me-1"></i> <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mb-3">
                    <label class="form-label fw-semibold"><i class="fas fa-key text-danger me-1"></i> Admin Access Code (From Management)</label>
                    <input type="text" name="secret_code" class="form-control border-danger" placeholder="Enter security clearance code" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold"><i class="fas fa-id-badge text-danger me-1"></i> Admin Staff ID</label>
                    <input type="text" name="admin_id" class="form-control" placeholder="e.g., ADM-2026-001" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold"><i class="fas fa-user text-danger me-1"></i> Full Name</label>
                    <input type="text" name="fullname" class="form-control" placeholder="Staff Full Name" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold"><i class="fas fa-envelope text-danger me-1"></i> Email Address</label>
                    <input type="email" name="email" class="form-control" placeholder="admin@bcp.edu.ph" required>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold"><i class="fas fa-lock text-danger me-1"></i> Password</label>
                    <div class="input-group">
                        <input type="password" id="adminRegPass" name="password" class="form-control" placeholder="Create secure password" required>
                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('adminRegPass', 'adminRegIcon')">
                            <i class="fas fa-eye" id="adminRegIcon"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" name="register_admin" class="btn btn-danger w-100 py-2 fw-bold mb-3">
                    <i class="fas fa-user-plus me-1"></i> Register Admin Account
                </button>
            </form>

            <div class="text-center border-top pt-3 mt-2">
                <a href="admin_login.php" class="text-decoration-none fw-semibold text-danger">
                    <i class="fas fa-arrow-left me-1"></i> Already have an admin account? Login
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function togglePassword(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
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