<?php
// ============================================================
// views/member/my_reservations.php
// Shows member's book reservations
// ============================================================

require_once 'config/Connect.php';
require_once 'config/Close.php';
require_once 'models/ReviewModel.php';

$conn         = connect();
$reservations = getReservationsByUser($conn, $_SESSION['user']);
closeConn($conn);

require_once 'views/header.php';
?>

<h1 class="page-title">&#128203; My Reservations</h1>

<div class="table-container">
    <?php if (empty($reservations)): ?>
        <p style="color:#888;">You have no reservations. <a href="index.php?page=browse_books">Browse books</a> to reserve one.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Book Title</th>
                    <th>Author</th>
                    <th>Branch</th>
                    <th>Reserved On</th>
                    <th>Expires On</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php $i = 1; foreach ($reservations as $r): ?>
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td><?php echo htmlspecialchars($r['title']); ?></td>
                    <td><?php echo htmlspecialchars($r['author']); ?></td>
                    <td><?php echo htmlspecialchars($r['branch_name']); ?></td>
                    <td><?php echo date('d M Y', strtotime($r['reservation_date'])); ?></td>
                    <td><?php echo $r['expiry_date'] ? date('d M Y', strtotime($r['expiry_date'])) : '-'; ?></td>
                    <td><span class="badge badge-<?php echo $r['status']; ?>"><?php echo strtoupper($r['status']); ?></span></td>
                    <td>
                        <?php if ($r['status'] == 'pending' || $r['status'] == 'active'): ?>
                            <a href="index.php?page=do_reservation&action=cancel&id=<?php echo $r['id']; ?>"
                               class="btn btn-danger btn-sm"
                               onclick="return confirmDelete('Cancel this reservation?')">Cancel</a>
                        <?php else: ?>
                            <span style="color:#999; font-size:12px;">N/A</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php require_once 'views/footer.php'; ?>
