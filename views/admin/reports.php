<?php
// ============================================================
// views/admin/reports.php
// Platform-wide reports for admin
// ============================================================

require_once 'config/Connect.php';
require_once 'config/Close.php';
require_once 'models/BorrowModel.php';
require_once 'models/ReviewModel.php';
require_once 'models/BookModel.php';

$conn = connect();

// Full borrow records
$all_borrows = getAllBorrowRecords($conn);

// Fine totals per branch
$sql_branch_fines = "
    SELECT bn.branch_name,
           COUNT(f.id) AS fine_count,
           SUM(f.amount) AS total_amount,
           SUM(CASE WHEN f.status='unpaid' THEN f.amount ELSE 0 END) AS unpaid,
           SUM(CASE WHEN f.status='paid'   THEN f.amount ELSE 0 END) AS paid
    FROM fines f
    JOIN borrow_records br ON f.borrow_id = br.id
    JOIN branches bn ON br.branch_id = bn.id
    GROUP BY bn.id, bn.branch_name
";
$branch_fines = array();
$result = mysqli_query($conn, $sql_branch_fines);
while ($row = mysqli_fetch_assoc($result)) {
    $branch_fines[] = $row;
}

// Top borrowed books
$sql_top = "
    SELECT b.title, b.author, COUNT(br.id) AS borrow_count
    FROM borrow_records br
    JOIN books b ON br.book_id = b.id
    GROUP BY b.id, b.title, b.author
    ORDER BY borrow_count DESC
    LIMIT 10
";
$top_books = array();
$result = mysqli_query($conn, $sql_top);
while ($row = mysqli_fetch_assoc($result)) {
    $top_books[] = $row;
}

// Genre popularity
$sql_genre = "
    SELECT g.genre_name, COUNT(b.id) AS book_count
    FROM books b
    LEFT JOIN genres g ON b.genre_id = g.id
    GROUP BY g.id, g.genre_name
    ORDER BY book_count DESC
";
$genre_stats = array();
$result = mysqli_query($conn, $sql_genre);
while ($row = mysqli_fetch_assoc($result)) {
    $genre_stats[] = $row;
}

// Overall counts
$total_borrows  = count($all_borrows);
$total_returned = 0;
$total_active   = 0;
$total_overdue  = 0;
foreach ($all_borrows as $b) {
    if ($b['status'] == 'returned') $total_returned++;
    if ($b['status'] == 'approved') $total_active++;
    if ($b['status'] == 'overdue')  $total_overdue++;
}

$all_fines = getAllFines($conn);
$grand_unpaid = 0;
$grand_paid   = 0;
foreach ($all_fines as $f) {
    if ($f['status'] == 'unpaid') $grand_unpaid += $f['amount'];
    else                           $grand_paid   += $f['amount'];
}

closeConn($conn);
require_once 'views/header.php';
?>

<h1 class="page-title">&#128202; Platform Reports</h1>

<!-- Summary Cards -->
<div class="card-row">
    <div class="card">
        <h3>Total Borrows</h3>
        <div class="stat-number"><?php echo $total_borrows; ?></div>
    </div>
    <div class="card green">
        <h3>Returned</h3>
        <div class="stat-number"><?php echo $total_returned; ?></div>
    </div>
    <div class="card orange">
        <h3>Active</h3>
        <div class="stat-number"><?php echo $total_active; ?></div>
    </div>
    <div class="card red">
        <h3>Overdue</h3>
        <div class="stat-number"><?php echo $total_overdue; ?></div>
    </div>
</div>

<div class="card-row">
    <div class="card red">
        <h3>Unpaid Fines (Tk)</h3>
        <div class="stat-number"><?php echo number_format($grand_unpaid, 0); ?></div>
    </div>
    <div class="card green">
        <h3>Fines Collected (Tk)</h3>
        <div class="stat-number"><?php echo number_format($grand_paid, 0); ?></div>
    </div>
</div>

<!-- Top Borrowed Books -->
<div class="section-box">
    <h3>&#128218; Top 10 Most Borrowed Books</h3>
    <?php if (empty($top_books)): ?>
        <p style="color:#888;">No borrow data available.</p>
    <?php else: ?>
        <table>
            <thead><tr><th>#</th><th>Title</th><th>Author</th><th>Times Borrowed</th></tr></thead>
            <tbody>
                <?php $i = 1; foreach ($top_books as $bk): ?>
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td><?php echo htmlspecialchars($bk['title']); ?></td>
                    <td><?php echo htmlspecialchars($bk['author']); ?></td>
                    <td style="font-weight:bold; color:#2c6e9e;"><?php echo $bk['borrow_count']; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<!-- Fine Stats by Branch -->
<?php if (!empty($branch_fines)): ?>
<div class="section-box">
    <h3>&#128181; Fine Statistics by Branch</h3>
    <table>
        <thead><tr><th>Branch</th><th>Total Fines</th><th>Total Amount (Tk)</th><th>Unpaid (Tk)</th><th>Collected (Tk)</th></tr></thead>
        <tbody>
            <?php foreach ($branch_fines as $bf): ?>
            <tr>
                <td><?php echo htmlspecialchars($bf['branch_name']); ?></td>
                <td><?php echo $bf['fine_count']; ?></td>
                <td style="font-weight:bold;"><?php echo number_format($bf['total_amount'], 2); ?></td>
                <td style="color:#dc3545;"><?php echo number_format($bf['unpaid'], 2); ?></td>
                <td style="color:#28a745;"><?php echo number_format($bf['paid'], 2); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<!-- Genre Stats -->
<div class="section-box">
    <h3>&#127991; Books by Genre</h3>
    <table>
        <thead><tr><th>#</th><th>Genre</th><th>Number of Books</th></tr></thead>
        <tbody>
            <?php $i = 1; foreach ($genre_stats as $gs): ?>
            <tr>
                <td><?php echo $i++; ?></td>
                <td><?php echo htmlspecialchars($gs['genre_name'] ?? 'Uncategorized'); ?></td>
                <td><?php echo $gs['book_count']; ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Full Borrow Records -->
<div class="section-box">
    <h3>&#128218; All Borrow Records</h3>
    <table>
        <thead>
            <tr><th>#</th><th>Member</th><th>Book</th><th>Branch</th><th>Borrow Date</th><th>Due Date</th><th>Status</th></tr>
        </thead>
        <tbody>
            <?php $i = 1; foreach ($all_borrows as $b): ?>
            <tr>
                <td><?php echo $i++; ?></td>
                <td><?php echo htmlspecialchars($b['full_name']); ?></td>
                <td><?php echo htmlspecialchars($b['title']); ?></td>
                <td><?php echo htmlspecialchars($b['branch_name']); ?></td>
                <td><?php echo $b['borrow_date'] ? date('d M Y', strtotime($b['borrow_date'])) : '-'; ?></td>
                <td><?php echo $b['due_date'] ? date('d M Y', strtotime($b['due_date'])) : '-'; ?></td>
                <td><span class="badge badge-<?php echo $b['status']; ?>"><?php echo strtoupper($b['status']); ?></span></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once 'views/footer.php'; ?>
