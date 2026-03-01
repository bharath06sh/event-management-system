<?php
require_once "config/db.php";

echo "Fixing venues table structure...<br><br>";

// Check current table structure
$check_sql = "DESCRIBE venues";
$result = mysqli_query($conn, $check_sql);

echo "Current venues table columns:<br>";
$columns = [];
while($row = mysqli_fetch_assoc($result)) {
    $columns[] = $row['Field'];
    echo "- " . $row['Field'] . " (" . $row['Type'] . ")<br>";
}

echo "<br>";

// Add address column if it doesn't exist
if (!in_array('address', $columns)) {
    $sql = "ALTER TABLE venues ADD COLUMN address VARCHAR(255) AFTER name";
    if(mysqli_query($conn, $sql)) {
        echo "✓ Added 'address' column<br>";
    } else {
        echo "Error adding address column: " . mysqli_error($conn) . "<br>";
    }
}

// Add image_path column if it doesn't exist
if (!in_array('image_path', $columns)) {
    $sql = "ALTER TABLE venues ADD COLUMN image_path VARCHAR(255) AFTER description";
    if(mysqli_query($conn, $sql)) {
        echo "✓ Added 'image_path' column<br>";
    } else {
        echo "Error adding image_path column: " . mysqli_error($conn) . "<br>";
    }
}

// Populate address with location data if location exists and address is empty
if (in_array('location', $columns) && in_array('address', $columns)) {
    $update_sql = "UPDATE venues SET address = location WHERE address IS NULL OR address = ''";
    if(mysqli_query($conn, $update_sql)) {
        echo "✓ Populated address column with location data<br>";
    } else {
        echo "Warning: Could not populate address column: " . mysqli_error($conn) . "<br>";
    }
}

echo "<br>";

// Verify new structure
$verify_sql = "DESCRIBE venues";
$result = mysqli_query($conn, $verify_sql);

echo "Updated venues table columns:<br>";
while($row = mysqli_fetch_assoc($result)) {
    echo "- " . $row['Field'] . " (" . $row['Type'] . ")<br>";
}

echo "<br><strong style='color: green;'>✓ Table structure fixed!</strong><br>";
echo "You can now add venues successfully.";

mysqli_close($conn);
?>
