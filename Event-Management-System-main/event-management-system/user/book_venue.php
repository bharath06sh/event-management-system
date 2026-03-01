<?php
require_once "../config/db.php";
require_once "../auth/auth_check.php";

// Check if user is logged in
checkLogin();

// Get available venues
$venues_sql = "SELECT * FROM venues WHERE is_available = 1 ORDER BY name ASC";
$venues_result = mysqli_query($conn, $venues_sql);

$venue_id = $booking_date = $start_time = $end_time = "";
$venue_err = $date_err = $time_err = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){
    // Validate venue
    if(empty(trim($_POST["venue_id"]))){
        $venue_err = "Please select a venue.";
    } else {
        $venue_id = trim($_POST["venue_id"]);
    }
    
    // Validate date
    if(empty(trim($_POST["booking_date"]))){
        $date_err = "Please select booking date.";
    } else {
        $booking_date = trim($_POST["booking_date"]);
    }
    
    // Validate times
    if(empty(trim($_POST["start_time"])) || empty(trim($_POST["end_time"]))){
        $time_err = "Please select both start and end times.";
    } else {
        $start_time = trim($_POST["start_time"]);
        $end_time = trim($_POST["end_time"]);
    }
    
    // If no errors, proceed with booking
    if(empty($venue_err) && empty($date_err) && empty($time_err)){
        $sql = "INSERT INTO venue_bookings (user_id, venue_id, booking_date, start_time, end_time) 
                VALUES (?, ?, ?, ?, ?)";
        
        if($stmt = mysqli_prepare($conn, $sql)){
            mysqli_stmt_bind_param($stmt, "iisss", $param_user, $param_venue, $param_date, $param_start, $param_end);
            
            $param_user = $_SESSION["id"];
            $param_venue = $venue_id;
            $param_date = $booking_date;
            $param_start = $start_time;
            $param_end = $end_time;
            
            if(mysqli_stmt_execute($stmt)){
                header("location: dashboard.php");
                exit();
            } else{
                echo "Something went wrong. Please try again later.";
            }

            mysqli_stmt_close($stmt);
        }
    }
    
    mysqli_close($conn);
}

include "../includes/header.php";
?>

<div class="book-venue-container">
    <div class="book-venue-header">
        <h1><i class="fas fa-map-marked-alt"></i> Book a Venue</h1>
        <p>Select a venue and choose your preferred date and time</p>
    </div>

    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="venue-booking-form">
        <!-- Venue Selection Grid -->
        <div class="venue-selection-section">
            <h2 class="section-title"><i class="fas fa-building"></i> Available Venues</h2>
            <div class="venues-grid">
                <?php while($venue = mysqli_fetch_assoc($venues_result)): ?>
                    <div class="venue-card-wrapper">
                        <input type="radio" name="venue_id" value="<?php echo $venue['id']; ?>" 
                               id="venue_<?php echo $venue['id']; ?>" class="venue-radio"
                               <?php echo ($venue_id == $venue['id']) ? 'checked' : ''; ?>>
                        
                        <label for="venue_<?php echo $venue['id']; ?>" class="venue-card">
                            <div class="venue-image-wrapper">
                                <?php if(!empty($venue['image_path']) && file_exists("../" . $venue['image_path'])): ?>
                                    <img src="<?php echo htmlspecialchars("../" . $venue['image_path']); ?>" 
                                         alt="<?php echo htmlspecialchars($venue['name']); ?>" 
                                         class="venue-image" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <?php else: ?>
                                    <div class="venue-image-placeholder" style="display: <?php echo (!empty($venue['image_path']) && file_exists("../" . $venue['image_path'])) ? 'none' : 'flex'; ?>;">
                                        <i class="fas fa-building"></i>
                                        <span>No Image</span>
                                    </div>
                                <?php endif; ?>
                                <div class="venue-overlay">
                                    <button type="button" class="select-btn">
                                        <i class="fas fa-check-circle"></i> Select
                                    </button>
                                </div>
                            </div>
                            
                            <div class="venue-details">
                                <h3 class="venue-name"><?php echo htmlspecialchars($venue['name']); ?></h3>
                                
                                <div class="venue-info">
                                    <span class="info-item">
                                        <i class="fas fa-map-pin"></i>
                                        <?php echo htmlspecialchars($venue['address'] ?? $venue['location']); ?>
                                    </span>
                                </div>
                                
                                <div class="venue-specs">
                                    <div class="spec-item">
                                        <i class="fas fa-users"></i>
                                        <span><?php echo $venue['capacity']; ?> Guests</span>
                                    </div>
                                    <div class="spec-item">
                                        <i class="fas fa-dollar-sign"></i>
                                        <span>$<?php echo number_format($venue['price_per_day'], 2); ?>/day</span>
                                    </div>
                                </div>
                                
                                <?php if(!empty($venue['description'])): ?>
                                    <p class="venue-description"><?php echo htmlspecialchars(substr($venue['description'], 0, 100)); ?>...</p>
                                <?php endif; ?>
                            </div>
                        </label>
                    </div>
                <?php endwhile; ?>
            </div>
            <?php if(!empty($venue_err)): ?>
                <div class="error-message"><?php echo $venue_err; ?></div>
            <?php endif; ?>
        </div>

        <!-- Booking Details Section -->
        <div class="booking-details-section">
            <h2 class="section-title"><i class="fas fa-calendar-check"></i> Booking Details</h2>
            
            <div class="form-group">
                <label class="form-label">Booking Date</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                    <input type="date" name="booking_date" class="form-control <?php echo (!empty($date_err)) ? 'is-invalid' : ''; ?>" 
                           value="<?php echo $booking_date; ?>" min="<?php echo date('Y-m-d'); ?>">
                </div>
                <?php if(!empty($date_err)): ?>
                    <span class="invalid-feedback"><?php echo $date_err; ?></span>
                <?php endif; ?>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Start Time</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-clock"></i></span>
                        <input type="time" name="start_time" class="form-control <?php echo (!empty($time_err)) ? 'is-invalid' : ''; ?>" 
                               value="<?php echo $start_time; ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">End Time</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-clock"></i></span>
                        <input type="time" name="end_time" class="form-control <?php echo (!empty($time_err)) ? 'is-invalid' : ''; ?>" 
                               value="<?php echo $end_time; ?>">
                    </div>
                </div>
            </div>
            <?php if(!empty($time_err)): ?>
                <span class="invalid-feedback" style="display: block;"><?php echo $time_err; ?></span>
            <?php endif; ?>
        </div>

        <!-- Action Buttons -->
        <div class="form-actions">
            <button type="submit" class="btn-book">
                <i class="fas fa-check-circle"></i> Book Venue
            </button>
            <a href="dashboard.php" class="btn-cancel">
                <i class="fas fa-times"></i> Cancel
            </a>
        </div>
    </form>
