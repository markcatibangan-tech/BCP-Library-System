<?php
session_start();
include 'config.php';

if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION['student_id'];
$fullname = $_SESSION['fullname'];

// --- LANGUAGE SWITCHER SETUP ---
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
        'admin' => 'Admin Panel',
        'logout' => 'Logout',
        'tab_catalog' => 'Book Catalog & Search',
        'tab_reservations' => 'My Active Reservations',
        'tab_history' => 'History',
        'search_title' => 'Search Library Catalog',
        'search_placeholder' => 'Search by book title or category...',
        'search_btn' => 'Search',
        'reset_btn' => 'Reset',
        'avail_books' => 'Library Books Inventory',
        'title' => 'Title',
        'author' => 'Author',
        'category' => 'Category',
        'qty' => 'Available Stock',
        'action' => 'Action',
        'reserve_btn' => 'Reserve Book',
        'not_available' => 'Not Available',
        'no_books' => 'No books found.',
        'reserved_books' => 'My Active Reservations',
        'return_btn' => 'Return Item',
        'cancel_btn' => 'Cancel Reservation',
        'no_reservations' => 'You have no active book reservations.',
        'history' => 'History',
        'trans_id' => 'Trans ID',
        'action_type' => 'Action Type',
        'timestamp' => 'Timestamp',
        'no_history' => 'No transaction logs found.',
        'msg_reserved' => 'Book successfully reserved!',
        'msg_returned' => 'Book returned successfully!',
        'msg_cancelled' => 'Reservation cancelled successfully!',
        'msg_out_of_stock' => 'Sorry, this book is currently out of stock.'
    ],
    'tl' => [
        'portal_title' => 'BESTLINK COLLEGE OF THE PHILIPPINES',
        'tagline' => '"Be trained to be the BEST, Be Linked to SUCCESS"',
        'admin' => 'Admin Panel',
        'logout' => 'Mag-logout',
        'tab_catalog' => 'Katalogo at Paghahanap',
        'tab_reservations' => 'Aking Aktibong Reservations',
        'tab_history' => 'Kasaysayan',
        'search_title' => 'Maghanap sa Catalog ng Silid-Aklatan',
        'search_placeholder' => 'Maghanap sa pamagat o kategorya...',
        'search_btn' => 'Maghanap',
        'reset_btn' => 'I-reset',
        'avail_books' => 'Listahan ng mga Libro sa Library',
        'title' => 'Pamagat',
        'author' => 'May-akda',
        'category' => 'Kategorya',
        'qty' => 'Natitirang Dami',
        'action' => 'Aksyon',
        'reserve_btn' => 'I-reserve',
        'not_available' => 'Not Available',
        'no_books' => 'Walang librong nahanap.',
        'reserved_books' => 'Aking Aktibong Reservations',
        'return_btn' => 'Isoli ang Libro',
        'cancel_btn' => 'Kanselahin',
        'no_reservations' => 'Wala kang aktibong reservation.',
        'history' => 'Kasaysayan',
        'trans_id' => 'Trans ID',
        'action_type' => 'Uri ng Aksyon',
        'timestamp' => 'Oras',
        'no_history' => 'Walang nakatalang transaksyon.',
        'msg_reserved' => 'Matagumpay na na-reserve ang libro!',
        'msg_returned' => 'Matagumpay na naibalik ang libro!',
        'msg_cancelled' => 'Matagumpay na nakansela ang reservation!',
        'msg_out_of_stock' => 'Pasensya na, wala nang stock ang librong ito.'
    ]
];
$t = $translations[$lang];

// --- HANDLE BOOK RESERVATION ---
if (isset($_POST['reserve'])) {
    $book_id = intval($_POST['book_id']);
    $book_title = $_POST['book_title'];

    $check_qty = $conn->query("SELECT quantity FROM books WHERE book_id = $book_id");
    if ($check_qty && $check_qty->num_rows > 0) {
        $row = $check_qty->fetch_assoc();
        if ($row['quantity'] > 0) {
            $conn->query("UPDATE books SET quantity = quantity - 1 WHERE book_id = $book_id");
            $conn->query("UPDATE books SET status = 'Not Available' WHERE book_id = $book_id AND quantity = 0");
            $conn->query("INSERT INTO transactions (student_id, book_title, action_type) VALUES ('$student_id', '$book_title', 'Reserved')");

            header("Location: dashboard.php?tab=reservations&success=reserved");
            exit();
        } else {
            header("Location: dashboard.php?error=out_of_stock");
            exit();
        }
    }
}

