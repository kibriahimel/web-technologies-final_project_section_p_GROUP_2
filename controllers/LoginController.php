<?php
// ============================================================
// controllers/LoginController.php
// Handles POST login form submission
// ============================================================

// ✅ REMOVED session_start() — already called in index.php
require_once 'config/Connect.php';
require_once 'config/Close.php';
require_once 'models/UserModel.php';

// Only process if form was submitted via POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Get form data and sanitize it
    $email    = htmlspecialchars(trim($_POST['email']));
    $password = htmlspecialchars(trim($_POST['password']));

    // Server-side validation
    if (empty($email) || empty($password)) {
        $_SESSION['error'] = "Please fill in all fields.";
        header("Location: index.php?page=login");
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Invalid email format.";
        header("Location: index.php?page=login");
        exit();
    }

    // Connect to database
    $conn = connect();

    // Find user by email
    $user = getUserByEmail($conn, $email);

    // Check user exists and password matches
    if ($user && $user['password'] == md5($password)) {
        // Check if account is active
        if ($user['status'] == 'inactive') {
            $_SESSION['error'] = "Your account has been deactivated. Contact admin.";
            header("Location: index.php?page=login");
            exit();
        }

        // Store user info in session
        $_SESSION['user']      = $user['id'];
        $_SESSION['user_name'] = $user['full_name'];
        $_SESSION['role']      = $user['role'];
        $_SESSION['branch_id'] = $user['branch_id'];
        $_SESSION['msg']       = "Welcome back, " . $user['full_name'] . "!";

        closeConn($conn);

        // Redirect based on role
        if ($user['role'] == 'admin') {
            header("Location: index.php?page=admin_dashboard");
        } elseif ($user['role'] == 'manager') {
            header("Location: index.php?page=manager_dashboard");
        } elseif ($user['role'] == 'librarian') {
            header("Location: index.php?page=librarian_dashboard");
        } else {
            header("Location: index.php?page=member_dashboard");
        }
        exit();

    } else {
        closeConn($conn);
        $_SESSION['error'] = "Invalid email or password. Please try again.";
        header("Location: index.php?page=login");
        exit();
    }

} else {
    // If someone tries to access this file directly via GET
    header("Location: index.php?page=login");
    exit();
}