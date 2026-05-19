<?php
// ============================================================
// controllers/LogoutController.php
// Destroys the session and redirects to login page
// ============================================================

session_start();
session_unset();
session_destroy();
header("Location: index.php?page=login");
exit();
