<?php
// ============================================================
// controllers/BranchController.php
// Handles branch management (admin/manager only)
// POST: create/update/policy | GET: delete/toggle/assign/remove
// ============================================================

session_start();
require_once 'config/Connect.php';
require_once 'config/Close.php';
require_once 'models/BranchModel.php';
require_once 'models/UserModel.php';

if (!isset($_SESSION['user']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'manager')) {
    header("Location: index.php?page=login");
    exit();
}

$conn   = connect();
$action = isset($_GET['action']) ? $_GET['action'] : '';

// Create new branch (POST)
if ($action == 'create' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $branch_name = htmlspecialchars(trim($_POST['branch_name']));
    $location    = htmlspecialchars(trim($_POST['location']));
    $phone       = htmlspecialchars(trim($_POST['phone']));
    $email       = htmlspecialchars(trim($_POST['email']));

    if (empty($branch_name)) {
        $_SESSION['error'] = "Branch name is required.";
        closeConn($conn);
        header("Location: index.php?page=manage_branches");
        exit();
    }

    $created = createBranch($conn, $branch_name, $location, $phone, $email);

    if ($created) {
        $_SESSION['msg'] = "Branch created successfully!";
    } else {
        $_SESSION['error'] = "Failed to create branch.";
    }

    closeConn($conn);
    header("Location: index.php?page=manage_branches");
    exit();
}

// Update branch (POST)
if ($action == 'update' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $id          = (int)$_POST['id'];
    $branch_name = htmlspecialchars(trim($_POST['branch_name']));
    $location    = htmlspecialchars(trim($_POST['location']));
    $phone       = htmlspecialchars(trim($_POST['phone']));
    $email       = htmlspecialchars(trim($_POST['email']));
    $manager_id  = (int)$_POST['manager_id'];

    if (empty($branch_name)) {
        $_SESSION['error'] = "Branch name is required.";
        closeConn($conn);
        header("Location: index.php?page=manage_branches&edit=" . $id);
        exit();
    }

    $updated = updateBranch($conn, $id, $branch_name, $location, $phone, $email, $manager_id);
    $_SESSION['msg'] = $updated ? "Branch updated!" : "Update failed.";
    closeConn($conn);
    header("Location: index.php?page=manage_branches");
    exit();
}

// Delete branch (GET)
if ($action == 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    deleteBranch($conn, $id);
    $_SESSION['msg'] = "Branch deleted.";
    closeConn($conn);
    header("Location: index.php?page=manage_branches");
    exit();
}

// Toggle branch status active/inactive (GET)
if ($action == 'toggle_status' && isset($_GET['id'])) {
    $id     = (int)$_GET['id'];
    $branch = getBranchById($conn, $id);
    if ($branch) {
        $new_status = ($branch['status'] == 'active') ? 'inactive' : 'active';
        toggleBranchStatus($conn, $id, $new_status);
        $_SESSION['msg'] = "Branch status changed to " . $new_status . ".";
    }
    closeConn($conn);
    header("Location: index.php?page=manage_branches");
    exit();
}

// Assign librarian to branch (POST)
if ($action == 'assign_librarian' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $librarian_id = (int)$_POST['librarian_id'];
    $branch_id    = (int)$_POST['branch_id'];

    if ($librarian_id <= 0 || $branch_id <= 0) {
        $_SESSION['error'] = "Please select a librarian and a branch.";
        closeConn($conn);
        header("Location: index.php?page=manage_librarians");
        exit();
    }

    $done = assignLibrarianToBranch($conn, $librarian_id, $branch_id);
    $_SESSION['msg'] = $done ? "Librarian assigned to branch!" : "Assignment failed.";
    closeConn($conn);
    header("Location: index.php?page=manage_librarians");
    exit();
}

// Remove librarian from branch (GET)
if ($action == 'remove_librarian' && isset($_GET['id'])) {
    $user_id = (int)$_GET['id'];
    $done    = removeLibrarianFromBranch($conn, $user_id);
    $_SESSION['msg'] = $done ? "Librarian removed from branch." : "Removal failed.";
    closeConn($conn);
    header("Location: index.php?page=manage_librarians");
    exit();
}

// Update branch policy (POST)
if ($action == 'policy' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $branch_id        = (int)$_POST['branch_id'];
    $max_borrow_days  = (int)$_POST['max_borrow_days'];
    $max_books        = (int)$_POST['max_books_per_member'];
    $fine_per_day     = (float)$_POST['fine_per_day'];
    $reservation_days = (int)$_POST['reservation_days'];

    if ($max_borrow_days <= 0 || $max_books <= 0 || $fine_per_day < 0 || $reservation_days <= 0) {
        $_SESSION['error'] = "All policy values must be positive numbers.";
        closeConn($conn);
        header("Location: index.php?page=branch_policy&id=" . $branch_id);
        exit();
    }

    $updated = updateBranchPolicy($conn, $branch_id, $max_borrow_days, $max_books, $fine_per_day, $reservation_days);
    $_SESSION['msg'] = $updated ? "Policy updated!" : "Policy update failed.";
    closeConn($conn);
    header("Location: index.php?page=branch_policy&id=" . $branch_id);
    exit();
}

// Post announcement (POST)
if ($action == 'announce' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $title      = htmlspecialchars(trim($_POST['title']));
    $content    = htmlspecialchars(trim($_POST['content']));
    $branch_id  = ($_POST['branch_id'] == '') ? null : (int)$_POST['branch_id'];
    $created_by = $_SESSION['user'];

    if (empty($title) || empty($content)) {
        $_SESSION['error'] = "Title and content are required.";
        closeConn($conn);
        header("Location: index.php?page=manager_announcements");
        exit();
    }

    $done = createAnnouncement($conn, $title, $content, $branch_id, $created_by);
    $_SESSION['msg'] = $done ? "Announcement posted!" : "Failed to post announcement.";
    closeConn($conn);
    header("Location: index.php?page=manager_announcements");
    exit();
}

// Delete announcement (GET)
if ($action == 'delete_announcement' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    deleteAnnouncement($conn, $id);
    $_SESSION['msg'] = "Announcement deleted.";
    closeConn($conn);
    header("Location: index.php?page=manager_announcements");
    exit();
}

closeConn($conn);
header("Location: index.php?page=manage_branches");
exit();
