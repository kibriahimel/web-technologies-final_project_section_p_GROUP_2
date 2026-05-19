<?php
// ============================================================
// controllers/RegisterController.php
// Handles POST registration form submission
// ============================================================

session_start();
require_once 'config/Connect.php';
require_once 'config/Close.php';
require_once 'models/UserModel.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Get form data and sanitize
    $full_name  = htmlspecialchars(trim($_POST['full_name']));
    $email      = htmlspecialchars(trim($_POST['email']));
    $password   = htmlspecialchars(trim($_POST['password']));
    $confirm    = htmlspecialchars(trim($_POST['confirm_password']));
    $phone      = htmlspecialchars(trim($_POST['phone']));
    $address    = htmlspecialchars(trim($_POST['address']));

    // Server-side validation
    if (empty($full_name) || empty($email) || empty($password)) {
        $_SESSION['error'] = "Please fill in all required fields.";
        header("Location: index.php?page=register");
        exit();
    }

    if (strlen($full_name) < 3) {
        $_SESSION['error'] = "Full name must be at least 3 characters.";
        header("Location: index.php?page=register");
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Invalid email format.";
        header("Location: index.php?page=register");
        exit();
    }

    if (strlen($password) < 6) {
        $_SESSION['error'] = "Password must be at least 6 characters.";
        header("Location: index.php?page=register");
        exit();
    }

    if ($password !== $confirm) {
        $_SESSION['error'] = "Passwords do not match.";
        header("Location: index.php?page=register");
        exit();
    }

    $conn = connect();

    // Check if email already exists
    if (emailExists($conn, $email)) {
        $_SESSION['error'] = "This email is already registered. Please login.";
        closeConn($conn);
        header("Location: index.php?page=register");
        exit();
    }

    // Create new member account (role = member, branch_id = NULL)
    $created = createUser($conn, $full_name, $email, $password, 'member', NULL, $phone, $address);

    closeConn($conn);

    if ($created) {
        $_SESSION['msg'] = "Registration successful! Please login.";
        header("Location: index.php?page=login");
    } else {
        $_SESSION['error'] = "Registration failed. Please try again.";
        header("Location: index.php?page=register");
    }
    exit();

} else {
    header("Location: index.php?page=register");
    exit();
}
