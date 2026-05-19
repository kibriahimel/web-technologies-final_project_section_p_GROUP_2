<?php
// ============================================================
// views/librarian/manage_borrows.php
// Librarian views & manages all borrow records for their branch
// ============================================================

require_once 'config/Connect.php';
require_once 'config/Close.php';
require_once 'models/BorrowModel.php';

$conn      = connect();
$branch_id = $_SESSION['branch_id'];
$borrows   = getBorrowsByBranch($conn, $branch_id);
closeConn($conn);

// Group by status
$pending  = array();
$active   = array();
$returned = array();
$rejected = array();

foreach ($borrows as $b) {
    if ($b['status'] == 'pending')                          $pending[]  = $b;
    elseif ($b['status'] == 'active' || $b['status'] == 'overdue') $active[] = $b;
    elseif ($b['status'] == 'returned')                     $returned[] = $b;
    elseif ($b['status'] == 'rejected')                     $rejected[] = $b;
}

require_once 'views/header.php';
?>

<h1 class="page-title">&#128203; Manage Borrow Records</h1>

<?php if (isset($_SESSION['msg'])): ?>
    <div class="alert-success"><?php echo $_SESSION['msg']; unset($_SESSION['msg']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['error'])): ?>
    <div class="alert-error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
<?php endif; ?>

<!-- Tab navigation -->
<div style="margin-bottom:20px; display:flex; gap:10px; flex-wrap:wrap;">
    <a href="#pending"  class="btn btn-warning">Pending (<?php echo count($pending); ?>)</a>
    <a href="#active"   class="btn btn-success">Active / Overdue (<?php echo count($active); ?>)</a>
    <a href="#returned" class="btn btn-info">Returned (<?php echo count($returned); ?>)</a>
    <a href="#rejected" class="btn btn-secondary">Rejected (<?php echo count($rejected); ?>)</a>
</div>

<!-- PENDING REQUESTS -->
<div class="section-box" id="pending">
    <h3>&#9203; Pending Borrow Requests</h3>
    <?php if (empty($pending)): ?>
        <p style="color:#888;">No pending requests.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Member</th>
                    <th>Book</th>
                    <th>Requested On</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php $i = 1; foreach ($pending as $b): ?>
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td><?php echo htmlspecialchars($b['full_name']); ?></td>
                    <td><?php echo htmlspecialchars($b['title']); ?></td>
                    <td><?php echo date('d M Y', strtotime($b['created_at'])); ?></td>
                    <td style="display:flex; gap:6px; flex-wrap:wrap; align-items:center;">

                        <!-- Approve Button -->
                        <a href="index.php?page=do_borrow&action=approve&id=<?php echo $b['id']; ?>"
                           class="btn btn-success btn-sm"
                           onclick="return confirm('Approve this borrow request?')">Approve</a>

                        <!-- Reject Form with reason -->
                        <form action="index.php?page=do_borrow&action=reject" method="POST"
                              style="display:flex; gap:6px; align-items:center;"
                              onsubmit="return confirm('Reject this borrow request?')">
                            <input type="hidden" name="borrow_id" value="<?php echo $b['id']; ?>">
                            <input type="text" name="reject_reason"
                                   placeholder="Reason (optional)"
                                   style="padding:4px 8px; font-size:12px; border:1px solid #ccc; border-radius:4px; width:160px;">
                            <button type="submit" class="btn btn-danger btn-sm">Reject</button>
                        </form>

                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<!-- ACTIVE / OVERDUE BORROWS -->
<div class="section-box" id="active">
    <h3>&#128218; Active &amp; Overdue Borrows</h3>
    <?php if (empty($active)): ?>
        <p style="color:#888;">No active borrows right now.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Member</th>
                    <th>Book</th>
                    <th>Due Date</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php $i = 1; foreach ($active as $b): ?>
                <?php $overdue = date('Y-m-d') > $b['due_date']; ?>
                <tr style="<?php echo $overdue ? 'background:#fff0f0;' : ''; ?>">
                    <td><?php echo $i++; ?></td>
                    <td><?php echo htmlspecialchars($b['full_name']); ?></td>
                    <td><?php echo htmlspecialchars($b['title']); ?></td>
                    <td style="<?php echo $overdue ? 'color:#dc3545; font-weight:bold;' : ''; ?>">
                        <?php echo date('d M Y', strtotime($b['due_date'])); ?>
                        <?php if ($overdue): ?>
                            <br><small>Overdue by <?php echo (int)((strtotime(date('Y-m-d')) - strtotime($b['due_date'])) / 86400); ?> day(s)</small>
                        <?php endif; ?>
                    </td>
                    <td><span class="badge badge-<?php echo $b['status']; ?>"><?php echo strtoupper($b['status']); ?></span></td>
                    <td>
                        <a href="index.php?page=do_borrow&action=return&id=<?php echo $b['id']; ?>"
                           class="btn btn-primary btn-sm"
                           onclick="return confirm('Process return for this book?')">Return</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<!-- RETURNED BORROWS -->
<div class="section-box" id="returned">
    <h3>&#10003; Returned Books</h3>
    <?php if (empty($returned)): ?>
        <p style="color:#888;">No returns yet.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Member</th>
                    <th>Book</th>
                    <th>Borrow Date</th>
                    <th>Due Date</th>
                    <th>Return Date</th>
                </tr>
            </thead>
            <tbody>
                <?php $i = 1; foreach ($returned as $b): ?>
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td><?php echo htmlspecialchars($b['full_name']); ?></td>
                    <td><?php echo htmlspecialchars($b['title']); ?></td>
                    <td><?php echo date('d M Y', strtotime($b['borrow_date'])); ?></td>
                    <td><?php echo date('d M Y', strtotime($b['due_date'])); ?></td>
                    <td><?php echo $b['return_date'] ? date('d M Y', strtotime($b['return_date'])) : '-'; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<!-- REJECTED REQUESTS -->
<div class="section-box" id="rejected">
    <h3>&#10006; Rejected Requests</h3>
    <?php if (empty($rejected)): ?>
        <p style="color:#888;">No rejected requests.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Member</th>
                    <th>Book</th>
                    <th>Requested On</th>
                    <th>Reason</th>
                </tr>
            </thead>
            <tbody>
                <?php $i = 1; foreach ($rejected as $b): ?>
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td><?php echo htmlspecialchars($b['full_name']); ?></td>
                    <td><?php echo htmlspecialchars($b['title']); ?></td>
                    <td><?php echo date('d M Y', strtotime($b['created_at'])); ?></td>
                    <td><?php echo htmlspecialchars($b['reject_reason'] ?? '-'); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php require_once 'views/footer.php'; ?>
