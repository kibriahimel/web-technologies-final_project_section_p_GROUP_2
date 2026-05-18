<?php
require_once '../models/Connect.php';
require_once '../models/AdminModel.php';
require_once '../models/Close.php';
session_start();

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: LoginPageController.php');
    die();
}

$conn = Connect();
$_SESSION['books']  = getAllBooks($conn);
$_SESSION['genres'] = getAllGenres($conn);
close($conn);

header('Location: ../views/admin/catalog.php');
