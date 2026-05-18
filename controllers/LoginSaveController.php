<?php
require_once '../models/Connect.php';
require_once '../models/AdminModel.php';
require_once '../models/Close.php';
session_start();

$_SESSION['error'] = "";

$email    = htmlspecialchars(trim($_POST['email']));
$password = trim($_POST['password']);

if ($email == "" || $password == "") {
    $_SESSION['error'] = "Please fill in all fields";
    header('Location: LoginPageController.php');
    die();
}

$conn = Connect();
$user = getUserByEmail($conn, $email);
close($conn);

if (!$user || !password_verify($password, $user['password_hash'])) {
    $_SESSION['error'] = "Invalid email or password";
    header('Location: LoginPageController.php');
    die();
}

// Store user info in session
$_SESSION['user_id']   = $user['id'];
$_SESSION['user_name'] = $user['name'];
$_SESSION['user_role'] = $user['role'];
$_SESSION['branch_id'] = $user['branch_id'];

// Redirect based on role
switch ($user['role']) {
    case 'admin':
        header('Location: AdminDashboardController.php');
        break;
    case 'librarian':
        header('Location: ../views/librarian/dashboard.php');
        break;
    case 'branch_manager':
        header('Location: ../views/branch_manager/dashboard.php');
        break;
    case 'member':
        header('Location: ../views/member/dashboard.php');
        break;
    default:
        $_SESSION['error'] = "Unknown role";
        header('Location: LoginPageController.php');
}
die();
