<?php
// ── AUTH ──────────────────────────────────────────────────────
function getUserByEmail($conn, $email)
{
    $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE email = ? AND is_active = 1");
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($result);
}

// ── DASHBOARD STATS ───────────────────────────────────────────
function getTotalMembers($conn)
{
    $stmt = mysqli_prepare($conn, "SELECT COUNT(*) AS total FROM users WHERE role = 'member'");
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    return $row['total'];
}

function getTotalBooks($conn)
{
    $stmt = mysqli_prepare($conn, "SELECT COUNT(*) AS total FROM books");
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    return $row['total'];
}

function getTotalActiveLoans($conn)
{
    $stmt = mysqli_prepare($conn, "SELECT COUNT(*) AS total FROM borrow_records WHERE status = 'active'");
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    return $row['total'];
}

function getTotalOverdueLoans($conn)
{
    $stmt = mysqli_prepare($conn, "SELECT COUNT(*) AS total FROM borrow_records WHERE status = 'active' AND due_date < CURDATE()");
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    return $row['total'];
}

function getTotalFinesOutstanding($conn)
{
    $stmt = mysqli_prepare($conn, "SELECT COALESCE(SUM(amount),0) AS total FROM fines WHERE is_paid = 0");
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    return $row['total'];
}

// ── USER MANAGEMENT ───────────────────────────────────────────
function getAllUsers($conn)
{
    $stmt = mysqli_prepare($conn,
        "SELECT u.id, u.name, u.email, u.phone, u.role, u.is_active, u.created_at,
                b.name AS branch_name
         FROM users u
         LEFT JOIN branches b ON u.branch_id = b.id
         ORDER BY u.created_at DESC");
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $users = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $users[] = $row;
    }
    return $users;
}

function searchUsers($conn, $keyword)
{
    $like = "%" . $keyword . "%";
    $stmt = mysqli_prepare($conn,
        "SELECT u.id, u.name, u.email, u.phone, u.role, u.is_active,
                b.name AS branch_name
         FROM users u
         LEFT JOIN branches b ON u.branch_id = b.id
         WHERE u.name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?
         ORDER BY u.name ASC");
    mysqli_stmt_bind_param($stmt, "sss", $like, $like, $like);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $users = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $users[] = $row;
    }
    return $users;
}

