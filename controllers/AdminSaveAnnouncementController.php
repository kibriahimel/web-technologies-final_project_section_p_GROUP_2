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

$title = htmlspecialchars(trim($_POST['title']));
$body  = htmlspecialchars(trim($_POST['body']));

if ($title == "" || $body == "") {
    $_SESSION['error'] = "Please fill in all fields";
    header('Location: AdminAnnouncementsController.php');
    die();
}

$conn = Connect();
createAnnouncement($conn, $_SESSION['user_id'], $title, $body);
logAction($conn, $_SESSION['user_id'], 'Posted announcement', "Title: $title");
close($conn);

$_SESSION['msg'] = "Announcement posted successfully";
header('Location: AdminAnnouncementsController.php');
