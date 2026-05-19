<?php
// ============================================================
// views/librarian/manage_fines.php
// Librarian views all fines, marks paid, and issues manual fines
// ============================================================

require_once 'config/Connect.php';
require_once 'config/Close.php';
require_once 'models/ReviewModel.php';
require_once 'models/BorrowModel.php';

// Handle mark as paid action (GET)
if (isset($_GET['action']) && $_GET['action'] == 'paid' && isset($_GET['id'])) {
    $conn = connect();
    markFinePaid($conn, (int)$_GET['id']);
    closeConn($conn);
    $_SESSION['msg'] = "Fine marked as paid.";
    header("Location: index.php?page=manage_fines");
    exit();
}

// Handle manual fine creation (POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'manual_fine') {
    $member_id = (int)$_POST['member_id'];
    $borrow_id = (int)$_POST['borrow_id'];
    $amount    = (float)$_POST['amount'];
    $reason    = htmlspecialchars(trim($_POST['reason']));

    if ($member_id <= 0 || $amount <= 0 || empty($reason)) {
        $_SESSION['error'] = "Please fill all fields correctly.";
        header("Location: index.php?page=manage_fines");
        exit();
    }

    $conn   = connect();
    $result = createFine($conn, $borrow_id, $member_id, $amount, $reason);
    closeConn($conn);

    $_SESSION['msg'] = $result ? "Manual fine issued successfully." : "Failed to issue fine.";
    header("Location: index.php?page=manage_fines");
    exit();
}

$conn       = connect();
$fines      = getAllFines($conn);
$branch_id  = $_SESSION['branch_id'];

// Get active borrows for this branch to select from
$sql    = "SELECT br.id, br.member_id, u.full_name, b.title
           FROM borrow_records br
           JOIN users u ON br.member_id = u.id
           JOIN books b ON br.book_id = b.id
           WHERE br.branch_id = ? AND br.status = 'active'
           ORDER BY u.full_name ASC";
$stmt   = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $branch_id);
mysqli_stmt_execute($stmt);
$result         = mysqli_stmt_get_result($stmt);
$active_borrows = array();
while ($row = mysqli_fetch_assoc($result)) {
    $active_borrows[] = $row;
}

closeConn($conn);

$total_unpaid = 0;
$total_paid   = 0;
foreach ($fines as $f) {
    if ($f['status'] == 'unpaid' || $f['status'] == 'pending_payment') $total_unpaid += $f['amount'];
    else $total_paid += $f['amount'];
}

require_once 'views/header.php';
?>

<h1 class="page-title">&#128181; Manage Fines</h1>

<?php if (isset($_SESSION['msg'])): ?>
    <div class="alert-success"><?php echo $_SESSION['msg']; unset($_SESSION['msg']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['error'])): ?>
    <div class="alert-error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
<?php endif; ?>

<!-- Stats -->
<div class="card-row" style="margin-bottom:24px;">
    <div class="card red">
        <h3>Total Unpaid (Tk)</h3>
        <div class="stat-number"><?php echo number_format($total_unpaid, 2); ?></div>
    </div>
    <div class="card green">
        <h3>Total Collected (Tk)</h3>
        <div class="stat-number"><?php echo number_format($total_paid, 2); ?></div>
    </div>
</div>

<!-- Manual Fine Form -->
<div class="section-box" style="margin-bottom:24px;">
    <h3>&#9998; Issue Manual Fine (Damaged / Lost Book)</h3>
    <p style="font-size:13px; color:#666; margin-bottom:16px;">Use this to issue a fine for a damaged or lost book outside of the overdue system.</p>

    <form action="index.php?page=manage_fines" method="POST" style="max-width:500px;">
        <input type="hidden" name="action" value="manual_fine">

        <div class="form-group">
            <label>Select Active Borrow Record:</label>
            <select name="borrow_id" id="borrow_select" onchange="fillMember(this)" required>
                <option value="">-- Select Member & Book --</option>
                <?php foreach ($active_borrows as $ab): ?>
                    <option value="<?php echo $ab['id']; ?>"
                            data-member="<?php echo $ab['member_id']; ?>">
                        <?php echo htmlspecialchars($ab['full_name'] . ' — ' . $ab['title']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <input type="hidden" name="member_id" id="member_id_field" value="0">

        <div class="form-group">
            <label>Fine Amount (Tk):</label>
            <input type="number" name="amount" min="1" step="0.01" placeholder="e.g. 200" required>
        </div>

        <div class="form-group">
            <label>Reason:</label>
            <input type="text" name="reason" placeholder="e.g. Book damaged, Book lost" required>
        </div>

        <button type="submit" class="btn btn-danger">Issue Fine</button>
    </form>
</div>

<!-- Fines Table -->
<div class="table-container">
    <?php if (empty($fines)): ?>
        <p style="color:#888;">No fines recorded.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Member</th>
                    <th>Book Title</th>
                    <th>Amount (Tk)</th>
                    <th>Reason</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php $i = 1; foreach ($fines as $f): ?>
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td><?php echo htmlspecialchars($f['full_name']); ?><br>
                        <small style="color:#888;"><?php echo htmlspecialchars($f['email']); ?></small></td>
                    <td><?php echo htmlspecialchars($f['book_title']); ?></td>
                    <td style="font-weight:bold; color:#dc3545;"><?php echo number_format($f['amount'], 2); ?></td>
                    <td><?php echo htmlspecialchars($f['reason']); ?></td>
                    <td>
                        <?php
                        $badge = 'badge-unpaid';
                        if ($f['status'] == 'paid') $badge = 'badge-returned';
                        if ($f['status'] == 'pending_payment') $badge = 'badge-pending';
                        ?>
                        <span class="badge <?php echo $badge; ?>">
                            <?php echo strtoupper(str_replace('_', ' ', $f['status'])); ?>
                        </span>
                    </td>
                    <td><?php echo date('d M Y', strtotime($f['created_at'])); ?></td>
                    <td>
                        <?php if ($f['status'] == 'unpaid' || $f['status'] == 'pending_payment'): ?>
                            <a href="index.php?page=manage_fines&action=paid&id=<?php echo $f['id']; ?>"
                               class="btn btn-success btn-sm"
                               onclick="return confirm('Mark this fine as paid?')">Mark Paid</a>
                        <?php else: ?>
                            <span style="color:#28a745; font-size:12px;">&#10003; Paid</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<script>
function fillMember(select) {
    var selected = select.options[select.selectedIndex];
    document.getElementById('member_id_field').value = selected.getAttribute('data-member') || 0;
}
</script>

<?php require_once 'views/footer.php'; ?>