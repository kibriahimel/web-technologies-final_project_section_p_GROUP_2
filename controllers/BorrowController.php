<?php
// ============================================================
// controllers/BorrowController.php
// Handles borrow requests, approvals, and returns
// ============================================================

session_start();
require_once 'config/Connect.php';
require_once 'config/Close.php';
require_once 'models/BorrowModel.php';
require_once 'models/BookModel.php';
require_once 'models/ReviewModel.php';

if (!isset($_SESSION['user'])) {
    header("Location: index.php?page=login");
    exit();
}

$conn   = connect();
$action = isset($_GET['action']) ? $_GET['action'] : '';

// -------------------------------------------------------
// ACTION: Member submits a borrow request (POST)
// -------------------------------------------------------
if ($action == 'request' && $_SERVER['REQUEST_METHOD'] == 'POST') {

    if ($_SESSION['role'] != 'member') {
        $_SESSION['error'] = "Only members can borrow books.";
        header("Location: index.php?page=browse_books");
        exit();
    }

    $book_id   = (int)$_POST['book_id'];
    $branch_id = (int)$_POST['branch_id'];
    $user_id   = $_SESSION['user'];

    // Check availability
    $inventory = getInventory($conn, $book_id, $branch_id);
    if (!$inventory || $inventory['available_copies'] < 1) {
        $_SESSION['error'] = "Sorry, this book is not available at the selected branch.";
        closeConn($conn);
        header("Location: index.php?page=book_detail&id=" . $book_id);
        exit();
    }

    $created = createBorrowRequest($conn, $user_id, $book_id, $branch_id);

    if ($created) {
        $_SESSION['msg'] = "Borrow request submitted! Wait for librarian approval.";
    } else {
        $_SESSION['error'] = "Failed to submit request. Try again.";
    }

    closeConn($conn);
    header("Location: index.php?page=my_borrows");
    exit();
}

// -------------------------------------------------------
// ACTION: Librarian approves a borrow request (GET)
// -------------------------------------------------------
if ($action == 'approve' && isset($_GET['id'])) {

    if ($_SESSION['role'] != 'librarian' && $_SESSION['role'] != 'admin') {
        $_SESSION['error'] = "Access denied.";
        header("Location: index.php?page=librarian_dashboard");
        exit();
    }

    $borrow_id   = (int)$_GET['id'];
    $librarian_id = $_SESSION['user'];
    $due_date     = date('Y-m-d', strtotime('+14 days')); // Default 14 days

    // Get borrow record to find book and branch
    $record = getBorrowById($conn, $borrow_id);

    if ($record) {
        // Approve the borrow
        $approved = approveBorrow($conn, $borrow_id, $librarian_id, $due_date);

        // Decrease available copies in inventory
        if ($approved) {
            $inventory = getInventory($conn, $record['book_id'], $record['branch_id']);
            if ($inventory) {
                $new_count = $inventory['available_copies'] - 1;
                updateInventory($conn, $record['book_id'], $record['branch_id'], $new_count);
            }
            $_SESSION['msg'] = "Borrow request approved! Due date: " . $due_date;
        }
    } else {
        $_SESSION['error'] = "Borrow record not found.";
    }

    closeConn($conn);
    header("Location: index.php?page=manage_borrows");
    exit();
}

