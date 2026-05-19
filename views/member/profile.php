<?php
// ============================================================
// views/member/profile.php
// Profile page for all logged-in users
// Submits to ProfileController via POST
// ============================================================

require_once 'config/Connect.php';
require_once 'config/Close.php';
require_once 'models/UserModel.php';

$conn = connect();
$user = getUserById($conn, $_SESSION['user']);
closeConn($conn);

require_once 'views/header.php';
?>

<h1 class="page-title"> My Profile</h1>

<div style="display:flex; gap:30px; flex-wrap:wrap;">

    <!-- Profile Picture & Info -->
    <div class="section-box" style="flex:0 0 220px; text-align:center;">
        <img src="uploads/<?php echo htmlspecialchars($user['profile_pic']); ?>"
             alt="Profile"
             style="width:120px; height:120px; border-radius:50%; object-fit:cover; border:3px solid #2c6e9e; margin-bottom:12px;"
             onerror="this.src='uploads/default.png'">
        <h3 style="color:#1a3c5e;"><?php echo htmlspecialchars($user['full_name']); ?></h3>
        <p style="color:#888; font-size:13px; margin-top:4px;"><?php echo htmlspecialchars($user['email']); ?></p>
        <span class="badge badge-active" style="margin-top:8px; display:inline-block; text-transform:uppercase;">
            <?php echo htmlspecialchars($user['role']); ?>
        </span>
        <p style="font-size:12px; color:#aaa; margin-top:10px;">Member since <?php echo date('M Y', strtotime($user['created_at'])); ?></p>
    </div>

    <!-- Edit Profile Form -->
    <div class="section-box" style="flex:1; min-width:280px;">
        <h3>Edit Profile</h3>

        <form action="index.php?page=do_profile" method="POST" enctype="multipart/form-data">

            <div class="form-group">
                <label>Full Name </label>
                <input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
            </div>

            <div class="form-group">
                <label>Email Address</label>
                <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled
                       style="background:#f0f0f0; cursor:not-allowed;">
                <small style="color:#888; font-size:12px;">Email cannot be changed.</small>
            </div>

            <div class="form-group">
                <label>Phone Number</label>
                <input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" placeholder="e.g. 01700000000">
            </div>

            <div class="form-group">
                <label>Address</label>
                <textarea name="address" placeholder="Your address"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
            </div>

            <div class="form-group">
                <label>Profile Picture</label>
                <input type="file" name="profile_pic" accept=".jpg,.jpeg,.png">
                <small style="color:#888; font-size:12px;">JPG or PNG, max 2MB. Leave empty to keep current.</small>
            </div>

            <button type="submit" class="btn btn-primary">Save Changes</button>

        </form>
    </div>

</div>
<!-- Change Password Section -->
<div class="section-box" style="margin-top:24px;">
    <h3>Change Password</h3>

    <form action="index.php?page=do_profile" method="POST" style="max-width:400px;">
        <input type="hidden" name="action" value="change_password">

        <div class="form-group">
            <label>Current Password</label>
            <input type="password" name="current_password" placeholder="Enter current password" required>
        </div>

        <div class="form-group">
            <label>New Password</label>
            <input type="password" name="new_password" placeholder="Min 6 characters" required>
        </div>

        <div class="form-group">
            <label>Confirm New Password</label>
            <input type="password" name="confirm_password" placeholder="Repeat new password" required>
        </div>

       <button type="submit" class="btn btn-primary">Change Password</button>
    </form>
</div>

<?php require_once 'views/footer.php'; ?>
