<?php
// ============================================================
// views/manager/member_activity.php
// Top borrowers, outstanding fines, new registrations
// ============================================================

require_once 'config/Connect.php';
require_once 'config/Close.php';
require_once 'models/BranchModel.php';

$conn        = connect();
$top_borrowers = getTopBorrowers($conn, 15);

// New member registrations in last 30 days
$sql    = "SELECT u.id, u.full_name, u.email, u.phone, b.branch_name, u.created_at
           FROM users u
           LEFT JOIN branches b ON u.branch_id = b.id
           WHERE u.role='member' AND u.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
           ORDER BY u.created_at DESC";
$result = mysqli_query($conn, $sql);
$new_members = array();
while ($row = mysqli_fetch_assoc($result)) {
    $new_members[] = $row;
}

// Members with outstanding fines
$sql2   = "SELECT u.id, u.full_name, u.email, SUM(f.amount) AS total_fines, COUNT(f.id) AS fine_count
           FROM fines f
           JOIN users u ON f.user_id = u.id
           WHERE f.status='unpaid'
           GROUP BY u.id, u.full_name, u.email
           ORDER BY total_fines DESC
           LIMIT 15";
$result2 = mysqli_query($conn, $sql2);
$outstanding = array();
while ($row = mysqli_fetch_assoc($result2)) {
    $outstanding[] = $row;
}

closeConn($conn);
require_once 'views/header.php';
?>

<h1 class="page-title">&#128101; Member Activity Report</h1>
<p style="color:#666; margin-bottom:24px;">Top borrowers, outstanding fines, and new member registrations.</p>

<!-- Top Borrowers -->
<div class="section-box">
    <h3>&#127942; Top Borrowers</h3>
    <?php if (empty($top_borrowers)): ?>
        <p style="color:#888;">No borrow records found.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr><th>#</th><th>Name</th><th>Email</th><th>Home Branch</th><th>Total Borrows</th><th>Outstanding Fines (Tk)</th></tr>
            </thead>
            <tbody>
                <?php $i = 1; foreach ($top_borrowers as $m): ?>
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td><strong><?php echo htmlspecialchars($m['full_name']); ?></strong></td>
                    <td style="font-size:13px;"><?php echo htmlspecialchars($m['email']); ?></td>
                    <td><?php echo htmlspecialchars($m['branch_name'] ?? '-'); ?></td>
                    <td style="font-weight:bold; color:#4a90e2;"><?php echo (int)$m['total_borrows']; ?></td>
                    <td style="color:<?php echo ($m['outstanding_fines'] ?? 0) > 0 ? '#dc3545' : '#28a745'; ?>; font-weight:bold;">
                        <?php echo number_format((float)($m['outstanding_fines'] ?? 0), 2); ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<!-- Outstanding Fines -->
<div class="section-box">
    <h3>&#9888; Members with Outstanding Fines</h3>
    <?php if (empty($outstanding)): ?>
        <p style="color:#888;">No unpaid fines found. Great!</p>
    <?php else: ?>
        <table>
            <thead>
                <tr><th>#</th><th>Name</th><th>Email</th><th>No. of Fines</th><th>Total Unpaid (Tk)</th></tr>
            </thead>
            <tbody>
                <?php $i = 1; foreach ($outstanding as $o): ?>
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td><strong><?php echo htmlspecialchars($o['full_name']); ?></strong></td>
                    <td style="font-size:13px;"><?php echo htmlspecialchars($o['email']); ?></td>
                    <td><?php echo (int)$o['fine_count']; ?></td>
                    <td style="color:#dc3545; font-weight:bold;"><?php echo number_format((float)$o['total_fines'], 2); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<!-- New Member Registrations -->
<div class="section-box">
    <h3>&#127381; New Registrations (Last 30 Days)</h3>
    <?php if (empty($new_members)): ?>
        <p style="color:#888;">No new members registered in the last 30 days.</p>
    <?php else: ?>
        <p style="color:#555; margin-bottom:12px;"><strong><?php echo count($new_members); ?></strong> new member(s) joined in the last 30 days.</p>
        <table>
            <thead>
                <tr><th>#</th><th>Name</th><th>Email</th><th>Phone</th><th>Branch</th><th>Registered</th></tr>
            </thead>
            <tbody>
                <?php $i = 1; foreach ($new_members as $m): ?>
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td><strong><?php echo htmlspecialchars($m['full_name']); ?></strong></td>
                    <td style="font-size:13px;"><?php echo htmlspecialchars($m['email']); ?></td>
                    <td><?php echo htmlspecialchars($m['phone'] ?? '-'); ?></td>
                    <td><?php echo htmlspecialchars($m['branch_name'] ?? '-'); ?></td>
                    <td style="font-size:13px;"><?php echo date('d M Y', strtotime($m['created_at'])); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php require_once 'views/footer.php'; ?>