// -------------------------------------------------------
// ACTION: Librarian processes a return (GET)
// -------------------------------------------------------
if ($action == 'return' && isset($_GET['id'])) {

    if ($_SESSION['role'] != 'librarian' && $_SESSION['role'] != 'admin') {
        $_SESSION['error'] = "Access denied.";
        header("Location: index.php?page=librarian_dashboard");
        exit();
    }

    $borrow_id = (int)$_GET['id'];
    $record    = getBorrowById($conn, $borrow_id);

    if ($record) {
        $returned = returnBook($conn, $borrow_id);

        if ($returned) {
            // Increase available copies back
            $inventory = getInventory($conn, $record['book_id'], $record['branch_id']);
            if ($inventory) {
                $new_count = $inventory['available_copies'] + 1;
                updateInventory($conn, $record['book_id'], $record['branch_id'], $new_count);
            }

            // Check if overdue and create fine
            $today    = date('Y-m-d');
            $due_date = $record['due_date'];
            if ($today > $due_date) {
                $diff_days = (strtotime($today) - strtotime($due_date)) / 86400;
                $fine_amt  = $diff_days * 5; // 5 taka per day
               createFine($conn, $borrow_id, $record['member_id'], $fine_amt, "Returned " . (int)$diff_days . " day(s) late");
                $_SESSION['msg'] = "Book returned. A fine of Tk " . $fine_amt . " has been created.";
            } else {
                $_SESSION['msg'] = "Book returned successfully!";
            }
        }
    } else {
        $_SESSION['error'] = "Borrow record not found.";
    }

    closeConn($conn);
    header("Location: index.php?page=manage_borrows");
    exit();
}
// -------------------------------------------------------
// ACTION: Member requests loan renewal (GET)
// -------------------------------------------------------
if ($action == 'renew' && isset($_GET['id'])) {

    if ($_SESSION['role'] != 'member') {
        $_SESSION['error'] = "Access denied.";
        closeConn($conn);
        header("Location: index.php?page=my_borrows");
        exit();
    }

    $borrow_id = (int)$_GET['id'];
    $member_id = $_SESSION['user'];
    $branch_id = $_SESSION['branch_id'];

    // Get branch policy for max renewals and borrow days
    $policy_sql  = "SELECT * FROM branch_policies WHERE branch_id=?";
    $policy_stmt = mysqli_prepare($conn, $policy_sql);
    mysqli_stmt_bind_param($policy_stmt, "i", $branch_id);
    mysqli_stmt_execute($policy_stmt);
    $policy_result = mysqli_stmt_get_result($policy_stmt);
    $policy        = mysqli_fetch_assoc($policy_result);

    $max_renewals = $policy ? $policy['max_renewals'] : 2;
    $extra_days   = $policy ? $policy['max_borrow_days'] : 14;

    $result = renewBorrow($conn, $borrow_id, $member_id, $max_renewals, $extra_days);

    if ($result == 'success') {
        $_SESSION['msg'] = "Loan renewed successfully! Due date extended by " . $extra_days . " days.";
    } elseif ($result == 'max_reached') {
        $_SESSION['error'] = "You have reached the maximum number of renewals for this book.";
    } elseif ($result == 'not_found') {
        $_SESSION['error'] = "Borrow record not found or already returned.";
    } else {
        $_SESSION['error'] = "Renewal failed. Please try again.";
    }

    closeConn($conn);
    header("Location: index.php?page=my_borrows");
    exit();
}

// -------------------------------------------------------
// ACTION: Librarian rejects a borrow request (POST)
// -------------------------------------------------------
if ($action == 'reject' && $_SERVER['REQUEST_METHOD'] == 'POST') {

    if ($_SESSION['role'] != 'librarian' && $_SESSION['role'] != 'admin') {
        $_SESSION['error'] = "Access denied.";
        closeConn($conn);
        header("Location: index.php?page=manage_borrows");
        exit();
    }

    $borrow_id     = (int)$_POST['borrow_id'];
    $reject_reason = htmlspecialchars(trim($_POST['reject_reason']));

    $sql  = "UPDATE borrow_records SET status='rejected', reject_reason=? WHERE id=?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "si", $reject_reason, $borrow_id);
    $done = mysqli_stmt_execute($stmt);

    $_SESSION['msg'] = $done ? "Borrow request rejected." : "Failed to reject request.";
    closeConn($conn);
    header("Location: index.php?page=manage_borrows");
    exit();
}
closeConn($conn);
header("Location: index.php?page=librarian_dashboard");
exit();
