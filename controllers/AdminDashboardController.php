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

$_SESSION['total_members']  = getTotalMembers($conn);
$_SESSION['total_books']    = getTotalBooks($conn);
$_SESSION['active_loans']   = getTotalActiveLoans($conn);
$_SESSION['overdue_loans']  = getTotalOverdueLoans($conn);
$_SESSION['fines_outstanding'] = getTotalFinesOutstanding($conn);

close($conn);

header('Location: ../views/admin/dashboard.php');
