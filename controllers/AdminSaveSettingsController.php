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

$fineRate    = htmlspecialchars(trim($_POST['default_fine_rate']));
$borrowDays  = htmlspecialchars(trim($_POST['default_max_borrow_days']));
$maxBooks    = htmlspecialchars(trim($_POST['default_max_books_per_member']));
$selfReg     = isset($_POST['allow_self_registration']) ? '1' : '0';

if ($fineRate == "" || $borrowDays == "" || $maxBooks == "") {
    $_SESSION['error'] = "Please fill in all fields";
    header('Location: AdminSettingsController.php');
    die();
}

$conn = Connect();
updateSetting($conn, 'default_fine_rate', $fineRate);
updateSetting($conn, 'default_max_borrow_days', $borrowDays);
updateSetting($conn, 'default_max_books_per_member', $maxBooks);
updateSetting($conn, 'allow_self_registration', $selfReg);
logAction($conn, $_SESSION['user_id'], 'Updated global settings', "Fine rate: $fineRate, Borrow days: $borrowDays");
close($conn);

$_SESSION['msg'] = "Settings updated successfully";
header('Location: AdminSettingsController.php');