</div>

<style>
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @keyframes slideIn {
        from { transform: translateX(-20px); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }

    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.02); }
        100% { transform: scale(1); }
    }

    @keyframes gradientBG {
        0% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
        100% { background-position: 0% 50%; }
    }

    body {
        background: linear-gradient(135deg, #87CEEB 0%, #B0E0E6 50%, #E0F7FF 100%);
        background-size: 400% 400%;
        animation: gradientBG 15s ease infinite;
        color: #2c3e50;
        min-height: 100vh;
    }

    .book-venue-container {
        max-width: 1200px;
        margin: 2rem auto;
        padding: 2rem;
        animation: fadeIn 0.8s ease-out;
    }

    .book-venue-header {
        text-align: center;
        margin-bottom: 2rem;
        animation: slideIn 0.8s ease-out;
    }

    .book-venue-header h1 {
        font-size: 2.5rem;
        color: #2c3e50;
        margin-bottom: 0.5rem;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        animation: pulse 3s infinite;
    }

    .book-venue-header p {
        color: #34495e;
        font-size: 1.1rem;
    }

    .venue-booking-form {
        background: rgba(255, 255, 255, 0.95);
        padding: 2rem;
        border-radius: 15px;
        box-shadow: 0 8px 32px rgba(52, 152, 219, 0.2);
        border: 1px solid rgba(52, 152, 219, 0.3);
        backdrop-filter: blur(10px);
        animation: fadeIn 1s ease-out;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .venue-booking-form:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 40px rgba(52, 152, 219, 0.3);
    }

    .section-title {
        font-size: 1.5rem;
        color: #2c3e50;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        border-bottom: 2px solid #3498db;
        padding-bottom: 0.5rem;
    }

    .venue-selection-section {
        margin-bottom: 2rem;
    }

    .venues-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .venue-radio {
        display: none;
    }

    .venue-card-wrapper {
        animation: fadeIn 0.6s ease-out forwards;
    }

    .venue-radio:checked + .venue-card {
        transform: scale(1.02);
        box-shadow: 0 0 0 3px #3498db, 0 10px 30px rgba(52, 152, 219, 0.4);
    }

    .venue-radio:checked + .venue-card::after {
        content: '';
        position: absolute;
        top: 10px;
        right: 10px;
        width: 30px;
        height: 30px;
        background: #27ae60;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.2rem;
        z-index: 10;
        box-shadow: 0 2px 8px rgba(39, 174, 96, 0.4);
    }

    .venue-card {
        display: flex;
        flex-direction: column;
        height: 100%;
        background: white;
        border: 2px solid #e0e0e0;
        border-radius: 12px;
        overflow: hidden;
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
    }

    .venue-card:hover {
        border-color: #3498db;
        transform: translateY(-8px);
        box-shadow: 0 8px 25px rgba(52, 152, 219, 0.2);
    }

    .venue-image-wrapper {
        position: relative;
        width: 100%;
        height: 200px;
        overflow: hidden;
        background: #f5f5f5;
    }

    .venue-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }

    .venue-card:hover .venue-image {
        transform: scale(1.05);
    }

    .venue-image-placeholder {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        background: linear-gradient(135deg, #e8f4f8 0%, #f0fafb 100%);
        color: #95a5a6;
        font-size: 3rem;
        gap: 0.5rem;
    }

    .venue-image-placeholder span {
        font-size: 0.9rem;
        font-weight: 500;
    }

    .venue-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.7);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .venue-card:hover .venue-overlay {
        opacity: 1;
    }

    .select-btn {
        background: #27ae60;
        color: white;
        border: none;
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.3s ease;
        font-size: 1rem;
    }

    .select-btn:hover {
        background: #229954;
        transform: scale(1.1);
        box-shadow: 0 4px 12px rgba(39, 174, 96, 0.4);
    }

    .venue-details {
        padding: 1rem;
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .venue-name {
        font-size: 1.1rem;
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 0.5rem;
        line-height: 1.3;
    }

    .venue-info {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 0.75rem;
        font-size: 0.9rem;
        color: #7f8c8d;
        flex-wrap: wrap;
    }

    .info-item {
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    .venue-specs {
        display: flex;
        gap: 1rem;
        margin-bottom: 0.75rem;
        flex-wrap: wrap;
    }

    .spec-item {
        display: flex;
        align-items: center;
        gap: 0.4rem;
        background: #f0f8ff;
        padding: 0.4rem 0.8rem;
        border-radius: 6px;
        font-size: 0.85rem;
        color: #3498db;
        font-weight: 500;
    }

    .venue-description {
        font-size: 0.85rem;
        color: #7f8c8d;
        margin: 0;
        flex: 1;
        line-height: 1.4;
    }

    .booking-details-section {
        margin-bottom: 2rem;
        padding-top: 2rem;
        border-top: 2px solid #f0f0f0;
    }

    .form-group {
        margin-bottom: 1.5rem;
        animation: slideIn 0.8s ease-out;
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .form-label {
        display: block;
        margin-bottom: 0.5rem;
        color: #2c3e50;
        font-weight: 500;
        transition: color 0.3s ease;
    }

    .input-group {
        display: flex;
        align-items: center;
        transition: transform 0.3s ease;
    }

    .input-group:hover {
        transform: translateX(5px);
    }

    .input-group-text {
        background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
        border: 1px solid #3498db;
        border-right: none;
        color: white;
        padding: 0.75rem 1rem;
        border-radius: 8px 0 0 8px;
        transition: all 0.3s ease;
    }

    .input-group:hover .input-group-text {
        background: linear-gradient(135deg, #2980b9 0%, #3498db 100%);
    }

    .form-control {
        background: white;
        border: 1px solid #bdc3c7;
        color: #2c3e50;
        padding: 0.75rem 1rem;
        border-radius: 0 8px 8px 0;
        width: 100%;
        transition: all 0.3s ease;
    }

    .form-control:focus {
        outline: none;
        border-color: #3498db;
        box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
        background: white;
        transform: translateY(-2px);
    }

    .invalid-feedback {
        color: #e74c3c;
        font-size: 0.875rem;
        margin-top: 0.25rem;
        animation: slideIn 0.3s ease-out;
    }

    .form-control.is-invalid {
        border-color: #e74c3c;
    }

    .error-message {
        background: #fadad1;
        color: #c0392b;
        padding: 1rem;
        border-radius: 8px;
        border-left: 4px solid #e74c3c;
        margin-top: 1rem;
    }

    .form-actions {
        display: flex;
        gap: 1rem;
        margin-top: 2rem;
        animation: fadeIn 1s ease-out;
    }

    .btn-book {
        background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
        color: white;
        border: none;
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.3s ease;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        position: relative;
        overflow: hidden;
        flex: 1;
        justify-content: center;
    }

    .btn-book::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
        transition: 0.5s;
    }

    .btn-book:hover::before {
        left: 100%;
    }

    .btn-book:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(52, 152, 219, 0.4);
        background: linear-gradient(135deg, #2980b9 0%, #3498db 100%);
    }

    .btn-cancel {
        background: white;
        color: #2c3e50;
        border: 1px solid #bdc3c7;
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        font-weight: 600;
        text-decoration: none;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        transition: all 0.3s ease;
    }

    .btn-cancel:hover {
        background: #f8f9fa;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        border-color: #3498db;
        color: #3498db;
    }

    @media (max-width: 768px) {
        .book-venue-container {
            padding: 1rem;
            margin: 1rem;
        }

        .venues-grid {
            grid-template-columns: 1fr;
        }

        .form-row {
            grid-template-columns: 1fr;
        }

        .form-actions {
            flex-direction: column;
        }

        .btn-book, .btn-cancel {
            width: 100%;
        }

        .book-venue-header h1 {
            font-size: 1.8rem;
        }
    }
</style>

<?php include "../includes/footer.php"; ?> 