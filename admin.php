<?php
session_start();
include 'config.php';

// Handle Admin Login Form Submission
$error = "";
if (isset($_POST['admin_login'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if ($username === 'admin' && $password === 'admin123') {
        $_SESSION['admin_logged_in'] = true;
        header("Location: admin.php");
        exit();
    } else {
        $error = "Invalid Admin Username or Password!";
    }
}

// Handle Admin Logout
if (isset($_GET['logout'])) {
    unset($_SESSION['admin_logged_in']);
    header("Location: admin.php");
    exit();
}

// Active Tab Handler for Admin
$admin_tab = isset($_GET['tab']) ? $_GET['tab'] : 'reservations';

// ADMIN ACTION: Update Registered Student Information
if (isset($_POST['edit_student']) && isset($_SESSION['admin_logged_in'])) {
    $student_id = $conn->real_escape_string($_POST['student_id']);
    $fullname = $conn->real_escape_string($_POST['fullname']);
    $email = $conn->real_escape_string($_POST['email']);
    $role = $conn->real_escape_string($_POST['role']);

    $conn->query("UPDATE users SET fullname = '$fullname', email = '$email', role = '$role' WHERE student_id = '$student_id'");
    header("Location: admin.php?tab=students&success=student_updated");
    exit();
}

// ADMIN ACTION: Update Book Stock Quantity
if (isset($_POST['edit_quantity']) && isset($_SESSION['admin_logged_in'])) {
    $book_id = intval($_POST['book_id']);
    $new_quantity = intval($_POST['quantity']);
    $status = ($new_quantity > 0) ? 'Available' : 'Not Available';

    $conn->query("UPDATE books SET quantity = $new_quantity, status = '$status' WHERE book_id = $book_id");
    header("Location: admin.php?tab=inventory&success=qty_updated");
    exit();
}

// Handle Adding a New Book
if (isset($_POST['add_book']) && isset($_SESSION['admin_logged_in'])) {
    $title = $conn->real_escape_string($_POST['title']);
    $author = $conn->real_escape_string($_POST['author']);
    $category = $conn->real_escape_string($_POST['category']);
    $quantity = intval($_POST['quantity']);
    $status = ($quantity > 0) ? 'Available' : 'Not Available';

    $sql = "INSERT INTO books (title, author, category, quantity, status) VALUES ('$title', '$author', '$category', $quantity, '$status')";
    if ($conn->query($sql) === TRUE) {
        header("Location: admin.php?tab=inventory&success=added");
        exit();
    }
}

// Handle Deleting a Book
if (isset($_GET['delete_id']) && isset($_SESSION['admin_logged_in'])) {
    $book_id = intval($_GET['delete_id']);
    $conn->query("DELETE FROM books WHERE book_id = $book_id");
    header("Location: admin.php?tab=inventory&success=deleted");
    exit();
}

// ADMIN ACTION: Issue Physical Book to Student
if (isset($_POST['issue_book']) && isset($_SESSION['admin_logged_in'])) {
    $trans_id = intval($_POST['trans_id']);
    $conn->query("UPDATE transactions SET action_type = 'Issued / Borrowed', status = 'Claimed' WHERE transaction_id = $trans_id");
    header("Location: admin.php?tab=reservations&success=issued");
    exit();
}

// ADMIN ACTION: Student Returns Book (Restocks Inventory)
if (isset($_POST['return_item']) && isset($_SESSION['admin_logged_in'])) {
    $trans_id = intval($_POST['trans_id']);
    $book_title = $conn->real_escape_string($_POST['book_title']);

    $conn->query("UPDATE transactions SET action_type = 'Returned', status = 'Completed' WHERE transaction_id = $trans_id");
    $conn->query("UPDATE books SET quantity = quantity + 1, status = 'Available' WHERE title = '$book_title'");

    header("Location: admin.php?tab=reservations&success=returned");
    exit();
}

// Count active pending reservations for tab badge
$pending_count_res = $conn->query("SELECT COUNT(*) as total FROM transactions WHERE action_type IN ('Reserved', 'Issued / Borrowed')");
$pending_count_data = $pending_count_res->fetch_assoc();
$active_res_count = $pending_count_data['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Portal - BCP Library</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-light">

<?php if (!isset($_SESSION['admin_logged_in'])): ?>
    <!-- Admin Login Screen -->
    <div class="container d-flex justify-content-center align-items-center vh-100">
        <div class="card bcp-card p-4 shadow-lg" style="width: 400px;">
            <div class="text-center mb-4">
                <i class="fas fa-user-shield fa-3x text-primary mb-2"></i>
                <h3 class="fw-bold">Admin Portal</h3>
                <p class="text-muted small">Bestlink College of the Philippines</p>
            </div>
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger py-2 small"><?php echo $error; ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-control" placeholder="Enter admin username" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Enter admin password" required>
                </div>
                <button type="submit" name="admin_login" class="btn btn-primary w-100 fw-bold py-2">Login to Portal</button>
            </form>
            <div class="text-center mt-3">
                <a href="dashboard.php" class="text-decoration-none small text-secondary"><i class="fas fa-arrow-left me-1"></i> Back to Student View</a>
            </div>
        </div>
    </div>

<?php else: ?>
    <!-- Admin Header Navigation -->
    <header class="bcp-header py-3 shadow-sm">
        <div class="container d-flex justify-content-between align-items-center">
            <div>
                <div class="bcp-logo-title fs-4"><i class="fas fa-tools me-2"></i>BCP LIBRARY ADMINISTRATION</div>
                <div class="bcp-tagline">"Be trained to be the BEST, Be Linked to SUCCESS"</div>
            </div>
            <div>
                <a href="dashboard.php" class="btn btn-outline-light btn-sm me-2"><i class="fas fa-home me-1"></i> Student View</a>
                <a href="admin.php?logout=true" class="btn btn-danger btn-sm"><i class="fas fa-sign-out-alt me-1"></i> Logout</a>
            </div>
        </div>
    </header>

    <div class="container my-4">

        <!-- System Alerts -->
        <?php if (isset($_GET['success'])): ?>
            <?php if ($_GET['success'] == 'added'): ?>
                <div class="alert alert-success alert-dismissible fade show">New book added to catalog! <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
            <?php elseif ($_GET['success'] == 'deleted'): ?>
                <div class="alert alert-warning alert-dismissible fade show">Book deleted from catalog! <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
            <?php elseif ($_GET['success'] == 'issued'): ?>
                <div class="alert alert-info alert-dismissible fade show">Book successfully issued to student! <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
            <?php elseif ($_GET['success'] == 'returned'): ?>
                <div class="alert alert-success alert-dismissible fade show">Book returned and stock increased by +1! <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
            <?php elseif ($_GET['success'] == 'student_updated'): ?>
                <div class="alert alert-success alert-dismissible fade show">Student record updated successfully! <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
            <?php elseif ($_GET['success'] == 'qty_updated'): ?>
                <div class="alert alert-success alert-dismissible fade show">Book quantity updated successfully! <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
            <?php endif; ?>
        <?php endif; ?>

        <!-- ADMIN NAVIGATION TABS -->
        <ul class="nav nav-tabs nav-fill mb-4 shadow-sm bg-white rounded-top" id="adminTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link py-3 fw-bold <?php echo ($admin_tab == 'reservations') ? 'active' : ''; ?>" id="reservations-tab" data-bs-toggle="tab" data-bs-target="#reservations" type="button" role="tab">
                    <i class="fas fa-clipboard-check me-2 text-warning"></i>Active Reservations
                    <?php if ($active_res_count > 0): ?>
                        <span class="badge bg-danger rounded-pill ms-1"><?php echo $active_res_count; ?></span>
                    <?php endif; ?>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link py-3 fw-bold <?php echo ($admin_tab == 'students') ? 'active' : ''; ?>" id="students-tab" data-bs-toggle="tab" data-bs-target="#students" type="button" role="tab">
                    <i class="fas fa-users me-2 text-primary"></i>Student Directory & History
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link py-3 fw-bold <?php echo ($admin_tab == 'inventory') ? 'active' : ''; ?>" id="inventory-tab" data-bs-toggle="tab" data-bs-target="#inventory" type="button" role="tab">
                    <i class="fas fa-boxes me-2 text-dark"></i>Book Catalog & Inventory
                </button>
            </li>
        </ul>

        <!-- TAB CONTENTS -->
        <div class="tab-content" id="adminTabsContent">

            <!-- TAB 1: ACTIVE RESERVATIONS & PICKUPS -->
            <div class="tab-pane fade <?php echo ($admin_tab == 'reservations') ? 'show active' : ''; ?>" id="reservations" role="tabpanel">
                <div class="card bcp-card mb-4">
                    <div class="card-header bg-warning text-dark py-3 fw-bold">
                        <h5 class="mb-0"><i class="fas fa-clipboard-check me-2"></i>Active Student Book Reservations & Counter Pickups</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Trans ID</th>
                                        <th>Student ID</th>
                                        <th>Student Name</th>
                                        <th>Reserved Book Title</th>
                                        <th>Status</th>
                                        <th>Date Recorded</th>
                                        <th class="text-center">Counter Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $active_res_query = "SELECT t.*, u.fullname FROM transactions t LEFT JOIN users u ON t.student_id = u.student_id WHERE t.action_type IN ('Reserved', 'Issued / Borrowed') ORDER BY t.transaction_id DESC";
                                    $active_res = $conn->query($active_res_query);

                                    if ($active_res && $active_res->num_rows > 0) {
                                        while($row = $active_res->fetch_assoc()) {
                                            $student_name = !empty($row['fullname']) ? $row['fullname'] : 'N/A';
                                            echo "<tr>";
                                            echo "<td>#" . $row['transaction_id'] . "</td>";
                                            echo "<td><code>" . htmlspecialchars($row['student_id']) . "</code></td>";
                                            echo "<td class='fw-bold text-primary'><i class='fas fa-user-graduate me-1'></i> " . htmlspecialchars($student_name) . "</td>";
                                            echo "<td class='fw-bold'>" . htmlspecialchars($row['book_title']) . "</td>";
                                            
                                            if ($row['action_type'] == 'Reserved') {
                                                echo "<td><span class='badge bg-warning text-dark'>Pending Pickup</span></td>";
                                                echo "<td class='small'>" . $row['date_recorded'] . "</td>";
                                                echo "<td class='text-center'>
                                                        <form method='POST' style='margin:0;'>
                                                            <input type='hidden' name='trans_id' value='" . $row['transaction_id'] . "'>
                                                            <button type='submit' name='issue_book' class='btn btn-success btn-sm'><i class='fas fa-hand-holding me-1'></i> Issue / Hand Book</button>
                                                        </form>
                                                      </td>";
                                            } else {
                                                echo "<td><span class='badge bg-primary'>Borrowed / Claimed</span></td>";
                                                echo "<td class='small'>" . $row['date_recorded'] . "</td>";
                                                echo "<td class='text-center'>
                                                        <form method='POST' style='margin:0;'>
                                                            <input type='hidden' name='trans_id' value='" . $row['transaction_id'] . "'>
                                                            <input type='hidden' name='book_title' value='" . htmlspecialchars($row['book_title']) . "'>
                                                            <button type='submit' name='return_item' class='btn btn-info btn-sm text-white'><i class='fas fa-undo me-1'></i> Mark as Returned</button>
                                                        </form>
                                                      </td>";
                                            }
                                            echo "</tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='7' class='text-center py-4 text-muted'>No active pending or borrowed reservations.</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 2: STUDENT DIRECTORY & HISTORY -->
            <div class="tab-pane fade <?php echo ($admin_tab == 'students') ? 'show active' : ''; ?>" id="students" role="tabpanel">
                <div class="card bcp-card mb-4">
                    <div class="card-header bg-primary text-white py-3">
                        <h5 class="mb-0"><i class="fas fa-users me-2"></i>Registered Student Accounts History</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped mb-0 align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Student ID</th>
                                        <th>Full Name</th>
                                        <th>Email Address</th>
                                        <th>Role</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $students_query = $conn->query("SELECT * FROM users ORDER BY student_id DESC");
                                    if ($students_query && $students_query->num_rows > 0) {
                                        while($s_row = $students_query->fetch_assoc()) {
                                            $sid = htmlspecialchars($s_row['student_id']);
                                            $sname = htmlspecialchars($s_row['fullname']);
                                            $semail = htmlspecialchars($s_row['email']);
                                            $srole = htmlspecialchars($s_row['role']);

                                            echo "<tr>";
                                            echo "<td><code>" . $sid . "</code></td>";
                                            echo "<td class='fw-bold'>" . $sname . "</td>";
                                            echo "<td>" . $semail . "</td>";
                                            echo "<td><span class='badge bg-secondary'>" . $srole . "</span></td>";
                                            echo "<td class='text-center'>
                                                    <button class='btn btn-outline-primary btn-sm' data-bs-toggle='modal' data-bs-target='#editStudentModal{$sid}'>
                                                        <i class='fas fa-edit me-1'></i> Edit
                                                    </button>
                                                  </td>";
                                            echo "</tr>";

                                            // Edit Student Modal
                                            echo "
                                            <div class='modal fade' id='editStudentModal{$sid}' tabindex='-1' aria-hidden='true'>
                                                <div class='modal-dialog'>
                                                    <div class='modal-content'>
                                                        <div class='modal-header bg-primary text-white'>
                                                            <h5 class='modal-title'><i class='fas fa-user-edit me-2'></i>Edit Student Details (#{$sid})</h5>
                                                            <button type='button' class='btn-close btn-close-white' data-bs-dismiss='modal'></button>
                                                        </div>
                                                        <form method='POST'>
                                                            <div class='modal-body text-start'>
                                                                <input type='hidden' name='student_id' value='{$sid}'>
                                                                <div class='mb-3'>
                                                                    <label class='form-label fw-bold'>Full Name</label>
                                                                    <input type='text' name='fullname' class='form-control' value='{$sname}' required>
                                                                </div>
                                                                <div class='mb-3'>
                                                                    <label class='form-label fw-bold'>Email Address</label>
                                                                    <input type='email' name='email' class='form-control' value='{$semail}' required>
                                                                </div>
                                                                <div class='mb-3'>
                                                                    <label class='form-label fw-bold'>Role</label>
                                                                    <select name='role' class='form-select'>
                                                                        <option value='Student' " . ($srole == 'Student' ? 'selected' : '') . ">Student</option>
                                                                        <option value='Admin' " . ($srole == 'Admin' ? 'selected' : '') . ">Admin</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class='modal-footer'>
                                                                <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Close</button>
                                                                <button type='submit' name='edit_student' class='btn btn-primary'><i class='fas fa-save me-1'></i> Save Changes</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='5' class='text-center py-4 text-muted'>No registered students found.</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 3: BOOK CATALOG & INVENTORY -->
            <div class="tab-pane fade <?php echo ($admin_tab == 'inventory') ? 'show active' : ''; ?>" id="inventory" role="tabpanel">
                
                <!-- Add Book Form -->
                <div class="card bcp-card mb-4">
                    <div class="card-header bcp-card-header py-3">
                        <h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i>Add New Book to Inventory</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Book Title</label>
                                <input type="text" name="title" class="form-control" placeholder="Enter title" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Author</label>
                                <input type="text" name="author" class="form-control" placeholder="Enter author" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Category</label>
                                <input type="text" name="category" class="form-control" placeholder="e.g. Information Tech" required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-bold">Quantity Stock</label>
                                <input type="number" name="quantity" class="form-control" value="5" min="1" required>
                            </div>
                            <div class="col-12 text-end">
                                <button type="submit" name="add_book" class="btn btn-bcp-primary"><i class="fas fa-save me-1"></i> Save Book to Catalog</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Complete Inventory Listing -->
                <div class="card bcp-card mb-5">
                    <div class="card-header bg-dark text-white py-3">
                        <h5 class="mb-0"><i class="fas fa-list me-2"></i>Library Catalog & Stock Quantities</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Title</th>
                                        <th>Author</th>
                                        <th>Category</th>
                                        <th class="text-center">Stock Quantity</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $all_books = $conn->query("SELECT * FROM books ORDER BY book_id DESC");
                                    if ($all_books && $all_books->num_rows > 0) {
                                        while($row = $all_books->fetch_assoc()) {
                                            $bid = $row['book_id'];
                                            $btitle = htmlspecialchars($row['title']);
                                            $bqty = $row['quantity'];
                                            $qty_badge = ($bqty > 0) ? 'bg-success' : 'bg-danger';

                                            echo "<tr>";
                                            echo "<td>#" . $bid . "</td>";
                                            echo "<td class='fw-bold'>" . $btitle . "</td>";
                                            echo "<td>" . htmlspecialchars($row['author']) . "</td>";
                                            echo "<td><span class='badge bg-secondary'>" . htmlspecialchars($row['category']) . "</span></td>";
                                            echo "<td class='text-center'><span class='badge $qty_badge fs-6'>" . $bqty . "</span></td>";
                                            echo "<td class='text-center'>
                                                    <div class='d-flex justify-content-center gap-1'>
                                                        <button class='btn btn-outline-primary btn-sm' data-bs-toggle='modal' data-bs-target='#editQtyModal{$bid}'>
                                                            <i class='fas fa-edit me-1'></i> Edit Qty
                                                        </button>
                                                        <a href='admin.php?tab=inventory&delete_id=" . $bid . "' class='btn btn-outline-danger btn-sm' onclick='return confirm(\"Delete this book?\");'><i class='fas fa-trash-alt me-1'></i> Delete</a>
                                                    </div>
                                                  </td>";
                                            echo "</tr>";

                                            // Modal for Editing Book Quantity
                                            echo "
                                            <div class='modal fade' id='editQtyModal{$bid}' tabindex='-1' aria-hidden='true'>
                                                <div class='modal-dialog modal-sm'>
                                                    <div class='modal-content'>
                                                        <div class='modal-header bg-dark text-white py-2'>
                                                            <h6 class='modal-title'><i class='fas fa-boxes me-2'></i>Update Quantity</h6>
                                                            <button type='button' class='btn-close btn-close-white' data-bs-dismiss='modal'></button>
                                                        </div>
                                                        <form method='POST'>
                                                            <div class='modal-body text-start'>
                                                                <input type='hidden' name='book_id' value='{$bid}'>
                                                                <p class='small text-muted mb-2 fw-bold'>{$btitle}</p>
                                                                <div class='mb-2'>
                                                                    <label class='form-label small fw-bold'>Available Stock Quantity</label>
                                                                    <input type='number' name='quantity' class='form-control' value='{$bqty}' min='0' required>
                                                                </div>
                                                            </div>
                                                            <div class='modal-footer py-1'>
                                                                <button type='button' class='btn btn-secondary btn-sm' data-bs-dismiss='modal'>Cancel</button>
                                                                <button type='submit' name='edit_quantity' class='btn btn-primary btn-sm'><i class='fas fa-save me-1'></i> Update</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='6' class='text-center py-4 text-muted'>No books in catalog.</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>

        </div>

    </div>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>