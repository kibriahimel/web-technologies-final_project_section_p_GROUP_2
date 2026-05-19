<?php
// ============================================================
// views/librarian/manage_genres.php
// Add and delete book genres
// ============================================================

require_once 'config/Connect.php';
require_once 'config/Close.php';
require_once 'models/BookModel.php';

// Handle add genre (POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['genre_name'])) {
    $genre_name  = htmlspecialchars(trim($_POST['genre_name']));
    $description = htmlspecialchars(trim($_POST['description']));
    if (!empty($genre_name)) {
        $conn = connect();
        createGenre($conn, $genre_name, $description);
        closeConn($conn);
        $_SESSION['msg'] = "Genre added successfully!";
    } else {
        $_SESSION['error'] = "Genre name is required.";
    }
    header("Location: index.php?page=manage_genres");
    exit();
}

// Handle delete genre (GET)
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $conn = connect();
    deleteGenre($conn, (int)$_GET['id']);
    closeConn($conn);
    $_SESSION['msg'] = "Genre deleted.";
    header("Location: index.php?page=manage_genres");
    exit();
}

$conn   = connect();
$genres = getAllGenres($conn);
closeConn($conn);

require_once 'views/header.php';
?>

<h1 class="page-title">&#127991; Manage Genres</h1>

<div style="display:flex; gap:24px; flex-wrap:wrap;">

    <!-- Add Genre Form -->
    <div class="section-box" style="flex:0 0 280px;">
        <h3>Add New Genre</h3>
        <form action="index.php?page=manage_genres" method="POST">
            <div class="form-group">
                <label>Genre Name *</label>
                <input type="text" name="genre_name" placeholder="e.g. Science Fiction" required>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" placeholder="Brief description..." style="height:70px;"></textarea>
            </div>
            <button type="submit" class="btn btn-success">Add Genre</button>
        </form>
    </div>

    <!-- Genre List -->
    <div class="section-box" style="flex:1; min-width:280px;">
        <h3>All Genres (<?php echo count($genres); ?>)</h3>
        <?php if (empty($genres)): ?>
            <p style="color:#888;">No genres added yet.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr><th>#</th><th>Genre Name</th><th>Description</th><th>Action</th></tr>
                </thead>
                <tbody>
                    <?php $i = 1; foreach ($genres as $g): ?>
                    <tr>
                        <td><?php echo $i++; ?></td>
                        <td><strong><?php echo htmlspecialchars($g['genre_name']); ?></strong></td>
                        <td style="font-size:13px; color:#666;"><?php echo htmlspecialchars($g['description'] ?? '-'); ?></td>
                        <td>
                            <a href="index.php?page=manage_genres&action=delete&id=<?php echo $g['id']; ?>"
                               class="btn btn-danger btn-sm"
                               onclick="return confirmDelete('Delete this genre?')">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

</div>

<?php require_once 'views/footer.php'; ?>
