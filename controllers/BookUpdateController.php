<?php
// ============================================================
// controllers/BookUpdateController.php
// Handles POST request to update a book
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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $id           = (int)$_POST['id'];
    $title        = htmlspecialchars(trim($_POST['title']));
    $author       = htmlspecialchars(trim($_POST['author']));
    $isbn         = htmlspecialchars(trim($_POST['isbn']));
    $genre_id     = (int)$_POST['genre_id'];
    $publisher    = htmlspecialchars(trim($_POST['publisher']));
    $publish_year = htmlspecialchars(trim($_POST['publish_year']));
    $description  = htmlspecialchars(trim($_POST['description']));
    $total_copies = (int)$_POST['total_copies'];

    if (empty($title) || empty($author)) {
        $_SESSION['error'] = "Title and Author are required.";
        header("Location: index.php?page=edit_book&id=" . $id);
        exit();
    }

    $conn    = connect();
    $updated = updateBook($conn, $id, $title, $author, $isbn, $genre_id, $publisher, $publish_year, $description, $total_copies);
    closeConn($conn);

    if ($updated) {
        $_SESSION['msg'] = "Book updated successfully!";
    } else {
        $_SESSION['error'] = "Failed to update book.";
    }

    header("Location: index.php?page=book_list");
    exit();

} else {
    header("Location: index.php?page=book_list");
    exit();
}
