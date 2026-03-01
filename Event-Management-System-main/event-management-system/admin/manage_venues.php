<?php
require_once "../config/db.php";
require_once "../auth/auth_check.php";

// Check if user is admin
checkAdmin();

// Initialize variables
$success_msg = "";
$error_msg = "";

// Create uploads directory if it doesn't exist
$upload_dir = "../uploads/venues/";
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Process form submission
if($_SERVER["REQUEST_METHOD"] == "POST"){
    if(isset($_POST["action"])){
        if($_POST["action"] == "add" || $_POST["action"] == "edit"){
            $name = trim($_POST["name"] ?? "");
            $address = trim($_POST["address"] ?? "");
            $capacity = !empty($_POST["capacity"]) ? (int)$_POST["capacity"] : 0;
            $price = !empty($_POST["price"]) ? (float)$_POST["price"] : 0;
            $description = trim($_POST["description"] ?? "");
            $image_path = null;
            
            // Handle image upload
            if(isset($_FILES["venue_image"]) && $_FILES["venue_image"]["error"] == 0) {
                $allowed = array("jpg" => "image/jpg", "jpeg" => "image/jpeg", "gif" => "image/gif", "png" => "image/png");
                $filename = $_FILES["venue_image"]["name"];
                $filetype = $_FILES["venue_image"]["type"];
                $filesize = $_FILES["venue_image"]["size"];
                
                // Verify file extension
                $ext = pathinfo($filename, PATHINFO_EXTENSION);
                if(!array_key_exists($ext, $allowed)) {
                    $error_msg = "Error: Please select a valid file format.";
                }
                
                // Verify file size - 5MB maximum
                $maxsize = 5 * 1024 * 1024;
                if($filesize > $maxsize) {
                    $error_msg = "Error: File size is larger than the allowed limit.";
                }
                
                if(empty($error_msg)) {
                    // Generate unique filename
                    $new_filename = uniqid() . '.' . $ext;
                    $upload_path = $upload_dir . $new_filename;
                    
                    if(move_uploaded_file($_FILES["venue_image"]["tmp_name"], $upload_path)) {
                        $image_path = "uploads/venues/" . $new_filename;
                    }
                }
            }
            
            if(!empty($name) && !empty($address) && $capacity > 0 && $price > 0 && empty($error_msg)){
                if($_POST["action"] == "add"){
                    $sql = "INSERT INTO venues (name, address, capacity, price_per_day, description, image_path) VALUES (?, ?, ?, ?, ?, ?)";
                    $stmt = mysqli_prepare($conn, $sql);
                    if(!$stmt) {
                        $error_msg = "Prepare failed: " . mysqli_error($conn);
                    } else {
                        mysqli_stmt_bind_param($stmt, "ssidss", $name, $address, $capacity, $price, $description, $image_path);
                        if(!mysqli_stmt_execute($stmt)) {
                            $error_msg = "Execute failed: " . mysqli_stmt_error($stmt);
                        } else {
                            $success_msg = "Venue added successfully!";
                        }
                        mysqli_stmt_close($stmt);
                    }
                } else {
                    // For edit, only update image if new one is uploaded
                    if($image_path) {
                        $sql = "UPDATE venues SET name = ?, address = ?, capacity = ?, price_per_day = ?, description = ?, image_path = ? WHERE id = ?";
                        $stmt = mysqli_prepare($conn, $sql);
                        if(!$stmt) {
                            $error_msg = "Prepare failed: " . mysqli_error($conn);
                        } else {
                            $venue_id = (int)$_POST["venue_id"];
                            mysqli_stmt_bind_param($stmt, "ssidssi", $name, $address, $capacity, $price, $description, $image_path, $venue_id);
                            if(!mysqli_stmt_execute($stmt)) {
                                $error_msg = "Execute failed: " . mysqli_stmt_error($stmt);
                            } else {
                                $success_msg = "Venue updated successfully!";
                            }
                            mysqli_stmt_close($stmt);
                        }
                    } else {
                        $sql = "UPDATE venues SET name = ?, address = ?, capacity = ?, price_per_day = ?, description = ? WHERE id = ?";
                        $stmt = mysqli_prepare($conn, $sql);
                        if(!$stmt) {
                            $error_msg = "Prepare failed: " . mysqli_error($conn);
                        } else {
                            $venue_id = (int)$_POST["venue_id"];
                            mysqli_stmt_bind_param($stmt, "ssidsi", $name, $address, $capacity, $price, $description, $venue_id);
                            if(!mysqli_stmt_execute($stmt)) {
                                $error_msg = "Execute failed: " . mysqli_stmt_error($stmt);
                            } else {
                                $success_msg = "Venue updated successfully!";
                            }
                            mysqli_stmt_close($stmt);
                        }
                    }
                }
            } else {
                if(empty($name)) $error_msg = "Venue name is required.";
                elseif(empty($address)) $error_msg = "Address is required.";
                elseif($capacity <= 0) $error_msg = "Capacity must be greater than 0.";
                elseif($price <= 0) $error_msg = "Price must be greater than 0.";
            }
        } elseif($_POST["action"] == "delete" && isset($_POST["venue_id"])){
            $venue_id = (int)$_POST["venue_id"];
            
            // Get image path before deleting
            $sql = "SELECT image_path FROM venues WHERE id = ?";
            $stmt = mysqli_prepare($conn, $sql);
            if(!$stmt) {
                $error_msg = "Prepare failed: " . mysqli_error($conn);
            } else {
                mysqli_stmt_bind_param($stmt, "i", $venue_id);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_bind_result($stmt, $old_image_path);
                mysqli_stmt_fetch($stmt);
                mysqli_stmt_close($stmt);
                
                // First, delete all bookings associated with events of this venue
                $sql = "DELETE FROM bookings WHERE event_id IN (SELECT id FROM events WHERE venue_id = ?)";
                $stmt = mysqli_prepare($conn, $sql);
                if($stmt) {
                    mysqli_stmt_bind_param($stmt, "i", $venue_id);
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_close($stmt);
                }
                
                // Second, delete all events associated with this venue
                $sql = "DELETE FROM events WHERE venue_id = ?";
                $stmt = mysqli_prepare($conn, $sql);
                if($stmt) {
                    mysqli_stmt_bind_param($stmt, "i", $venue_id);
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_close($stmt);
                }
                
                // Finally, delete the venue
                $sql = "DELETE FROM venues WHERE id = ?";
                if($stmt = mysqli_prepare($conn, $sql)){
                    mysqli_stmt_bind_param($stmt, "i", $venue_id);
                    if(mysqli_stmt_execute($stmt)){
                        $success_msg = "Venue deleted successfully!";
                        // Delete the image file if it exists
                        if($old_image_path && file_exists("../" . $old_image_path)) {
                            unlink("../" . $old_image_path);
                        }
                    } else {
                        $error_msg = "Error deleting venue: " . mysqli_stmt_error($stmt);
                    }
                    mysqli_stmt_close($stmt);
                } else {
                    $error_msg = "Prepare failed: " . mysqli_error($conn);
                }
            }
        }
    }
}

