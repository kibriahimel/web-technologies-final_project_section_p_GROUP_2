<?php
// ============================================================
// models/BranchModel.php
// Contains all database functions for the branches table
// ============================================================

require_once 'config/Connect.php';
require_once 'config/Close.php';

// ------------------------------------------------------------
// Get all branches
// ------------------------------------------------------------
function getAllBranches($conn) {
    $sql    = "SELECT b.*, u.full_name AS manager_name FROM branches b LEFT JOIN users u ON b.manager_id = u.id ORDER BY b.id ASC";
    $result = mysqli_query($conn, $sql);
    $list   = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $list[] = $row;
    }
    return $list;
}

// ------------------------------------------------------------
// Get a single branch by ID
// ------------------------------------------------------------
function getBranchById($conn, $id) {
    $sql  = "SELECT b.*, u.full_name AS manager_name FROM branches b LEFT JOIN users u ON b.manager_id = u.id WHERE b.id=?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($result);
}

// ------------------------------------------------------------
// Create a new branch
// ------------------------------------------------------------
function createBranch($conn, $branch_name, $location, $phone, $email) {
    $sql  = "INSERT INTO branches (branch_name, location, phone, email) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ssss", $branch_name, $location, $phone, $email);
    return mysqli_stmt_execute($stmt);
}

// ------------------------------------------------------------
// Update branch details
// ------------------------------------------------------------
function updateBranch($conn, $id, $branch_name, $location, $phone, $email, $manager_id) {
    $sql  = "UPDATE branches SET branch_name=?, location=?, phone=?, email=?, manager_id=? WHERE id=?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ssssi i", $branch_name, $location, $phone, $email, $manager_id, $id);
    return mysqli_stmt_execute($stmt);
}

// ------------------------------------------------------------
// Delete a branch
// ------------------------------------------------------------
function deleteBranch($conn, $id) {
    $sql  = "DELETE FROM branches WHERE id=?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    return mysqli_stmt_execute($stmt);
}

// ------------------------------------------------------------
// Get branch policy
// ------------------------------------------------------------
function getBranchPolicy($conn, $branch_id) {
    $sql  = "SELECT * FROM branch_policies WHERE branch_id=?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $branch_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($result);
}

// ------------------------------------------------------------
// Update branch policy
// ------------------------------------------------------------
function updateBranchPolicy($conn, $branch_id, $max_borrow_days, $max_books, $fine_per_day, $reservation_days) {
    // Check if policy exists
    $check = getBranchPolicy($conn, $branch_id);
    if ($check) {
        $sql  = "UPDATE branch_policies SET max_borrow_days=?, max_books_per_member=?, fine_per_day=?, reservation_days=? WHERE branch_id=?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "iidii", $max_borrow_days, $max_books, $fine_per_day, $reservation_days, $branch_id);
    } else {
        $sql  = "INSERT INTO branch_policies (branch_id, max_borrow_days, max_books_per_member, fine_per_day, reservation_days) VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "iidii", $branch_id, $max_borrow_days, $max_books, $fine_per_day, $reservation_days);
    }
    return mysqli_stmt_execute($stmt);
}

// ------------------------------------------------------------
// Get announcements (for a branch or all)
// ------------------------------------------------------------
// ------------------------------------------------------------
// Get announcements (for a branch or all)
// ------------------------------------------------------------
function getAnnouncements($conn) {
    $sql    = "SELECT a.*, u.full_name, b.branch_name 
               FROM announcements a 
               LEFT JOIN users u ON a.created_by = u.id 
               LEFT JOIN branches b ON a.branch_id = b.id 
               ORDER BY a.created_at DESC";
    
    $result = mysqli_query($conn, $sql);
    $list   = array();
    
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $list[] = $row;
        }
    }
    
    return $list;
}
// ------------------------------------------------------------
// Create an announcement
// ------------------------------------------------------------
function createAnnouncement($conn, $title, $content, $branch_id, $created_by) {
    $sql  = "INSERT INTO announcements (title, content, branch_id, created_by) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ssii", $title, $content, $branch_id, $created_by);
    return mysqli_stmt_execute($stmt);
}

