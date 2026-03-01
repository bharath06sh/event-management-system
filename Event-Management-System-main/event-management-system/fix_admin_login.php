<?php
require_once "config/db.php";

// Hash the password
$password = "admin123";
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Update the admin user's password
$sql = "UPDATE users SET password = ? WHERE username = 'admin'";
$stmt = mysqli_prepare($conn, $sql);

if(!$stmt) {
    die("Error preparing statement: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, "s", $hashed_password);

if(mysqli_stmt_execute($stmt)){
    echo "✓ Admin password updated successfully!<br>";
    echo "Username: <strong>admin</strong><br>";
    echo "Password: <strong>admin123</strong><br>";
    echo "<br>You can now login with these credentials.<br>";
} else{
    echo "Error updating password: " . mysqli_stmt_error($stmt) . "<br>";
}

mysqli_stmt_close($stmt);

// Verify the user exists and has admin role
$verify_sql = "SELECT id, username, role FROM users WHERE username = 'admin'";
$result = mysqli_query($conn, $verify_sql);
if ($result && mysqli_num_rows($result) > 0) {
    $user = mysqli_fetch_assoc($result);
    echo "<br>Admin user verified:<br>";
    echo "ID: " . $user['id'] . "<br>";
    echo "Username: " . $user['username'] . "<br>";
    echo "Role: " . $user['role'] . "<br>";
}

mysqli_close($conn);
?>
