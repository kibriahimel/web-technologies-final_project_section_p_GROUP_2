<?php
require_once '../models/Connect.php';
require_once '../models/AdminModel.php';
require_once '../models/Close.php';
session_start();

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: LoginPageController.php');
    die();
}

$id = (int) $_GET['id'];

$conn = Connect();
$_SESSION['edit_book'] = getBookById($conn, $id);
$_SESSION['genres']    = getAllGenres($conn);
close($conn);

if (!$_SESSION['edit_book']) {
    $_SESSION['error'] = "Book not found";
    header('Location: AdminCatalogController.php');
    die();
}

header('Location: ../views/admin/edit_book.php');
