<?php
// ============================================================
// controllers/TransferController.php
// Handles inter-branch transfer requests
// Separates DB logic from the transfer_requests view (MVC fix)
// ============================================================

session_start();
require_once 'config/Connect.php';
require_once 'config/Close.php';
require_once 'models/BranchModel.php';
require_once 'models/BookModel.php';

if (!isset($_SESSION['user']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'manager')) {
    header("Location: index.php?page=login");
    exit();
}

$conn   = connect();
$action = isset($_GET['action']) ? $_GET['action'] : '';

// Approve transfer
if ($action == 'approve' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    approveInterBranchRequest($conn, $id, $_SESSION['user']);
    $_SESSION['msg'] = "Transfer request approved.";
    closeConn($conn);
    header("Location: index.php?page=transfer_requests");
    exit();
}

// Reject transfer
if ($action == 'reject' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    rejectInterBranchRequest($conn, $id, $_SESSION['user']);
    $_SESSION['msg'] = "Transfer request rejected.";
    closeConn($conn);
    header("Location: index.php?page=transfer_requests");
    exit();
}

// Create new transfer request
if ($action == 'create' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $book_id     = (int)$_POST['book_id'];
    $from_branch = (int)$_POST['from_branch_id'];
    $to_branch   = (int)$_POST['to_branch_id'];

    if ($book_id <= 0 || $from_branch <= 0 || $to_branch <= 0) {
        $_SESSION['error'] = "Please select a book, source branch, and destination branch.";
        closeConn($conn);
        header("Location: index.php?page=transfer_requests");
        exit();
    }

    if ($from_branch == $to_branch) {
        $_SESSION['error'] = "Source and destination branch cannot be the same.";
        closeConn($conn);
        header("Location: index.php?page=transfer_requests");
        exit();
    }

    createInterBranchRequest($conn, $book_id, $from_branch, $to_branch, $_SESSION['user']);
    $_SESSION['msg'] = "Transfer request submitted.";
    closeConn($conn);
    header("Location: index.php?page=transfer_requests");
    exit();
}

closeConn($conn);
header("Location: index.php?page=transfer_requests");
exit();
