<?php
// ============================================================
// controllers/BookIndexController.php
// Fetches all books (GET) and sends to the book list view
// ============================================================

session_start();
require_once 'config/Connect.php';
require_once 'config/Close.php';
require_once 'models/BookModel.php';

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: index.php?page=login");
    exit();
}

$conn   = connect();
$books  = getAllBooks($conn);
$genres = getAllGenres($conn);
closeConn($conn);

// Pass data to the view
require_once 'views/librarian/book_list.php';
