<?php
// ============================================================
// models/BorrowModel.php
// Contains all database functions for borrow_records table
// ============================================================

require_once 'config/Connect.php';
require_once 'config/Close.php';

// ------------------------------------------------------------
// Create a borrow request (member submits borrow)
// ------------------------------------------------------------
function createBorrowRequest($conn, $user_id, $book_id, $branch_id) {
    $sql  = "INSERT INTO borrow_records (member_id, book_id, branch_id, status) VALUES (?, ?, ?, 'pending')";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "iii", $user_id, $book_id, $branch_id);
    return mysqli_stmt_execute($stmt);
}

// ------------------------------------------------------------
// Approve borrow request (librarian approves)
// ------------------------------------------------------------
function approveBorrow($conn, $borrow_id, $librarian_id, $due_date) {
    $borrow_date = date('Y-m-d');
    $sql         = "UPDATE borrow_records SET status='active', borrow_date=?, due_date=?, librarian_id=? WHERE id=?";
    $stmt        = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ssii", $borrow_date, $due_date, $librarian_id, $borrow_id);
    return mysqli_stmt_execute($stmt);
}

// ------------------------------------------------------------
// Process book return
// ------------------------------------------------------------
function returnBook($conn, $borrow_id) {
    $return_date = date('Y-m-d');
    $sql         = "UPDATE borrow_records SET status='returned', return_date=? WHERE id=?";
    $stmt        = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "si", $return_date, $borrow_id);
    return mysqli_stmt_execute($stmt);
}

// ------------------------------------------------------------
// Get all borrow records with book and user details
// ------------------------------------------------------------
function getAllBorrowRecords($conn) {
    $sql    = "SELECT br.*, u.full_name, u.email, b.title, b.author, bn.branch_name
               FROM borrow_records br
               JOIN users u ON br.member_id = u.id
               JOIN books b ON br.book_id = b.id
               JOIN branches bn ON br.branch_id = bn.id
               ORDER BY br.created_at DESC";
    $result = mysqli_query($conn, $sql);
    $list   = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $list[] = $row;
    }
    return $list;
}

// ------------------------------------------------------------
// Get borrow records for a specific member
// ------------------------------------------------------------
function getBorrowsByUser($conn, $user_id) {
    $sql  = "SELECT br.*, b.title, b.author, bn.branch_name
             FROM borrow_records br
             JOIN books b ON br.book_id = b.id
             JOIN branches bn ON br.branch_id = bn.id
             WHERE br.member_id = ?
             ORDER BY br.created_at DESC";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $list   = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $list[] = $row;
    }
    return $list;
}

// ------------------------------------------------------------
// Get borrow records for a specific branch
// ------------------------------------------------------------
function getBorrowsByBranch($conn, $branch_id) {
    $sql  = "SELECT br.*, u.full_name, b.title
             FROM borrow_records br
             JOIN users u ON br.member_id = u.id
             JOIN books b ON br.book_id = b.id
             WHERE br.branch_id = ?
             ORDER BY br.created_at DESC";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $branch_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $list   = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $list[] = $row;
    }
    return $list;
}

// ------------------------------------------------------------
// Get a single borrow record by ID
// ------------------------------------------------------------
function getBorrowById($conn, $borrow_id) {
    $sql  = "SELECT br.*, u.full_name, b.title, b.author
             FROM borrow_records br
             JOIN users u ON br.member_id = u.id
             JOIN books b ON br.book_id = b.id
             WHERE br.id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $borrow_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($result);
}

// ------------------------------------------------------------
// Get pending borrow requests (for librarian dashboard)
// ------------------------------------------------------------
function getPendingBorrows($conn, $branch_id) {
    $sql  = "SELECT br.*, u.full_name, b.title
             FROM borrow_records br
             JOIN users u ON br.member_id = u.id
             JOIN books b ON br.book_id = b.id
             WHERE br.status='pending' AND br.branch_id = ?
             ORDER BY br.created_at ASC";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $branch_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $list   = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $list[] = $row;
    }
    return $list;
}

// ------------------------------------------------------------
// Renew a borrow (member requests renewal)
// ------------------------------------------------------------
function renewBorrow($conn, $borrow_id, $member_id, $max_renewals, $extra_days) {
    // Check current record belongs to this member and is active
    $sql  = "SELECT * FROM borrow_records WHERE id=? AND member_id=? AND status='active'";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $borrow_id, $member_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $record = mysqli_fetch_assoc($result);

    if (!$record) return "not_found";
    if ($record['renewals_count'] >= $max_renewals) return "max_reached";

    // Extend due date
    $new_due = date('Y-m-d', strtotime($record['due_date'] . ' +' . $extra_days . ' days'));
    $sql2    = "UPDATE borrow_records SET due_date=?, renewals_count=renewals_count+1 WHERE id=?";
    $stmt2   = mysqli_prepare($conn, $sql2);
    mysqli_stmt_bind_param($stmt2, "si", $new_due, $borrow_id);
    $done = mysqli_stmt_execute($stmt2);

    return $done ? "success" : "error";
}

// ------------------------------------------------------------
// Get fines for a specific user
// ------------------------------------------------------------
function getFinesByUser($conn, $user_id) {
    $sql  = "SELECT f.*, b.title AS book_title
             FROM fines f
             JOIN borrow_records br ON f.borrow_id = br.id
             JOIN books b ON br.book_id = b.id
             WHERE f.user_id = ?
             ORDER BY f.created_at DESC";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $list   = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $list[] = $row;
    }
    return $list;
}

// ------------------------------------------------------------
// Create a fine record
// ------------------------------------------------------------
function createFine($conn, $borrow_id, $user_id, $amount, $reason) {
    $sql  = "INSERT INTO fines (borrow_id, user_id, amount, reason, status) VALUES (?, ?, ?, ?, 'unpaid')";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "iids", $borrow_id, $user_id, $amount, $reason);
    return mysqli_stmt_execute($stmt);
}