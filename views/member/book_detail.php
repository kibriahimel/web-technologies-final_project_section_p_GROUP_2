<?php
// ============================================================
// views/member/book_detail.php
// Book detail page with borrow form, reviews, reading list
// ============================================================

require_once 'config/Connect.php';
require_once 'config/Close.php';
require_once 'models/BookModel.php';
require_once 'models/ReviewModel.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$conn    = connect();
$book    = getBookById($conn, $id);
$reviews = getReviewsByBook($conn, $id);
$rating  = getAverageRating($conn, $id);
$branches = array();

// Get all branches for availability checking
$sql    = "SELECT * FROM branches WHERE status='active'";
$result = mysqli_query($conn, $sql);
while ($row = mysqli_fetch_assoc($result)) {
    $branches[] = $row;
}

$already_reviewed = false;
$in_reading_list  = false;
$my_review        = null;

if (isset($_SESSION['user'])) {
    $already_reviewed = userAlreadyReviewed($conn, $_SESSION['user'], $id);
    $in_reading_list  = isInReadingList($conn, $_SESSION['user'], $id);

    // Get member's own review for edit form
    if ($already_reviewed) {
        $rev_sql  = "SELECT * FROM book_reviews WHERE book_id=? AND user_id=?";
        $rev_stmt = mysqli_prepare($conn, $rev_sql);
        mysqli_stmt_bind_param($rev_stmt, "ii", $id, $_SESSION['user']);
        mysqli_stmt_execute($rev_stmt);
        $rev_result = mysqli_stmt_get_result($rev_stmt);
        $my_review  = mysqli_fetch_assoc($rev_result);
    }
}

closeConn($conn);

if (!$book) {
    echo "<p>Book not found.</p>";
    require_once 'views/footer.php';
    exit();
}

require_once 'views/header.php';
?>

<a href="index.php?page=browse_books" class="btn btn-secondary btn-sm" style="margin-bottom:16px;">&larr; Back to Browse</a>

<div style="display:flex; gap:30px; flex-wrap:wrap;">

    <!-- Book Cover -->
    <div style="flex:0 0 180px;">
        <img src="uploads/<?php echo htmlspecialchars($book['cover_image']); ?>"
             alt="<?php echo htmlspecialchars($book['title']); ?>"
             style="width:180px; border-radius:6px; box-shadow: 0 3px 10px rgba(0,0,0,0.15);"
             onerror="this.src='uploads/no_cover.png'">
    </div>

    <!-- Book Details -->
    <div style="flex:1; min-width:250px;">
        <h1 style="color:#1a3c5e; font-size:22px; margin-bottom:8px;"><?php echo htmlspecialchars($book['title']); ?></h1>
        <p style="color:#555; margin-bottom:5px;"><strong>Author:</strong> <?php echo htmlspecialchars($book['author']); ?></p>
        <p style="color:#555; margin-bottom:5px;"><strong>Genre:</strong> <?php echo htmlspecialchars($book['genre_name'] ?? 'N/A'); ?></p>
        <p style="color:#555; margin-bottom:5px;"><strong>Publisher:</strong> <?php echo htmlspecialchars($book['publisher'] ?? 'N/A'); ?></p>
        <p style="color:#555; margin-bottom:5px;"><strong>Published:</strong> <?php echo htmlspecialchars($book['publish_year'] ?? 'N/A'); ?></p>
        <p style="color:#555; margin-bottom:5px;"><strong>ISBN:</strong> <?php echo htmlspecialchars($book['isbn'] ?? 'N/A'); ?></p>

        <!-- Rating display -->
        <p style="margin-bottom:10px;">
            <strong>Rating:</strong>
            <?php
            $avg = $rating['avg_rating'] ? round($rating['avg_rating'], 1) : 0;
            for ($i = 1; $i <= 5; $i++) {
                echo $i <= $avg ? '<span class="stars">&#9733;</span>' : '<span class="stars-empty">&#9733;</span>';
            }
            echo ' <span style="color:#888; font-size:13px;">(' . $rating['total'] . ' reviews)</span>';
            ?>
        </p>

        <?php if ($book['description']): ?>
            <p style="font-size:14px; color:#666; line-height:1.6;"><?php echo htmlspecialchars($book['description']); ?></p>
        <?php endif; ?>

        <!-- Action Buttons -->
        <div style="margin-top:16px; display:flex; gap:10px; flex-wrap:wrap;">
            <?php if (isset($_SESSION['user']) && $_SESSION['role'] == 'member'): ?>
                <?php if (!$in_reading_list): ?>
                    <a href="index.php?page=reading_list&action=add&book_id=<?php echo $book['id']; ?>" class="btn btn-info btn-sm">+ Reading List</a>
                <?php else: ?>
                    <span class="btn btn-secondary btn-sm" style="cursor:default;">&#10003; In Reading List</span>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Borrow & Reserve Section -->
