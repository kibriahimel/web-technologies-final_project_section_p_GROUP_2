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
$_SESSION['report_borrows_per_month']  = getBorrowsPerMonth($conn);
$_SESSION['report_fines_per_month']    = getFinesPerMonth($conn);
$_SESSION['report_active_branches']    = getMostActiveBranches($conn);
$_SESSION['report_top_genres']         = getMostBorrowedGenres($conn);
$_SESSION['report_member_growth']      = getMemberGrowthPerMonth($conn);
close($conn);

header('Location: ../views/admin/reports.php');
