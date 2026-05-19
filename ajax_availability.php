<?php
// ============================================================
// ajax_availability.php
// Returns JSON: availability of a book at a branch
// Called by: views/js/main.js → checkAvailability()
// ============================================================

require_once 'config/Connect.php';
require_once 'config/Close.php';
require_once 'models/BookModel.php';

$book_id   = isset($_GET['book_id'])   ? (int)$_GET['book_id']   : 0;
$branch_id = isset($_GET['branch_id']) ? (int)$_GET['branch_id'] : 0;

$conn = connect();

$inventory = getInventory($conn, $book_id, $branch_id);

closeConn($conn);

$response = array(
    'available' => $inventory ? $inventory['available_copies'] : 0,
    'total'     => $inventory ? $inventory['total_copies']     : 0
);

header('Content-Type: application/json');
echo json_encode($response);
