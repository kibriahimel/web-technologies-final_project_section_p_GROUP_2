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

$name     = htmlspecialchars(trim($_POST['name']));
$email    = htmlspecialchars(trim($_POST['email']));
$password = trim($_POST['password']);
$phone    = htmlspecialchars(trim($_POST['phone']));
$role     = htmlspecialchars(trim($_POST['role']));
$branchId = (int) $_POST['branch_id'];

if ($name == "" || $email == "" || $password == "" || $role == "") {
    $_SESSION['error'] = "Please fill in all required fields";
    header('Location: AdminUsersController.php');
    die();
}

if (!in_array($role, ['librarian', 'branch_manager'])) {
    $_SESSION['error'] = "Invalid role selected";
    header('Location: AdminUsersController.php');
    die();
}

$conn = Connect();
$result = createStaffAccount($conn, $name, $email, $password, $phone, $role, $branchId);

if ($result) {
    logAction($conn, $_SESSION['user_id'], 'Created staff account', "Name: $name, Role: $role");
    $_SESSION['msg'] = "Account created successfully";
} else {
    $_SESSION['error'] = "Failed to create account. Email may already exist.";
}

close($conn);
header('Location: AdminUsersController.php');
