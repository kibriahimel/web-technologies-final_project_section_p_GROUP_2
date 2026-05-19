<?php
// ============================================================
// views/manager/dashboard.php
// Branch Manager dashboard
// ============================================================

require_once 'config/Connect.php';
require_once 'config/Close.php';
require_once 'models/BranchModel.php';
require_once 'models/BorrowModel.php';
require_once 'models/BookModel.php';

$conn      = connect();
$branch_id = $_SESSION['branch_id'];

$branch       = getBranchById($conn, $branch_id);
$all_branches = getAllBranches($conn);
$borrows      = getBorrowsByBranch($conn, $branch_id);
$books        = getBooksByBranch($conn, $branch_id);
$policy       = getBranchPolicy($conn, $branch_id);

$active_borrows = 0;
$overdue        = 0;
foreach ($borrows as $b) {
    if ($b['status'] == 'approved') $active_borrows++;
    if ($b['status'] == 'overdue')  $overdue++;
}

closeConn($conn);
require_once 'views/header.php';
?>

<h1 class="page-title">&#127970; Branch Manager Dashboard</h1>
<p style="color:#666; margin-bottom:20px;">Managing: <strong><?php echo htmlspecialchars($branch['branch_name'] ?? 'Unassigned Branch'); ?></strong></p>

<div class="card-row">
    <div class="card">
        <h3>Books in Branch</h3>
        <div class="stat-number"><?php echo count($books); ?></div>
    </div>
    <div class="card green">
        <h3>Active Borrows</h3>
        <div class="stat-number"><?php echo $active_borrows; ?></div>
    </div>
    <div class="card red">
        <h3>Overdue</h3>
        <div class="stat-number"><?php echo $overdue; ?></div>
    </div>
    <div class="card orange">
        <h3>Total Branches</h3>
        <div class="stat-number"><?php echo count($all_branches); ?></div>
    </div>
</div>

<!-- Branch Policy Summary -->
<?php if ($policy): ?>
<div class="section-box">
    <div class="flex-between">
        <h3>&#128220; Branch Policy</h3>
        <a href="index.php?page=branch_policy&id=<?php echo $branch_id; ?>" class="btn btn-warning btn-sm">Edit Policy</a>
    </div>
    <table style="max-width:400px;">
        <tr><td><strong>Max Borrow Days:</strong></td><td><?php echo $policy['max_borrow_days']; ?> days</td></tr>
        <tr><td><strong>Max Books/Member:</strong></td><td><?php echo $policy['max_books_per_member']; ?> books</td></tr>
        <tr><td><strong>Fine Per Day:</strong></td><td>Tk <?php echo $policy['fine_per_day']; ?></td></tr>
        <tr><td><strong>Reservation Days:</strong></td><td><?php echo $policy['reservation_days']; ?> days</td></tr>
    </table>
</div>
<?php endif; ?>

<!-- Recent Borrow Activity -->
<div class="section-box">
    <div class="flex-between">
        <h3>&#128218; Recent Borrow Activity</h3>
        <a href="index.php?page=branch_report" class="btn btn-info btn-sm">Full Report</a>
    </div>
    <?php if (empty($borrows)): ?>
        <p style="color:#888;">No borrow records for this branch.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr><th>Member</th><th>Book</th><th>Due Date</th><th>Status</th></tr>
            </thead>
            <tbody>
                <?php $count = 0; foreach ($borrows as $b): if ($count >= 5) break; $count++; ?>
                <tr>
                    <td><?php echo htmlspecialchars($b['full_name']); ?></td>
                    <td><?php echo htmlspecialchars($b['title']); ?></td>
                    <td><?php echo $b['due_date'] ? date('d M Y', strtotime($b['due_date'])) : 'Pending'; ?></td>
                    <td><span class="badge badge-<?php echo $b['status']; ?>"><?php echo strtoupper($b['status']); ?></span></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<div style="display:flex; gap:12px; flex-wrap:wrap;">
    <a href="index.php?page=manage_branches"      class="btn btn-primary">&#127970; Manage Branches</a>
    <a href="index.php?page=manage_librarians"    class="btn btn-secondary">&#128101; Librarians</a>
    <a href="index.php?page=branch_policy&id=<?php echo $branch_id; ?>" class="btn btn-warning">&#128220; Branch Policy</a>
    <a href="index.php?page=cross_branch_report"  class="btn btn-info">&#128202; Cross-Branch Report</a>
    <a href="index.php?page=most_borrowed"        class="btn btn-secondary">&#128218; Most Borrowed</a>
    <a href="index.php?page=member_activity"      class="btn btn-primary">&#128101; Member Activity</a>
    <a href="index.php?page=overdue_alerts"       class="btn btn-danger">&#9888; Overdue Alerts</a>
    <a href="index.php?page=monthly_report"       class="btn btn-info">&#128197; Monthly Report</a>
    <a href="index.php?page=librarian_activity"   class="btn btn-secondary">&#128203; Librarian Activity</a>
    <a href="index.php?page=manager_announcements" class="btn btn-primary">&#128226; Announcements</a>
    <a href="index.php?page=transfer_requests"    class="btn btn-secondary">&#128661; Transfers</a>
</div>

<?php require_once 'views/footer.php'; ?>
