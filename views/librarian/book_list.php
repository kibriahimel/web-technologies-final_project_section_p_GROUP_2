<?php
// ============================================================
// views/librarian/book_list.php
// Lists all books – librarian and admin view
// ============================================================

require_once 'config/Connect.php';
require_once 'config/Close.php';
require_once 'models/BookModel.php';

$conn  = connect();
$books = getAllBooks($conn);
closeConn($conn);

require_once 'views/header.php';
?>

<h1 class="page-title">&#128218; Book List</h1>

<div class="flex-between">
    <div class="search-bar" style="flex:1; margin-bottom:0; margin-right:20px;">
        <input type="text" id="search_input" placeholder="Search books by title, author or ISBN..." oninput="searchBooksAjax()">
        <button class="btn btn-primary" onclick="searchBooksAjax()">Search</button>
        <button class="btn btn-secondary" onclick="document.getElementById('search_input').value=''; loadAllBooks();">Clear</button>
    </div>
    <a href="index.php?page=add_book" class="btn btn-success">+ Add New Book</a>
</div>

<p id="search-result-msg" style="margin: 12px 0; color:#666;">Showing <strong><?php echo count($books); ?></strong> books.</p>

<!-- Table view by default; AJAX replaces with cards when user searches -->
<div id="book_results">
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Cover</th>
                    <th>Title</th>
                    <th>Author</th>
                    <th>Genre</th>
                    <th>ISBN</th>
                    <th>Year</th>
                    <th>Copies</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($books)): ?>
                    <tr><td colspan="9" style="text-align:center; color:#888;">No books found. <a href="index.php?page=add_book">Add the first book</a></td></tr>
                <?php else: ?>
                    <?php $i = 1; foreach ($books as $book): ?>
                    <tr>
                        <td><?php echo $i++; ?></td>
                        <td>
                            <img src="uploads/<?php echo htmlspecialchars($book['cover_image']); ?>"
                                 style="width:40px; height:55px; object-fit:cover; border-radius:3px;"
                                 onerror="this.src='uploads/no_cover.png'">
                        </td>
                        <td><strong><?php echo htmlspecialchars($book['title']); ?></strong></td>
                        <td><?php echo htmlspecialchars($book['author']); ?></td>
                        <td><?php echo htmlspecialchars($book['genre_name'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($book['isbn'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($book['publish_year'] ?? '-'); ?></td>
                        <td style="text-align:center;"><?php echo $book['total_copies']; ?></td>
                        <td>
                            <a href="index.php?page=book_detail&id=<?php echo $book['id']; ?>" class="btn btn-info btn-sm">View</a>
                            <a href="index.php?page=edit_book&id=<?php echo $book['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                            <a href="index.php?page=do_delete_book&id=<?php echo $book['id']; ?>"
                               class="btn btn-danger btn-sm"
                               onclick="return confirmDelete('Delete this book permanently?')">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'views/footer.php'; ?>
