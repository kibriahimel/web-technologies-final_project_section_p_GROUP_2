<?php
// ============================================================
// views/librarian/add_book.php
// Form to add a new book – submits POST to do_save_book
// ============================================================

require_once 'config/Connect.php';
require_once 'config/Close.php';
require_once 'models/BookModel.php';

$conn   = connect();
$genres = getAllGenres($conn);
closeConn($conn);

require_once 'views/header.php';
?>

<h1 class="page-title">+ Add New Book</h1>

<div class="form-box" style="max-width:700px;">
    <form action="index.php?page=do_save_book" method="POST" enctype="multipart/form-data"
          onsubmit="return validateBookForm();">

        <div style="display:flex; gap:20px; flex-wrap:wrap;">
            <div style="flex:1; min-width:240px;">

                <div class="form-group">
                    <label>Book Title *</label>
                    <input type="text" name="title" id="title" placeholder="e.g. The Great Adventure" required>
                </div>

                <div class="form-group">
                    <label>Author *</label>
                    <input type="text" name="author" id="author" placeholder="e.g. John Smith" required>
                </div>

                <div class="form-group">
                    <label>ISBN</label>
                    <input type="text" name="isbn" placeholder="e.g. 978-0001">
                </div>

                <div class="form-group">
                    <label>Genre</label>
                    <select name="genre_id">
                        <option value="">-- Select Genre --</option>
                        <?php foreach ($genres as $g): ?>
                            <option value="<?php echo $g['id']; ?>"><?php echo htmlspecialchars($g['genre_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

            </div>

            <div style="flex:1; min-width:240px;">

                <div class="form-group">
                    <label>Publisher</label>
                    <input type="text" name="publisher" placeholder="e.g. Penguin Books">
                </div>

                <div class="form-group">
                    <label>Publish Year</label>
                    <input type="number" name="publish_year" placeholder="e.g. 2020" min="1800" max="<?php echo date('Y'); ?>">
                </div>

                <div class="form-group">
                    <label>Total Copies *</label>
                    <input type="number" name="total_copies" value="1" min="1" required>
                </div>

                <div class="form-group">
                    <label>Cover Image</label>
                    <input type="file" name="cover_image" accept=".jpg,.jpeg,.png,.gif">
                    <small style="color:#888; font-size:12px;">JPG/PNG, max 2MB (optional)</small>
                </div>

            </div>
        </div>

        <div class="form-group">
            <label>Description</label>
            <textarea name="description" placeholder="Brief description of the book..." style="height:80px;"></textarea>
        </div>

        <div style="display:flex; gap:10px;">
            <button type="submit" class="btn btn-success">Save Book</button>
            <a href="index.php?page=book_list" class="btn btn-secondary">Cancel</a>
        </div>

    </form>
</div>

<?php require_once 'views/footer.php'; ?>
