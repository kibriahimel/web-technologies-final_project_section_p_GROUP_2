<?php
// ============================================================
// views/member/my_fines.php
// Shows member's fines with payment intent button
// ============================================================
require_once 'config/Connect.php';
require_once 'config/Close.php';
require_once 'models/BorrowModel.php';

$conn  = connect();

// Handle payment intent action
if (isset($_GET['action']) && $_GET['action'] == 'pay_intent' && isset($_GET['id'])) {
    $fine_id = (int)$_GET['id'];
    $user_id = $_SESSION['user'];
    $sql     = "UPDATE fines SET status='pending_payment' WHERE id=? AND user_id=? AND status='unpaid'";
    $stmt    = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $fine_id, $user_id);
    mysqli_stmt_execute($stmt);
    $_SESSION['msg'] = "Payment intent submitted! A librarian will confirm your payment shortly.";
    closeConn($conn);
    header("Location: index.php?page=my_fines");
    exit();
}

$fines = getFinesByUser($conn, $_SESSION['user']);
closeConn($conn);

$total_unpaid = 0;
foreach ($fines as $f) {
    if ($f['status'] == 'unpaid' || $f['status'] == 'pending_payment') {
        $total_unpaid += $f['amount'];
    }
}

require_once 'views/header.php';
?>

<h1 class="page-title">&#128181; My Fines</h1>

<?php if ($total_unpaid > 0): ?>
    <div class="alert-error">
        You have <strong>Tk <?php echo number_format($total_unpaid, 2); ?></strong> in unpaid fines. Please pay at the library counter.
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['msg'])): ?>
    <div class="alert-success"><?php echo $_SESSION['msg']; unset($_SESSION['msg']); ?></div>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert-error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
<?php endif; ?>

<div class="table-container">
    <?php if (empty($fines)): ?>
        <p style="color:#888;">&#10003; You have no fines. Great job returning books on time!</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Book Title</th>
                    <th>Amount (Tk)</th>
                    <th>Reason</th>
                    <th>Status</th>
                    <th>Paid At</th>
                    <th>Date Created</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php $i = 1; foreach ($fines as $f): ?>
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td><?php echo htmlspecialchars($f['book_title']); ?></td>
                    <td style="color:#dc3545; font-weight:bold;"><?php echo number_format($f['amount'], 2); ?></td>
                    <td><?php echo htmlspecialchars($f['reason']); ?></td>
                    <td>
                        <?php
                        $badge_color = 'badge-unpaid';
                        if ($f['status'] == 'paid') $badge_color = 'badge-returned';
                        if ($f['status'] == 'pending_payment') $badge_color = 'badge-pending';
                        ?>
                        <span class="badge <?php echo $badge_color; ?>">
                            <?php echo strtoupper(str_replace('_', ' ', $f['status'])); ?>
                        </span>
                    </td>
                    <td><?php echo $f['paid_at'] ? date('d M Y', strtotime($f['paid_at'])) : '-'; ?></td>
                    <td><?php echo date('d M Y', strtotime($f['created_at'])); ?></td>
                    <td>
                        <?php if ($f['status'] == 'unpaid'): ?>
                            <a href="index.php?page=my_fines&action=pay_intent&id=<?php echo $f['id']; ?>"
                               class="btn btn-primary btn-sm"
                               onclick="return confirm('Confirm that you have paid this fine at the library counter?')">
                               &#128178; I Have Paid
                            </a>
                        <?php elseif ($f['status'] == 'pending_payment'): ?>
                            <span style="color:orange; font-size:12px;">&#9203; Awaiting librarian confirmation</span>
                        <?php elseif ($f['status'] == 'paid'): ?>
                            <span style="color:green; font-size:12px;">&#10003; Confirmed</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php require_once 'views/footer.php'; ?>