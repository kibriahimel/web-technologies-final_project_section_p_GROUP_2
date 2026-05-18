<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Admin — Reports</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; background: #f0f2f5; }
        nav { background: #4f46e5; color: #fff; padding: 14px 24px; display: flex; justify-content: space-between; align-items: center; }
        nav a { color: #fff; text-decoration: none; margin-left: 20px; font-size: 14px; }
        nav a:hover { text-decoration: underline; }
        .container { max-width: 1100px; margin: 30px auto; padding: 0 20px; }
        h2 { margin-bottom: 20px; color: #333; }
        h3 { margin-bottom: 12px; color: #4f46e5; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 24px; }
        .card { background: #fff; padding: 24px; border-radius: 8px; box-shadow: 0 1px 6px rgba(0,0,0,0.08); }
        table { width: 100%; border-collapse: collapse; }
        th { background: #4f46e5; color: #fff; padding: 10px 12px; text-align: left; font-size: 13px; }
        td { padding: 9px 12px; border-bottom: 1px solid #f0f0f0; font-size: 14px; }
        tr:last-child td { border-bottom: none; }
        .export-btn { display: inline-block; margin-bottom: 20px; padding: 10px 24px; background: #059669; color: #fff; border-radius: 6px; text-decoration: none; font-size: 14px; }
        .export-btn:hover { background: #047857; }
    </style>
</head>
<body>
<?php
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../../controllers/LoginPageController.php'); die();
}
$borrows  = $_SESSION['report_borrows_per_month'] ?? [];
$fines    = $_SESSION['report_fines_per_month']   ?? [];
$branches = $_SESSION['report_active_branches']    ?? [];
$genres   = $_SESSION['report_top_genres']         ?? [];
$growth   = $_SESSION['report_member_growth']      ?? [];
?>
<nav>
    <strong> Library Admin</strong>
    <div>
        <a href="../../controllers/AdminDashboardController.php">Dashboard</a>
        <a href="../../controllers/AdminUsersController.php">Users</a>
        <a href="../../controllers/AdminBranchesController.php">Branches</a>
        <a href="../../controllers/AdminTransfersController.php">Transfers</a>
        <a href="../../controllers/AdminReportsController.php">Reports</a>
        <a href="../../controllers/AdminAnnouncementsController.php">Announcements</a>
        <a href="../../controllers/AdminSettingsController.php">Settings</a>
        <a href="../../controllers/AdminAuditController.php">Audit Log</a>
        <a href="../../controllers/LogoutController.php">Logout</a>
    </div>
</nav>

<div class="container">
    <h2>Platform-Wide Reports</h2>

    <a class="export-btn" href="../../controllers/AdminExportController.php">⬇ Export as HTML</a>

    <div class="grid">

        <div class="card">
            <h3>Borrows Per Month</h3>
            <table>
                <thead><tr><th>Month</th><th>Total</th></tr></thead>
                <tbody>
                <?php if (empty($borrows)): ?>
                    <tr><td colspan="2" style="color:#999">No data</td></tr>
                <?php else: ?>
                    <?php foreach ($borrows as $r): ?>
                        <tr><td><?= $r['month'] ?></td><td><?= $r['total'] ?></td></tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="card">
            <h3>Fines Collected Per Month</h3>
            <table>
                <thead><tr><th>Month</th><th>Amount (৳)</th></tr></thead>
                <tbody>
                <?php if (empty($fines)): ?>
                    <tr><td colspan="2" style="color:#999">No data</td></tr>
                <?php else: ?>
                    <?php foreach ($fines as $r): ?>
                        <tr><td><?= $r['month'] ?></td><td>৳<?= number_format($r['total'], 2) ?></td></tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="card">
            <h3>Most Active Branches</h3>
            <table>
                <thead><tr><th>Branch</th><th>Borrows</th></tr></thead>
                <tbody>
                <?php if (empty($branches)): ?>
                    <tr><td colspan="2" style="color:#999">No data</td></tr>
                <?php else: ?>
                    <?php foreach ($branches as $r): ?>
                        <tr><td><?= htmlspecialchars($r['branch_name']) ?></td><td><?= $r['total_borrows'] ?></td></tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="card">
            <h3>Most Borrowed Genres</h3>
            <table>
                <thead><tr><th>Genre</th><th>Borrows</th></tr></thead>
                <tbody>
                <?php if (empty($genres)): ?>
                    <tr><td colspan="2" style="color:#999">No data</td></tr>
                <?php else: ?>
                    <?php foreach ($genres as $r): ?>
                        <tr><td><?= htmlspecialchars($r['genre_name']) ?></td><td><?= $r['total_borrows'] ?></td></tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="card">
            <h3>Member Growth Per Month</h3>
            <table>
                <thead><tr><th>Month</th><th>New Members</th></tr></thead>
                <tbody>
                <?php if (empty($growth)): ?>
                    <tr><td colspan="2" style="color:#999">No data</td></tr>
                <?php else: ?>
                    <?php foreach ($growth as $r): ?>
                        <tr><td><?= $r['month'] ?></td><td><?= $r['total'] ?></td></tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>
</body>
</html>
