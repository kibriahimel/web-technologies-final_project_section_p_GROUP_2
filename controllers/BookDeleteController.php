<?php
// ============================================================
// controllers/BookDeleteController.php
// Handles GET request to delete a book by ID
// Used by: Librarian, Admin
// ============================================================

session_start();
require_once 'config/Connect.php';
require_once 'config/Close.php';
require_once 'models/BookModel.php';

if (!isset($_SESSION['user']) || ($_SESSION['role'] != 'librarian' && $_SESSION['role'] != 'admin')) {
    header("Location: index.php?page=login");
    exit();
}

// GET method: get book id from URL
if (isset($_GET['id'])) {
    $id   = (int)$_GET['id'];
    $conn = connect();
    $deleted = deleteBook($conn, $id);
    closeConn($conn);

    if ($deleted) {
        $_SESSION['msg'] = "Book deleted successfully!";
    } else {
        $_SESSION['error'] = "Failed to delete book.";
    }
} else {
    $_SESSION['error'] = "No book ID provided.";
}

header("Location: index.php?page=book_list");
exit();
