<?php
require_once '../models/Connect.php';
require_once '../models/AdminModel.php';
require_once '../models/Close.php';
session_start();

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: LoginPageController.php');
    die();
}

$id = (int) $_POST['id'];

$conn = Connect();
deleteAnnouncement($conn, $id);
logAction($conn, $_SESSION['user_id'], 'Deleted announcement', "Announcement ID: $id");
close($conn);

$_SESSION['msg'] = "Announcement deleted";
header('Location: AdminAnnouncementsController.php');
