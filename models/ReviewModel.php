<?php
// ============================================================
// models/ReviewModel.php
// Contains all database functions for book_reviews table
// ============================================================

// ------------------------------------------------------------
// Get all reviews for a specific book
// ------------------------------------------------------------
function getReviewsByBook($conn, $book_id) {
    $sql  = "SELECT r.*, u.full_name FROM book_reviews r JOIN users u ON r.user_id = u.id WHERE r.book_id=? ORDER BY r.created_at DESC";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $book_id);
    mysqli_stmt_execute($stmt);
    $result  = mysqli_stmt_get_result($stmt);
    $reviews = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $reviews[] = $row;
    }
    return $reviews;
}

// ------------------------------------------------------------
// Get average rating for a book
// ------------------------------------------------------------
function getAverageRating($conn, $book_id) {
    $sql  = "SELECT AVG(rating) AS avg_rating, COUNT(*) AS total FROM book_reviews WHERE book_id=?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $book_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($result);
}

// ------------------------------------------------------------
// Create a book review
// ------------------------------------------------------------
function createReview($conn, $book_id, $user_id, $rating, $review_text) {
    $sql  = "INSERT INTO book_reviews (book_id, user_id, rating, review_text) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "iiis", $book_id, $user_id, $rating, $review_text);
    return mysqli_stmt_execute($stmt);
}

// ------------------------------------------------------------
// Delete a review
// ------------------------------------------------------------
function deleteReview($conn, $id) {
    $sql  = "DELETE FROM book_reviews WHERE id=?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    return mysqli_stmt_execute($stmt);
}

// ------------------------------------------------------------
// Check if user already reviewed this book
// ------------------------------------------------------------
function userAlreadyReviewed($conn, $user_id, $book_id) {
    $sql  = "SELECT id FROM book_reviews WHERE user_id=? AND book_id=?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $user_id, $book_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    return mysqli_stmt_num_rows($stmt) > 0;
}

// ============================================================
// models/FineModel.php functionality included here
// Contains all database functions for fines table
// ============================================================

// ------------------------------------------------------------
// Get all fines with user and borrow details
// ------------------------------------------------------------
function getAllFines($conn) {
    $sql    = "SELECT f.*, u.full_name, u.email, b.title AS book_title FROM fines f JOIN users u ON f.user_id = u.id JOIN borrow_records br ON f.borrow_id = br.id JOIN books b ON br.book_id = b.id ORDER BY f.created_at DESC";
    $result = mysqli_query($conn, $sql);
    $list   = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $list[] = $row;
    }
    return $list;
}

// ------------------------------------------------------------
// Mark fine as paid
// ------------------------------------------------------------
function markFinePaid($conn, $fine_id) {
    $paid_at = date('Y-m-d H:i:s');
    $sql     = "UPDATE fines SET status='paid', paid_at=? WHERE id=?";
    $stmt    = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "si", $paid_at, $fine_id);
    return mysqli_stmt_execute($stmt);
}

// ============================================================
// Reading list functions (also stored here for simplicity)
// ============================================================

// ------------------------------------------------------------
// Add book to reading list
// ------------------------------------------------------------
function addToReadingList($conn, $user_id, $book_id) {
    $sql  = "INSERT INTO reading_lists (user_id, book_id) VALUES (?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $user_id, $book_id);
    return mysqli_stmt_execute($stmt);
}

// ------------------------------------------------------------
// Get reading list for a user
// ------------------------------------------------------------
function getReadingList($conn, $user_id) {
    $sql  = "SELECT rl.*, b.title, b.author, b.cover_image FROM reading_lists rl JOIN books b ON rl.book_id = b.id WHERE rl.user_id=? ORDER BY rl.added_at DESC";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $list   = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $list[] = $row;
    }
    return $list;
}

// ------------------------------------------------------------
// Remove from reading list
// ------------------------------------------------------------
function removeFromReadingList($conn, $id, $user_id) {
    $sql  = "DELETE FROM reading_lists WHERE id=? AND user_id=?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $id, $user_id);
    return mysqli_stmt_execute($stmt);
}

// ------------------------------------------------------------
// Check if book already in reading list
// ------------------------------------------------------------
function isInReadingList($conn, $user_id, $book_id) {
    $sql  = "SELECT id FROM reading_lists WHERE user_id=? AND book_id=?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $user_id, $book_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    return mysqli_stmt_num_rows($stmt) > 0;
}

// ============================================================
// Reservation functions
// ============================================================

// ------------------------------------------------------------
// Create a reservation
// ------------------------------------------------------------
function createReservation($conn, $user_id, $book_id, $branch_id) {
    $reservation_date = date('Y-m-d');
    $expiry_date      = date('Y-m-d', strtotime('+3 days'));
    $sql              = "INSERT INTO reservations (user_id, book_id, branch_id, reservation_date, expiry_date) VALUES (?, ?, ?, ?, ?)";
    $stmt             = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "iiiss", $user_id, $book_id, $branch_id, $reservation_date, $expiry_date);
    return mysqli_stmt_execute($stmt);
}

// ------------------------------------------------------------
// Get reservations by user
// ------------------------------------------------------------
function getReservationsByUser($conn, $user_id) {
    $sql  = "SELECT r.*, b.title, b.author, bn.branch_name FROM reservations r JOIN books b ON r.book_id = b.id JOIN branches bn ON r.branch_id = bn.id WHERE r.user_id=? ORDER BY r.created_at DESC";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $list   = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $list[] = $row;
    }
    return $list;
}

// ------------------------------------------------------------
// Cancel a reservation
// ------------------------------------------------------------
function cancelReservation($conn, $id, $user_id) {
    $sql  = "UPDATE reservations SET status='cancelled' WHERE id=? AND user_id=?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $id, $user_id);
    return mysqli_stmt_execute($stmt);
}