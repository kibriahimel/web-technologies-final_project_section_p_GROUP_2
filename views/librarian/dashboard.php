<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Admin Dashboard</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; background: #f0f2f5; }
        nav { background: #4f46e5; color: #fff; padding: 14px 24px; display: flex; justify-content: space-between; align-items: center; }
        nav a { color: #fff; text-decoration: none; margin-left: 20px; font-size: 14px; }
        nav a:hover { text-decoration: underline; }
        .container { max-width: 1100px; margin: 30px auto; padding: 0 20px; }
        h2 { margin-bottom: 24px; color: #333; }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; margin-bottom: 30px; }
        .stat-card { background: #fff; padding: 24px; border-radius: 8px; box-shadow: 0 1px 6px rgba(0,0,0,0.08); text-align: center; }
        .stat-card .number { font-size: 36px; font-weight: bold; color: #4f46e5; }
        .stat-card .label { font-size: 13px; color: #666; margin-top: 6px; }
        .stat-card.danger .number { color: #dc2626; }
        .stat-card.warning .number { color: #d97706; }
        .msg { background: #d1fae5; color: #065f46; padding: 10px 16px; border-radius: 6px; margin-bottom: 20px; }
    </style>
</head>
<body>
<?php
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../../controllers/LoginPageController.php'); die();
}
?>
<nav>
    <strong>📚 Library Admin</strong>
    <div>
        <a href="../../controllers/AdminDashboardController.php">Dashboard</a>
        <a href="../../controllers/AdminUsersController.php">Users</a>
        <a href="../../controllers/AdminBranchesController.php">Branches</a>
        <a href="../../controllers/AdminSettingsController.php">Settings</a>
        <a href="../../controllers/AdminAuditController.php">Audit Log</a>
        <a href="../../controllers/LogoutController.php">Logout</a>
    </div>
</nav>

<div class="container">
    <h2>Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?></h2>

    <?php if (!empty($_SESSION['msg'])): ?>
        <div class="msg"><?= $_SESSION['msg']; $_SESSION['msg'] = ''; ?></div>
    <?php endif; ?>

    <div class="stats">
        <div class="stat-card">
            <div class="number"><?= $_SESSION['total_members'] ?? 0 ?></div>
            <div class="label">Total Members</div>
        </div>
        <div class="stat-card">
            <div class="number"><?= $_SESSION['total_books'] ?? 0 ?></div>
            <div class="label">Total Books</div>
        </div>
        <div class="stat-card">
            <div class="number"><?= $_SESSION['active_loans'] ?? 0 ?></div>
            <div class="label">Active Loans</div>
        </div>
        <div class="stat-card danger">
            <div class="number"><?= $_SESSION['overdue_loans'] ?? 0 ?></div>
            <div class="label">Overdue Loans</div>
        </div>
        <div class="stat-card warning">
            <div class="number">৳<?= number_format($_SESSION['fines_outstanding'] ?? 0, 2) ?></div>
            <div class="label">Fines Outstanding</div>
        </div>
    </div>
</div>
</body>
</html>