// ------------------------------------------------------------
// Delete announcement
// ------------------------------------------------------------
function deleteAnnouncement($conn, $id) {
    $sql  = "DELETE FROM announcements WHERE id=?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    return mysqli_stmt_execute($stmt);
}

// ------------------------------------------------------------
// Get inter-branch requests
// ------------------------------------------------------------
function getInterBranchRequests($conn) {
    $sql    = "SELECT ibr.*, b.title AS book_title, fb.branch_name AS from_branch, tb.branch_name AS to_branch, u.full_name AS requester FROM inter_branch_requests ibr JOIN books b ON ibr.book_id = b.id JOIN branches fb ON ibr.from_branch_id = fb.id JOIN branches tb ON ibr.to_branch_id = tb.id JOIN users u ON ibr.requested_by = u.id ORDER BY ibr.created_at DESC";
    $result = mysqli_query($conn, $sql);
    $list   = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $list[] = $row;
    }
    return $list;
}

// ------------------------------------------------------------
// Create inter-branch transfer request
// ------------------------------------------------------------
function createInterBranchRequest($conn, $book_id, $from_branch, $to_branch, $requested_by) {
    $sql  = "INSERT INTO inter_branch_requests (book_id, from_branch_id, to_branch_id, requested_by) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "iiii", $book_id, $from_branch, $to_branch, $requested_by);
    return mysqli_stmt_execute($stmt);
}

// ------------------------------------------------------------
// Approve inter-branch request
// ------------------------------------------------------------
function approveInterBranchRequest($conn, $request_id, $approved_by) {
    $sql  = "UPDATE inter_branch_requests SET status='approved', approved_by=? WHERE id=?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $approved_by, $request_id);
    return mysqli_stmt_execute($stmt);
}

// ------------------------------------------------------------
// Reject inter-branch request
// ------------------------------------------------------------
function rejectInterBranchRequest($conn, $request_id, $rejected_by) {
    $sql  = "UPDATE inter_branch_requests SET status='rejected', approved_by=? WHERE id=?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $rejected_by, $request_id);
    return mysqli_stmt_execute($stmt);
}

// ------------------------------------------------------------
// Toggle branch status (active/inactive)
// ------------------------------------------------------------
function toggleBranchStatus($conn, $id, $status) {
    $sql  = "UPDATE branches SET status=? WHERE id=?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "si", $status, $id);
    return mysqli_stmt_execute($stmt);
}

// ------------------------------------------------------------
// Get all branches managed by a specific manager
// ------------------------------------------------------------
function getBranchesByManager($conn, $manager_id) {
    $sql  = "SELECT b.*, u.full_name AS manager_name FROM branches b LEFT JOIN users u ON b.manager_id = u.id WHERE b.manager_id=? ORDER BY b.id ASC";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $manager_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $list   = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $list[] = $row;
    }
    return $list;
}

// ------------------------------------------------------------
// Get cross-branch borrow stats for all branches
// ------------------------------------------------------------
function getCrossBranchBorrowStats($conn) {
    $sql    = "SELECT bn.id, bn.branch_name, bn.status,
                COUNT(br.id) AS total_borrows,
                SUM(CASE WHEN br.status='active' THEN 1 ELSE 0 END) AS active_loans,
                SUM(CASE WHEN br.status='returned' THEN 1 ELSE 0 END) AS returned,
                SUM(CASE WHEN br.status='pending' THEN 1 ELSE 0 END) AS pending
               FROM branches bn
               LEFT JOIN borrow_records br ON bn.id = br.branch_id
               GROUP BY bn.id, bn.branch_name, bn.status
               ORDER BY bn.id ASC";
    $result = mysqli_query($conn, $sql);
    $list   = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $list[] = $row;
    }
    return $list;
}

