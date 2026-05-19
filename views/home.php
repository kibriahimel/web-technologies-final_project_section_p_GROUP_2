<?php
// ============================================================
// views/home.php
// Public home page - no login required
// ============================================================

require_once 'config/Connect.php';
require_once 'config/Close.php';
require_once 'models/BookModel.php';
require_once 'models/BranchModel.php';

$conn         = connect();
$recent_books = getAllBooks($conn);
$recent_books = array_slice($recent_books, 0, 6); // Show latest 6
$announcements = getAnnouncements($conn);
$announcements = array_slice($announcements, 0, 3); // Show latest 3
closeConn($conn);

require_once 'views/header.php';
?>

<!-- Hero Section -->
<div style="background: linear-gradient(135deg, #1a3c5e, #2c6e9e); color:white; padding: 50px 30px; border-radius: 10px; text-align:center; margin-bottom: 30px;">
    <h1 style="font-size: 32px; margin-bottom: 12px;">&#128218; Welcome to Book Stories </h1>
    <p style="font-size: 16px; margin-bottom: 20px; color: #cce0f5;">A Multi-Branch Digital Library Platform</p>
    <?php if (!isset($_SESSION['user'])): ?>
        <a href="index.php?page=register" class="btn btn-warning" style="margin-right:10px;">Register Now</a>
        <a href="index.php?page=login" class="btn btn-success">Login</a>
    <?php else: ?>
        <a href="index.php?page=browse_books" class="btn btn-warning">Browse Books</a>
    <?php endif; ?>
</div>

<!-- Latest Announcements -->
<?php if (!empty($announcements)): ?>
<div class="section-box">
    <h3>&#128226; Latest Announcements</h3>
    <?php foreach ($announcements as $ann): ?>
        <div style="border-bottom: 1px solid #eee; padding: 10px 0;">
            <strong><?php echo htmlspecialchars($ann['title']); ?></strong>
            <span style="font-size:12px; color:#888; margin-left:10px;"><?php echo date('d M Y', strtotime($ann['created_at'])); ?></span>
            <p style="font-size:13px; color:#555; margin-top:4px;"><?php echo htmlspecialchars($ann['content']); ?></p>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

  
<!--<h2 class="page-title">Recently Added Books</h2>
<div class="book-grid">
    <?php foreach ($recent_books as $book): ?>
        <div class="book-card">
            <img src="uploads/<?php echo htmlspecialchars($book['cover_image']); ?>"
                 alt="<?php echo htmlspecialchars($book['title']); ?>"
                 onerror="this.src='uploads/no_cover.png'">
            <h4><?php echo htmlspecialchars($book['title']); ?></h4>
            <p><?php echo htmlspecialchars($book['author']); ?></p>
            <p style="color:#2c6e9e;"><?php echo htmlspecialchars($book['genre_name'] ?? 'Unknown'); ?></p>
            <a href="index.php?page=book_detail&id=<?php echo $book['id']; ?>" class="btn btn-primary btn-sm">View</a>
        </div>
    <?php endforeach; ?>
</div>-->

<!-- Features Section -->
<div class="card-row" style="margin-top:40px;">
    <div class="card">
        <h3>&#128218; Borrow Books</h3>
        <p style="font-size:13px; color:#666; margin-top:8px;">Request to borrow books from any branch</p>
    </div>
    <div class="card orange">
        <h3>&#128203; Reserve Books</h3>
        <p style="font-size:13px; color:#666; margin-top:8px;">Reserve books for up to 3 days</p>
    </div>
    <div class="card green">
        <h3>&#9733; Write Reviews</h3>
        <p style="font-size:13px; color:#666; margin-top:8px;">Rate and review your reads</p>
    </div>
    <div class="card purple">
        <h3>&#128221; Reading List</h3>
        <p style="font-size:13px; color:#666; margin-top:8px;">Keep a personal reading wishlist</p>
    </div>
</div>

<?php require_once 'views/footer.php'; ?>
