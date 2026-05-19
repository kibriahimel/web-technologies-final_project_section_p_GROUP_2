<?php
// ============================================================
// views/manager/cross_branch_report.php
// Cross-branch inventory + borrowing + fine stats
// ============================================================

require_once 'config/Connect.php';
require_once 'config/Close.php';
require_once 'models/BranchModel.php';

$conn        = connect();
$inv_stats   = getCrossBranchInventory($conn);
$borrow_stats = getCrossBranchBorrowStats($conn);
$fine_stats  = getCrossBranchFineStats($conn);
closeConn($conn);

// Merge stats by branch id
$merged = array();
foreach ($borrow_stats as $bs) {
    $merged[$bs['id']] = $bs;
}
foreach ($inv_stats as $inv) {
    $merged[$inv['branch_id']]['unique_books']     = $inv['unique_books'];
    $merged[$inv['branch_id']]['total_copies']     = $inv['total_copies'];
    $merged[$inv['branch_id']]['available_copies'] = $inv['available_copies'];
}
foreach ($fine_stats as $fs) {
    $merged[$fs['id']]['unpaid_fines']  = $fs['unpaid_fines'];
    $merged[$fs['id']]['paid_fines']    = $fs['paid_fines'];
    $merged[$fs['id']]['overdue_loans'] = $fs['overdue_loans'];
}

require_once 'views/header.php';
?>

<h1 class="page-title"> Cross-Branch Report</h1>
<p style="color:#666; margin-bottom:24px;">Overview of all branches — inventory, borrows, overdue, and fines.</p>

<!-- Summary Cards -->
<?php
$total_branches   = count($merged);
$total_active     = 0;
$total_borrows_all = 0;
$total_overdue_all = 0;
$total_unpaid_all  = 0;
foreach ($merged as $b) {
    if (isset($b['status']) && $b['status'] == 'active') $total_active++;
    $total_borrows_all += (int)($b['total_borrows'] ?? 0);
    $total_overdue_all += (int)($b['overdue_loans'] ?? 0);
    $total_unpaid_all  += (float)($b['unpaid_fines'] ?? 0);
}
?>
<div class="card-row">
    <div class="card">
        <h3>Total Branches</h3>
        <div class="stat-number"><?php echo $total_branches; ?></div>
    </div>
    <div class="card green">
        <h3>Active Branches</h3>
        <div class="stat-number"><?php echo $total_active; ?></div>
    </div>
    <div class="card orange">
        <h3>Total Borrows</h3>
        <div class="stat-number"><?php echo $total_borrows_all; ?></div>
    </div>
    <div class="card red">
        <h3>Total Overdue</h3>
        <div class="stat-number"><?php echo $total_overdue_all; ?></div>
    </div>
</div>

<!-- Cross Branch Table -->
<div class="section-box">
    <h3>Branch-by-Branch Breakdown</h3>
    <?php if (empty($merged)): ?>
        <p style="color:#888;">No branch data available.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Branch</th>
                    <th>Status</th>
                    <th>Books (Unique)</th>
                    <th>Copies (Total)</th>
                    <th>Available</th>
                    <th>Total Borrows</th>
                    <th>Active Loans</th>
                    <th>Overdue</th>
                    <th>Unpaid Fines (Tk)</th>
                    <th>Fines Collected (Tk)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($merged as $b): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($b['branch_name']); ?></strong></td>
                    <td><span class="badge badge-<?php echo $b['status'] ?? 'inactive'; ?>"><?php echo strtoupper($b['status'] ?? 'inactive'); ?></span></td>
                    <td><?php echo (int)($b['unique_books'] ?? 0); ?></td>
                    <td><?php echo (int)($b['total_copies'] ?? 0); ?></td>
                    <td style="color:<?php echo ($b['available_copies'] ?? 0) > 0 ? '#28a745' : '#dc3545'; ?>; font-weight:bold;"><?php echo (int)($b['available_copies'] ?? 0); ?></td>
                    <td><?php echo (int)($b['total_borrows'] ?? 0); ?></td>
                    <td><?php echo (int)($b['active_loans'] ?? 0); ?></td>
                    <td style="color:<?php echo ($b['overdue_loans'] ?? 0) > 0 ? '#dc3545' : 'inherit'; ?>; font-weight:<?php echo ($b['overdue_loans'] ?? 0) > 0 ? 'bold' : 'normal'; ?>;">
                        <?php echo (int)($b['overdue_loans'] ?? 0); ?>
                    </td>
                    <td style="color:<?php echo ($b['unpaid_fines'] ?? 0) > 0 ? '#dc3545' : 'inherit'; ?>;">
                        <?php echo number_format((float)($b['unpaid_fines'] ?? 0), 2); ?>
                    </td>
                    <td style="color:#28a745;">
                        <?php echo number_format((float)($b['paid_fines'] ?? 0), 2); ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php require_once 'views/footer.php'; ?>
