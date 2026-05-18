<?php
require_once '../models/Connect.php';
require_once '../models/AdminModel.php';
require_once '../models/Close.php';
session_start();

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: LoginPageController.php');
    die();
}

$_SESSION['error'] = "";
$_SESSION['msg']   = "";

$id            = (int) $_POST['id'];
$title         = htmlspecialchars(trim($_POST['title']));
$author        = htmlspecialchars(trim($_POST['author']));
$isbn          = htmlspecialchars(trim($_POST['isbn']));
$genreId       = (int) $_POST['genre_id'];
$publisher     = htmlspecialchars(trim($_POST['publisher']));
$publishedYear = (int) $_POST['published_year'];
$description   = htmlspecialchars(trim($_POST['description']));

if ($title == "" || $author == "") {
    $_SESSION['error'] = "Title and author are required";
    header('Location: AdminEditBookController.php?id=' . $id);
    die();
}

$conn = Connect();
$result = updateBook($conn, $id, $title, $author, $isbn, $genreId, $publisher, $publishedYear, $description);

if ($result) {
    logAction($conn, $_SESSION['user_id'], 'Edited book', "Book ID: $id, Title: $title");
    $_SESSION['msg'] = "Book updated successfully";
} else {
    $_SESSION['error'] = "Failed to update book";
}

close($conn);
header('Location: AdminCatalogController.php');
