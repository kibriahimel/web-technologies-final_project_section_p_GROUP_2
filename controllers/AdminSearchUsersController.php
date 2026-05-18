<?php
require_once '../models/Connect.php';
require_once '../models/AdminModel.php';
require_once '../models/Close.php';
session_start();

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode(['error' => 'Unauthorized']);
    die();
}

$keyword = isset($_GET['q']) ? htmlspecialchars(trim($_GET['q'])) : '';

$conn    = Connect();
$users   = searchUsers($conn, $keyword);
close($conn);

echo json_encode($users);
