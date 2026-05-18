<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Admin — User Management</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; background: #f0f2f5; }
        nav { background: #4f46e5; color: #fff; padding: 14px 24px; display: flex; justify-content: space-between; align-items: center; }
        nav a { color: #fff; text-decoration: none; margin-left: 20px; font-size: 14px; }
        nav a:hover { text-decoration: underline; }
        .container { max-width: 1100px; margin: 30px auto; padding: 0 20px; }
        h2 { margin-bottom: 20px; color: #333; }
        .toolbar { display: flex; gap: 12px; margin-bottom: 20px; align-items: center; }
        .toolbar input { flex: 1; padding: 10px 14px; border: 1px solid #ccc; border-radius: 6px; font-size: 14px; }
        table { width: 100%; background: #fff; border-radius: 8px; box-shadow: 0 1px 6px rgba(0,0,0,0.08); border-collapse: collapse; }
        th { background: #4f46e5; color: #fff; padding: 12px 14px; text-align: left; font-size: 13px; }
        td { padding: 11px 14px; border-bottom: 1px solid #f0f0f0; font-size: 14px; color: #333; }
        tr:last-child td { border-bottom: none; }
        .badge { padding: 3px 10px; border-radius: 12px; font-size: 12px; font-weight: bold; }
        .badge.admin    { background: #ede9fe; color: #5b21b6; }
        .badge.librarian { background: #dbeafe; color: #1d4ed8; }
        .badge.branch_manager { background: #fef3c7; color: #92400e; }
        .badge.member   { background: #d1fae5; color: #065f46; }
        .badge.inactive { background: #fee2e2; color: #991b1b; }
        .btn { padding: 6px 14px; border: none; border-radius: 6px; cursor: pointer; font-size: 13px; }
        .btn-deactivate { background: #fee2e2; color: #b91c1c; }
        .btn-activate   { background: #d1fae5; color: #065f46; }
        .modal-bg { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.4); justify-content:center; align-items:center; z-index:100; }
        .modal-bg.open { display:flex; }
        .modal { background:#fff; padding:32px; border-radius:8px; width:100%; max-width:420px; }
        .modal h3 { margin-bottom:20px; }
        .modal label { display:block; margin-bottom:5px; font-size:13px; color:#555; }
        .modal input, .modal select { width:100%; padding:9px 12px; border:1px solid #ccc; border-radius:6px; font-size:14px; margin-bottom:14px; }
        .modal-actions { display:flex; gap:10px; justify-content:flex-end; }
        .btn-primary { background:#4f46e5; color:#fff; padding:9px 20px; border:none; border-radius:6px; cursor:pointer; }
        .btn-cancel  { background:#e5e7eb; color:#333; padding:9px 20px; border:none; border-radius:6px; cursor:pointer; }
        .msg   { background:#d1fae5; color:#065f46; padding:10px 16px; border-radius:6px; margin-bottom:16px; }
        .error { background:#fee2e2; color:#b91c1c; padding:10px 16px; border-radius:6px; margin-bottom:16px; }
    </style>
</head>
<body>
<?php
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../../controllers/LoginPageController.php'); die();
}
$users    = $_SESSION['users']    ?? [];
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
    <h2>User Management</h2>

    <?php if (!empty($_SESSION['msg'])): ?>
        <div class="msg"><?= $_SESSION['msg']; $_SESSION['msg'] = ''; ?></div>
    <?php endif; ?>
    <?php if (!empty($_SESSION['error'])): ?>
        <div class="error"><?= $_SESSION['error']; $_SESSION['error'] = ''; ?></div>
    <?php endif; ?>

    <div class="toolbar">
        <!-- AJAX live search input -->
        <input type="text" id="searchInput" placeholder="Search by name, email or phone...">
        <button class="btn-primary btn" onclick="document.getElementById('createModal').classList.add('open')">+ Create Staff Account</button>
    </div>

    <table id="usersTable">
        <thead>
            <tr>
                <th>#</th><th>Name</th><th>Email</th><th>Phone</th>
                <th>Role</th><th>Branch</th><th>Status</th><th>Action</th>
            </tr>
        </thead>
        <tbody id="usersBody">
        <?php foreach ($users as $i => $u): ?>
            <tr>
                <td><?= $i + 1 ?></td>
                <td><?= htmlspecialchars($u['name']) ?></td>
                <td><?= htmlspecialchars($u['email']) ?></td>
                <td><?= htmlspecialchars($u['phone']) ?></td>
                <td><span class="badge <?= $u['role'] ?>"><?= $u['role'] ?></span></td>
                <td><?= htmlspecialchars($u['branch_name'] ?? '—') ?></td>
                <td>
                    <?php if ($u['is_active']): ?>
                        <span class="badge member">Active</span>
                    <?php else: ?>
                        <span class="badge inactive">Inactive</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($u['is_active']): ?>
                        <form method="POST" action="../../controllers/AdminToggleUserController.php" style="display:inline">
                            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                            <input type="hidden" name="status" value="0">
                            <button class="btn btn-deactivate" type="submit">Deactivate</button>
                        </form>
                    <?php else: ?>
                        <form method="POST" action="../../controllers/AdminToggleUserController.php" style="display:inline">
                            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                            <input type="hidden" name="status" value="1">
                            <button class="btn btn-activate" type="submit">Activate</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Create Staff Modal -->
<div class="modal-bg" id="createModal">
    <div class="modal">
        <h3>Create Staff Account</h3>
        <form method="POST" action="../../controllers/AdminCreateStaffController.php">
            <label>Full Name *</label>
            <input type="text" name="name" required>

            <label>Email *</label>
            <input type="email" name="email" required>

            <label>Password *</label>
            <input type="password" name="password" required>

            <label>Phone</label>
            <input type="text" name="phone">

            <label>Role *</label>
            <select name="role" required>
                <option value="">Select role</option>
                <option value="librarian">Librarian</option>
                <option value="branch_manager">Branch Manager</option>
            </select>

            <label>Branch *</label>
            <select name="branch_id" required>
                <option value="">Select branch</option>
                <?php foreach ($branches as $b): ?>
                    <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['name']) ?></option>
                <?php endforeach; ?>
            </select>

            <div class="modal-actions">
                <button type="button" class="btn-cancel" onclick="document.getElementById('createModal').classList.remove('open')">Cancel</button>
                <button type="submit" class="btn-primary">Create</button>
            </div>
        </form>
    </div>
</div>

<script>
// ── AJAX live search (XMLHttpRequest as required) ──────────────
var searchInput = document.getElementById('searchInput');
var usersBody   = document.getElementById('usersBody');
var timer;

searchInput.addEventListener('keyup', function () {
    clearTimeout(timer);
    var q = this.value.trim();
    timer = setTimeout(function () {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', '../../controllers/AdminSearchUsersController.php?q=' + encodeURIComponent(q), true);
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) {
                var users = JSON.parse(xhr.responseText);
                var html  = '';
                if (users.length === 0) {
                    html = '<tr><td colspan="8" style="text-align:center;color:#999;padding:20px">No users found</td></tr>';
                } else {
                    users.forEach(function (u, i) {
                        var statusBadge = u.is_active == 1
                            ? '<span class="badge member">Active</span>'
                            : '<span class="badge inactive">Inactive</span>';
                        var actionBtn = u.is_active == 1
                            ? '<form method="POST" action="../../controllers/AdminToggleUserController.php" style="display:inline"><input type="hidden" name="user_id" value="' + u.id + '"><input type="hidden" name="status" value="0"><button class="btn btn-deactivate" type="submit">Deactivate</button></form>'
                            : '<form method="POST" action="../../controllers/AdminToggleUserController.php" style="display:inline"><input type="hidden" name="user_id" value="' + u.id + '"><input type="hidden" name="status" value="1"><button class="btn btn-activate" type="submit">Activate</button></form>';
                        html += '<tr>'
                            + '<td>' + (i + 1) + '</td>'
                            + '<td>' + u.name + '</td>'
                            + '<td>' + u.email + '</td>'
                            + '<td>' + (u.phone || '—') + '</td>'
                            + '<td><span class="badge ' + u.role + '">' + u.role + '</span></td>'
                            + '<td>' + (u.branch_name || '—') + '</td>'
                            + '<td>' + statusBadge + '</td>'
                            + '<td>' + actionBtn + '</td>'
                            + '</tr>';
                    });
                }
                usersBody.innerHTML = html;
            }
        };
        xhr.send();
    }, 300);
});
</script>
</body>
</html>
