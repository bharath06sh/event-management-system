<?php
// Database initialization script
require_once "config/db.php";

// Check connection
if($conn === false){
    die("ERROR: Could not connect. " . mysqli_connect_error());
}

echo "Connected to database successfully!<br><br>";

// Drop existing tables if they exist (in reverse order of dependencies)
$tables_to_drop = ['payments', 'bookings', 'events', 'venues', 'users'];
foreach($tables_to_drop as $table) {
    $sql = "DROP TABLE IF EXISTS $table";
    mysqli_query($conn, $sql);
}
echo "✓ Dropped existing tables (if any)<br><br>";

// Create Users table
$sql = "CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if(mysqli_query($conn, $sql)){
    echo "✓ Users table created successfully.<br>";
} else{
    echo "Error creating users table: " . mysqli_error($conn) . "<br>";
}

// Create Venues table
$sql = "CREATE TABLE venues (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    address TEXT NOT NULL,
    capacity INT NOT NULL,
    price_per_day DECIMAL(10,2) NOT NULL,
    description TEXT,
    image_path VARCHAR(255),
    is_available BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if(mysqli_query($conn, $sql)){
    echo "✓ Venues table created successfully.<br>";
} else{
    echo "Error creating venues table: " . mysqli_error($conn) . "<br>";
}

// Create Events table
$sql = "CREATE TABLE events (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    venue_id INT,
    user_id INT,
    event_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    ticket_price DECIMAL(10,2) NOT NULL,
    total_tickets INT NOT NULL,
    image_path VARCHAR(255),
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (venue_id) REFERENCES venues(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if(mysqli_query($conn, $sql)){
    echo "✓ Events table created successfully.<br>";
} else{
    echo "Error creating events table: " . mysqli_error($conn) . "<br>";
}

// Create Bookings table
$sql = "CREATE TABLE bookings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    event_id INT,
    user_id INT,
    num_tickets INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    payment_status ENUM('pending', 'completed') DEFAULT 'pending',
    booking_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if(mysqli_query($conn, $sql)){
    echo "✓ Bookings table created successfully.<br>";
} else{
    echo "Error creating bookings table: " . mysqli_error($conn) . "<br>";
}

// Create Payments table
$sql = "CREATE TABLE payments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    booking_id INT NOT NULL,
    bank_name VARCHAR(100) NOT NULL,
    account_number VARCHAR(18) NOT NULL,
    ifsc_code VARCHAR(11) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if(mysqli_query($conn, $sql)){
    echo "✓ Payments table created successfully.<br>";
} else{
    echo "Error creating payments table: " . mysqli_error($conn) . "<br>";
}

// Insert default admin user
$admin_user = "admin";
$admin_email = "admin@example.com";
$admin_password = password_hash("admin123", PASSWORD_DEFAULT);

$sql = "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'admin')";
$stmt = mysqli_prepare($conn, $sql);
if(!$stmt) {
    die("Error preparing statement: " . mysqli_error($conn));
}
mysqli_stmt_bind_param($stmt, "sss", $admin_user, $admin_email, $admin_password);

if(mysqli_stmt_execute($stmt)){
    echo "✓ Default admin user created (Username: admin, Password: admin123)<br>";
} else{
    echo "Error creating admin user: " . mysqli_stmt_error($stmt) . "<br>";
}

mysqli_stmt_close($stmt);

echo "<br><strong style='color: green;'>✓ Database initialization complete!</strong><br>";
echo "You can now access the application.<br>";
echo "<a href='auth/login.php'>Go to Login Page</a>";

mysqli_close($conn);
?>
