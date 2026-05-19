<?php
// ============================================================
// models/BookModel.php
// Contains all database functions for the books table
// ============================================================

require_once 'config/Connect.php';
require_once 'config/Close.php';

// ------------------------------------------------------------
// Get all books with genre name joined
// ------------------------------------------------------------
function getAllBooks($conn) {
    $sql    = "SELECT b.*, g.genre_name FROM books b LEFT JOIN genres g ON b.genre_id = g.id ORDER BY b.id DESC";
    $result = mysqli_query($conn, $sql);
    $books  = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $books[] = $row;
    }
    return $books;
}

// ------------------------------------------------------------
// Get a single book by ID
// ------------------------------------------------------------
function getBookById($conn, $id) {
    $sql  = "SELECT b.*, g.genre_name FROM books b LEFT JOIN genres g ON b.genre_id = g.id WHERE b.id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($result);
}

// ------------------------------------------------------------
// Search books by keyword (title or author)
// ------------------------------------------------------------
function searchBooks($conn, $keyword) {
    $like = "%" . $keyword . "%";
    $sql  = "SELECT b.*, g.genre_name FROM books b LEFT JOIN genres g ON b.genre_id = g.id WHERE b.title LIKE ? OR b.author LIKE ? OR b.isbn LIKE ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "sss", $like, $like, $like);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $books  = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $books[] = $row;
    }
    return $books;
}

// ------------------------------------------------------------
// Create / Insert a new book
// ------------------------------------------------------------
function createBook($conn, $title, $author, $isbn, $genre_id, $publisher, $publish_year, $description, $cover_image, $total_copies) {
    $sql  = "INSERT INTO books (title, author, isbn, genre_id, publisher, publish_year, description, cover_image, total_copies) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ssssisssi", $title, $author, $isbn, $genre_id, $publisher, $publish_year, $description, $cover_image, $total_copies);
    return mysqli_stmt_execute($stmt);
}

// ------------------------------------------------------------
// Update an existing book
// ------------------------------------------------------------
function updateBook($conn, $id, $title, $author, $isbn, $genre_id, $publisher, $publish_year, $description, $total_copies) {
    $sql  = "UPDATE books SET title=?, author=?, isbn=?, genre_id=?, publisher=?, publish_year=?, description=?, total_copies=? WHERE id=?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "sssisssii", $title, $author, $isbn, $genre_id, $publisher, $publish_year, $description, $total_copies, $id);
    return mysqli_stmt_execute($stmt);
}

// ------------------------------------------------------------
// Delete a book by ID
// ------------------------------------------------------------
function deleteBook($conn, $id) {
    $sql  = "DELETE FROM books WHERE id=?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    return mysqli_stmt_execute($stmt);
}

// ------------------------------------------------------------
// Get last inserted book ID
// ------------------------------------------------------------
function getLastBookId($conn) {
    return mysqli_insert_id($conn);
}

// ------------------------------------------------------------
// Get all genres
// ------------------------------------------------------------
function getAllGenres($conn) {
    $sql    = "SELECT * FROM genres ORDER BY genre_name ASC";
    $result = mysqli_query($conn, $sql);
    $genres = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $genres[] = $row;
    }
    return $genres;
}

// ------------------------------------------------------------
// Create a new genre
// ------------------------------------------------------------
function createGenre($conn, $genre_name, $description) {
    $sql  = "INSERT INTO genres (genre_name, description) VALUES (?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ss", $genre_name, $description);
    return mysqli_stmt_execute($stmt);
}

// ------------------------------------------------------------
// Delete a genre
// ------------------------------------------------------------
function deleteGenre($conn, $id) {
    $sql  = "DELETE FROM genres WHERE id=?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    return mysqli_stmt_execute($stmt);
}

// ------------------------------------------------------------
// Get books by branch inventory
// ------------------------------------------------------------
function getBooksByBranch($conn, $branch_id) {
    $sql  = "SELECT b.*, g.genre_name, bi.available_copies, bi.total_copies FROM books b JOIN branch_inventory bi ON b.id = bi.book_id LEFT JOIN genres g ON b.genre_id = g.id WHERE bi.branch_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $branch_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $books  = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $books[] = $row;
    }
    return $books;
}

// ------------------------------------------------------------
// Get branch inventory for a specific book
// ------------------------------------------------------------
function getInventory($conn, $book_id, $branch_id) {
    $sql  = "SELECT * FROM branch_inventory WHERE book_id=? AND branch_id=?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $book_id, $branch_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($result);
}

// ------------------------------------------------------------
// Update available copies in inventory
// ------------------------------------------------------------
function updateInventory($conn, $book_id, $branch_id, $available_copies) {
    $sql  = "UPDATE branch_inventory SET available_copies=? WHERE book_id=? AND branch_id=?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "iii", $available_copies, $book_id, $branch_id);
    return mysqli_stmt_execute($stmt);
}

// ------------------------------------------------------------
// Add book to branch inventory
// ------------------------------------------------------------
function addToInventory($conn, $branch_id, $book_id, $available_copies, $total_copies) {
    $sql  = "INSERT INTO branch_inventory (branch_id, book_id, available_copies, total_copies) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "iiii", $branch_id, $book_id, $available_copies, $total_copies);
    return mysqli_stmt_execute($stmt);
}
