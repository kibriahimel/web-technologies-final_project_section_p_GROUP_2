<?php
// ============================================================
// views/librarian/edit_book.php
// Pre-filled form to edit a book – submits POST to do_update_book
// Uses GET: ?page=edit_book&id=N to load this view
// ============================================================

require_once 'config/Connect.php';
require_once 'config/Close.php';
require_once 'models/BookModel.php';

$id   = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$conn = connect();
$book = getBookById($conn, $id);

if (!$book) {
    $_SESSION['error'] = "Book not found.";
    closeConn($conn);
    header("Location: index.php?page=book_list");
    exit();
}

$genres = getAllGenres($conn);
closeConn($conn);

require_once 'views/header.php';
?>

<h1 class="page-title">&#9998; Edit Book</h1>

<div class="form-box" style="max-width:700px;">
    <form action="index.php?page=do_update_book" method="POST" onsubmit="return validateBookForm();">

        <!-- Hidden book ID -->
        <input type="hidden" name="id" value="<?php echo $book['id']; ?>">

        <div style="display:flex; gap:20px; flex-wrap:wrap;">
            <div style="flex:1; min-width:240px;">

                <div class="form-group">
                    <label>Book Title *</label>
                    <input type="text" name="title" id="title" value="<?php echo htmlspecialchars($book['title']); ?>" required>
                </div>

                <div class="form-group">
                    <label>Author *</label>
                    <input type="text" name="author" id="author" value="<?php echo htmlspecialchars($book['author']); ?>" required>
                </div>

                <div class="form-group">
                    <label>ISBN</label>
                    <input type="text" name="isbn" value="<?php echo htmlspecialchars($book['isbn'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label>Genre</label>
                    <select name="genre_id">
                        <option value="">-- Select Genre --</option>
                        <?php foreach ($genres as $g): ?>
                            <option value="<?php echo $g['id']; ?>"
                                <?php echo $g['id'] == $book['genre_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($g['genre_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

            </div>

            <div style="flex:1; min-width:240px;">

                <div class="form-group">
                    <label>Publisher</label>
                    <input type="text" name="publisher" value="<?php echo htmlspecialchars($book['publisher'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label>Publish Year</label>
                    <input type="number" name="publish_year" value="<?php echo htmlspecialchars($book['publish_year'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label>Total Copies *</label>
                    <input type="number" name="total_copies" value="<?php echo $book['total_copies']; ?>" min="1" required>
                </div>

                <!-- Show current cover -->
                <div style="margin-bottom:12px;">
                    <label style="font-size:13px; font-weight:bold;">Current Cover:</label><br>
                    <img src="uploads/<?php echo htmlspecialchars($book['cover_image']); ?>"
                         style="width:60px; height:80px; object-fit:cover; border-radius:4px; border:1px solid #ddd; margin-top:6px;"
                         onerror="this.src='uploads/no_cover.png'">
                </div>

            </div>
        </div>

        <div class="form-group">
            <label>Description</label>
            <textarea name="description" style="height:80px;"><?php echo htmlspecialchars($book['description'] ?? ''); ?></textarea>
        </div>

        <div style="display:flex; gap:10px;">
            <button type="submit" class="btn btn-warning">Update Book</button>
            <a href="index.php?page=book_list" class="btn btn-secondary">Cancel</a>
        </div>

    </form>
</div>

<?php require_once 'views/footer.php'; ?>