// Fetch all venues
$sql = "SELECT * FROM venues ORDER BY name ASC";
$result = mysqli_query($conn, $sql);
if(!$result) {
    die("Query failed: " . mysqli_error($conn));
}

include "../includes/header.php";
?>

<style>
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @keyframes slideIn {
        from { transform: translateX(-20px); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }

    .manage-venues-container {
        padding: 2rem;
        animation: fadeIn 0.8s ease-out;
    }

    .page-header {
        margin-bottom: 2rem;
        animation: slideIn 0.8s ease-out;
    }

    .page-header h2 {
        color: #2d3748;
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }

    .page-header p {
        color: #718096;
        font-size: 1.1rem;
    }

    .form-card {
        background: white;
        border-radius: 15px;
        padding: 1.5rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        margin-bottom: 2rem;
        animation: fadeIn 0.8s ease-out;
    }

    .form-card h3 {
        color: #2d3748;
        font-size: 1.5rem;
        font-weight: 600;
        margin-bottom: 1.5rem;
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-group label {
        color: #4a5568;
        font-weight: 600;
        margin-bottom: 0.5rem;
        display: block;
    }

    .form-control {
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 0.75rem;
        width: 100%;
        transition: all 0.3s ease;
    }

    .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        outline: none;
    }

    .btn {
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        font-weight: 500;
        transition: all 0.3s ease;
        cursor: pointer;
        border: none;
    }

    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    }

    .btn-secondary {
        background: #e2e8f0;
        color: #4a5568;
    }

    .btn-secondary:hover {
        background: #cbd5e0;
        transform: translateY(-2px);
    }

    .btn-danger {
        background: linear-gradient(135deg, #f56565 0%, #c53030 100%);
        color: white;
    }

    .btn-danger:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(245, 101, 101, 0.3);
    }

    .venue-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 1.5rem;
        padding: 1rem;
    }

    .venue-card {
        background: white;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
        animation: fadeIn 0.8s ease-out;
    }

    .venue-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
    }

    .venue-image {
        width: 100%;
        height: 200px;
        object-fit: cover;
    }

    .venue-image-placeholder {
        width: 100%;
        height: 200px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-style: italic;
    }

    .venue-details {
        padding: 1.5rem;
    }

    .venue-details h4 {
        color: #2d3748;
        font-size: 1.25rem;
        font-weight: 600;
        margin-bottom: 1rem;
    }

    .venue-details p {
        color: #4a5568;
        margin-bottom: 0.5rem;
    }

    .venue-details strong {
        color: #2d3748;
    }

    .venue-actions {
        margin-top: 1rem;
        display: flex;
        gap: 0.5rem;
    }

    .modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 1000;
        animation: fadeIn 0.3s ease-out;
    }

    .modal-content {
        background: white;
        padding: 2rem;
        border-radius: 15px;
        width: 500px;
        max-width: 90%;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .modal-content h3 {
        color: #2d3748;
        font-size: 1.5rem;
        font-weight: 600;
        margin-bottom: 1.5rem;
    }

    #current_image {
        margin-top: 1rem;
    }

    #current_image img {
        max-width: 200px;
        max-height: 150px;
        object-fit: cover;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .alert {
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1.5rem;
        animation: fadeIn 0.8s ease-out;
    }

    .alert-success {
        background: #c6f6d5;
        color: #2f855a;
        border: 1px solid #9ae6b4;
    }

    .alert-danger {
        background: #fed7d7;
        color: #c53030;
        border: 1px solid #feb2b2;
    }

    .alert-info {
        background: #e9d8fd;
        color: #553c9a;
        border: 1px solid #d6bcfa;
    }

    @media (max-width: 768px) {
        .manage-venues-container {
            padding: 1rem;
        }

        .page-header h2 {
            font-size: 2rem;
        }

        .venue-grid {
            grid-template-columns: 1fr;
        }

        .btn {
            width: 100%;
            margin-bottom: 0.5rem;
        }
    }
