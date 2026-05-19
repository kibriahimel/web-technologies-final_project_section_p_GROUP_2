<?php
// ============================================================
// controllers/UserController.php
// Handles user management by Admin
// POST: create user | GET: delete/status change
// ============================================================

session_start();
require_once 'config/Connect.php';
require_once 'config/Close.php';
require_once 'models/UserModel.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php?page=login");
    exit();
}

$conn   = connect();
$action = isset($_GET['action']) ? $_GET['action'] : '';

// Create a new user by Admin (POST)
if ($action == 'create' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = htmlspecialchars(trim($_POST['full_name']));
    $email     = htmlspecialchars(trim($_POST['email']));
    $password  = htmlspecialchars(trim($_POST['password']));
    $role      = htmlspecialchars(trim($_POST['role']));
    $branch_id = !empty($_POST['branch_id']) ? (int)$_POST['branch_id'] : NULL;
    $phone     = htmlspecialchars(trim($_POST['phone']));
    $address   = htmlspecialchars(trim($_POST['address']));

    if (empty($full_name) || empty($email) || empty($password)) {
        $_SESSION['error'] = "Fill all required fields.";
        closeConn($conn);
        header("Location: index.php?page=manage_users");
        exit();
    }

    if (emailExists($conn, $email)) {
        $_SESSION['error'] = "Email already registered.";
        closeConn($conn);
        header("Location: index.php?page=manage_users");
        exit();
    }

    $created = createUser($conn, $full_name, $email, $password, $role, $branch_id, $phone, $address);
    $_SESSION['msg'] = $created ? "User created successfully!" : "Failed to create user.";
    closeConn($conn);
    header("Location: index.php?page=manage_users");
    exit();
}

// Toggle user status (GET)
if ($action == 'status' && isset($_GET['id']) && isset($_GET['status'])) {
    $id     = (int)$_GET['id'];
    $status = $_GET['status'] == 'active' ? 'inactive' : 'active';
    updateUserStatus($conn, $id, $status);
    $_SESSION['msg'] = "User status updated.";
    closeConn($conn);
    header("Location: index.php?page=manage_users");
    exit();
}

// Delete a user (GET)
if ($action == 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    // Prevent deleting self
    if ($id == $_SESSION['user']) {
        $_SESSION['error'] = "You cannot delete your own account.";
    } else {
        deleteUser($conn, $id);
        $_SESSION['msg'] = "User deleted successfully.";
    }
    closeConn($conn);
    header("Location: index.php?page=manage_users");
    exit();
}

closeConn($conn);
header("Location: index.php?page=manage_users");
exit();