// --- HANDLE RETURN ITEM ---
if (isset($_POST['return_book'])) {
    $book_id = intval($_POST['book_id']);
    $book_title = $_POST['book_title'];

    $conn->query("UPDATE books SET quantity = quantity + 1, status = 'Available' WHERE book_id = $book_id");
    $conn->query("INSERT INTO transactions (student_id, book_title, action_type) VALUES ('$student_id', '$book_title', 'Returned')");

    header("Location: dashboard.php?tab=reservations&success=returned");
    exit();
}

// --- HANDLE CANCEL RESERVATION ---
if (isset($_POST['cancel_book'])) {
    $book_id = intval($_POST['book_id']);
    $book_title = $_POST['book_title'];

    $conn->query("UPDATE books SET quantity = quantity + 1, status = 'Available' WHERE book_id = $book_id");
    $conn->query("INSERT INTO transactions (student_id, book_title, action_type) VALUES ('$student_id', '$book_title', 'Cancelled')");

    header("Location: dashboard.php?tab=reservations&success=cancelled");
    exit();
}

// Active Tab Handler
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'catalog';

// Search Logic
$search = "";
if (isset($_GET['search'])) {
    $search = $conn->real_escape_string($_GET['search']);
    $books_query = "SELECT * FROM books WHERE title LIKE '%$search%' OR category LIKE '%$search%' ORDER BY book_id DESC";
} else {
    $books_query = "SELECT * FROM books ORDER BY book_id DESC";
}
$books_result = $conn->query($books_query);

