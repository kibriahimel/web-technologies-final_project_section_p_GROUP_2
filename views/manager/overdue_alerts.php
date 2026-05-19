<?php
// ============================================================
// views/manager/overdue_alerts.php
// Overdue loan alerts with configurable day threshold
// ============================================================

require_once 'config/Connect.php';
require_once 'config/Close.php';
require_once 'models/BranchModel.php';

// Configurable threshold from query param, default 0 (all overdue)
$threshold = isset($_GET['days']) ? (int)$_GET['days'] : 0;
if ($threshold < 0) $threshold = 0;

$conn   = connect();
$loans  = getOverdueLoans($conn, $threshold);
closeConn($conn);

require_once 'views/header.php';
?>

<h1 class="page-title">&#9888; Overdue Loan Alerts</h1>

<!-- Threshold filter form -->
<div class="section-box" style="max-width:500px; margin-bottom:20px;">
    <h3>Filter by Overdue Threshold</h3>
    <form method="GET" action="index.php" style="display:flex; gap:12px; align-items:flex-end; flex-wrap:wrap;">
        <input type="hidden" name="page" value="overdue_alerts">
        <div class="form-group" style="margin:0;">
            <label>Show loans overdue by at least:</label>
            <input type="number" name="days" value="<?php echo $threshold; ?>" min="0" style="width:100px;"> days
        </div>
        <button type="submit" class="btn btn-primary">Apply Filter</button>
        <?php if ($threshold > 0): ?>
            <a href="index.php?page=overdue_alerts" class="btn btn-secondary">Clear Filter</a>
        <?php endif; ?>
    </form>
</div>

<!-- Results -->
<div class="section-box">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; flex-wrap:wrap; gap:8px;">
        <h3>Overdue Loans<?php echo $threshold > 0 ? ' (' . $threshold . '+ days)' : ' (All)'; ?></h3>
        <span style="background:#dc3545; color:#fff; padding:4px 12px; border-radius:20px; font-size:14px; font-weight:bold;">
            <?php echo count($loans); ?> found
        </span>
    </div>
    <?php if (empty($loans)): ?>
        <p style="color:#28a745; font-weight:bold;">&#10003; No overdue loans found<?php echo $threshold > 0 ? ' for threshold of ' . $threshold . ' days' : ''; ?>!</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Member</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Book</th>
                    <th>Branch</th>
                    <th>Due Date</th>
                    <th>Days Overdue</th>
                </tr>
            </thead>
            <tbody>
                <?php $i = 1; foreach ($loans as $loan): ?>
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td><strong><?php echo htmlspecialchars($loan['full_name']); ?></strong></td>
                    <td style="font-size:13px;"><?php echo htmlspecialchars($loan['email']); ?></td>
                    <td><?php echo htmlspecialchars($loan['phone'] ?? '-'); ?></td>
                    <td><?php echo htmlspecialchars($loan['book_title']); ?></td>
                    <td><?php echo htmlspecialchars($loan['branch_name']); ?></td>
                    <td><?php echo date('d M Y', strtotime($loan['due_date'])); ?></td>
                    <td style="color:#dc3545; font-weight:bold; font-size:16px;">
                        <?php echo (int)$loan['days_overdue']; ?> days
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php require_once 'views/footer.php'; ?>