<?php if (isset($_SESSION['user']) && $_SESSION['role'] == 'member'): ?>
<div class="section-box" style="margin-top:24px;">
    <h3>&#128218; Borrow or Reserve This Book</h3>
    <p style="font-size:13px; color:#666; margin-bottom:16px;">Select a branch and check availability, then submit your request.</p>

    <div style="display:flex; gap:30px; flex-wrap:wrap;">

        <!-- Borrow Form -->
        <div style="flex:1; min-width:220px;">
            <h4 style="margin-bottom:10px;">Borrow Request</h4>
            <form action="index.php?page=do_borrow&action=request" method="POST">
                <input type="hidden" name="book_id" value="<?php echo $book['id']; ?>">
                <div class="form-group">
                    <label>Select Branch:</label>
                    <select name="branch_id" id="borrow_branch" onchange="checkAvailability(<?php echo $book['id']; ?>, this.value)">
                        <option value="">-- Select Branch --</option>
                        <?php foreach ($branches as $branch): ?>
                            <option value="<?php echo $branch['id']; ?>"><?php echo htmlspecialchars($branch['branch_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <p>Availability: <span id="availability_result" style="font-weight:bold;">Select a branch to check</span></p>
                <button type="submit" class="btn btn-primary btn-sm" style="margin-top:10px;">Submit Borrow Request</button>
            </form>
        </div>

        <!-- Reserve Form -->
        <div style="flex:1; min-width:220px;">
            <h4 style="margin-bottom:10px;">Reserve Book</h4>
            <form action="index.php?page=do_reservation&action=reserve" method="POST">
                <input type="hidden" name="book_id" value="<?php echo $book['id']; ?>">
                <div class="form-group">
                    <label>Select Branch:</label>
                    <select name="branch_id">
                        <option value="">-- Select Branch --</option>
                        <?php foreach ($branches as $branch): ?>
                            <option value="<?php echo $branch['id']; ?>"><?php echo htmlspecialchars($branch['branch_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <p style="font-size:12px; color:#888;">Reservation expires in 3 days.</p>
                <button type="submit" class="btn btn-warning btn-sm" style="margin-top:10px;">Reserve Book</button>
            </form>
        </div>

    </div>
</div>
<?php endif; ?>

<!-- Reviews Section -->
<div class="section-box">
    <h3>&#9733; Reviews & Ratings</h3>

    <?php if (isset($_SESSION['user']) && $_SESSION['role'] == 'member' && !$already_reviewed): ?>
        <!-- Submit New Review Form -->
        <div style="margin-bottom:24px; background:#f9f9f9; padding:16px; border-radius:6px;">
            <h4 style="margin-bottom:12px;">Write a Review</h4>
            <form action="index.php?page=do_review" method="POST">
                <input type="hidden" name="book_id" value="<?php echo $book['id']; ?>">
                <div class="form-group">
                    <label>Rating (1 to 5):</label>
                    <select name="rating" required>
                        <option value="">-- Select Rating --</option>
                        <option value="5">&#9733;&#9733;&#9733;&#9733;&#9733; Excellent</option>
                        <option value="4">&#9733;&#9733;&#9733;&#9733; Good</option>
                        <option value="3">&#9733;&#9733;&#9733; Average</option>
                        <option value="2">&#9733;&#9733; Poor</option>
                        <option value="1">&#9733; Very Poor</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Your Review:</label>
                    <textarea name="review_text" placeholder="Share your thoughts about this book..." required></textarea>
                </div>
                <button type="submit" class="btn btn-success btn-sm">Submit Review</button>
            </form>
        </div>

    <?php elseif (isset($_SESSION['user']) && $_SESSION['role'] == 'member' && $already_reviewed && $my_review): ?>
        <!-- Edit Own Review Form -->
        <div style="margin-bottom:24px; background:#fff8e1; padding:16px; border-radius:6px; border-left:4px solid #f0ad4e;">
            <h4 style="margin-bottom:12px;">&#9998; Edit Your Review</h4>
            <form action="index.php?page=do_review&action=edit" method="POST">
                <input type="hidden" name="book_id" value="<?php echo $book['id']; ?>">
                <input type="hidden" name="review_id" value="<?php echo $my_review['id']; ?>">
                <div class="form-group">
                    <label>Rating (1 to 5):</label>
                    <select name="rating" required>
                        <option value="">-- Select Rating --</option>
                        <option value="5" <?php echo $my_review['rating'] == 5 ? 'selected' : ''; ?>>&#9733;&#9733;&#9733;&#9733;&#9733; Excellent</option>
                        <option value="4" <?php echo $my_review['rating'] == 4 ? 'selected' : ''; ?>>&#9733;&#9733;&#9733;&#9733; Good</option>
                        <option value="3" <?php echo $my_review['rating'] == 3 ? 'selected' : ''; ?>>&#9733;&#9733;&#9733; Average</option>
                        <option value="2" <?php echo $my_review['rating'] == 2 ? 'selected' : ''; ?>>&#9733;&#9733; Poor</option>
                        <option value="1" <?php echo $my_review['rating'] == 1 ? 'selected' : ''; ?>>&#9733; Very Poor</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Your Review:</label>
                    <textarea name="review_text" required><?php echo htmlspecialchars($my_review['review_text']); ?></textarea>
                </div>
                <div style="display:flex; gap:10px;">
                    <button type="submit" class="btn btn-primary btn-sm">Update Review</button>
                    <a href="index.php?page=do_review&action=delete&id=<?php echo $my_review['id']; ?>&book_id=<?php echo $book['id']; ?>"
                       class="btn btn-danger btn-sm"
                       onclick="return confirm('Delete your review?')">Delete Review</a>
                </div>
            </form>
        </div>
    <?php endif; ?>

    <!-- Display All Reviews -->
    <?php if (empty($reviews)): ?>
        <p style="color:#888;">No reviews yet. Be the first to review!</p>
    <?php else: ?>
        <?php foreach ($reviews as $review): ?>
            <div style="border-bottom:1px solid #eee; padding:12px 0;">
                <strong><?php echo htmlspecialchars($review['full_name']); ?></strong>
                <span style="color:#888; font-size:12px; margin-left:8px;"><?php echo date('d M Y', strtotime($review['created_at'])); ?></span>
                <div style="margin:4px 0;">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <span class="<?php echo $i <= $review['rating'] ? 'stars' : 'stars-empty'; ?>">&#9733;</span>
                    <?php endfor; ?>
                </div>
                <p style="font-size:13px; color:#555;"><?php echo htmlspecialchars($review['review_text']); ?></p>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
                    <a href="index.php?page=do_review&action=delete&id=<?php echo $review['id']; ?>&book_id=<?php echo $book['id']; ?>"
                       class="btn btn-danger btn-sm"
                       onclick="return confirm('Delete this review?')">Delete</a>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php require_once 'views/footer.php'; ?>
