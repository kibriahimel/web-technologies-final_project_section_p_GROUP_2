<?php
// ============================================================
// views/librarian/announcements.php
// View all announcements; librarians/admin can add/delete
// ============================================================

require_once 'config/Connect.php';
require_once 'config/Close.php';
require_once 'models/BranchModel.php';

$can_manage = isset($_SESSION['role']) && in_array($_SESSION['role'], array('librarian','admin','manager'));

// Handle create announcement (POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $can_manage) {
    $title     = htmlspecialchars(trim($_POST['title']));
    $content   = htmlspecialchars(trim($_POST['content']));
    $branch_id = !empty($_POST['branch_id']) ? (int)$_POST['branch_id'] : NULL;
    $created_by = $_SESSION['user'];

    if (!empty($title) && !empty($content)) {
        $conn = connect();
        createAnnouncement($conn, $title, $content, $branch_id, $created_by);
        closeConn($conn);
        $_SESSION['msg'] = "Announcement posted!";
    } else {
        $_SESSION['error'] = "Title and content are required.";
    }
    header("Location: index.php?page=announcements");
    exit();
}

// Handle delete (GET)
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id']) && $can_manage) {
    $conn = connect();
    deleteAnnouncement($conn, (int)$_GET['id']);
    closeConn($conn);
    $_SESSION['msg'] = "Announcement deleted.";
    header("Location: index.php?page=announcements");
    exit();
}

$conn          = connect();
$announcements = getAnnouncements($conn);
$branches      = getAllBranches($conn);
closeConn($conn);

require_once 'views/header.php';
?>

<h1 class="page-title">&#128226; Announcements</h1>

<div style="display:flex; gap:24px; flex-wrap:wrap;">

    <?php if ($can_manage): ?>
    <!-- Post Announcement Form -->
    <div class="section-box" style="flex:0 0 300px;">
        <h3>Post New Announcement</h3>
        <form action="index.php?page=announcements" method="POST">
            <div class="form-group">
                <label>Title *</label>
                <input type="text" name="title" placeholder="Announcement title" required>
            </div>
            <div class="form-group">
                <label>Content *</label>
                <textarea name="content" placeholder="Write your announcement..." required></textarea>
            </div>
            <div class="form-group">
                <label>Branch (optional)</label>
                <select name="branch_id">
                    <option value="">-- All Branches --</option>
                    <?php foreach ($branches as $b): ?>
                        <option value="<?php echo $b['id']; ?>"><?php echo htmlspecialchars($b['branch_name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-success">Post Announcement</button>
        </form>
    </div>
    <?php endif; ?>

    <!-- Announcements List -->
    <div style="flex:1; min-width:280px;">
        <?php if (empty($announcements)): ?>
            <div class="section-box">
                <p style="color:#888;">No announcements yet.</p>
            </div>
        <?php else: ?>
            <?php foreach ($announcements as $ann): ?>
            <div class="section-box" style="margin-bottom:16px;">
                <div class="flex-between">
                    <h3 style="font-size:16px;"><?php echo htmlspecialchars($ann['title']); ?></h3>
                    <?php if ($can_manage): ?>
                        <a href="index.php?page=announcements&action=delete&id=<?php echo $ann['id']; ?>"
                           class="btn btn-danger btn-sm"
                           onclick="return confirmDelete('Delete this announcement?')">Delete</a>
                    <?php endif; ?>
                </div>
                <p style="font-size:13px; color:#555; line-height:1.6;"><?php echo htmlspecialchars($ann['content']); ?></p>
                <p style="font-size:12px; color:#aaa; margin-top:8px;">
                    Posted by <strong><?php echo htmlspecialchars($ann['full_name']); ?></strong>
                    <?php echo $ann['branch_name'] ? '&mdash; ' . htmlspecialchars($ann['branch_name']) : '&mdash; All Branches'; ?>
                    &mdash; <?php echo date('d M Y', strtotime($ann['created_at'])); ?>
                </p>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

</div>

<?php require_once 'views/footer.php'; ?>
