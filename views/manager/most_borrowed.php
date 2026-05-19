<?php
// ============================================================
// views/manager/most_borrowed.php
// Most borrowed books across all managed branches
// ============================================================

require_once 'config/Connect.php';
require_once 'config/Close.php';
require_once 'models/BranchModel.php';

$conn  = connect();
$books = getMostBorrowedBooks($conn, 20);
closeConn($conn);

require_once 'views/header.php';
?>

<h1 class="page-title">&#128218; Most Borrowed Books</h1>
<p style="color:#666; margin-bottom:24px;">Top borrowed books across all branches.</p>

<div class="section-box">
    <?php if (empty($books)): ?>
        <p style="color:#888;">No borrow records found.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Rank</th>
                    <th>Title</th>
                    <th>Author</th>
                    <th>Genre</th>
                    <th>Total Borrows</th>
                    <th>Borrowed From Branches</th>
                </tr>
            </thead>
            <tbody>
                <?php $rank = 1; foreach ($books as $b): ?>
                <tr>
                    <td>
                        <?php if ($rank == 1): ?>
                            <span style="font-size:20px;">&#127941;</span>
                        <?php elseif ($rank == 2): ?>
                            <span style="font-size:20px;">&#129352;</span>
                        <?php elseif ($rank == 3): ?>
                            <span style="font-size:20px;">&#129353;</span>
                        <?php else: ?>
                            #<?php echo $rank; ?>
                        <?php endif; ?>
                    </td>
                    <td><strong><?php echo htmlspecialchars($b['title']); ?></strong></td>
                    <td><?php echo htmlspecialchars($b['author']); ?></td>
                    <td><?php echo htmlspecialchars($b['genre_name'] ?? '-'); ?></td>
                    <td style="font-weight:bold; font-size:18px; color:#4a90e2;"><?php echo (int)$b['borrow_count']; ?></td>
                    <td style="font-size:13px; color:#555;"><?php echo htmlspecialchars($b['branches']); ?></td>
                </tr>
                <?php $rank++; endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php require_once 'views/footer.php'; ?>
