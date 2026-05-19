<?php
// ============================================================
// views/admin/dashboard.php
// Admin dashboard – full platform overview
// ============================================================

require_once 'config/Connect.php';
require_once 'config/Close.php';
require_once 'models/UserModel.php';
require_once 'models/BookModel.php';
require_once 'models/BorrowModel.php';
require_once 'models/ReviewModel.php';
require_once 'models/BranchModel.php';

$conn = connect();

// Platform-wide counts
$sql_users    = "SELECT COUNT(*) AS total FROM users";
$sql_books    = "SELECT COUNT(*) AS total FROM books";
$sql_borrows  = "SELECT COUNT(*) AS total FROM borrow_records WHERE status='approved'";
$sql_fines    = "SELECT SUM(amount) AS total FROM fines WHERE status='unpaid'";
$sql_branches = "SELECT COUNT(*) AS total FROM branches";
$sql_members  = "SELECT COUNT(*) AS total FROM users WHERE role='member'";

$total_users    = mysqli_fetch_assoc(mysqli_query($conn, $sql_users))['total'];
$total_books    = mysqli_fetch_assoc(mysqli_query($conn, $sql_books))['total'];
$active_borrows = mysqli_fetch_assoc(mysqli_query($conn, $sql_borrows))['total'];
$unpaid_fines   = mysqli_fetch_assoc(mysqli_query($conn, $sql_fines))['total'] ?? 0;
$total_branches = mysqli_fetch_assoc(mysqli_query($conn, $sql_branches))['total'];
$total_members  = mysqli_fetch_assoc(mysqli_query($conn, $sql_members))['total'];

// Recent registrations
$sql_recent = "SELECT id, full_name, email, role, created_at FROM users ORDER BY created_at DESC LIMIT 5";
$recent_users = array();
$result = mysqli_query($conn, $sql_recent);
while ($row = mysqli_fetch_assoc($result)) {
    $recent_users[] = $row;
}

// Recent borrow records
$recent_borrows = getAllBorrowRecords($conn);
$recent_borrows = array_slice($recent_borrows, 0, 5);

closeConn($conn);
require_once 'views/header.php';
?>

<h1 class="page-title">&#9881;&#65039; Admin Dashboard</h1>
<p style="color:#666; margin-bottom:20px;">Welcome, <strong><?php echo htmlspecialchars($_SESSION['user_name']); ?></strong> &mdash; Full Platform Overview</p>

<!-- Platform Stats -->
<div class="card-row">
    <div class="card">
        <h3>Total Users</h3>
        <div class="stat-number"><?php echo $total_users; ?></div>
    </div>
    <div class="card orange">
        <h3>Total Members</h3>
        <div class="stat-number"><?php echo $total_members; ?></div>
    </div>
    <div class="card green">
        <h3>Total Books</h3>
        <div class="stat-number"><?php echo $total_books; ?></div>
    </div>
    <div class="card purple">
        <h3>Branches</h3>
        <div class="stat-number"><?php echo $total_branches; ?></div>
    </div>
</div>

<div class="card-row">
    <div class="card">
        <h3>Active Borrows</h3>
        <div class="stat-number"><?php echo $active_borrows; ?></div>
    </div>
    <div class="card red">
        <h3>Unpaid Fines (Tk)</h3>
        <div class="stat-number"><?php echo number_format($unpaid_fines, 0); ?></div>
    </div>
</div>

<!-- Quick Links -->
<div style="display:flex; gap:12px; flex-wrap:wrap; margin-bottom:28px;">
    <a href="index.php?page=manage_users"        class="btn btn-primary">&#128100; Manage Users</a>
    <a href="index.php?page=manage_branches"     class="btn btn-info">&#127970; Manage Branches</a>
    <a href="index.php?page=book_list"           class="btn btn-success">&#128218; Manage Books</a>
    <a href="index.php?page=manage_borrows"      class="btn btn-warning">&#128203; Manage Borrows</a>
    <a href="index.php?page=admin_reports"       class="btn btn-secondary">&#128202; Reports</a>
    <a href="index.php?page=admin_announcements" class="btn btn-danger">&#128226; Announcements</a>
</div>

<!-- Recent Users -->
<div class="section-box">
    <div class="flex-between">
        <h3>&#128100; Recently Registered Users</h3>
        <a href="index.php?page=manage_users" class="btn btn-primary btn-sm">View All</a>
    </div>
    <table>
        <thead>
            <tr><th>#</th><th>Name</th><th>Email</th><th>Role</th><th>Joined</th></tr>
        </thead>
        <tbody>
            <?php $i = 1; foreach ($recent_users as $u): ?>
            <tr>
                <td><?php echo $i++; ?></td>
                <td><?php echo htmlspecialchars($u['full_name']); ?></td>
                <td><?php echo htmlspecialchars($u['email']); ?></td>
                <td><span class="badge badge-active"><?php echo strtoupper($u['role']); ?></span></td>
                <td><?php echo date('d M Y', strtotime($u['created_at'])); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Recent Borrows -->
<div class="section-box">
    <div class="flex-between">
        <h3>&#128218; Recent Borrow Activity</h3>
        <a href="index.php?page=admin_reports" class="btn btn-info btn-sm">Full Report</a>
    </div>
    <table>
        <thead>
            <tr><th>#</th><th>Member</th><th>Book</th><th>Branch</th><th>Due Date</th><th>Status</th></tr>
        </thead>
        <tbody>
            <?php $i = 1; foreach ($recent_borrows as $b): ?>
            <tr>
                <td><?php echo $i++; ?></td>
                <td><?php echo htmlspecialchars($b['full_name']); ?></td>
                <td><?php echo htmlspecialchars($b['title']); ?></td>
                <td><?php echo htmlspecialchars($b['branch_name']); ?></td>
                <td><?php echo $b['due_date'] ? date('d M Y', strtotime($b['due_date'])) : 'Pending'; ?></td>
                <td><span class="badge badge-<?php echo $b['status']; ?>"><?php echo strtoupper($b['status']); ?></span></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once 'views/footer.php'; ?>
