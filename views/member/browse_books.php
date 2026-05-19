<?php
// ============================================================
// views/member/browse_books.php
// Browse all books with AJAX search
// ============================================================

require_once 'config/Connect.php';
require_once 'config/Close.php';
require_once 'models/BookModel.php';

$conn  = connect();
$books = getAllBooks($conn);
closeConn($conn);

require_once 'views/header.php';
?>

<h1 class="page-title">&#128270; Browse Books</h1>

<!-- AJAX Search Bar -->
<div class="search-bar">
    <input type="text" id="search_input"
           placeholder="Search by title, author or ISBN..."
           oninput="searchBooksAjax()">
    <button class="btn btn-primary" onclick="searchBooksAjax()">Search</button>
    <button class="btn btn-secondary" onclick="document.getElementById('search_input').value=''; loadAllBooks();">Clear</button>
</div>

<p id="search-result-msg">Showing all <strong><?php echo count($books); ?></strong> books.</p>

<!-- Initial book grid (loaded by PHP, then replaced by AJAX) -->
<div id="book_results">
    <div class="book-grid">
        <?php foreach ($books as $book): ?>
            <div class="book-card">
                <img src="uploads/<?php echo htmlspecialchars($book['cover_image']); ?>"
                     alt="<?php echo htmlspecialchars($book['title']); ?>"
                     onerror="this.src='uploads/no_cover.png'">
                <h4><?php echo htmlspecialchars($book['title']); ?></h4>
                <p><?php echo htmlspecialchars($book['author']); ?></p>
                <p style="color:#2c6e9e; font-size:12px;"><?php echo htmlspecialchars($book['genre_name'] ?? 'Unknown'); ?></p>
                <a href="index.php?page=book_detail&id=<?php echo $book['id']; ?>" class="btn btn-primary btn-sm">View Details</a>
            </div>
        <?php endforeach; ?>
        <?php if (empty($books)): ?>
            <p style="color:#888;">No books found in the library.</p>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'views/footer.php'; ?>
