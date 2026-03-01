<?php
require_once "config/db.php";

// Check admin user in database
$sql = "SELECT id, username, password, role FROM users WHERE username = 'admin'";
$result = mysqli_query($conn, $sql);
$user = mysqli_fetch_assoc($result);

echo "Admin user from database:\n";
echo "Username: " . $user['username'] . "\n";
echo "Password hash: " . $user['password'] . "\n";
echo "Role: " . $user['role'] . "\n\n";

// Test password verification
$test_password = "admin123";
$is_valid = password_verify($test_password, $user['password']);

echo "Testing password 'admin123':\n";
echo "Result: " . ($is_valid ? "CORRECT" : "WRONG") . "\n";
?>
