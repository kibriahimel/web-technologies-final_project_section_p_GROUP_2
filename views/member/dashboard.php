<?php
// ============================================================
// views/member/dashboard.php
// Member dashboard - shows borrow/fine summary
// ============================================================

require_once 'config/Connect.php';
require_once 'config/Close.php';
require_once 'models/BorrowModel.php';
require_once 'models/ReviewModel.php';
require_once 'models/BranchModel.php';

$conn         = connect();
$user_id      = $_SESSION['user'];

// Get member's borrow records
$my_borrows   = getBorrowsByUser($conn, $user_id);
$my_fines     = getFinesByUser($conn, $user_id);
$my_reading   = getReadingList($conn, $user_id);
$announcements = getAnnouncements($conn);

// Count summaries
$active_borrows = 0;
$overdue        = 0;
foreach ($my_borrows as $b) {
    if ($b['status'] == 'approved') $active_borrows++;
    if ($b['status'] == 'overdue')  $overdue++;
}

$unpaid_fines = 0;
foreach ($my_fines as $f) {
    if ($f['status'] == 'unpaid') $unpaid_fines += $f['amount'];
}

closeConn($conn);

require_once 'views/header.php';
?>

<h1 class="page-title"> My Dashboard</h1>
<p style="color:#666; margin-bottom:20px;">Welcome back, <strong><?php echo htmlspecialchars($_SESSION['user_name']); ?></strong>!</p>

<!-- Stats Cards -->
<div class="card-row">
    <div class="card">
        <h3>Active Borrows</h3>
        <div class="stat-number"><?php echo $active_borrows; ?></div>
    </div>
    <div class="card orange">
        <h3>Overdue Books</h3>
        <div class="stat-number"><?php echo $overdue; ?></div>
    </div>
    <div class="card red">
        <h3>Unpaid Fines (Tk)</h3>
        <div class="stat-number"><?php echo number_format($unpaid_fines, 2); ?></div>
    </div>
    <div class="card green">
        <h3>Reading List</h3>
        <div class="stat-number"><?php echo count($my_reading); ?></div>
    </div>
</div>

<!-- Recent Borrow Activity -->
<div class="section-box">
    <div class="flex-between">
        <h3>&#128218; Recent Borrow Activity</h3>
        <a href="index.php?page=my_borrows" class="btn btn-primary btn-sm">View All</a>
    </div>

    <?php if (empty($my_borrows)): ?>
        <p style="color:#888;">You haven't borrowed any books yet. <a href="index.php?page=browse_books">Browse books</a></p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Book Title</th>
                    <th>Branch</th>
                    <th>Due Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $count = 0;
                foreach ($my_borrows as $borrow):
                    if ($count >= 5) break;
                    $count++;
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($borrow['title']); ?></td>
                    <td><?php echo htmlspecialchars($borrow['branch_name']); ?></td>
                    <td><?php echo $borrow['due_date'] ? date('d M Y', strtotime($borrow['due_date'])) : '-'; ?></td>
                    <td><span class="badge badge-<?php echo $borrow['status']; ?>"><?php echo strtoupper($borrow['status']); ?></span></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<!-- Announcements -->
<?php if (!empty($announcements)): ?>
<div class="section-box">
    <h3>&#128226; Announcements</h3>
    <?php foreach (array_slice($announcements, 0, 3) as $ann): ?>
        <div style="border-bottom:1px solid #eee; padding:8px 0;">
            <strong><?php echo htmlspecialchars($ann['title']); ?></strong>
            <p style="font-size:13px; color:#666; margin-top:3px;"><?php echo htmlspecialchars($ann['content']); ?></p>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<div style="display:flex; gap:12px; flex-wrap:wrap;">
    <a href="index.php?page=browse_books" class="btn btn-primary">&#128270; Browse Books</a>
    <a href="index.php?page=my_reservations" class="btn btn-warning">&#128203; My Reservations</a>
    <a href="index.php?page=reading_list" class="btn btn-info">&#128221; Reading List</a>
    <a href="index.php?page=my_fines" class="btn btn-danger">&#128181; My Fines</a>
</div>

<?php require_once 'views/footer.php'; ?>
