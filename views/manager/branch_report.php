<?php
// ============================================================
// views/manager/branch_report.php
// Shows borrow, fine, and book stats for manager's branch
// ============================================================

require_once 'config/Connect.php';
require_once 'config/Close.php';
require_once 'models/BorrowModel.php';
require_once 'models/ReviewModel.php';
require_once 'models/BookModel.php';
require_once 'models/BranchModel.php';

$conn      = connect();
$branch_id = $_SESSION['branch_id'];

$branch   = getBranchById($conn, $branch_id);
$borrows  = getBorrowsByBranch($conn, $branch_id);
$books    = getBooksByBranch($conn, $branch_id);
$all_fines = getAllFines($conn);

// Calculate stats
$total_borrows = count($borrows);
$pending = $active = $returned = $overdue = 0;
foreach ($borrows as $b) {
    if ($b['status'] == 'pending')  $pending++;
    if ($b['status'] == 'approved') $active++;
    if ($b['status'] == 'returned') $returned++;
    if ($b['status'] == 'overdue')  $overdue++;
}

$total_fines_unpaid = 0;
$total_fines_paid   = 0;
foreach ($all_fines as $f) {
    if ($f['status'] == 'unpaid') $total_fines_unpaid += $f['amount'];
    else                           $total_fines_paid   += $f['amount'];
}

$total_books     = count($books);
$available_books = 0;
foreach ($books as $bk) {
    $available_books += $bk['available_copies'];
}

closeConn($conn);
require_once 'views/header.php';
?>

<h1 class="page-title"> Branch Report</h1>
<p style="color:#666; margin-bottom:24px;">Report for: <strong><?php echo htmlspecialchars($branch['branch_name'] ?? 'Your Branch'); ?></strong></p>

<!-- Summary Cards -->
<div class="card-row">
    <div class="card">
        <h3>Total Borrows</h3>
        <div class="stat-number"><?php echo $total_borrows; ?></div>
    </div>
    <div class="card orange">
        <h3>Pending</h3>
        <div class="stat-number"><?php echo $pending; ?></div>
    </div>
    <div class="card green">
        <h3>Active</h3>
        <div class="stat-number"><?php echo $active; ?></div>
    </div>
    <div class="card red">
        <h3>Overdue</h3>
        <div class="stat-number"><?php echo $overdue; ?></div>
    </div>
</div>

<div class="card-row">
    <div class="card">
        <h3>Books in Branch</h3>
        <div class="stat-number"><?php echo $total_books; ?></div>
    </div>
    <div class="card green">
        <h3>Available Copies</h3>
        <div class="stat-number"><?php echo $available_books; ?></div>
    </div>
    <div class="card red">
        <h3>Unpaid Fines (Tk)</h3>
        <div class="stat-number"><?php echo number_format($total_fines_unpaid, 0); ?></div>
    </div>
    <div class="card purple">
        <h3>Fines Collected (Tk)</h3>
        <div class="stat-number"><?php echo number_format($total_fines_paid, 0); ?></div>
    </div>
</div>

<!-- Full Borrow Table -->
<div class="section-box">
    <h3>All Borrow Records for This Branch</h3>
    <?php if (empty($borrows)): ?>
        <p style="color:#888;">No borrow records found.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr><th>#</th><th>Member</th><th>Book</th><th>Borrow Date</th><th>Due Date</th><th>Return Date</th><th>Status</th></tr>
            </thead>
            <tbody>
                <?php $i = 1; foreach ($borrows as $b): ?>
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td><?php echo htmlspecialchars($b['full_name']); ?></td>
                    <td><?php echo htmlspecialchars($b['title']); ?></td>
                    <td><?php echo $b['borrow_date'] ? date('d M Y', strtotime($b['borrow_date'])) : '-'; ?></td>
                    <td><?php echo $b['due_date'] ? date('d M Y', strtotime($b['due_date'])) : '-'; ?></td>
                    <td><?php echo $b['return_date'] ? date('d M Y', strtotime($b['return_date'])) : '-'; ?></td>
                    <td><span class="badge badge-<?php echo $b['status']; ?>"><?php echo strtoupper($b['status']); ?></span></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<!-- Inventory Table -->
<div class="section-box">
    <h3>Book Inventory at This Branch</h3>
    <?php if (empty($books)): ?>
        <p style="color:#888;">No books in this branch inventory.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr><th>#</th><th>Title</th><th>Author</th><th>Genre</th><th>Available</th><th>Total</th></tr>
            </thead>
            <tbody>
                <?php $i = 1; foreach ($books as $bk): ?>
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td><?php echo htmlspecialchars($bk['title']); ?></td>
                    <td><?php echo htmlspecialchars($bk['author']); ?></td>
                    <td><?php echo htmlspecialchars($bk['genre_name'] ?? '-'); ?></td>
                    <td style="color:<?php echo $bk['available_copies'] > 0 ? '#28a745' : '#dc3545'; ?>; font-weight:bold;">
                        <?php echo $bk['available_copies']; ?>
                    </td>
                    <td><?php echo $bk['total_copies']; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php require_once 'views/footer.php'; ?>
