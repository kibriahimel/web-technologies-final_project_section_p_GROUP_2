<?php
require_once 'models/Connect.php';
$conn = Connect();

$email = 'admin@library.com';
$stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE email = ?");
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

echo "<pre>";
print_r($user);
echo "</pre>";

echo "Password verify result: ";
echo password_verify("password123", $user['password_hash']) ? "MATCH ✅" : "NO MATCH ❌";
?>