<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Admin — Book Catalog</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; background: #f0f2f5; }
        nav { background: #4f46e5; color: #fff; padding: 14px 24px; display: flex; justify-content: space-between; align-items: center; }
        nav a { color: #fff; text-decoration: none; margin-left: 20px; font-size: 14px; }
        nav a:hover { text-decoration: underline; }
        .container { max-width: 1200px; margin: 30px auto; padding: 0 20px; }
        h2 { margin-bottom: 20px; color: #333; }
        .toolbar { display: flex; gap: 12px; margin-bottom: 20px; }
        .toolbar input { flex: 1; padding: 10px 14px; border: 1px solid #ccc; border-radius: 6px; font-size: 14px; }
        table { width: 100%; background: #fff; border-radius: 8px; box-shadow: 0 1px 6px rgba(0,0,0,0.08); border-collapse: collapse; }
        th { background: #4f46e5; color: #fff; padding: 12px 14px; text-align: left; font-size: 13px; }
        td { padding: 11px 14px; border-bottom: 1px solid #f0f0f0; font-size: 14px; color: #333; vertical-align: top; }
        tr:last-child td { border-bottom: none; }
        .btn-edit { padding: 6px 14px; background: #dbeafe; color: #1d4ed8; border: none; border-radius: 6px; cursor: pointer; font-size: 13px; text-decoration: none; }
        .btn-edit:hover { background: #bfdbfe; }
        .msg   { background: #d1fae5; color: #065f46; padding: 10px 16px; border-radius: 6px; margin-bottom: 16px; }
        .error { background: #fee2e2; color: #b91c1c; padding: 10px 16px; border-radius: 6px; margin-bottom: 16px; }
        .desc { color: #888; font-size: 12px; max-width: 250px; }
    </style>
</head>
<body>
<?php
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../../controllers/LoginPageController.php'); die();
}
$books = $_SESSION['books'] ?? [];
?>
<nav>
    <strong> Library Admin</strong>
    <div>
        <a href="../../controllers/AdminDashboardController.php">Dashboard</a>
        <a href="../../controllers/AdminUsersController.php">Users</a>
        <a href="../../controllers/AdminBranchesController.php">Branches</a>
        <a href="../../controllers/AdminCatalogController.php">Catalog</a>
        <a href="../../controllers/AdminTransfersController.php">Transfers</a>
        <a href="../../controllers/AdminReportsController.php">Reports</a>
        <a href="../../controllers/AdminAnnouncementsController.php">Announcements</a>
        <a href="../../controllers/AdminSettingsController.php">Settings</a>
        <a href="../../controllers/AdminAuditController.php">Audit Log</a>
        <a href="../../controllers/LogoutController.php">Logout</a>
    </div>
</nav>

<div class="container">
    <h2>Global Book Catalog</h2>

    <?php if (!empty($_SESSION['msg'])): ?>
        <div class="msg"><?= $_SESSION['msg']; $_SESSION['msg'] = ''; ?></div>
    <?php endif; ?>
    <?php if (!empty($_SESSION['error'])): ?>
        <div class="error"><?= $_SESSION['error']; $_SESSION['error'] = ''; ?></div>
    <?php endif; ?>

    <div class="toolbar">
        <input type="text" id="searchInput" placeholder="Search by title, author or ISBN...">
    </div>

    <table id="catalogTable">
        <thead>
            <tr>
                <th>#</th>
                <th>Title</th>
                <th>Author</th>
                <th>ISBN</th>
                <th>Genre</th>
                <th>Publisher</th>
                <th>Year</th>
                <th>Description</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody id="catalogBody">
        <?php if (empty($books)): ?>
            <tr><td colspan="9" style="text-align:center;color:#999;padding:20px">No books found</td></tr>
        <?php else: ?>
            <?php foreach ($books as $i => $b): ?>
            <tr>
                <td><?= $i + 1 ?></td>
                <td><strong><?= htmlspecialchars($b['title']) ?></strong></td>
                <td><?= htmlspecialchars($b['author']) ?></td>
                <td><?= htmlspecialchars($b['isbn'] ?? '—') ?></td>
                <td><?= htmlspecialchars($b['genre_name'] ?? '—') ?></td>
                <td><?= htmlspecialchars($b['publisher'] ?? '—') ?></td>
                <td><?= $b['published_year'] ?? '—' ?></td>
                <td class="desc"><?= htmlspecialchars(substr($b['description'] ?? '', 0, 80)) ?>...</td>
                <td>
                    <a class="btn-edit" href="../../controllers/AdminEditBookController.php?id=<?= $b['id'] ?>">Edit</a>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<script>

document.getElementById('searchInput').addEventListener('keyup', function () {
    var q = this.value.toLowerCase();
    var rows = document.querySelectorAll('#catalogBody tr');
    rows.forEach(function (row) {
        row.style.display = row.innerText.toLowerCase().includes(q) ? '' : 'none';
    });
});
</script>
</body>
</html>
