<?php
// ============================================================
// views/manager/manage_branches.php
// Create, edit, view, and manage library branches
// ============================================================

require_once 'config/Connect.php';
require_once 'config/Close.php';
require_once 'models/BranchModel.php';
require_once 'models/UserModel.php';

$conn     = connect();
$branches = getAllBranches($conn);
$managers = array();

// Get all managers to assign to branches
$sql    = "SELECT id, full_name FROM users WHERE role='manager' AND status='active'";
$result = mysqli_query($conn, $sql);
while ($row = mysqli_fetch_assoc($result)) {
    $managers[] = $row;
}

// Check if we are editing a branch
$edit_branch = null;
if (isset($_GET['edit'])) {
    $edit_branch = getBranchById($conn, (int)$_GET['edit']);
}

closeConn($conn);
require_once 'views/header.php';
?>

<h1 class="page-title">&#127970; Manage Branches</h1>

<div style="display:flex; gap:24px; flex-wrap:wrap;">

    <!-- Add / Edit Branch Form -->
    <div class="section-box" style="flex:0 0 300px;">
        <?php if ($edit_branch): ?>
            <h3>Edit Branch</h3>
            <form action="index.php?page=do_branch&action=update" method="POST">
                <input type="hidden" name="id" value="<?php echo $edit_branch['id']; ?>">
                <div class="form-group">
                    <label>Branch Name *</label>
                    <input type="text" name="branch_name" value="<?php echo htmlspecialchars($edit_branch['branch_name']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Location</label>
                    <input type="text" name="location" value="<?php echo htmlspecialchars($edit_branch['location'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label>Phone</label>
                    <input type="text" name="phone" value="<?php echo htmlspecialchars($edit_branch['phone'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($edit_branch['email'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label>Assign Manager</label>
                    <select name="manager_id">
                        <option value="0">-- None --</option>
                        <?php foreach ($managers as $m): ?>
                            <option value="<?php echo $m['id']; ?>"
                                <?php echo ($edit_branch['manager_id'] == $m['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($m['full_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="display:flex; gap:8px;">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                    <a href="index.php?page=manage_branches" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        <?php else: ?>
            <h3>Add New Branch</h3>
            <form action="index.php?page=do_branch&action=create" method="POST">
                <div class="form-group">
                    <label>Branch Name *</label>
                    <input type="text" name="branch_name" placeholder="e.g. North Branch" required>
                </div>
                <div class="form-group">
                    <label>Location</label>
                    <input type="text" name="location" placeholder="e.g. 45 North Campus, Dhaka">
                </div>
                <div class="form-group">
                    <label>Phone</label>
                    <input type="text" name="phone" placeholder="e.g. 01711000001">
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" placeholder="branch@library.com">
                </div>
                <button type="submit" class="btn btn-success">Create Branch</button>
            </form>
        <?php endif; ?>
    </div>

    <!-- Branch List -->
    <div style="flex:1; min-width:280px;">
        <div class="table-container">
            <h3 style="margin-bottom:16px;">All Branches (<?php echo count($branches); ?>)</h3>
            <?php if (empty($branches)): ?>
                <p style="color:#888;">No branches found.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr><th>#</th><th>Branch Name</th><th>Location</th><th>Phone</th><th>Manager</th><th>Status</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php $i = 1; foreach ($branches as $b): ?>
                        <tr>
                            <td><?php echo $i++; ?></td>
                            <td><strong><?php echo htmlspecialchars($b['branch_name']); ?></strong></td>
                            <td style="font-size:13px;"><?php echo htmlspecialchars($b['location'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($b['phone'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($b['manager_name'] ?? 'Unassigned'); ?></td>
                            <td><span class="badge badge-<?php echo $b['status']; ?>"><?php echo strtoupper($b['status']); ?></span></td>
                            <td>
                                <a href="index.php?page=manage_branches&edit=<?php echo $b['id']; ?>" class="btn btn-primary btn-sm">Edit</a>
                                <a href="index.php?page=do_branch&action=toggle_status&id=<?php echo $b['id']; ?>"
                                   class="btn btn-sm <?php echo $b['status'] == 'active' ? 'btn-warning' : 'btn-success'; ?>"
                                   onclick="return confirm('Toggle status for this branch?')">
                                   <?php echo $b['status'] == 'active' ? 'Deactivate' : 'Activate'; ?>
                                </a>
                                <a href="index.php?page=branch_policy&id=<?php echo $b['id']; ?>" class="btn btn-warning btn-sm">Policy</a>
                                <?php if ($_SESSION['role'] == 'admin'): ?>
                                    <a href="index.php?page=do_branch&action=delete&id=<?php echo $b['id']; ?>"
                                       class="btn btn-danger btn-sm"
                                       onclick="return confirmDelete('Delete this branch?')">Delete</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

</div>

<?php require_once 'views/footer.php'; ?>
