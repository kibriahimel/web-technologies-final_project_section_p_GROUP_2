<?php
// ============================================================
// ajax_search.php
// Returns JSON response for book search (used by AJAX)
// Called by: views/js/main.js → searchBooksAjax()
// ============================================================

require_once 'config/Connect.php';
require_once 'config/Close.php';
require_once 'models/BookModel.php';

$keyword = isset($_GET['keyword']) ? htmlspecialchars(trim($_GET['keyword'])) : '';

$conn = connect();

if ($keyword == '') {
    $books = getAllBooks($conn);
} else {
    $books = searchBooks($conn, $keyword);
}

closeConn($conn);

// Return data as JSON
header('Content-Type: application/json');
echo json_encode($books);