// Count active student reservations for badge display
$res_count_query = $conn->query("SELECT COUNT(*) as total FROM transactions WHERE student_id = '$student_id' AND action_type IN ('Reserved', 'Issued / Borrowed')");
$res_count_data = $res_count_query->fetch_assoc();
$reservation_count = $res_count_data['total'];
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BCP Student Library Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <!-- Header Navigation -->
    <header class="bcp-header py-3 shadow-sm">
        <div class="container d-flex justify-content-between align-items-center">
            <div>
                <div class="bcp-logo-title fs-5 fs-md-4"><i class="fas fa-university me-2"></i><?php echo $t['portal_title']; ?></div>
                <div class="bcp-tagline"><?php echo $t['tagline']; ?></div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <!-- Language Switcher -->
                <div class="dropdown">
                    <button class="btn btn-outline-light btn-sm dropdown-toggle" type="button" id="langDropdown" data-bs-toggle="dropdown">
                        <i class="fas fa-globe me-1"></i> <?php echo ($lang == 'en') ? 'English' : 'Tagalog'; ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                        <li><a class="dropdown-item <?php echo ($lang == 'en') ? 'active' : ''; ?>" href="?lang=en">🇬🇧 English</a></li>
                        <li><a class="dropdown-item <?php echo ($lang == 'tl') ? 'active' : ''; ?>" href="?lang=tl">🇵🇭 Tagalog</a></li>
                    </ul>
                </div>

                <span class="badge bg-warning text-dark me-1 d-none d-lg-inline"><i class="fas fa-user-graduate me-1"></i> <?php echo htmlspecialchars($fullname); ?></span>
                <a href="admin.php" class="btn btn-bcp-gold btn-sm"><i class="fas fa-user-shield me-1"></i> <?php echo $t['admin']; ?></a>
                <a href="logout.php" class="btn btn-outline-light btn-sm"><i class="fas fa-sign-out-alt me-1"></i> <?php echo $t['logout']; ?></a>
            </div>
        </div>
    </header>

    <div class="container my-4">

        <!-- System Alerts -->
        <?php if (isset($_GET['success'])): ?>
            <?php if ($_GET['success'] == 'reserved'): ?>
                <div class="alert alert-success alert-dismissible fade show shadow-sm">
                    <i class="fas fa-check-circle me-2"></i><?php echo $t['msg_reserved']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php elseif ($_GET['success'] == 'returned'): ?>
                <div class="alert alert-info alert-dismissible fade show shadow-sm">
                    <i class="fas fa-undo me-2"></i><?php echo $t['msg_returned']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php elseif ($_GET['success'] == 'cancelled'): ?>
                <div class="alert alert-warning alert-dismissible fade show shadow-sm">
                    <i class="fas fa-times-circle me-2"></i><?php echo $t['msg_cancelled']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <?php if (isset($_GET['error']) && $_GET['error'] == 'out_of_stock'): ?>
            <div class="alert alert-danger alert-dismissible fade show shadow-sm">
                <i class="fas fa-exclamation-circle me-2"></i><?php echo $t['msg_out_of_stock']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- NAVIGATION TABS -->
        <ul class="nav nav-tabs nav-fill mb-4 shadow-sm bg-white rounded-top" id="portalTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link py-3 fw-bold <?php echo ($active_tab == 'catalog') ? 'active' : ''; ?>" id="catalog-tab" data-bs-toggle="tab" data-bs-target="#catalog" type="button" role="tab">
                    <i class="fas fa-book-open me-2 text-primary"></i><?php echo $t['tab_catalog']; ?>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link py-3 fw-bold <?php echo ($active_tab == 'reservations') ? 'active' : ''; ?>" id="reservations-tab" data-bs-toggle="tab" data-bs-target="#reservations" type="button" role="tab">
                    <i class="fas fa-bookmark me-2 text-warning"></i><?php echo $t['tab_reservations']; ?>
                    <?php if ($reservation_count > 0): ?>
                        <span class="badge bg-danger rounded-pill ms-1"><?php echo $reservation_count; ?></span>
                    <?php endif; ?>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link py-3 fw-bold <?php echo ($active_tab == 'history') ? 'active' : ''; ?>" id="history-tab" data-bs-toggle="tab" data-bs-target="#history" type="button" role="tab">
                    <i class="fas fa-history me-2 text-dark"></i><?php echo $t['tab_history']; ?>
                </button>
            </li>
        </ul>

        <!-- TAB CONTENTS -->
        <div class="tab-content" id="portalTabsContent">

            <!-- TAB 1: CATALOG & SEARCH -->
            <div class="tab-pane fade <?php echo ($active_tab == 'catalog') ? 'show active' : ''; ?>" id="catalog" role="tabpanel">
                <div class="card bcp-card mb-4">
                    <div class="card-body">
                        <h5 class="card-title fw-bold text-dark mb-3"><i class="fas fa-search me-2" style="color: var(--bcp-blue);"></i><?php echo $t['search_title']; ?></h5>
                        <form method="GET" action="" class="row g-2">
                            <input type="hidden" name="tab" value="catalog">
                            <div class="col-md-9">
                                <input type="text" name="search" class="form-control" placeholder="<?php echo $t['search_placeholder']; ?>" value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                            <div class="col-md-3 d-flex gap-2">
                                <button type="submit" class="btn btn-bcp-primary w-100"><i class="fas fa-search me-1"></i> <?php echo $t['search_btn']; ?></button>
                                <?php if (!empty($search)): ?>
                                    <a href="dashboard.php?tab=catalog" class="btn btn-secondary"><?php echo $t['reset_btn']; ?></a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card bcp-card mb-4">
                    <div class="card-header bcp-card-header py-3">
                        <h5 class="mb-0"><i class="fas fa-book me-2"></i><?php echo $t['avail_books']; ?></h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th><?php echo $t['title']; ?></th>
                                        <th><?php echo $t['author']; ?></th>
                                        <th><?php echo $t['category']; ?></th>
                                        <th class="text-center"><?php echo $t['qty']; ?></th>
                                        <th class="text-center"><?php echo $t['action']; ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if ($books_result && $books_result->num_rows > 0) {
                                        while($row = $books_result->fetch_assoc()) {
                                            echo "<tr>";
                                            echo "<td class='fw-bold'>" . htmlspecialchars($row['title']) . "</td>";
                                            echo "<td>" . htmlspecialchars($row['author']) . "</td>";
                                            echo "<td><span class='badge bg-primary'>" . htmlspecialchars($row['category']) . "</span></td>";
                                            echo "<td class='text-center'><span class='badge bg-secondary fs-6'>" . $row['quantity'] . "</span></td>";
                                            echo "<td class='text-center'>";
                                            if ($row['quantity'] > 0) {
                                                echo "<form method='POST' style='margin:0;'>
                                                        <input type='hidden' name='book_id' value='" . $row['book_id'] . "'>
                                                        <input type='hidden' name='book_title' value='" . htmlspecialchars($row['title']) . "'>
                                                        <button type='submit' name='reserve' class='btn btn-bcp-gold btn-sm'><i class='fas fa-bookmark me-1'></i> " . $t['reserve_btn'] . "</button>
                                                      </form>";
                                            } else {
                                                echo "<span class='badge bg-danger p-2'><i class='fas fa-ban me-1'></i> " . $t['not_available'] . "</span>";
                                            }
                                            echo "</td>";
                                            echo "</tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='5' class='text-center py-4 text-muted'>" . $t['no_books'] . "</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 2: MY ACTIVE RESERVATIONS -->
            <div class="tab-pane fade <?php echo ($active_tab == 'reservations') ? 'show active' : ''; ?>" id="reservations" role="tabpanel">
                <div class="card bcp-card mb-4">
                    <div class="card-header bg-warning text-dark py-3 fw-bold" style="border-top-left-radius:8px; border-top-right-radius:8px;">
                        <h5 class="mb-0"><i class="fas fa-user-clock me-2"></i><?php echo $t['reserved_books']; ?></h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th><?php echo $t['title']; ?></th>
                                        <th><?php echo $t['author']; ?></th>
                                        <th><?php echo $t['category']; ?></th>
                                        <th class="text-center"><?php echo $t['action']; ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $my_reserved = $conn->query("SELECT DISTINCT t.action_type, b.* FROM books b JOIN transactions t ON b.title = t.book_title WHERE t.student_id = '$student_id' AND t.action_type IN ('Reserved', 'Issued / Borrowed') ORDER BY t.transaction_id DESC");
                                    if ($my_reserved && $my_reserved->num_rows > 0) {
                                        while($row = $my_reserved->fetch_assoc()) {
                                            echo "<tr>";
                                            echo "<td class='fw-bold'>" . htmlspecialchars($row['title']) . "</td>";
                                            echo "<td>" . htmlspecialchars($row['author']) . "</td>";
                                            echo "<td><span class='badge bg-secondary'>" . htmlspecialchars($row['category']) . "</span></td>";
                                            echo "<td class='text-center'>";
                                            
                                            if ($row['action_type'] == 'Issued / Borrowed') {
                                                echo "<span class='badge bg-success p-2 fs-6'><i class='fas fa-check-circle me-1'></i> Done</span>";
                                            } else {
                                                echo "<form method='POST' style='margin:0;'>
                                                        <input type='hidden' name='book_id' value='" . $row['book_id'] . "'>
                                                        <input type='hidden' name='book_title' value='" . htmlspecialchars($row['title']) . "'>
                                                        <button type='submit' name='cancel_book' class='btn btn-outline-danger btn-sm'><i class='fas fa-times me-1'></i> " . $t['cancel_btn'] . "</button>
                                                      </form>";
                                            }
                                            
                                            echo "</td>";
                                            echo "</tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='4' class='text-center py-4 text-muted'>" . $t['no_reservations'] . "</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 3: HISTORY -->
            <div class="tab-pane fade <?php echo ($active_tab == 'history') ? 'show active' : ''; ?>" id="history" role="tabpanel">
                <div class="card bcp-card mb-5">
                    <div class="card-header bg-dark text-white py-3" style="border-top-left-radius:8px; border-top-right-radius:8px;">
                        <h5 class="mb-0"><i class="fas fa-history me-2"></i><?php echo $t['history']; ?></h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped mb-0 align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th><?php echo $t['trans_id']; ?></th>
                                        <th><?php echo $t['title']; ?></th>
                                        <th><?php echo $t['action_type']; ?></th>
                                        <th><?php echo $t['timestamp']; ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $history_result = $conn->query("SELECT * FROM transactions WHERE student_id = '$student_id' ORDER BY date_recorded DESC");
                                    if ($history_result && $history_result->num_rows > 0) {
                                        while($row = $history_result->fetch_assoc()) {
                                            $badge = 'bg-secondary';
                                            if ($row['action_type'] == 'Reserved') $badge = 'bg-success';
                                            if ($row['action_type'] == 'Issued / Borrowed') $badge = 'bg-primary';
                                            if ($row['action_type'] == 'Returned') $badge = 'bg-info text-dark';
                                            if ($row['action_type'] == 'Cancelled') $badge = 'bg-danger';

                                            echo "<tr>";
                                            echo "<td>#" . $row['transaction_id'] . "</td>";
                                            echo "<td class='fw-bold'>" . htmlspecialchars($row['book_title']) . "</td>";
                                            echo "<td><span class='badge $badge'>" . htmlspecialchars($row['action_type']) . "</span></td>";
                                            echo "<td>" . $row['date_recorded'] . "</td>";
                                            echo "</tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='4' class='text-center py-4 text-muted'>" . $t['no_history'] . "</td></tr>";
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>