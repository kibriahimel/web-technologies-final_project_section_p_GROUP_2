<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Admin — Announcements</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; background: #f0f2f5; }
        nav { background: #4f46e5; color: #fff; padding: 14px 24px; display: flex; justify-content: space-between; align-items: center; }
        nav a { color: #fff; text-decoration: none; margin-left: 20px; font-size: 14px; }
        nav a:hover { text-decoration: underline; }
        .container { max-width: 900px; margin: 30px auto; padding: 0 20px; }
        h2 { margin-bottom: 20px; color: #333; }
        .card { background: #fff; padding: 24px; border-radius: 8px; box-shadow: 0 1px 6px rgba(0,0,0,0.08); margin-bottom: 20px; }
        .card h3 { margin-bottom: 10px; color: #4f46e5; }
        .card h4 { margin-bottom: 6px; color: #333; }
        .card p  { color: #555; font-size: 14px; line-height: 1.6; }
        .meta { font-size: 12px; color: #999; margin-bottom: 10px; }
        label { display: block; margin-bottom: 6px; font-size: 14px; color: #555; margin-top: 14px; }
        input[type=text], textarea { width: 100%; padding: 10px 12px; border: 1px solid #ccc; border-radius: 6px; font-size: 14px; }
        textarea { height: 100px; resize: vertical; }
        .btn-primary { margin-top: 16px; padding: 10px 24px; background: #4f46e5; color: #fff; border: none; border-radius: 6px; cursor: pointer; font-size: 14px; }
        .btn-delete  { padding: 6px 14px; background: #fee2e2; color: #b91c1c; border: none; border-radius: 6px; cursor: pointer; font-size: 13px; float: right; }
        .msg   { background: #d1fae5; color: #065f46; padding: 10px 16px; border-radius: 6px; margin-bottom: 16px; }
        .error { background: #fee2e2; color: #b91c1c; padding: 10px 16px; border-radius: 6px; margin-bottom: 16px; }
        .divider { border: none; border-top: 1px solid #eee; margin: 10px 0; }
    </style>
</head>
<body>
<?php
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../../controllers/LoginPageController.php'); die();
}
$announcements = $_SESSION['announcements'] ?? [];
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
    <h2>Platform-Wide Announcements</h2>

    <?php if (!empty($_SESSION['msg'])): ?>
        <div class="msg"><?= $_SESSION['msg']; $_SESSION['msg'] = ''; ?></div>
    <?php endif; ?>
    <?php if (!empty($_SESSION['error'])): ?>
        <div class="error"><?= $_SESSION['error']; $_SESSION['error'] = ''; ?></div>
    <?php endif; ?>

    
    <div class="card">
        <h3>Post New Announcement</h3>
        <form method="POST" action="../../controllers/AdminSaveAnnouncementController.php">
            <label>Title</label>
            <input type="text" name="title" placeholder="Announcement title" required>

            <label>Message</label>
            <textarea name="body" placeholder="Write your announcement here..." required></textarea>

            <button class="btn-primary" type="submit">Post Announcement</button>
        </form>
    </div>

  
    <?php if (empty($announcements)): ?>
        <div class="card"><p style="color:#999">No announcements yet.</p></div>
    <?php else: ?>
        <?php foreach ($announcements as $a): ?>
        <div class="card">
            <form method="POST" action="../../controllers/AdminDeleteAnnouncementController.php" style="display:inline">
                <input type="hidden" name="id" value="<?= $a['id'] ?>">
                <button class="btn-delete" type="submit" onclick="return confirm('Delete this announcement?')">Delete</button>
            </form>
            <h4><?= htmlspecialchars($a['title']) ?></h4>
            <div class="meta">
                Posted by <?= htmlspecialchars($a['author_name']) ?> &bull;
                <?= $a['branch_name'] ?> &bull;
                <?= $a['published_at'] ?>
            </div>
            <hr class="divider">
            <p><?= nl2br(htmlspecialchars($a['body'])) ?></p>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>

</div>
</body>
</html>
