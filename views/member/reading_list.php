<?php
// ============================================================
// views/member/reading_list.php
// Shows and manages member's personal reading list
// Handles add/remove actions via GET then reloads
// ============================================================

session_start();
require_once 'config/Connect.php';
require_once 'config/Close.php';
require_once 'models/ReviewModel.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] != 'member') {
    header("Location: index.php?page=login");
    exit();
}

$conn    = connect();
$user_id = $_SESSION['user'];

// Handle add action (GET)
if (isset($_GET['action']) && $_GET['action'] == 'add' && isset($_GET['book_id'])) {
    $book_id = (int)$_GET['book_id'];
    if (!isInReadingList($conn, $user_id, $book_id)) {
        addToReadingList($conn, $user_id, $book_id);
        $_SESSION['msg'] = "Book added to your reading list!";
    } else {
        $_SESSION['error'] = "This book is already in your reading list.";
    }
    closeConn($conn);
    header("Location: index.php?page=reading_list");
    exit();
}

// Handle remove action (GET)
if (isset($_GET['action']) && $_GET['action'] == 'remove' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    removeFromReadingList($conn, $id, $user_id);
    $_SESSION['msg'] = "Book removed from reading list.";
    closeConn($conn);
    header("Location: index.php?page=reading_list");
    exit();
}

$reading_list = getReadingList($conn, $user_id);
closeConn($conn);

require_once 'views/header.php';
?>

<h1 class="page-title">&#128221; My Reading List</h1>
<p style="color:#666; margin-bottom:20px;">Keep track of books you want to read next.</p>

<?php if (empty($reading_list)): ?>
    <div class="section-box">
        <p style="color:#888;">Your reading list is empty. <a href="index.php?page=browse_books">Browse books</a> and click &ldquo;+ Reading List&rdquo; to add books.</p>
    </div>
<?php else: ?>
    <div class="book-grid">
        <?php foreach ($reading_list as $item): ?>
            <div class="book-card">
                <img src="uploads/<?php echo htmlspecialchars($item['cover_image']); ?>"
                     alt="<?php echo htmlspecialchars($item['title']); ?>"
                     onerror="this.src='uploads/no_cover.png'">
                <h4><?php echo htmlspecialchars($item['title']); ?></h4>
                <p><?php echo htmlspecialchars($item['author']); ?></p>
                <p style="font-size:11px; color:#aaa;">Added: <?php echo date('d M Y', strtotime($item['added_at'])); ?></p>
                <div style="display:flex; gap:6px; justify-content:center; margin-top:6px;">
                    <a href="index.php?page=book_detail&id=<?php echo $item['book_id']; ?>" class="btn btn-primary btn-sm">View</a>
                    <a href="index.php?page=reading_list&action=remove&id=<?php echo $item['id']; ?>"
                       class="btn btn-danger btn-sm"
                       onclick="return confirmDelete('Remove from reading list?')">Remove</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require_once 'views/footer.php'; ?>
