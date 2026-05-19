<?php
// ============================================================
// config/Close.php
// This file closes the MySQL database connection
// ============================================================

function closeConn($conn) {
    mysqli_close($conn);
}
