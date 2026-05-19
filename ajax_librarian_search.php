<?php
// ============================================================
// ajax_librarian_search.php
// AJAX endpoint: search librarians by name for manager
// Returns plain HTML rows
// ============================================================

session_start();
require_once 'config/Connect.php';
require_once 'config/Close.php';

if (!isset($_SESSION['user']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'manager')) {
    echo '<span style="color:red;">Unauthorized</span>';
    exit();
}

$query = isset($_GET['q']) ? htmlspecialchars(trim($_GET['q'])) : '';

if (strlen($query) < 2) {
    exit();
}

$conn  = connect();
$like  = "%" . $query . "%";
$sql   = "SELECT u.id, u.full_name, u.email, u.status, b.branch_name
          FROM users u
          LEFT JOIN branches b ON u.branch_id = b.id
          WHERE u.role='librarian' AND (u.full_name LIKE ? OR u.email LIKE ?)
          ORDER BY u.full_name ASC
          LIMIT 10";
$stmt  = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ss", $like, $like);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$found = false;
while ($row = mysqli_fetch_assoc($result)) {
    $found = true;
    $branch = !empty($row['branch_name']) ? htmlspecialchars($row['branch_name']) : '<em>Unassigned</em>';
    echo '<div style="padding:5px 0; border-bottom:1px solid #eee;">';
    echo '<strong>' . htmlspecialchars($row['full_name']) . '</strong>';
    echo ' &mdash; ' . htmlspecialchars($row['email']);
    echo ' <span style="color:#888;">Branch: ' . $branch . '</span>';
    echo '</div>';
}

if (!$found) {
    echo '<span style="color:#999;">No librarians found for "' . htmlspecialchars($query) . '"</span>';
}

closeConn($conn);
