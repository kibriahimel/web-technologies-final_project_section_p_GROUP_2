<?php
// ============================================================
// models/UserModel.php
// Contains all database functions for the users table
// ============================================================

require_once 'config/Connect.php';
require_once 'config/Close.php';

// ------------------------------------------------------------
// Get all users from the database
// ------------------------------------------------------------
function getAllUsers($conn) {
    $sql    = "SELECT u.*, b.branch_name FROM users u LEFT JOIN branches b ON u.branch_id = b.id ORDER BY u.id DESC";
    $result = mysqli_query($conn, $sql);
    $users  = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $users[] = $row;
    }
    return $users;
}

// ------------------------------------------------------------
// Get a single user by ID
// ------------------------------------------------------------
function getUserById($conn, $id) {
    $sql  = "SELECT u.*, b.branch_name FROM users u LEFT JOIN branches b ON u.branch_id = b.id WHERE u.id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($result);
}

// ------------------------------------------------------------
// Get user by email (used for login)
// ------------------------------------------------------------
function getUserByEmail($conn, $email) {
    $sql  = "SELECT * FROM users WHERE email = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($result);
}

// ------------------------------------------------------------
// Create a new user (Registration)
// ------------------------------------------------------------
function createUser($conn, $full_name, $email, $password, $role, $branch_id, $phone, $address) {
    $hashed = md5($password); // Simple hash for beginner project
    $sql    = "INSERT INTO users (full_name, email, password, role, branch_id, phone, address) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt   = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ssssiis", $full_name, $email, $hashed, $role, $branch_id, $phone, $address);
    return mysqli_stmt_execute($stmt);
}

// ------------------------------------------------------------
// Update user profile
// ------------------------------------------------------------
function updateUser($conn, $id, $full_name, $phone, $address, $profile_pic) {
    $sql  = "UPDATE users SET full_name=?, phone=?, address=?, profile_pic=? WHERE id=?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ssssi", $full_name, $phone, $address, $profile_pic, $id);
    return mysqli_stmt_execute($stmt);
}

// ------------------------------------------------------------
// Update user status (active/inactive) by admin
// ------------------------------------------------------------
function updateUserStatus($conn, $id, $status) {
    $sql  = "UPDATE users SET status=? WHERE id=?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "si", $status, $id);
    return mysqli_stmt_execute($stmt);
}

// ------------------------------------------------------------
// Delete a user by ID
// ------------------------------------------------------------
function deleteUser($conn, $id) {
    $sql  = "DELETE FROM users WHERE id=?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    return mysqli_stmt_execute($stmt);
}

// ------------------------------------------------------------
// Get all librarians (for manager to assign)
// ------------------------------------------------------------
function getAllLibrarians($conn) {
    $sql    = "SELECT * FROM users WHERE role='librarian' AND status='active'";
    $result = mysqli_query($conn, $sql);
    $list   = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $list[] = $row;
    }
    return $list;
}

// ------------------------------------------------------------
// Check if email already exists
// ------------------------------------------------------------
function emailExists($conn, $email) {
    $sql  = "SELECT id FROM users WHERE email=?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    return mysqli_stmt_num_rows($stmt) > 0;
}
