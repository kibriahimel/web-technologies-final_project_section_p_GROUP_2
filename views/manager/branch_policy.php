<?php
// ============================================================
// views/manager/branch_policy.php
// Configure borrowing rules for a specific branch
// ============================================================

require_once 'config/Connect.php';
require_once 'config/Close.php';
require_once 'models/BranchModel.php';

$branch_id = isset($_GET['id']) ? (int)$_GET['id'] : $_SESSION['branch_id'];
$conn      = connect();
$branch    = getBranchById($conn, $branch_id);
$policy    = getBranchPolicy($conn, $branch_id);
closeConn($conn);

require_once 'views/header.php';
?>

<h1 class="page-title"> Branch Policy</h1>
<p style="color:#666; margin-bottom:20px;">
    Configuring policy for: <strong><?php echo htmlspecialchars($branch['branch_name'] ?? 'Unknown Branch'); ?></strong>
</p>

<div class="form-box" style="max-width:500px;">

    <form action="index.php?page=do_branch&action=policy" method="POST">

        <input type="hidden" name="branch_id" value="<?php echo $branch_id; ?>">

        <div class="form-group">
            <label>Maximum Borrow Days </label>
            <input type="number" name="max_borrow_days"
                   value="<?php echo $policy['max_borrow_days'] ?? 14; ?>"
                   min="1" max="60" required>
            <small style="color:#888; font-size:12px;">How many days a member can keep a borrowed book.</small>
        </div>

        <div class="form-group">
            <label>Maximum Books Per Member </label>
            <input type="number" name="max_books_per_member"
                   value="<?php echo $policy['max_books_per_member'] ?? 5; ?>"
                   min="1" max="20" required>
            <small style="color:#888; font-size:12px;">Maximum books one member can borrow at a time.</small>
        </div>

        <div class="form-group">
            <label>Fine Per Day (Tk) </label>
            <input type="number" name="fine_per_day" step="0.50"
                   value="<?php echo $policy['fine_per_day'] ?? 5.00; ?>"
                   min="0" required>
            <small style="color:#888; font-size:12px;">Fine charged per day for overdue books.</small>
        </div>

        <div class="form-group">
            <label>Reservation Expiry Days </label>
            <input type="number" name="reservation_days"
                   value="<?php echo $policy['reservation_days'] ?? 3; ?>"
                   min="1" max="14" required>
            <small style="color:#888; font-size:12px;">How many days a reservation stays active.</small>
        </div>

        <div style="display:flex; gap:10px;">
            <button type="submit" class="btn btn-warning">Save Policy</button>
            <a href="index.php?page=manage_branches" class="btn btn-secondary">Back</a>
        </div>

    </form>
</div>

<?php require_once 'views/footer.php'; ?>
