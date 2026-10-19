<?php
session_start();
include 'config.php';

// Language Setup
if (!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = 'en';
}
if (isset($_GET['lang']) && ($_GET['lang'] == 'tl' || $_GET['lang'] == 'en')) {
    $_SESSION['lang'] = $_GET['lang'];
}
$lang = $_SESSION['lang'];

$translations = [
    'en' => [
        'portal_title' => 'BESTLINK COLLEGE OF THE PHILIPPINES',
        'tagline' => '"Be trained to be the BEST, Be Linked to SUCCESS"',
        'reg_title' => 'Library Account Registration',
        'reg_sub' => 'Register your student details to start borrowing books',
        'student_id' => 'Student ID Number',
        'id_placeholder' => 'e.g., 22017940',
        'fullname' => 'Full Name',
        'name_placeholder' => 'First Name Middle Initial Last Name',
        'email' => 'Email Address',
        'email_placeholder' => 'student@bcp.edu.ph',
        'password' => 'Password',
        'pass_placeholder' => 'Create a secure password',
        'reg_btn' => 'Register Account',
        'has_account' => 'Already registered?',
        'login_link' => 'Back to Student Login',
        'msg_exists' => 'Student ID is already registered! Please log in.',
        'msg_success' => 'Account created successfully! You can now log in.'
    ],
    'tl' => [
        'portal_title' => 'BESTLINK COLLEGE OF THE PHILIPPINES',
        'tagline' => '"Be trained to be the BEST, Be Linked to SUCCESS"',
        'reg_title' => 'Rehistrasyon ng Account sa Library',
        'reg_sub' => 'Magrehistro upang makapagsimula sa pag-i-reserve ng libro',
        'student_id' => 'Student ID Number',
        'id_placeholder' => 'Halimbawa: 22017940',
        'fullname' => 'Buong Pangalan',
        'name_placeholder' => 'Pangalan Apelyido',
        'email' => 'Email Address',
        'email_placeholder' => 'student@bcp.edu.ph',
        'password' => 'Password',
        'pass_placeholder' => 'Gumawa ng password',
        'reg_btn' => 'Mag-rehistro ng Account',
        'has_account' => 'May account ka na ba?',
        'login_link' => 'Bumalik sa Login',
        'msg_exists' => 'Ang Student ID na ito ay rehistrado na! Mag-log in na lamang.',
        'msg_success' => 'Matagumpay na na-rehistro ang iyong account! Maaari ka nang mag-log in.'
    ]
];
$t = $translations[$lang];

$message = "";
$msg_type = "";

