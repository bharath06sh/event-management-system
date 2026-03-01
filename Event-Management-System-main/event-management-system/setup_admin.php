<?php
// Connect to database
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'event_management_system';

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set admin password to 'admin123'
$admin_password = 'admin123';
$hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);

$sql = "UPDATE users SET password = ? WHERE username = 'admin'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $hashed_password);

if ($stmt->execute()) {
    echo "Admin password has been set successfully!<br>";
    echo "Username: <strong>admin</strong><br>";
    echo "Password: <strong>admin123</strong><br>";
    echo "<br><a href='auth/login.php'>Go to Login Page</a>";
} else {
    echo "Error updating password: " . $conn->error;
}

$stmt->close();
$conn->close();
?>
