<?php
// ============================================================
// views/manager/manager_announcements.php
// Post and manage platform-wide announcements
// ============================================================

require_once 'config/Connect.php';
require_once 'config/Close.php';
require_once 'models/BranchModel.php';

$conn          = connect();
$announcements = getAnnouncements($conn);
$branches      = getAllBranches($conn);
closeConn($conn);

require_once 'views/header.php';
?>

<h1 class="page-title">&#128226; Announcements</h1>

<div style="display:flex; gap:24px; flex-wrap:wrap;">

    <!-- Post Announcement -->
    <div class="section-box" style="flex:0 0 300px;">
        <h3>Post New Announcement</h3>
        <form action="index.php?page=do_branch&action=announce" method="POST">
            <div class="form-group">
                <label>Title *</label>
                <input type="text" name="title" placeholder="Announcement title" required>
            </div>
            <div class="form-group">
                <label>Content *</label>
                <textarea name="content" rows="5" placeholder="Write your announcement here..." required></textarea>
            </div>
            <div class="form-group">
                <label>Target Branch (leave blank for all branches)</label>
                <select name="branch_id">
                    <option value="">-- All Branches --</option>
                    <?php foreach ($branches as $b): ?>
                        <option value="<?php echo $b['id']; ?>"><?php echo htmlspecialchars($b['branch_name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Post Announcement</button>
        </form>
    </div>

    <!-- Announcements List -->
    <div style="flex:1; min-width:280px;">
        <div class="table-container">
            <h3 style="margin-bottom:16px;">All Announcements (<?php echo count($announcements); ?>)</h3>
            <?php if (empty($announcements)): ?>
                <p style="color:#888;">No announcements yet.</p>
            <?php else: ?>
                <?php foreach ($announcements as $a): ?>
                <div style="border:1px solid #e0e0e0; border-radius:8px; padding:16px; margin-bottom:12px; background:#fff;">
                    <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:8px;">
                        <div>
                            <h4 style="margin:0 0 6px 0; color:#333;"><?php echo htmlspecialchars($a['title']); ?></h4>
                            <p style="margin:0 0 8px 0; color:#555; font-size:14px; line-height:1.5;"><?php echo nl2br(htmlspecialchars($a['content'])); ?></p>
                            <div style="font-size:12px; color:#888;">
                                <span>&#128197; <?php echo date('d M Y, h:i A', strtotime($a['created_at'])); ?></span>
                                &nbsp;&bull;&nbsp;
                                <span>&#128100; <?php echo htmlspecialchars($a['full_name']); ?></span>
                                &nbsp;&bull;&nbsp;
                                <span>&#127970; <?php echo !empty($a['branch_name']) ? htmlspecialchars($a['branch_name']) : '<em>All Branches</em>'; ?></span>
                            </div>
                        </div>
                        <a href="index.php?page=do_branch&action=delete_announcement&id=<?php echo $a['id']; ?>"
                           class="btn btn-danger btn-sm"
                           onclick="return confirm('Delete this announcement?')">Delete</a>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

</div>

<?php require_once 'views/footer.php'; ?>
