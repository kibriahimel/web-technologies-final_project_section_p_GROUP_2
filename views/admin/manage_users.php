<?php
// ============================================================
// views/admin/manage_users.php
// Admin: view, create, activate/deactivate, delete users
// ============================================================

require_once 'config/Connect.php';
require_once 'config/Close.php';
require_once 'models/UserModel.php';
require_once 'models/BranchModel.php';

$conn     = connect();
$users    = getAllUsers($conn);
$branches = getAllBranches($conn);
closeConn($conn);

require_once 'views/header.php';
?>

<h1 class="page-title">&#128100; Manage Users</h1>

<!-- Add User Form -->
<div class="section-box">
    <h3>Create New User</h3>
    <form action="index.php?page=do_user&action=create" method="POST">
        <div style="display:flex; gap:20px; flex-wrap:wrap;">
            <div style="flex:1; min-width:220px;">
                <div class="form-group">
                    <label>Full Name *</label>
                    <input type="text" name="full_name" placeholder="Full name" required>
                </div>
                <div class="form-group">
                    <label>Email *</label>
                    <input type="email" name="email" placeholder="email@example.com" required>
                </div>
                <div class="form-group">
                    <label>Password *</label>
                    <input type="password" name="password" placeholder="Min 6 chars" required>
                </div>
            </div>
            <div style="flex:1; min-width:220px;">
                <div class="form-group">
                    <label>Role *</label>
                    <select name="role" required>
                        <option value="member">Member</option>
                        <option value="librarian">Librarian</option>
                        <option value="manager">Branch Manager</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Branch (for librarian/manager)</label>
                    <select name="branch_id">
                        <option value="">-- No Branch --</option>
                        <?php foreach ($branches as $b): ?>
                            <option value="<?php echo $b['id']; ?>"><?php echo htmlspecialchars($b['branch_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Phone</label>
                    <input type="text" name="phone" placeholder="Phone number">
                </div>
            </div>
        </div>
        <div class="form-group">
            <label>Address</label>
            <input type="text" name="address" placeholder="Address (optional)" style="max-width:500px;">
        </div>
        <button type="submit" class="btn btn-success">Create User</button>
    </form>
</div>

<!-- Users Table -->
<div class="table-container">
    <div class="flex-between">
        <h3>All Users (<?php echo count($users); ?>)</h3>
    </div>
    <table>
        <thead>
            <tr><th>#</th><th>Name</th><th>Email</th><th>Role</th><th>Branch</th><th>Phone</th><th>Status</th><th>Joined</th><th>Actions</th></tr>
        </thead>
        <tbody>
            <?php $i = 1; foreach ($users as $u): ?>
            <tr>
                <td><?php echo $i++; ?></td>
                <td><strong><?php echo htmlspecialchars($u['full_name']); ?></strong></td>
                <td style="font-size:13px;"><?php echo htmlspecialchars($u['email']); ?></td>
                <td><span class="badge badge-active"><?php echo strtoupper($u['role']); ?></span></td>
                <td style="font-size:13px;"><?php echo htmlspecialchars($u['branch_name'] ?? 'None'); ?></td>
                <td style="font-size:13px;"><?php echo htmlspecialchars($u['phone'] ?? '-'); ?></td>
                <td>
                    <span class="badge badge-<?php echo $u['status']; ?>">
                        <?php echo strtoupper($u['status']); ?>
                    </span>
                </td>
                <td style="font-size:12px;"><?php echo date('d M Y', strtotime($u['created_at'])); ?></td>
                <td>
                    <!-- Toggle Status -->
                    <a href="index.php?page=do_user&action=status&id=<?php echo $u['id']; ?>&status=<?php echo $u['status']; ?>"
                       class="btn btn-sm <?php echo $u['status'] == 'active' ? 'btn-warning' : 'btn-success'; ?>"
                       onclick="return confirm('Toggle this user\'s status?')">
                        <?php echo $u['status'] == 'active' ? 'Deactivate' : 'Activate'; ?>
                    </a>
                    <!-- Delete -->
                    <?php if ($u['id'] != $_SESSION['user']): ?>
                        <a href="index.php?page=do_user&action=delete&id=<?php echo $u['id']; ?>"
                           class="btn btn-danger btn-sm"
                           onclick="return confirmDelete('Delete user <?php echo htmlspecialchars($u['full_name']); ?>?')">Delete</a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once 'views/footer.php'; ?>
