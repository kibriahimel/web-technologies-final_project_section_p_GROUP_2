<?php
// ============================================================
// views/manager/monthly_report.php
// Monthly reports per branch: borrows, returns, fines, new members
// ============================================================

require_once 'config/Connect.php';
require_once 'config/Close.php';
require_once 'models/BranchModel.php';

$conn     = connect();
$branches = getAllBranches($conn);

// Default to current month/year
$year  = isset($_GET['year'])  ? (int)$_GET['year']  : (int)date('Y');
$month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('m');

// Clamp values
if ($month < 1 || $month > 12) $month = (int)date('m');
if ($year < 2020 || $year > (int)date('Y')) $year = (int)date('Y');

$report_data = array();
foreach ($branches as $b) {
    $report = getMonthlyBranchReport($conn, $b['id'], $year, $month);
    $report['branch_name'] = $b['branch_name'];
    $report['branch_status'] = $b['status'];
    $report_data[] = $report;
}

closeConn($conn);

$month_name = date('F', mktime(0, 0, 0, $month, 1));

require_once 'views/header.php';
?>

<h1 class="page-title">&#128197; Monthly Branch Report</h1>

<!-- Month/Year filter -->
<div class="section-box" style="max-width:500px; margin-bottom:24px;">
    <h3>Select Period</h3>
    <form method="GET" action="index.php" style="display:flex; gap:12px; align-items:flex-end; flex-wrap:wrap;">
        <input type="hidden" name="page" value="monthly_report">
        <div class="form-group" style="margin:0;">
            <label>Month</label>
            <select name="month">
                <?php for ($m = 1; $m <= 12; $m++): ?>
                    <option value="<?php echo $m; ?>" <?php echo $m == $month ? 'selected' : ''; ?>>
                        <?php echo date('F', mktime(0, 0, 0, $m, 1)); ?>
                    </option>
                <?php endfor; ?>
            </select>
        </div>
        <div class="form-group" style="margin:0;">
            <label>Year</label>
            <select name="year">
                <?php for ($y = (int)date('Y'); $y >= 2020; $y--): ?>
                    <option value="<?php echo $y; ?>" <?php echo $y == $year ? 'selected' : ''; ?>><?php echo $y; ?></option>
                <?php endfor; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">View Report</button>
    </form>
</div>

<h2 style="font-size:18px; color:#333; margin-bottom:16px;">
    &#128202; Report for: <strong><?php echo $month_name . ' ' . $year; ?></strong>
</h2>

<div class="section-box">
    <?php if (empty($report_data)): ?>
        <p style="color:#888;">No branch data available.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Branch</th>
                    <th>Status</th>
                    <th>Total Borrows</th>
                    <th>Returns</th>
                    <th>Overdue</th>
                    <th>Total Fines (Tk)</th>
                    <th>Fines Collected (Tk)</th>
                    <th>New Members</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($report_data as $r): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($r['branch_name']); ?></strong></td>
                    <td><span class="badge badge-<?php echo $r['branch_status']; ?>"><?php echo strtoupper($r['branch_status']); ?></span></td>
                    <td><?php echo (int)($r['total_borrows'] ?? 0); ?></td>
                    <td><?php echo (int)($r['returns'] ?? 0); ?></td>
                    <td style="color:<?php echo ($r['overdue'] ?? 0) > 0 ? '#dc3545' : 'inherit'; ?>; font-weight:<?php echo ($r['overdue'] ?? 0) > 0 ? 'bold' : 'normal'; ?>;">
                        <?php echo (int)($r['overdue'] ?? 0); ?>
                    </td>
                    <td><?php echo number_format((float)($r['total_fines'] ?? 0), 2); ?></td>
                    <td style="color:#28a745;"><?php echo number_format((float)($r['collected'] ?? 0), 2); ?></td>
                    <td style="color:#4a90e2; font-weight:bold;"><?php echo (int)($r['new_members'] ?? 0); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php require_once 'views/footer.php'; ?>
