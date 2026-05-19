<?php
// ============================================================
// controllers/ProfileController.php
// Handles user profile update and password change (POST)
// ============================================================

session_start();
require_once 'config/Connect.php';
require_once 'config/Close.php';
require_once 'models/UserModel.php';

if (!isset($_SESSION['user'])) {
    header("Location: index.php?page=login");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $action = isset($_POST['action']) ? $_POST['action'] : 'update_profile';

    // -------------------------------------------------------
    // ACTION: Update profile info
    // -------------------------------------------------------
    if ($action == 'update_profile') {

        $id        = $_SESSION['user'];
        $full_name = htmlspecialchars(trim($_POST['full_name']));
        $phone     = htmlspecialchars(trim($_POST['phone']));
        $address   = htmlspecialchars(trim($_POST['address']));

        if (empty($full_name)) {
            $_SESSION['error'] = "Full name cannot be empty.";
            header("Location: index.php?page=profile");
            exit();
        }

        $conn        = connect();
        $current     = getUserById($conn, $id);
        $profile_pic = $current['profile_pic'];

        // Handle profile picture upload
        if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0) {
            $allowed  = array('jpg', 'jpeg', 'png');
            $file_ext = strtolower(pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION));
            if (in_array($file_ext, $allowed)) {
                $profile_pic = time() . "_" . $_FILES['profile_pic']['name'];
                move_uploaded_file($_FILES['profile_pic']['tmp_name'], __DIR__ . "/../uploads/" . $profile_pic);
            } else {
                $_SESSION['error'] = "Only JPG and PNG files are allowed.";
                closeConn($conn);
                header("Location: index.php?page=profile");
                exit();
            }
        }

        $updated = updateUser($conn, $id, $full_name, $phone, $address, $profile_pic);
        closeConn($conn);

        if ($updated) {
            $_SESSION['user_name'] = $full_name;
            $_SESSION['msg']       = "Profile updated successfully!";
        } else {
            $_SESSION['error'] = "Failed to update profile.";
        }

        header("Location: index.php?page=profile");
        exit();
    }

    // -------------------------------------------------------
    // ACTION: Change password
    // -------------------------------------------------------
    if ($action == 'change_password') {

        $id           = $_SESSION['user'];
        $current_pass = $_POST['current_password'];
        $new_pass     = $_POST['new_password'];
        $confirm_pass = $_POST['confirm_password'];

        // Validation
        if (empty($current_pass) || empty($new_pass) || empty($confirm_pass)) {
            $_SESSION['error'] = "All password fields are required.";
            header("Location: index.php?page=profile");
            exit();
        }

        if (strlen($new_pass) < 6) {
            $_SESSION['error'] = "New password must be at least 6 characters.";
            header("Location: index.php?page=profile");
            exit();
        }

        if ($new_pass !== $confirm_pass) {
            $_SESSION['error'] = "New passwords do not match.";
            header("Location: index.php?page=profile");
            exit();
        }

        $conn = connect();
        $user = getUserById($conn, $id);

        // Verify current password
        if (!password_verify($current_pass, $user['password'])) {
            $_SESSION['error'] = "Current password is incorrect.";
            closeConn($conn);
            header("Location: index.php?page=profile");
            exit();
        }

        // Hash new password and save
        $new_hash = password_hash($new_pass, PASSWORD_BCRYPT);
        $sql      = "UPDATE users SET password = ? WHERE id = ?";
        $stmt     = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'si', $new_hash, $id);
        $done = mysqli_stmt_execute($stmt);
        closeConn($conn);

        if ($done) {
            $_SESSION['msg'] = "Password changed successfully!";
        } else {
            $_SESSION['error'] = "Failed to change password. Try again.";
        }

        header("Location: index.php?page=profile");
        exit();
    }

} else {
    header("Location: index.php?page=profile");
    exit();
}