// ------------------------------------------------------------
// Get cross-branch overdue and fine stats
// ------------------------------------------------------------
function getCrossBranchFineStats($conn) {
    $sql    = "SELECT bn.id, bn.branch_name,
                SUM(CASE WHEN f.status='unpaid' THEN f.amount ELSE 0 END) AS unpaid_fines,
                SUM(CASE WHEN f.status='paid'   THEN f.amount ELSE 0 END) AS paid_fines,
                COUNT(DISTINCT CASE WHEN br.status='active' AND br.due_date < CURDATE() THEN br.id END) AS overdue_loans
               FROM branches bn
               LEFT JOIN borrow_records br ON bn.id = br.branch_id
               LEFT JOIN fines f ON f.borrow_id = br.id
               GROUP BY bn.id, bn.branch_name
               ORDER BY bn.id ASC";
    $result = mysqli_query($conn, $sql);
    $list   = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $list[] = $row;
    }
    return $list;
}

// ------------------------------------------------------------
// Get most borrowed books across all branches
// ------------------------------------------------------------
function getMostBorrowedBooks($conn, $limit = 10) {
    $sql    = "SELECT b.id, b.title, b.author, g.genre_name,
                COUNT(br.id) AS borrow_count,
                GROUP_CONCAT(DISTINCT bn.branch_name SEPARATOR ', ') AS branches
               FROM books b
               JOIN borrow_records br ON b.id = br.book_id
               JOIN branches bn ON br.branch_id = bn.id
               LEFT JOIN genres g ON b.genre_id = g.id
               GROUP BY b.id, b.title, b.author, g.genre_name
               ORDER BY borrow_count DESC
               LIMIT ?";
    $stmt   = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $limit);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $list   = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $list[] = $row;
    }
    return $list;
}

// ------------------------------------------------------------
// Get member activity report (top borrowers, fines, new registrations)
// ------------------------------------------------------------
function getTopBorrowers($conn, $limit = 10) {
    $sql    = "SELECT u.id, u.full_name, u.email, bn.branch_name,
                COUNT(br.id) AS total_borrows,
                SUM(CASE WHEN f.status='unpaid' THEN f.amount ELSE 0 END) AS outstanding_fines
               FROM users u
               JOIN borrow_records br ON u.id = br.member_id
               JOIN branches bn ON br.branch_id = bn.id
               LEFT JOIN fines f ON f.user_id = u.id
               WHERE u.role='member'
               GROUP BY u.id, u.full_name, u.email, bn.branch_name
               ORDER BY total_borrows DESC
               LIMIT ?";
    $stmt   = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $limit);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $list   = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $list[] = $row;
    }
    return $list;
}

// ------------------------------------------------------------
// Get overdue loans with configurable threshold
// ------------------------------------------------------------
function getOverdueLoans($conn, $days_threshold = 0) {
    $sql    = "SELECT br.*, u.full_name, u.email, u.phone, b.title AS book_title,
                bn.branch_name,
                DATEDIFF(CURDATE(), br.due_date) AS days_overdue
               FROM borrow_records br
               JOIN users u ON br.member_id = u.id
               JOIN books b ON br.book_id = b.id
               JOIN branches bn ON br.branch_id = bn.id
               WHERE br.status='active' AND br.due_date < CURDATE()
               AND DATEDIFF(CURDATE(), br.due_date) >= ?
               ORDER BY days_overdue DESC";
    $stmt   = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $days_threshold);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $list   = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $list[] = $row;
    }
    return $list;
}

