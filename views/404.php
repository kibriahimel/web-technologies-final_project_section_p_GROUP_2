<?php
// ============================================================
// views/404.php
// Shown when a page is not found
// ============================================================

require_once 'views/header.php';
?>

<div style="text-align:center; padding:60px 20px;">
    <h1 style="font-size:80px; color:#1a3c5e; margin-bottom:0;">404</h1>
    <h2 style="color:#666; margin-bottom:20px;">Page Not Found</h2>
    <p style="color:#888; margin-bottom:30px;">The page you are looking for does not exist or has been moved.</p>
    <a href="index.php?page=home" class="btn btn-primary">&#8592; Go to Home</a>
</div>

<?php require_once 'views/footer.php'; ?>
