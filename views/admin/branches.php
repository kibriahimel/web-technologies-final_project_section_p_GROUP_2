<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Admin — Branches</title>
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
        .badge { padding: 3px 10px; border-radius: 12px; font-size: 12px; font-weight: bold; }
        .badge.active   { background: #d1fae5; color: #065f46; }
        .badge.inactive { background: #fee2e2; color: #991b1b; }
        .msg   { background: #d1fae5; color: #065f46; padding: 10px 16px; border-radius: 6px; margin-bottom: 16px; }
        .error { background: #fee2e2; color: #b91c1c; padding: 10px 16px; border-radius: 6px; margin-bottom: 16px; }
    </style>
</head>
<body>
<?php
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../../controllers/LoginPageController.php'); die();
}
$branches = $_SESSION['branches'] ?? [];
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
    <h2>All Branches</h2>

    <?php if (!empty($_SESSION['msg'])): ?>
        <div class="msg"><?= $_SESSION['msg']; $_SESSION['msg'] = ''; ?></div>
    <?php endif; ?>
    <?php if (!empty($_SESSION['error'])): ?>
        <div class="error"><?= $_SESSION['error']; $_SESSION['error'] = ''; ?></div>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Branch Name</th>
                <th>Address</th>
                <th>City</th>
                <th>Phone</th>
                <th>Manager</th>
                <th>Librarians</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($branches)): ?>
            <tr><td colspan="8" style="text-align:center;color:#999;padding:20px">No branches found</td></tr>
        <?php else: ?>
            <?php foreach ($branches as $i => $b): ?>
            <tr>
                <td><?= $i + 1 ?></td>
                <td><?= htmlspecialchars($b['name']) ?></td>
                <td><?= htmlspecialchars($b['address']) ?></td>
                <td><?= htmlspecialchars($b['city']) ?></td>
                <td><?= htmlspecialchars($b['phone']) ?></td>
                <td><?= htmlspecialchars($b['manager_name'] ?? '—') ?></td>
                <td><?= $b['librarian_count'] ?></td>
                <td>
                    <?php if ($b['is_active']): ?>
                        <span class="badge active">Active</span>
                    <?php else: ?>
                        <span class="badge inactive">Inactive</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>