function setUserActiveStatus($conn, $userId, $status)
{
    $stmt = mysqli_prepare($conn, "UPDATE users SET is_active = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "ii", $status, $userId);
    return mysqli_stmt_execute($stmt);
}

function changeUserRole($conn, $userId, $role)
{
    $stmt = mysqli_prepare($conn, "UPDATE users SET role = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "si", $role, $userId);
    return mysqli_stmt_execute($stmt);
}

function createStaffAccount($conn, $name, $email, $password, $phone, $role, $branchId)
{
    $hash = password_hash($password, PASSWORD_BCRYPT);
    $stmt = mysqli_prepare($conn,
        "INSERT INTO users (name, email, password_hash, phone, role, branch_id, is_active)
         VALUES (?, ?, ?, ?, ?, ?, 1)");
    mysqli_stmt_bind_param($stmt, "sssssi", $name, $email, $hash, $phone, $role, $branchId);
    return mysqli_stmt_execute($stmt);
}

// ── BRANCHES ──────────────────────────────────────────────────
function getAllBranches($conn)
{
    $stmt = mysqli_prepare($conn,
        "SELECT b.*, u.name AS manager_name,
                (SELECT COUNT(*) FROM users WHERE branch_id = b.id AND role = 'librarian') AS librarian_count
         FROM branches b
         LEFT JOIN users u ON b.manager_id = u.id
         ORDER BY b.name ASC");
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $branches = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $branches[] = $row;
    }
    return $branches;
}

// ── GLOBAL SETTINGS ───────────────────────────────────────────
function getAllSettings($conn)
{
    $stmt = mysqli_prepare($conn, "SELECT setting_key, setting_value FROM global_settings");
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $settings = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
    return $settings;
}

function updateSetting($conn, $key, $value)
{
    $stmt = mysqli_prepare($conn,
        "UPDATE global_settings SET setting_value = ? WHERE setting_key = ?");
    mysqli_stmt_bind_param($stmt, "ss", $value, $key);
    return mysqli_stmt_execute($stmt);
}

// ── AUDIT LOG ─────────────────────────────────────────────────
function logAction($conn, $userId, $action, $details)
{
    $stmt = mysqli_prepare($conn,
        "INSERT INTO audit_log (user_id, action, details) VALUES (?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "iss", $userId, $action, $details);
    return mysqli_stmt_execute($stmt);
}

function getAuditLog($conn)
{
    $stmt = mysqli_prepare($conn,
        "SELECT a.*, u.name AS user_name
         FROM audit_log a
         LEFT JOIN users u ON a.user_id = u.id
         ORDER BY a.created_at DESC
         LIMIT 100");
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $logs = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $logs[] = $row;
    }
    return $logs;
}

// ── BOOK CATALOG ──────────────────────────────────────────────
function getAllBooks($conn)
{
    $stmt = mysqli_prepare($conn,
        "SELECT b.*, g.name AS genre_name
         FROM books b
         LEFT JOIN genres g ON b.genre_id = g.id
         ORDER BY b.title ASC");
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    return $rows;
}

function getBookById($conn, $id)
{
    $stmt = mysqli_prepare($conn, "SELECT * FROM books WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($result);
}

function getAllGenres($conn)
{
    $stmt = mysqli_prepare($conn, "SELECT * FROM genres ORDER BY name ASC");
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    return $rows;
}

function updateBook($conn, $id, $title, $author, $isbn, $genreId, $publisher, $publishedYear, $description)
{
    $stmt = mysqli_prepare($conn,
        "UPDATE books SET title=?, author=?, isbn=?, genre_id=?, publisher=?, published_year=?, description=?
         WHERE id=?");
    mysqli_stmt_bind_param($stmt, "sssisssi",
        $title, $author, $isbn, $genreId, $publisher, $publishedYear, $description, $id);
    return mysqli_stmt_execute($stmt);
}

// ── REPORTS ───────────────────────────────────────────────────
function getBorrowsPerMonth($conn)
{
    $stmt = mysqli_prepare($conn,
        "SELECT DATE_FORMAT(borrow_date, '%Y-%m') AS month, COUNT(*) AS total
         FROM borrow_records
         WHERE borrow_date IS NOT NULL
         GROUP BY month
         ORDER BY month DESC
         LIMIT 12");
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    return $rows;
}

function getFinesPerMonth($conn)
{
    $stmt = mysqli_prepare($conn,
        "SELECT DATE_FORMAT(paid_at, '%Y-%m') AS month, SUM(amount) AS total
         FROM fines
         WHERE is_paid = 1 AND paid_at IS NOT NULL
         GROUP BY month
         ORDER BY month DESC
         LIMIT 12");
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    return $rows;
}

function getMostActiveBranches($conn)
{
    $stmt = mysqli_prepare($conn,
        "SELECT b.name AS branch_name, COUNT(br.id) AS total_borrows
         FROM borrow_records br
         JOIN branches b ON br.branch_id = b.id
         GROUP BY br.branch_id
         ORDER BY total_borrows DESC");
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    return $rows;
}

function getMostBorrowedGenres($conn)
{
    $stmt = mysqli_prepare($conn,
        "SELECT g.name AS genre_name, COUNT(br.id) AS total_borrows
         FROM borrow_records br
         JOIN books bk ON br.book_id = bk.id
         JOIN genres g ON bk.genre_id = g.id
         GROUP BY g.id
         ORDER BY total_borrows DESC");
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    return $rows;
}

function getMemberGrowthPerMonth($conn)
{
    $stmt = mysqli_prepare($conn,
        "SELECT DATE_FORMAT(created_at, '%Y-%m') AS month, COUNT(*) AS total
         FROM users
         WHERE role = 'member'
         GROUP BY month
         ORDER BY month DESC
         LIMIT 12");
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    return $rows;
}

// ── ANNOUNCEMENTS ─────────────────────────────────────────────
function getAllAnnouncements($conn)
{
    $stmt = mysqli_prepare($conn,
        "SELECT a.*, u.name AS author_name,
                COALESCE(b.name, 'Platform-wide') AS branch_name
         FROM announcements a
         JOIN users u ON a.author_id = u.id
         LEFT JOIN branches b ON a.branch_id = b.id
         ORDER BY a.published_at DESC");
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    return $rows;
}

function createAnnouncement($conn, $authorId, $title, $body)
{
    $stmt = mysqli_prepare($conn,
        "INSERT INTO announcements (branch_id, author_id, title, body)
         VALUES (NULL, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "iss", $authorId, $title, $body);
    return mysqli_stmt_execute($stmt);
}

function deleteAnnouncement($conn, $id)
{
    $stmt = mysqli_prepare($conn, "DELETE FROM announcements WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    return mysqli_stmt_execute($stmt);
}

// ── INTER-BRANCH TRANSFERS ────────────────────────────────────
function getAllTransferRequests($conn)
{
    $stmt = mysqli_prepare($conn,
        "SELECT r.*, bk.title AS book_title,
                b1.name AS from_branch, b2.name AS to_branch,
                u.name AS requested_by_name
         FROM inter_branch_requests r
         JOIN books bk ON r.book_id = bk.id
         JOIN branches b1 ON r.from_branch_id = b1.id
         JOIN branches b2 ON r.to_branch_id = b2.id
         JOIN users u ON r.requested_by = u.id
         ORDER BY r.created_at DESC");
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $requests = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $requests[] = $row;
    }
    return $requests;
}
