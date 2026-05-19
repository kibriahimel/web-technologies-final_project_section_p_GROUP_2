<?php
// ============================================================
// views/manager/transfer_requests.php
// Inter-branch book transfer requests — view only (MVC fixed)
// All actions handled by controllers/TransferController.php
// ============================================================

require_once 'config/Connect.php';
require_once 'config/Close.php';
require_once 'models/BranchModel.php';
require_once 'models/BookModel.php';

$conn     = connect();
$requests = getInterBranchRequests($conn);
$branches = getAllBranches($conn);
$books    = getAllBooks($conn);
closeConn($conn);

require_once 'views/header.php';
?>

<h1 class="page-title">&#128661; Inter-Branch Transfer Requests</h1>

<div style="display:flex; gap:24px; flex-wrap:wrap;">

    <!-- Create Request Form -->
    <div class="section-box" style="flex:0 0 300px;">
        <h3>New Transfer Request</h3>
        <form action="index.php?page=do_transfer&action=create" method="POST">
            <div class="form-group">
                <label>Book *</label>
                <select name="book_id" required>
                    <option value="">-- Select Book --</option>
                    <?php foreach ($books as $b): ?>
                        <option value="<?php echo $b['id']; ?>"><?php echo htmlspecialchars($b['title']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>From Branch *</label>
                <select name="from_branch_id" required>
                    <option value="">-- Select Branch --</option>
                    <?php foreach ($branches as $b): ?>
                        <option value="<?php echo $b['id']; ?>"><?php echo htmlspecialchars($b['branch_name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>To Branch *</label>
                <select name="to_branch_id" required>
                    <option value="">-- Select Branch --</option>
                    <?php foreach ($branches as $b): ?>
                        <option value="<?php echo $b['id']; ?>"><?php echo htmlspecialchars($b['branch_name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Submit Request</button>
        </form>
    </div>

    <!-- Requests Table -->
    <div class="table-container" style="flex:1; min-width:280px;">
        <h3 style="margin-bottom:16px;">All Requests</h3>
        <?php if (empty($requests)): ?>
            <p style="color:#888;">No transfer requests yet.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr><th>#</th><th>Book</th><th>From</th><th>To</th><th>Requested By</th><th>Status</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <?php $i = 1; foreach ($requests as $r): ?>
                    <tr>
                        <td><?php echo $i++; ?></td>
                        <td><?php echo htmlspecialchars($r['book_title']); ?></td>
                        <td><?php echo htmlspecialchars($r['from_branch']); ?></td>
                        <td><?php echo htmlspecialchars($r['to_branch']); ?></td>
                        <td><?php echo htmlspecialchars($r['requester']); ?></td>
                        <td><span class="badge badge-<?php echo $r['status']; ?>"><?php echo strtoupper($r['status']); ?></span></td>
                        <td>
                            <?php if ($r['status'] == 'pending'): ?>
                                <a href="index.php?page=do_transfer&action=approve&id=<?php echo $r['id']; ?>"
                                   class="btn btn-success btn-sm"
                                   onclick="return confirm('Approve this transfer?')">Approve</a>
                                <a href="index.php?page=do_transfer&action=reject&id=<?php echo $r['id']; ?>"
                                   class="btn btn-danger btn-sm"
                                   onclick="return confirm('Reject this transfer?')">Reject</a>
                            <?php else: ?>
                                <span style="color:#888; font-size:12px;">Done</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

</div>

<?php require_once 'views/footer.php'; ?>
