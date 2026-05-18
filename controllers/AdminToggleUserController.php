<?php
require_once '../models/Connect.php';
require_once '../models/AdminModel.php';
require_once '../models/Close.php';
session_start();

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: LoginPageController.php');
    die();
}

$userId = (int) $_POST['user_id'];
$status = (int) $_POST['status'];  // 1 = active, 0 = inactive

$conn = Connect();
setUserActiveStatus($conn, $userId, $status);
logAction($conn, $_SESSION['user_id'], 'Changed user status', "User ID: $userId, Status: $status");
close($conn);

$_SESSION['msg'] = "User status updated";
header('Location: AdminUsersController.php');
