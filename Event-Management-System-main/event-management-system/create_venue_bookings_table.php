<?php
require_once "../config/db.php";

echo "Creating venue_bookings table if it doesn't exist...<br><br>";

$sql = "CREATE TABLE IF NOT EXISTS venue_bookings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    venue_id INT NOT NULL,
    booking_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    status ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
    total_price DECIMAL(10,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (venue_id) REFERENCES venues(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if(mysqli_query($conn, $sql)) {
    echo "✓ venue_bookings table created successfully!<br>";
} else {
    echo "Error creating table: " . mysqli_error($conn) . "<br>";
}

// Check table structure
$describe_sql = "DESCRIBE venue_bookings";
$result = mysqli_query($conn, $describe_sql);

echo "<br>Table structure:<br>";
while($row = mysqli_fetch_assoc($result)) {
    echo "- " . $row['Field'] . " (" . $row['Type'] . ")<br>";
}

mysqli_close($conn);
?>
