<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

// ============================================================
// index.php - Main Front Controller (Router)
// All pages go through this file using ?page=
// Example: index.php?page=login
// ============================================================

session_start();

// Get the page from GET parameter, default to 'home'
$page = isset($_GET['page']) ? $_GET['page'] : 'home';

// ============================================================
// HANDLE CONTROLLER ACTIONS (POST/GET form submissions)
// These redirect after processing, they don't show views
// ============================================================

if ($page == 'do_login') {
    require_once 'controllers/LoginController.php';
    exit();
}

if ($page == 'do_logout') {
    require_once 'controllers/LogoutController.php';
    exit();
}

if ($page == 'do_register') {
    require_once 'controllers/RegisterController.php';
    exit();
}

if ($page == 'do_save_book') {
    require_once 'controllers/BookSaveController.php';
    exit();
}

if ($page == 'do_update_book') {
    require_once 'controllers/BookUpdateController.php';
    exit();
}

if ($page == 'do_delete_book') {
    require_once 'controllers/BookDeleteController.php';
    exit();
}

if ($page == 'do_borrow') {
    require_once 'controllers/BorrowController.php';
    exit();
}

if ($page == 'do_reservation') {
    require_once 'controllers/ReservationController.php';
    exit();
}

if ($page == 'do_review') {
    require_once 'controllers/ReviewController.php';
    exit();
}

if ($page == 'do_profile') {
    require_once 'controllers/ProfileController.php';
    exit();
}

if ($page == 'do_branch') {
    require_once 'controllers/BranchController.php';
    exit();
}

if ($page == 'do_transfer') {
    require_once 'controllers/TransferController.php';
    exit();
}

if ($page == 'do_user') {
    require_once 'controllers/UserController.php';
    exit();
}

// ============================================================
// VIEWS - Load the correct view based on the page
// ============================================================

// Public pages (no login required)
if ($page == 'home') {
    require_once 'views/home.php';

} elseif ($page == 'login') {
    require_once 'views/login.php';

} elseif ($page == 'register') {
    require_once 'views/register.php';

// ============================================================
// MEMBER PAGES
// ============================================================
} elseif ($page == 'member_dashboard') {
    checkRole(array('member'));
    require_once 'views/member/dashboard.php';

} elseif ($page == 'browse_books') {
    checkLogin();
    require_once 'views/member/browse_books.php';

} elseif ($page == 'book_detail') {
    checkLogin();
    require_once 'views/member/book_detail.php';

} elseif ($page == 'my_borrows') {
    checkRole(array('member'));
    require_once 'views/member/my_borrows.php';

} elseif ($page == 'my_reservations') {
    checkRole(array('member'));
    require_once 'views/member/my_reservations.php';

} elseif ($page == 'my_fines') {
    checkRole(array('member'));
    require_once 'views/member/my_fines.php';

} elseif ($page == 'reading_list') {
    checkRole(array('member'));
    require_once 'views/member/reading_list.php';

} elseif ($page == 'profile') {
    checkLogin();
    require_once 'views/member/profile.php';

// ============================================================
// LIBRARIAN PAGES
// ============================================================
} elseif ($page == 'librarian_dashboard') {
    checkRole(array('librarian'));
    require_once 'views/librarian/dashboard.php';

} elseif ($page == 'book_list') {
    checkRole(array('librarian', 'admin'));
    require_once 'views/librarian/book_list.php';

} elseif ($page == 'add_book') {
    checkRole(array('librarian', 'admin'));
    require_once 'views/librarian/add_book.php';

} elseif ($page == 'edit_book') {
    checkRole(array('librarian', 'admin'));
    require_once 'views/librarian/edit_book.php';

} elseif ($page == 'manage_borrows') {
    checkRole(array('librarian', 'admin'));
    require_once 'views/librarian/manage_borrows.php';

} elseif ($page == 'manage_fines') {
    checkRole(array('librarian', 'admin'));
    require_once 'views/librarian/manage_fines.php';

} elseif ($page == 'manage_genres') {
    checkRole(array('librarian', 'admin'));
    require_once 'views/librarian/manage_genres.php';

} elseif ($page == 'announcements') {
    checkLogin();
    require_once 'views/librarian/announcements.php';

// ============================================================
// MANAGER PAGES
// ============================================================
} elseif ($page == 'manager_dashboard') {
    checkRole(array('manager'));
    require_once 'views/manager/dashboard.php';

} elseif ($page == 'manage_branches') {
    checkRole(array('manager', 'admin'));
    require_once 'views/manager/manage_branches.php';

} elseif ($page == 'branch_policy') {
    checkRole(array('manager', 'admin'));
    require_once 'views/manager/branch_policy.php';

} elseif ($page == 'branch_report') {
    checkRole(array('manager', 'admin'));
    require_once 'views/manager/branch_report.php';

} elseif ($page == 'transfer_requests') {
    checkRole(array('manager', 'admin'));
    require_once 'views/manager/transfer_requests.php';

} elseif ($page == 'manage_librarians') {
    checkRole(array('manager', 'admin'));
    require_once 'views/manager/manage_librarians.php';

} elseif ($page == 'cross_branch_report') {
    checkRole(array('manager', 'admin'));
    require_once 'views/manager/cross_branch_report.php';

} elseif ($page == 'most_borrowed') {
    checkRole(array('manager', 'admin'));
    require_once 'views/manager/most_borrowed.php';

} elseif ($page == 'member_activity') {
    checkRole(array('manager', 'admin'));
    require_once 'views/manager/member_activity.php';

} elseif ($page == 'manager_announcements') {
    checkRole(array('manager', 'admin'));
    require_once 'views/manager/manager_announcements.php';

} elseif ($page == 'overdue_alerts') {
    checkRole(array('manager', 'admin'));
    require_once 'views/manager/overdue_alerts.php';

} elseif ($page == 'monthly_report') {
    checkRole(array('manager', 'admin'));
    require_once 'views/manager/monthly_report.php';

} elseif ($page == 'librarian_activity') {
    checkRole(array('manager', 'admin'));
    require_once 'views/manager/librarian_activity.php';

// ============================================================
// ADMIN PAGES
// ============================================================
} elseif ($page == 'admin_dashboard') {
    checkRole(array('admin'));
    require_once 'views/admin/dashboard.php';

} elseif ($page == 'manage_users') {
    checkRole(array('admin'));
    require_once 'views/admin/manage_users.php';

} elseif ($page == 'admin_reports') {
    checkRole(array('admin'));
    require_once 'views/admin/reports.php';

} elseif ($page == 'admin_announcements') {
    checkRole(array('admin'));
    require_once 'views/admin/announcements.php';

} else {
    // Page not found - show 404
    require_once 'views/404.php';
}

// ============================================================
// HELPER FUNCTIONS FOR SESSION/ROLE CHECKING
// ============================================================

// Check if user is logged in at all
function checkLogin() {
    if (!isset($_SESSION['user'])) {
        $_SESSION['error'] = "Please login to continue.";
        header("Location: index.php?page=login");
        exit();
    }
}

// Check if user has the required role
function checkRole($allowed_roles) {
    if (!isset($_SESSION['user'])) {
        $_SESSION['error'] = "Please login to continue.";
        header("Location: index.php?page=login");
        exit();
    }
    if (!in_array($_SESSION['role'], $allowed_roles)) {
        $_SESSION['error'] = "You do not have permission to access this page.";
        header("Location: index.php?page=home");
        exit();
    }
}
