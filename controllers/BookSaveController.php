<?php
// ============================================================
// controllers/BookSaveController.php
// Handles POST request to add a new book
// Used by: Librarian
// ============================================================

session_start();
require_once 'config/Connect.php';
require_once 'config/Close.php';
require_once 'models/BookModel.php';

// Role check - only librarian or admin can add books
if (!isset($_SESSION['user']) || ($_SESSION['role'] != 'librarian' && $_SESSION['role'] != 'admin')) {
    header("Location: index.php?page=login");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Get and sanitize form data
    $title        = htmlspecialchars(trim($_POST['title']));
    $author       = htmlspecialchars(trim($_POST['author']));
    $isbn         = htmlspecialchars(trim($_POST['isbn']));
    $genre_id     = (int)$_POST['genre_id'];
    $publisher    = htmlspecialchars(trim($_POST['publisher']));
    $publish_year = htmlspecialchars(trim($_POST['publish_year']));
    $description  = htmlspecialchars(trim($_POST['description']));
    $total_copies = (int)$_POST['total_copies'];
    $cover_image  = 'no_cover.png';

    // Validation
    if (empty($title) || empty($author)) {
        $_SESSION['error'] = "Title and Author are required.";
        header("Location: index.php?page=add_book");
        exit();
    }

    // File upload for book cover
    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] == 0) {
        $allowed      = array('jpg', 'jpeg', 'png', 'gif');
        $file_name    = $_FILES['cover_image']['name'];
        $file_tmp     = $_FILES['cover_image']['tmp_name'];
        $file_size    = $_FILES['cover_image']['size'];
        $file_ext     = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        if (!in_array($file_ext, $allowed)) {
            $_SESSION['error'] = "Only JPG, PNG, GIF files are allowed.";
            header("Location: index.php?page=add_book");
            exit();
        }

        if ($file_size > 2000000) { // 2MB limit
            $_SESSION['error'] = "File size must be less than 2MB.";
            header("Location: index.php?page=add_book");
            exit();
        }

        $cover_image = time() . "_" . $file_name;
        move_uploaded_file($file_tmp, "uploads/" . $cover_image);
    }

    $conn    = connect();
    $created = createBook($conn, $title, $author, $isbn, $genre_id, $publisher, $publish_year, $description, $cover_image, $total_copies);

    // Also add to branch inventory if librarian has a branch
    if ($created && isset($_SESSION['branch_id']) && $_SESSION['branch_id']) {
        $book_id = mysqli_insert_id($conn);
        addToInventory($conn, $_SESSION['branch_id'], $book_id, $total_copies, $total_copies);
    }

    closeConn($conn);

    if ($created) {
        $_SESSION['msg'] = "Book added successfully!";
    } else {
        $_SESSION['error'] = "Failed to add book. Please try again.";
    }

    header("Location: index.php?page=book_list");
    exit();

} else {
    header("Location: index.php?page=add_book");
    exit();
}