</style>

<div class="manage-venues-container">
    <div class="page-header">
        <h2><i class="fas fa-building"></i> Manage Venues</h2>
        <p>Add, edit, and manage venue information</p>
    </div>

    <?php if(!empty($success_msg)): ?>
        <div style="padding: 15px; margin-bottom: 20px; background-color: #d4edda; border: 1px solid #c3e6cb; border-radius: 5px; color: #155724;">
            <strong>✓ Success!</strong> <?php echo htmlspecialchars($success_msg); ?>
        </div>
    <?php endif; ?>

    <?php if(!empty($error_msg)): ?>
        <div style="padding: 15px; margin-bottom: 20px; background-color: #f8d7da; border: 1px solid #f5c6cb; border-radius: 5px; color: #721c24;">
            <strong>✗ Error!</strong> <?php echo htmlspecialchars($error_msg); ?>
        </div>
    <?php endif; ?>

    <div class="form-card">
        <h3><i class="fas fa-plus-circle"></i> Add New Venue</h3>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
            <input type="hidden" name="action" value="add">
            <div class="form-group">
                <label><i class="fas fa-signature"></i> Venue Name</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="form-group">
                <label><i class="fas fa-map-marker-alt"></i> Address</label>
                <textarea name="address" class="form-control" required></textarea>
            </div>
            <div class="form-group">
                <label><i class="fas fa-users"></i> Capacity</label>
                <input type="number" name="capacity" class="form-control" required>
            </div>
            <div class="form-group">
                <label><i class="fas fa-dollar-sign"></i> Price per Day ($)</label>
                <input type="number" step="0.01" name="price" class="form-control" required>
            </div>
            <div class="form-group">
                <label><i class="fas fa-align-left"></i> Description</label>
                <textarea name="description" class="form-control"></textarea>
            </div>
            <div class="form-group">
                <label><i class="fas fa-image"></i> Venue Image</label>
                <input type="file" name="venue_image" class="form-control" accept="image/*">
                <small class="form-text text-muted">Max file size: 5MB. Allowed formats: JPG, JPEG, PNG, GIF</small>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add Venue
                </button>
            </div>
        </form>
    </div>

    <div class="venue-grid">
        <?php if(mysqli_num_rows($result) > 0): ?>
            <?php while($venue = mysqli_fetch_assoc($result)): ?>
                <div class="venue-card">
                    <?php if(isset($venue['image_path']) && $venue['image_path']): ?>
                        <img src="../<?php echo htmlspecialchars($venue['image_path']); ?>" 
                             alt="<?php echo htmlspecialchars($venue['name']); ?>" 
                             class="venue-image">
                    <?php else: ?>
                        <div class="venue-image-placeholder">
                            <i class="fas fa-image"></i> No Image
                        </div>
                    <?php endif; ?>
                    <div class="venue-details">
                        <h4><?php echo htmlspecialchars($venue['name']); ?></h4>
                        <p><strong><i class="fas fa-map-marker-alt"></i> Address:</strong> <?php echo htmlspecialchars($venue['address']); ?></p>
                        <p><strong><i class="fas fa-users"></i> Capacity:</strong> <?php echo number_format($venue['capacity']); ?> people</p>
                        <p><strong><i class="fas fa-dollar-sign"></i> Price/Day:</strong> $<?php echo number_format($venue['price_per_day'], 2); ?></p>
                        <p><strong><i class="fas fa-info-circle"></i> Status:</strong> 
                            <?php if($venue['is_available']): ?>
                                <span class="badge badge-success">Available</span>
                            <?php else: ?>
                                <span class="badge badge-warning">Not Available</span>
                            <?php endif; ?>
                        </p>
                        <div class="venue-actions">
                            <button class="btn btn-primary" onclick="editVenue(<?php echo htmlspecialchars(json_encode($venue)); ?>)">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" style="display: inline;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="venue_id" value="<?php echo $venue['id']; ?>">
                                <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this venue?')">
                                    <i class="fas fa-trash-alt"></i> Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> No venues found.
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Edit Venue Modal -->
<div id="editModal" class="modal" style="display: none;">
    <div class="modal-content">
        <h3><i class="fas fa-edit"></i> Edit Venue</h3>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="venue_id" id="edit_venue_id">
            <div class="form-group">
                <label><i class="fas fa-signature"></i> Venue Name</label>
                <input type="text" name="name" id="edit_name" class="form-control" required>
            </div>
            <div class="form-group">
                <label><i class="fas fa-map-marker-alt"></i> Address</label>
                <textarea name="address" id="edit_address" class="form-control" required></textarea>
            </div>
            <div class="form-group">
                <label><i class="fas fa-users"></i> Capacity</label>
                <input type="number" name="capacity" id="edit_capacity" class="form-control" required>
            </div>
            <div class="form-group">
                <label><i class="fas fa-dollar-sign"></i> Price per Day ($)</label>
                <input type="number" step="0.01" name="price" id="edit_price" class="form-control" required>
            </div>
            <div class="form-group">
                <label><i class="fas fa-align-left"></i> Description</label>
                <textarea name="description" id="edit_description" class="form-control"></textarea>
            </div>
            <div class="form-group">
                <label><i class="fas fa-image"></i> Venue Image</label>
                <input type="file" name="venue_image" class="form-control" accept="image/*">
                <small class="form-text text-muted">Max file size: 5MB. Allowed formats: JPG, JPEG, PNG, GIF</small>
                <div id="current_image" class="mt-2"></div>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Venue
                </button>
                <button type="button" class="btn btn-secondary" onclick="closeModal()">
                    <i class="fas fa-times"></i> Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function editVenue(venue) {
    document.getElementById('edit_venue_id').value = venue.id;
    document.getElementById('edit_name').value = venue.name;
    document.getElementById('edit_address').value = venue.address;
    document.getElementById('edit_capacity').value = venue.capacity;
    document.getElementById('edit_price').value = venue.price_per_day;
    document.getElementById('edit_description').value = venue.description;
    
    // Show current image if exists
    const currentImageDiv = document.getElementById('current_image');
    if(venue.image_path) {
        currentImageDiv.innerHTML = `
            <p><i class="fas fa-image"></i> Current Image:</p>
            <img src="../${venue.image_path}" alt="Current venue image">
        `;
    } else {
        currentImageDiv.innerHTML = '<p><i class="fas fa-image"></i> No image uploaded</p>';
    }
    
    document.getElementById('editModal').style.display = 'flex';
}

function closeModal() {
    document.getElementById('editModal').style.display = 'none';
}

// Close modal when clicking outside
window.onclick = function(event) {
    var modal = document.getElementById('editModal');
    if (event.target == modal) {
        closeModal();
    }
}
</script>

<?php include "../includes/footer.php"; ?> 