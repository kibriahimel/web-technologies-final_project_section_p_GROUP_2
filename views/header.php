<!-- ============================================================
     views/header.php
     Shared navigation bar included in all views
     ============================================================ -->
<?php
// session_start() is already called in index.php
// This file is included by every view
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Management System</title>
    <link rel="stylesheet" href="views/css/style.css">
</head>
<body>

<nav class="navbar">
    <div class="logo"> <span>Book Stories</span></div>
    <ul>
        <li><a href="index.php?page=home">Home</a></li>
        <li><a href="index.php?page=browse_books">Browse Books</a></li>
        <li><a href="index.php?page=announcements">Announcements</a></li>

        <?php if (isset($_SESSION['user'])): ?>

            <?php if ($_SESSION['role'] == 'member'): ?>
                <li><a href="index.php?page=member_dashboard">Dashboard</a></li>
                <li><a href="index.php?page=my_borrows">My Borrows</a></li>
                <li><a href="index.php?page=my_reservations">Reservations</a></li>
                <li><a href="index.php?page=reading_list">Reading List</a></li>
                <li><a href="index.php?page=my_fines">Fines</a></li>
            <?php endif; ?>

            <?php if ($_SESSION['role'] == 'librarian'): ?>
                <li><a href="index.php?page=librarian_dashboard">Dashboard</a></li>
                <li><a href="index.php?page=book_list">Books</a></li>
                <li><a href="index.php?page=manage_borrows">Borrows</a></li>
                <li><a href="index.php?page=manage_fines">Fines</a></li>
                <li><a href="index.php?page=manage_genres">Genres</a></li>
            <?php endif; ?>

            <?php if ($_SESSION['role'] == 'manager'): ?>
                <li><a href="index.php?page=manager_dashboard">Dashboard</a></li>
                <li><a href="index.php?page=manage_branches">Branches</a></li>
                <li><a href="index.php?page=manage_librarians">Librarians</a></li>
                <li><a href="index.php?page=cross_branch_report">Reports</a></li>
                <li><a href="index.php?page=most_borrowed">Top Books</a></li>
                <li><a href="index.php?page=member_activity">Members</a></li>
                <li><a href="index.php?page=overdue_alerts">Overdue</a></li>
                <li><a href="index.php?page=monthly_report">Monthly</a></li>
                <li><a href="index.php?page=librarian_activity">Librarians Report</a></li>
                <li><a href="index.php?page=manager_announcements">Announcements</a></li>
                <li><a href="index.php?page=transfer_requests">Transfers</a></li>
            <?php endif; ?>

            <?php if ($_SESSION['role'] == 'admin'): ?>
                <li><a href="index.php?page=admin_dashboard">Dashboard</a></li>
                <li><a href="index.php?page=manage_users">Users</a></li>
                <li><a href="index.php?page=manage_branches">Branches</a></li>
                <li><a href="index.php?page=book_list">Books</a></li>
                <li><a href="index.php?page=admin_reports">Reports</a></li>
            <?php endif; ?>

            <li><a href="index.php?page=profile"> <?php echo htmlspecialchars($_SESSION['user_name']); ?></a></li>
            <li><a href="index.php?page=do_logout" style="color:#f0a500;">Logout</a></li>

        <?php else: ?>
            <li><a href="index.php?page=login">Login</a></li>
            <li><a href="index.php?page=register">Register</a></li>
        <?php endif; ?>
    </ul>
</nav>

<div class="container">
<!-- Flash messages shown on every page -->
<?php if (isset($_SESSION['msg'])): ?>
    <div class="alert-success"><?php echo htmlspecialchars($_SESSION['msg']); ?></div>
    <?php unset($_SESSION['msg']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert-error"><?php echo htmlspecialchars($_SESSION['error']); ?></div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>
