<?php
// ============================================================
// views/manager/manage_librarians.php
// Assign / remove librarians to/from branches
// ============================================================

require_once 'config/Connect.php';
require_once 'config/Close.php';
require_once 'models/BranchModel.php';
require_once 'models/UserModel.php';

$conn        = connect();
$branches    = getAllBranches($conn);
$librarians  = getAllLibrarians($conn);

// Get all librarians with their branch info
$sql    = "SELECT u.*, b.branch_name FROM users u LEFT JOIN branches b ON u.branch_id = b.id WHERE u.role='librarian' ORDER BY u.full_name ASC";
$result = mysqli_query($conn, $sql);
$all_librarians = array();
while ($row = mysqli_fetch_assoc($result)) {
    $all_librarians[] = $row;
}

// Unassigned librarians
$unassigned = array();
foreach ($all_librarians as $lib) {
    if (empty($lib['branch_id'])) {
        $unassigned[] = $lib;
    }
}

closeConn($conn);
require_once 'views/header.php';
?>

<h1 class="page-title">&#128101; Manage Librarians</h1>

<div style="display:flex; gap:24px; flex-wrap:wrap;">

    <!-- Assign Librarian Form -->
    <div class="section-box" style="flex:0 0 300px;">
        <h3>Assign Librarian to Branch</h3>
        <?php if (empty($unassigned)): ?>
            <p style="color:#888; font-size:14px;">All librarians are already assigned to a branch.</p>
        <?php else: ?>
            <form action="index.php?page=do_branch&action=assign_librarian" method="POST">
                <div class="form-group">
                    <label>Select Librarian *</label>
                    <select name="librarian_id" id="librarian_select" required>
                        <option value="">-- Select Librarian --</option>
                        <?php foreach ($unassigned as $lib): ?>
                            <option value="<?php echo $lib['id']; ?>"><?php echo htmlspecialchars($lib['full_name']); ?> (<?php echo htmlspecialchars($lib['email']); ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Select Branch *</label>
                    <select name="branch_id" id="branch_select" required>
                        <option value="">-- Select Branch --</option>
                        <?php foreach ($branches as $b): ?>
                            <?php if ($b['status'] == 'active'): ?>
                                <option value="<?php echo $b['id']; ?>"><?php echo htmlspecialchars($b['branch_name']); ?></option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-success">Assign Librarian</button>
            </form>
        <?php endif; ?>

        <!-- AJAX search librarian -->
        <div style="margin-top:20px;">
            <h4 style="font-size:14px; color:#555; margin-bottom:8px;">Quick Search Librarian</h4>
            <input type="text" id="librarian_search" placeholder="Type name to search..." style="width:100%; padding:8px; border:1px solid #ddd; border-radius:6px; font-size:14px;">
            <div id="search_results" style="margin-top:8px; font-size:13px; color:#555;"></div>
        </div>
    </div>

    <!-- Librarians List -->
    <div style="flex:1; min-width:280px;">
        <div class="table-container">
            <h3 style="margin-bottom:16px;">All Librarians (<?php echo count($all_librarians); ?>)</h3>
            <?php if (empty($all_librarians)): ?>
                <p style="color:#888;">No librarians found.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr><th>#</th><th>Name</th><th>Email</th><th>Phone</th><th>Assigned Branch</th><th>Status</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php $i = 1; foreach ($all_librarians as $lib): ?>
                        <tr>
                            <td><?php echo $i++; ?></td>
                            <td><strong><?php echo htmlspecialchars($lib['full_name']); ?></strong></td>
                            <td style="font-size:13px;"><?php echo htmlspecialchars($lib['email']); ?></td>
                            <td><?php echo htmlspecialchars($lib['phone'] ?? '-'); ?></td>
                            <td>
                                <?php if (!empty($lib['branch_name'])): ?>
                                    <span class="badge badge-active"><?php echo htmlspecialchars($lib['branch_name']); ?></span>
                                <?php else: ?>
                                    <span style="color:#999; font-size:12px;">Unassigned</span>
                                <?php endif; ?>
                            </td>
                            <td><span class="badge badge-<?php echo $lib['status']; ?>"><?php echo strtoupper($lib['status']); ?></span></td>
                            <td>
                                <?php if (!empty($lib['branch_id'])): ?>
                                    <a href="index.php?page=do_branch&action=remove_librarian&id=<?php echo $lib['id']; ?>"
                                       class="btn btn-danger btn-sm"
                                       onclick="return confirm('Remove this librarian from their branch?')">Remove</a>
                                <?php else: ?>
                                    <span style="color:#999; font-size:12px;">—</span>
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

<script>
// AJAX: live search librarians by name
document.getElementById('librarian_search').addEventListener('keyup', function() {
    var query = this.value.trim();
    var resultsDiv = document.getElementById('search_results');

    if (query.length < 2) {
        resultsDiv.innerHTML = '';
        return;
    }

    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'ajax_librarian_search.php?q=' + encodeURIComponent(query), true);
    xhr.onload = function() {
        if (xhr.status === 200) {
            resultsDiv.innerHTML = xhr.responseText;
        }
    };
    xhr.send();
});
</script>

<?php require_once 'views/footer.php'; ?>
