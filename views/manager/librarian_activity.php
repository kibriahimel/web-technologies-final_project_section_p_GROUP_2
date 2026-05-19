<?php
// ============================================================
// views/manager/librarian_activity.php
// Librarian activity report per branch
// ============================================================

require_once 'config/Connect.php';
require_once 'config/Close.php';
require_once 'models/BranchModel.php';

$conn        = connect();
$librarians  = getLibrarianActivityByBranch($conn);
closeConn($conn);

require_once 'views/header.php';
?>

<h1 class="page-title">&#128203; Librarian Activity Report</h1>
<p style="color:#666; margin-bottom:24px;">Borrow transactions processed by each librarian, grouped by branch.</p>

<div class="section-box">
    <?php if (empty($librarians)): ?>
        <p style="color:#888;">No librarian data found.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Librarian</th>
                    <th>Email</th>
                    <th>Branch</th>
                    <th>Borrows Processed</th>
                    <th>Account Status</th>
                </tr>
            </thead>
            <tbody>
                <?php $i = 1; $current_branch = ''; foreach ($librarians as $lib): ?>
                    <?php if ($lib['branch_name'] !== $current_branch): ?>
                        <tr style="background:#f0f4ff;">
                            <td colspan="6" style="font-weight:bold; color:#4a90e2; padding:10px 12px;">
                                &#127970; <?php echo htmlspecialchars($lib['branch_name'] ?? 'Unassigned'); ?>
                            </td>
                        </tr>
                        <?php $current_branch = $lib['branch_name']; ?>
                    <?php endif; ?>
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td><strong><?php echo htmlspecialchars($lib['full_name']); ?></strong></td>
                    <td style="font-size:13px;"><?php echo htmlspecialchars($lib['email']); ?></td>
                    <td><?php echo htmlspecialchars($lib['branch_name'] ?? '—'); ?></td>
                    <td style="font-weight:bold; font-size:18px; color:#4a90e2;"><?php echo (int)$lib['borrows_processed']; ?></td>
                    <td><span class="badge badge-<?php echo $lib['status']; ?>"><?php echo strtoupper($lib['status']); ?></span></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php require_once 'views/footer.php'; ?>