if (isset($_POST['register'])) {
    $student_id = trim($_POST['student_id']);
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $role = 'Student';

    $check_query = "SELECT * FROM users WHERE student_id = '$student_id'";
    $check_result = $conn->query($check_query);

    if ($check_result && $check_result->num_rows > 0) {
        $message = $t['msg_exists'];
        $msg_type = "danger";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (student_id, fullname, email, password, role) VALUES ('$student_id', '$fullname', '$email', '$hashed_password', '$role')";
        
        if ($conn->query($sql) === TRUE) {
            $message = $t['msg_success'];
            $msg_type = "success";
        } else {
            $message = "Database Error: " . $conn->error;
            $msg_type = "danger";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration - BCP Library</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body class="d-flex flex-column min-vh-100 bg-light">

    <header class="bcp-header py-3 shadow-sm">
        <div class="container d-flex justify-content-between align-items-center">
            <div>
                <div class="bcp-logo-title fs-4"><i class="fas fa-university me-2"></i><?php echo $t['portal_title']; ?></div>
                <div class="bcp-tagline"><?php echo $t['tagline']; ?></div>
            </div>
            <!-- Language Switcher Button -->
            <div class="dropdown">
                <button class="btn btn-outline-light btn-sm dropdown-toggle" type="button" id="langDropdown" data-bs-toggle="dropdown">
                    <i class="fas fa-globe me-1"></i> <?php echo ($lang == 'en') ? 'English' : 'Tagalog'; ?>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                    <li><a class="dropdown-item <?php echo ($lang == 'en') ? 'active' : ''; ?>" href="?lang=en">🇬🇧 English</a></li>
                    <li><a class="dropdown-item <?php echo ($lang == 'tl') ? 'active' : ''; ?>" href="?lang=tl">🇵🇭 Tagalog</a></li>
                </ul>
            </div>
        </div>
    </header>

    <div class="container d-flex justify-content-center align-items-center my-auto py-4">
        <div class="card bcp-card p-4 shadow-lg" style="max-width: 450px; width: 100%;">
            <div class="text-center mb-4">
                <i class="fas fa-user-graduate fa-3x mb-2" style="color: var(--bcp-blue);"></i>
                <h4 class="fw-bold text-dark"><?php echo $t['reg_title']; ?></h4>
                <p class="text-muted small"><?php echo $t['reg_sub']; ?></p>
            </div>

            <?php if (!empty($message)): ?>
                <div class="alert alert-<?php echo $msg_type; ?> py-2 small shadow-sm" role="alert">
                    <i class="fas fa-info-circle me-1"></i> <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mb-3">
                    <label class="form-label fw-semibold"><i class="fas fa-id-card me-1" style="color: var(--bcp-blue);"></i> <?php echo $t['student_id']; ?></label>
                    <input type="text" name="student_id" class="form-control" placeholder="<?php echo $t['id_placeholder']; ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold"><i class="fas fa-user me-1" style="color: var(--bcp-blue);"></i> <?php echo $t['fullname']; ?></label>
                    <input type="text" name="fullname" class="form-control" placeholder="<?php echo $t['name_placeholder']; ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold"><i class="fas fa-envelope me-1" style="color: var(--bcp-blue);"></i> <?php echo $t['email']; ?></label>
                    <input type="email" name="email" class="form-control" placeholder="<?php echo $t['email_placeholder']; ?>" required>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold"><i class="fas fa-key me-1" style="color: var(--bcp-blue);"></i> <?php echo $t['password']; ?></label>
                    <div class="input-group">
                        <input type="password" id="regPassword" name="password" class="form-control" placeholder="<?php echo $t['pass_placeholder']; ?>" required>
                        <button class="btn btn-outline-secondary" type="button" id="toggleRegPasswordBtn" onclick="togglePasswordVisibility('regPassword', 'toggleRegPasswordIcon')">
                            <i class="fas fa-eye" id="toggleRegPasswordIcon"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" name="register" class="btn btn-bcp-gold w-100 py-2 fw-bold mb-3">
                    <i class="fas fa-check-circle me-1"></i> <?php echo $t['reg_btn']; ?>
                </button>
            </form>

            <div class="text-center border-top pt-3 mt-2">
                <p class="small text-muted mb-0"><?php echo $t['has_account']; ?></p>
                <a href="login.php" class="fw-bold text-decoration-none" style="color: var(--bcp-blue);">
                    <i class="fas fa-arrow-left me-1"></i> <?php echo $t['login_link']; ?>
                </a>
            </div>
        </div>
    </div>

    <!-- Footer with GitHub Link -->
    <footer class="text-center py-3 bg-dark text-white-50 mt-auto">
        <div class="container">
            <small>&copy; BCP Library System | Connected via Open Source Repository</small><br>
            <a href="https://github.com" target="_blank" class="text-white text-decoration-none small mt-1 d-inline-block">
                <i class="fab fa-github me-1 fs-6"></i> View Project Source on GitHub
            </a>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function togglePasswordVisibility(inputId, iconId) {
            const passwordInput = document.getElementById(inputId);
            const toggleIcon = document.getElementById(iconId);
            if (passwordInput.type === "password") {
                passwordInput.type = "text";
                toggleIcon.classList.remove("fa-eye");
                toggleIcon.classList.add("fa-eye-slash");
            } else {
                passwordInput.type = "password";
                toggleIcon.classList.remove("fa-eye-slash");
                toggleIcon.classList.add("fa-eye");
            }
        }
    </script>
</body>
</html>