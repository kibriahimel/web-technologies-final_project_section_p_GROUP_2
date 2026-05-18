<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Admin — Audit Log</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; background: #f0f2f5; }
        nav { background: #4f46e5; color: #fff; padding: 14px 24px; display: flex; justify-content: space-between; align-items: center; }
        nav a { color: #fff; text-decoration: none; margin-left: 20px; font-size: 14px; }
        nav a:hover { text-decoration: underline; }
        .container { max-width: 1100px; margin: 30px auto; padding: 0 20px; }
        h2 { margin-bottom: 20px; color: #333; }
        table { width: 100%; background: #fff; border-radius: 8px; box-shadow: 0 1px 6px rgba(0,0,0,0.08); border-collapse: collapse; }
        th { background: #4f46e5; color: #fff; padding: 12px 14px; text-align: left; font-size: 13px; }
        td { padding: 11px 14px; border-bottom: 1px solid #f0f0f0; font-size: 14px; color: #333; }
        tr:last-child td { border-bottom: none; }
    </style>
</head>
<body>
<?php
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../../controllers/LoginPageController.php'); die();
}
$logs = $_SESSION['audit_logs'] ?? [];
?>
<nav>
    <strong> Library Admin</strong>
    <div>
    <a href="../../controllers/AdminDashboardController.php">Dashboard</a>
    <a href="../../controllers/AdminUsersController.php">Users</a>
    <a href="../../controllers/AdminBranchesController.php">Branches</a>
    <a href="../../controllers/AdminTransfersController.php">Transfers</a>
    <a href="../../controllers/AdminSettingsController.php">Settings</a>
    <a href="../../controllers/AdminAuditController.php">Audit Log</a>
    <a href="../../controllers/LogoutController.php">Logout</a>
</div>
</nav>

<div class="container">
    <h2>Audit Log</h2>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>User</th>
                <th>Action</th>
                <th>Details</th>
                <th>Date &amp; Time</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($logs)): ?>
            <tr><td colspan="5" style="text-align:center;color:#999;padding:20px">No logs found</td></tr>
        <?php else: ?>
            <?php foreach ($logs as $i => $log): ?>
            <tr>
                <td><?= $i + 1 ?></td>
                <td><?= htmlspecialchars($log['user_name'] ?? 'System') ?></td>
                <td><?= htmlspecialchars($log['action']) ?></td>
                <td><?= htmlspecialchars($log['details'] ?? '—') ?></td>
                <td><?= $log['created_at'] ?></td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>
