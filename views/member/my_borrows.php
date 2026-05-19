<?php
// ============================================================
// views/member/my_borrows.php
// Shows member's borrow history with days remaining and renew
// ============================================================
require_once 'config/Connect.php';
require_once 'config/Close.php';
require_once 'models/BorrowModel.php';

$conn    = connect();
$borrows = getBorrowsByUser($conn, $_SESSION['user']);
closeConn($conn);

require_once 'views/header.php';
?>

<h1 class="page-title">&#128218; My Borrow History</h1>

<div class="table-container">
    <?php if (empty($borrows)): ?>
        <p style="color:#888;">You have no borrow records. <a href="index.php?page=browse_books">Browse books</a></p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Book Title</th>
                    <th>Author</th>
                    <th>Branch</th>
                    <th>Borrow Date</th>
                    <th>Due Date</th>
                    <th>Return Date</th>
                    <th>Status</th>
                    <th>Days Left</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php $i = 1; foreach ($borrows as $b): ?>
                <?php
                    // Check if overdue for row highlighting
                    $is_overdue = false;
                    if ($b['status'] == 'active' && $b['due_date']) {
                        $today     = strtotime(date('Y-m-d'));
                        $due       = strtotime($b['due_date']);
                        $is_overdue = $today > $due;
                    }
                ?>
                <tr style="<?php echo $is_overdue ? 'background-color:#fff0f0;' : ''; ?>">
                    <td><?php echo $i++; ?></td>
                    <td><?php echo htmlspecialchars($b['title']); ?></td>
                    <td><?php echo htmlspecialchars($b['author']); ?></td>
                    <td><?php echo htmlspecialchars($b['branch_name']); ?></td>
                    <td><?php echo $b['borrow_date'] ? date('d M Y', strtotime($b['borrow_date'])) : 'Pending'; ?></td>
                    <td style="<?php echo $is_overdue ? 'color:red; font-weight:bold;' : ''; ?>">
                        <?php echo $b['due_date'] ? date('d M Y', strtotime($b['due_date'])) : '-'; ?>
                    </td>
                    <td><?php echo $b['return_date'] ? date('d M Y', strtotime($b['return_date'])) : '-'; ?></td>
                    <td><span class="badge badge-<?php echo $b['status']; ?>"><?php echo strtoupper($b['status']); ?></span></td>
                    <td>
                        <?php
                        if ($b['status'] == 'active' && $b['due_date']) {
                            $days_left = (int)((strtotime($b['due_date']) - strtotime(date('Y-m-d'))) / 86400);
                            if ($days_left < 0) {
                                echo '<span style="color:red; font-weight:bold;">Overdue by ' . abs($days_left) . ' day(s)</span>';
                            } elseif ($days_left == 0) {
                                echo '<span style="color:orange; font-weight:bold;">Due Today</span>';
                            } else {
                                echo '<span style="color:green;">' . $days_left . ' day(s)</span>';
                            }
                        } else {
                            echo '-';
                        }
                        ?>
                    </td>
                    <td>
                        <?php if ($b['status'] == 'active'): ?>
                            <a href="index.php?page=do_borrow&action=renew&id=<?php echo $b['id']; ?>"
                               class="btn btn-primary btn-sm"
                               onclick="return confirm('Renew this loan? Due date will be extended.')">
                               Renew
                            </a>
                        <?php else: ?>
                            <span style="color:#999; font-size:12px;">-</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php require_once 'views/footer.php'; ?>
<div id="book_results">
    <!-- AJAX replaces this content dynamically -->
</div>