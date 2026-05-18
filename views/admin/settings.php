<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Admin — Settings</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; background: #f0f2f5; }
        nav { background: #4f46e5; color: #fff; padding: 14px 24px; display: flex; justify-content: space-between; align-items: center; }
        nav a { color: #fff; text-decoration: none; margin-left: 20px; font-size: 14px; }
        nav a:hover { text-decoration: underline; }
        .container { max-width: 600px; margin: 30px auto; padding: 0 20px; }
        h2 { margin-bottom: 20px; color: #333; }
        .card { background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 1px 6px rgba(0,0,0,0.08); }
        label { display: block; margin-bottom: 6px; font-size: 14px; color: #555; margin-top: 16px; }
        input[type=text], input[type=number] { width: 100%; padding: 10px 12px; border: 1px solid #ccc; border-radius: 6px; font-size: 14px; }
        .checkbox-row { display: flex; align-items: center; gap: 10px; margin-top: 16px; }
        .checkbox-row input { width: auto; }
        button { margin-top: 24px; width: 100%; padding: 12px; background: #4f46e5; color: #fff; border: none; border-radius: 6px; font-size: 15px; cursor: pointer; }
        button:hover { background: #4338ca; }
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
$settings = $_SESSION['settings'] ?? [];
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
    <h2>Global Settings</h2>

    <?php if (!empty($_SESSION['msg'])): ?>
        <div class="msg"><?= $_SESSION['msg']; $_SESSION['msg'] = ''; ?></div>
    <?php endif; ?>
    <?php if (!empty($_SESSION['error'])): ?>
        <div class="error"><?= $_SESSION['error']; $_SESSION['error'] = ''; ?></div>
    <?php endif; ?>

    <div class="card">
        <form method="POST" action="../../controllers/AdminSaveSettingsController.php">

            <label>Default Fine Rate Per Day (৳)</label>
            <input type="number" name="default_fine_rate" step="0.01" min="0"
                   value="<?= htmlspecialchars($settings['default_fine_rate'] ?? '5.00') ?>" required>

            <label>Default Maximum Borrow Days</label>
            <input type="number" name="default_max_borrow_days" min="1"
                   value="<?= htmlspecialchars($settings['default_max_borrow_days'] ?? '14') ?>" required>

            <label>Default Maximum Books Per Member</label>
            <input type="number" name="default_max_books_per_member" min="1"
                   value="<?= htmlspecialchars($settings['default_max_books_per_member'] ?? '5') ?>" required>

            <div class="checkbox-row">
                <input type="checkbox" name="allow_self_registration" id="selfReg"
                    <?= ($settings['allow_self_registration'] ?? '1') == '1' ? 'checked' : '' ?>>
                <label for="selfReg" style="margin:0">Allow members to self-register</label>
            </div>

            <button type="submit">Save Settings</button>
        </form>
    </div>
</div>
</body>
</html>