// ------------------------------------------------------------
// Get monthly report per branch
// ------------------------------------------------------------
function getMonthlyBranchReport($conn, $branch_id, $year, $month) {
    // Borrows this month
    $sql  = "SELECT COUNT(*) AS total_borrows,
              SUM(CASE WHEN status='returned' THEN 1 ELSE 0 END) AS returns,
              SUM(CASE WHEN status='active' AND due_date < CURDATE() THEN 1 ELSE 0 END) AS overdue
             FROM borrow_records
             WHERE branch_id=? AND YEAR(created_at)=? AND MONTH(created_at)=?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "iii", $branch_id, $year, $month);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $data   = mysqli_fetch_assoc($result);

    // Fines this month
    $sql2  = "SELECT SUM(f.amount) AS total_fines, SUM(CASE WHEN f.status='paid' THEN f.amount ELSE 0 END) AS collected
              FROM fines f
              JOIN borrow_records br ON f.borrow_id = br.id
              WHERE br.branch_id=? AND YEAR(f.created_at)=? AND MONTH(f.created_at)=?";
    $stmt2 = mysqli_prepare($conn, $sql2);
    mysqli_stmt_bind_param($stmt2, "iii", $branch_id, $year, $month);
    mysqli_stmt_execute($stmt2);
    $result2 = mysqli_stmt_get_result($stmt2);
    $fdata   = mysqli_fetch_assoc($result2);

    // New members registered at this branch
    $sql3  = "SELECT COUNT(*) AS new_members FROM users WHERE branch_id=? AND YEAR(created_at)=? AND MONTH(created_at)=? AND role='member'";
    $stmt3 = mysqli_prepare($conn, $sql3);
    mysqli_stmt_bind_param($stmt3, "iii", $branch_id, $year, $month);
    mysqli_stmt_execute($stmt3);
    $result3 = mysqli_stmt_get_result($stmt3);
    $mdata   = mysqli_fetch_assoc($result3);

    return array_merge($data, $fdata, $mdata);
}

// ------------------------------------------------------------
// Get librarian activity per branch
// ------------------------------------------------------------
function getLibrarianActivityByBranch($conn) {
    $sql    = "SELECT u.id, u.full_name, u.email, bn.branch_name,
                COUNT(DISTINCT br.id) AS borrows_processed,
                u.status
               FROM users u
               LEFT JOIN borrow_records br ON u.id = br.librarian_id
               LEFT JOIN branches bn ON u.branch_id = bn.id
               WHERE u.role='librarian'
               GROUP BY u.id, u.full_name, u.email, bn.branch_name, u.status
               ORDER BY bn.branch_name, borrows_processed DESC";
    $result = mysqli_query($conn, $sql);
    $list   = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $list[] = $row;
    }
    return $list;
}

// ------------------------------------------------------------
// Get librarians for a specific branch
// ------------------------------------------------------------
function getLibrariansByBranch($conn, $branch_id) {
    $sql  = "SELECT * FROM users WHERE role='librarian' AND branch_id=? ORDER BY full_name ASC";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $branch_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $list   = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $list[] = $row;
    }
    return $list;
}

// ------------------------------------------------------------
// Assign librarian to branch (update user's branch_id)
// ------------------------------------------------------------
function assignLibrarianToBranch($conn, $user_id, $branch_id) {
    $sql  = "UPDATE users SET branch_id=? WHERE id=? AND role='librarian'";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $branch_id, $user_id);
    return mysqli_stmt_execute($stmt);
}

// ------------------------------------------------------------
// Remove librarian from branch (set branch_id to NULL)
// ------------------------------------------------------------
function removeLibrarianFromBranch($conn, $user_id) {
    $sql  = "UPDATE users SET branch_id=NULL WHERE id=? AND role='librarian'";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    return mysqli_stmt_execute($stmt);
}

// ------------------------------------------------------------
// Get cross-branch inventory report
// ------------------------------------------------------------
function getCrossBranchInventory($conn) {
    $sql    = "SELECT bn.id AS branch_id, bn.branch_name, bn.status,
                COUNT(DISTINCT bi.book_id) AS unique_books,
                SUM(bi.total_copies) AS total_copies,
                SUM(bi.available_copies) AS available_copies
               FROM branches bn
               LEFT JOIN branch_inventory bi ON bn.id = bi.branch_id
               GROUP BY bn.id, bn.branch_name, bn.status
               ORDER BY bn.id ASC";
    $result = mysqli_query($conn, $sql);
    $list   = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $list[] = $row;
    }
    return $list;
}
