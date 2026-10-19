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
        'login_title' => 'Library Portal Login',
        'login_sub' => 'Enter your Student ID and Password',
        'student_id' => 'Student ID Number',
        'id_placeholder' => 'e.g., 22017940',
        'password' => 'Account Password',
        'pass_placeholder' => 'Enter password',
        'login_btn' => 'Login to Portal',
        'no_account' => "Don't have an account?",
        'register_link' => 'Create an Account',
        'err_pass' => 'Incorrect password. Please try again.',
        'err_id' => 'Student ID not found. Please register first.'
    ],
    'tl' => [
        'portal_title' => 'BESTLINK COLLEGE OF THE PHILIPPINES',
        'tagline' => '"Be trained to be the BEST, Be Linked to SUCCESS"',
        'login_title' => 'Login sa Library Portal',
        'login_sub' => 'I-type ang iyong Student ID at Password',
        'student_id' => 'Student ID Number',
        'id_placeholder' => 'Halimbawa: 22017940',
        'password' => 'Password ng Account',
        'pass_placeholder' => 'I-type ang password',
        'login_btn' => 'Mag-login sa Portal',
        'no_account' => 'Wala ka pang account?',
        'register_link' => 'Gumawa ng Account',
        'err_pass' => 'Mali ang iyong password. Subukang muli.',
        'err_id' => 'Hindi makita ang Student ID na ito. Magrehistro muna.'
    ]
];
$t = $translations[$lang];

$error = "";

if (isset($_POST['login'])) {
    $student_id = trim($_POST['student_id']);
    $password = trim($_POST['password']);

    $sql = "SELECT * FROM users WHERE student_id = '$student_id'";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['student_id'] = $user['student_id'];
            $_SESSION['fullname'] = $user['fullname'];
            header("Location: dashboard.php");
            exit();
        } else {
            $error = $t['err_pass'];
        }
    } else {
        $error = $t['err_id'];
    }
}
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Login - BCP Library</title>
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
        <div class="card bcp-card p-4 shadow-lg" style="max-width: 420px; width: 100%;">
            <div class="text-center mb-4">
                <i class="fas fa-book-reader fa-3x mb-2" style="color: var(--bcp-blue);"></i>
                <h4 class="fw-bold text-dark"><?php echo $t['login_title']; ?></h4>
                <p class="text-muted small"><?php echo $t['login_sub']; ?></p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger py-2 small shadow-sm" role="alert">
                    <i class="fas fa-exclamation-circle me-1"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mb-3">
                    <label class="form-label fw-semibold"><i class="fas fa-id-card me-1" style="color: var(--bcp-blue);"></i> <?php echo $t['student_id']; ?></label>
                    <input type="text" name="student_id" class="form-control" placeholder="<?php echo $t['id_placeholder']; ?>" required>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold"><i class="fas fa-lock me-1" style="color: var(--bcp-blue);"></i> <?php echo $t['password']; ?></label>
                    <div class="input-group">
                        <input type="password" id="loginPassword" name="password" class="form-control" placeholder="<?php echo $t['pass_placeholder']; ?>" required>
                        <button class="btn btn-outline-secondary" type="button" id="togglePasswordBtn" onclick="togglePasswordVisibility('loginPassword', 'togglePasswordIcon')">
                            <i class="fas fa-eye" id="togglePasswordIcon"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" name="login" class="btn btn-bcp-primary w-100 py-2 fw-bold mb-3">
                    <i class="fas fa-sign-in-alt me-1"></i> <?php echo $t['login_btn']; ?>
                </button>
            </form>

            <div class="text-center border-top pt-3 mt-2">
                <p class="small text-muted mb-0"><?php echo $t['no_account']; ?></p>
                <a href="register.php" class="fw-bold text-decoration-none" style="color: var(--bcp-blue);">
                    <i class="fas fa-user-plus me-1"></i> <?php echo $t['register_link']; ?>
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