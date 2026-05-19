<?php
// ============================================================
// controllers/ReservationController.php
// Handles book reservations by members
// ============================================================

session_start();
require_once 'config/Connect.php';
require_once 'config/Close.php';
require_once 'models/ReviewModel.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] != 'member') {
    header("Location: index.php?page=login");
    exit();
}

$conn   = connect();
$action = isset($_GET['action']) ? $_GET['action'] : '';

// Reserve a book (POST)
if ($action == 'reserve' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $book_id   = (int)$_POST['book_id'];
    $branch_id = (int)$_POST['branch_id'];
    $user_id   = $_SESSION['user'];

    $created = createReservation($conn, $user_id, $book_id, $branch_id);

    if ($created) {
        $_SESSION['msg'] = "Book reserved successfully! Pick it up within 3 days.";
    } else {
        $_SESSION['error'] = "Failed to reserve book. Try again.";
    }

    closeConn($conn);
    header("Location: index.php?page=my_reservations");
    exit();
}

// Cancel a reservation (GET)
if ($action == 'cancel' && isset($_GET['id'])) {
    $id      = (int)$_GET['id'];
    $user_id = $_SESSION['user'];
    cancelReservation($conn, $id, $user_id);
    $_SESSION['msg'] = "Reservation cancelled.";
    closeConn($conn);
    header("Location: index.php?page=my_reservations");
    exit();
}

closeConn($conn);
header("Location: index.php?page=member_dashboard");
exit();
