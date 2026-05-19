<?php
// ============================================================
// views/librarian/dashboard.php
// Librarian dashboard - pending borrows, recent activity
// ============================================================

require_once 'config/Connect.php';
require_once 'config/Close.php';
require_once 'models/BorrowModel.php';
require_once 'models/BookModel.php';
require_once 'models/ReviewModel.php';

$conn      = connect();
$branch_id = $_SESSION['branch_id'];

$all_borrows  = getBorrowsByBranch($conn, $branch_id);
$pending      = getPendingBorrows($conn, $branch_id);
$all_books    = getAllBooks($conn);
$all_fines    = getAllFines($conn);

// Count stats
$active  = 0;
$overdue = 0;
foreach ($all_borrows as $b) {
    if ($b['status'] == 'approved') $active++;
    if ($b['status'] == 'overdue')  $overdue++;
}

$unpaid_fines = 0;
foreach ($all_fines as $f) {
    if ($f['status'] == 'unpaid') $unpaid_fines++;
}

closeConn($conn);
require_once 'views/header.php';
?>

<h1 class="page-title">&#128218; Librarian Dashboard</h1>
<p style="color:#666; margin-bottom:20px;">Welcome, <strong><?php echo htmlspecialchars($_SESSION['user_name']); ?></strong></p>

<div class="card-row">
    <div class="card">
        <h3>Total Books</h3>
        <div class="stat-number"><?php echo count($all_books); ?></div>
    </div>
    <div class="card orange">
        <h3>Pending Requests</h3>
        <div class="stat-number"><?php echo count($pending); ?></div>
    </div>
    <div class="card green">
        <h3>Active Borrows</h3>
        <div class="stat-number"><?php echo $active; ?></div>
    </div>
    <div class="card red">
        <h3>Unpaid Fines</h3>
        <div class="stat-number"><?php echo $unpaid_fines; ?></div>
    </div>
</div>

<!-- Pending Requests Table -->
<div class="section-box">
    <div class="flex-between">
        <h3>&#9203; Pending Borrow Requests</h3>
        <a href="index.php?page=manage_borrows" class="btn btn-primary btn-sm">Manage All</a>
    </div>

    <?php if (empty($pending)): ?>
        <p style="color:#888;">No pending requests at the moment.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Member</th>
                    <th>Book Title</th>
                    <th>Requested On</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pending as $p): ?>
                <tr>
                    <td><?php echo htmlspecialchars($p['full_name']); ?></td>
                    <td><?php echo htmlspecialchars($p['title']); ?></td>
                    <td><?php echo date('d M Y', strtotime($p['created_at'])); ?></td>
                    <td>
                        <a href="index.php?page=do_borrow&action=approve&id=<?php echo $p['id']; ?>"
                           class="btn btn-success btn-sm"
                           onclick="return confirm('Approve this borrow request?')">Approve</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<div style="display:flex; gap:12px; flex-wrap:wrap;">
    <a href="index.php?page=add_book"       class="btn btn-primary">+ Add Book</a>
    <a href="index.php?page=book_list"      class="btn btn-info">&#128218; All Books</a>
    <a href="index.php?page=manage_borrows" class="btn btn-warning">&#128203; Manage Borrows</a>
    <a href="index.php?page=manage_fines"   class="btn btn-danger">&#128181; Manage Fines</a>
    <a href="index.php?page=manage_genres"  class="btn btn-secondary">&#127991; Genres</a>
    <a href="index.php?page=announcements"  class="btn btn-success">&#128226; Announcements</a>
</div>

<?php require_once 'views/footer.php'; ?>
