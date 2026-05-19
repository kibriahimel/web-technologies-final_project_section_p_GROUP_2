<?php
// ============================================================
// controllers/ReviewController.php
// Handles book review submissions by members
// POST: submit/edit review | GET: delete review
// ============================================================

session_start();
require_once 'config/Connect.php';
require_once 'config/Close.php';
require_once 'models/ReviewModel.php';

if (!isset($_SESSION['user'])) {
    header("Location: index.php?page=login");
    exit();
}

$conn   = connect();
$action = isset($_GET['action']) ? $_GET['action'] : '';

// -------------------------------------------------------
// Submit a new review (POST)
// -------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $action != 'edit') {
    $book_id     = (int)$_POST['book_id'];
    $user_id     = $_SESSION['user'];
    $rating      = (int)$_POST['rating'];
    $review_text = htmlspecialchars(trim($_POST['review_text']));

    if ($rating < 1 || $rating > 5) {
        $_SESSION['error'] = "Rating must be between 1 and 5.";
        closeConn($conn);
        header("Location: index.php?page=book_detail&id=" . $book_id);
        exit();
    }

    if (empty($review_text)) {
        $_SESSION['error'] = "Review text cannot be empty.";
        closeConn($conn);
        header("Location: index.php?page=book_detail&id=" . $book_id);
        exit();
    }

    if (userAlreadyReviewed($conn, $user_id, $book_id)) {
        $_SESSION['error'] = "You have already reviewed this book.";
        closeConn($conn);
        header("Location: index.php?page=book_detail&id=" . $book_id);
        exit();
    }

    $created = createReview($conn, $book_id, $user_id, $rating, $review_text);

    if ($created) {
        $_SESSION['msg'] = "Review submitted! Thank you.";
    } else {
        $_SESSION['error'] = "Failed to submit review.";
    }

    closeConn($conn);
    header("Location: index.php?page=book_detail&id=" . $book_id);
    exit();
}

// -------------------------------------------------------
// Edit an existing review (POST with action=edit)
// -------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $action == 'edit') {
    $review_id   = (int)$_POST['review_id'];
    $book_id     = (int)$_POST['book_id'];
    $user_id     = $_SESSION['user'];
    $rating      = (int)$_POST['rating'];
    $review_text = htmlspecialchars(trim($_POST['review_text']));

    if ($rating < 1 || $rating > 5) {
        $_SESSION['error'] = "Rating must be between 1 and 5.";
        closeConn($conn);
        header("Location: index.php?page=book_detail&id=" . $book_id);
        exit();
    }

    if (empty($review_text)) {
        $_SESSION['error'] = "Review text cannot be empty.";
        closeConn($conn);
        header("Location: index.php?page=book_detail&id=" . $book_id);
        exit();
    }

    // Only allow member to edit their own review
    $sql  = "UPDATE book_reviews SET rating=?, review_text=? WHERE id=? AND user_id=?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "isii", $rating, $review_text, $review_id, $user_id);
    $updated = mysqli_stmt_execute($stmt);

    if ($updated) {
        $_SESSION['msg'] = "Review updated successfully!";
    } else {
        $_SESSION['error'] = "Failed to update review.";
    }

    closeConn($conn);
    header("Location: index.php?page=book_detail&id=" . $book_id);
    exit();
}

// -------------------------------------------------------
// Delete a review (GET action=delete)
// -------------------------------------------------------
if ($action == 'delete' && isset($_GET['id'])) {
    $id      = (int)$_GET['id'];
    $book_id = isset($_GET['book_id']) ? (int)$_GET['book_id'] : 0;
    $user_id = $_SESSION['user'];

    // Member can only delete own review; admin can delete any
    if ($_SESSION['role'] == 'admin') {
        deleteReview($conn, $id);
    } else {
        $sql  = "DELETE FROM book_reviews WHERE id=? AND user_id=?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ii", $id, $user_id);
        mysqli_stmt_execute($stmt);
    }

    $_SESSION['msg'] = "Review deleted.";
    closeConn($conn);
    header("Location: index.php?page=book_detail&id=" . $book_id);
    exit();
}

closeConn($conn);
header("Location: index.php?page=browse_books");
exit();
