<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Admin — Edit Book</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; background: #f0f2f5; }
        nav { background: #4f46e5; color: #fff; padding: 14px 24px; display: flex; justify-content: space-between; align-items: center; }
        nav a { color: #fff; text-decoration: none; margin-left: 20px; font-size: 14px; }
        nav a:hover { text-decoration: underline; }
        .container { max-width: 700px; margin: 30px auto; padding: 0 20px; }
        h2 { margin-bottom: 20px; color: #333; }
        .card { background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 1px 6px rgba(0,0,0,0.08); }
        label { display: block; margin-bottom: 6px; font-size: 14px; color: #555; margin-top: 14px; }
        input[type=text], input[type=number], select, textarea {
            width: 100%; padding: 10px 12px; border: 1px solid #ccc;
            border-radius: 6px; font-size: 14px;
        }
        textarea { height: 120px; resize: vertical; }
        .actions { display: flex; gap: 12px; margin-top: 24px; }
        .btn-primary { flex: 1; padding: 12px; background: #4f46e5; color: #fff; border: none; border-radius: 6px; font-size: 15px; cursor: pointer; }
        .btn-cancel  { flex: 1; padding: 12px; background: #e5e7eb; color: #333; border: none; border-radius: 6px; font-size: 15px; cursor: pointer; text-align: center; text-decoration: none; display: flex; align-items: center; justify-content: center; }
        .btn-primary:hover { background: #4338ca; }
        .error { background: #fee2e2; color: #b91c1c; padding: 10px 16px; border-radius: 6px; margin-bottom: 16px; }
    </style>
</head>
<body>
<?php
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../../controllers/LoginPageController.php'); die();
}
$book   = $_SESSION['edit_book'] ?? [];
$genres = $_SESSION['genres']    ?? [];
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
    <h2>Edit Book</h2>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="error"><?= $_SESSION['error']; $_SESSION['error'] = ''; ?></div>
    <?php endif; ?>

    <div class="card">
        <form method="POST" action="../../controllers/AdminSaveBookController.php">
            <input type="hidden" name="id" value="<?= $book['id'] ?>">

            <label>Title *</label>
            <input type="text" name="title" value="<?= htmlspecialchars($book['title'] ?? '') ?>" required>

            <label>Author *</label>
            <input type="text" name="author" value="<?= htmlspecialchars($book['author'] ?? '') ?>" required>

            <label>ISBN</label>
            <input type="text" name="isbn" value="<?= htmlspecialchars($book['isbn'] ?? '') ?>">

            <label>Genre</label>
            <select name="genre_id">
                <option value="">-- Select Genre --</option>
                <?php foreach ($genres as $g): ?>
                    <option value="<?= $g['id'] ?>" <?= $book['genre_id'] == $g['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($g['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label>Publisher</label>
            <input type="text" name="publisher" value="<?= htmlspecialchars($book['publisher'] ?? '') ?>">

            <label>Published Year</label>
            <input type="number" name="published_year" min="1000" max="2099"
                   value="<?= $book['published_year'] ?? '' ?>">

            <label>Description</label>
            <textarea name="description"><?= htmlspecialchars($book['description'] ?? '') ?></textarea>

            <div class="actions">
                <a class="btn-cancel" href="../../controllers/AdminCatalogController.php">Cancel</a>
                <button class="btn-primary" type="submit">Save Changes</button>
            </div>
        </form>
    </div>
</div>
</body>
</html>
