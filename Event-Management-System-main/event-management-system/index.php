<?php
require_once "config/db.php";
include "includes/header.php";

// Fetch featured events (approved events)
$sql = "SELECT e.*, v.name as venue_name, u.username as organizer 
        FROM events e 
        LEFT JOIN venues v ON e.venue_id = v.id 
        LEFT JOIN users u ON e.user_id = u.id 
        WHERE e.status = 'approved' 
        ORDER BY e.event_date DESC 
        LIMIT 6";
$result = mysqli_query($conn, $sql);
?>

<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        background: #000000;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        min-height: 100vh;
    }

    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
    }

    h1.text-center {
        color: #ffffff;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        margin: 40px 0;
        font-size: 3rem;
        font-weight: 700;
        letter-spacing: 1px;
    }

    /* Hero Section Styling */
    .card {
        background: rgba(255, 255, 255, 0.98);
        border: 1px solid rgba(5, 19, 83, 0.3);
        box-shadow: 0 10px 40px rgba(0,0,0,0.4);
        border-radius: 15px;
        padding: 40px;
        margin-bottom: 40px;
        backdrop-filter: blur(10px);
    }

    .card h2 {
        color: #051353;
        margin-bottom: 15px;
        font-size: 2rem;
        font-weight: 600;
    }

    .card p {
        color: #555;
        margin-bottom: 25px;
        font-size: 1.1rem;
        line-height: 1.6;
    }

    .card > div {
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
    }

    .btn-primary {
        background: linear-gradient(135deg, #051353 0%, #051353 100%);
        border: none;
        color: white;
        padding: 12px 30px;
        border-radius: 8px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s ease;
        display: inline-block;
    }

    .btn-primary:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(5, 19, 83, 0.91);
        color: white;
    }

    .btn-primary:active {
        transform: translateY(-1px);
    }

    .btn-secondary {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        border: none;
        color: white;
        padding: 12px 30px;
        border-radius: 8px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s ease;
        display: inline-block;
    }

    .btn-secondary:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(245, 87, 108, 0.4);
        color: white;
    }

    h2 {
        color: #ffffff;
        margin: 40px 0 30px;
        font-size: 2.2rem;
        font-weight: 700;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
    }

    /* Event Grid */
    .event-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 25px;
        padding: 10px;
        margin-bottom: 40px;
    }

    .event-card {
        background: rgba(255, 255, 255, 0.99);
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.4);
        transition: all 0.3s ease;
        overflow: hidden;
        border-left: 5px solid #051353;
    }

    .event-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 15px 50px rgba(0,0,0,0.5);
        border-left-color: #f5576c;
    }

    .event-card-body {
        padding: 25px;
    }

    .event-card-body h3 {
        color: #051353;
        margin-bottom: 15px;
        font-size: 1.5rem;
        font-weight: 700;
    }

    .event-card-body p {
        color: #666;
        margin-bottom: 12px;
        line-height: 1.6;
        font-size: 0.95rem;
    }

    .event-card-body strong {
        color: #333;
        font-weight: 600;
    }

    .event-details {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 8px;
        margin: 15px 0;
    }

    .event-details p {
        margin-bottom: 8px;
        font-size: 0.9rem;
    }

    .price-tag {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        color: white;
        padding: 8px 15px;
        border-radius: 20px;
        font-weight: 700;
        display: inline-block;
        margin: 15px 0;
    }

    .btn-book {
        background: linear-gradient(135deg, #051353 0%, #051353 100%);
        border: none;
        color: white;
        padding: 10px 25px;
        border-radius: 8px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s ease;
        display: inline-block;
        width: 100%;
        text-align: center;
        margin-top: 15px;
    }

    .btn-book:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(5, 19, 83, 0.6);
        color: white;
    }

    .no-events {
        background: rgba(255, 255, 255, 0.98);
        padding: 40px;
        border-radius: 15px;
        text-align: center;
        color: #333;
        font-size: 1.1rem;
        border: 1px solid rgba(5, 19, 83, 0.3);
        box-shadow: 0 10px 40px rgba(0,0,0,0.4);
    }

    @media (max-width: 768px) {
        .event-grid {
            grid-template-columns: 1fr;
        }

        h1.text-center {
            font-size: 2rem;
        }

        h2 {
            font-size: 1.5rem;
        }

        .card {
            padding: 20px;
        }
    }
</style>

<h1 class="text-center">🎪 Welcome to Event Management System</h1>

<?php if(!isset($_SESSION["loggedin"])): ?>
<div class="card mb-2">
    <h2>🎉 Get Started</h2>
    <p>Join us to create and manage events, book venues, and more!</p>
    <div>
        <a href="auth/register.php" class="btn btn-primary">Reister Now</a>
        <a href="auth/login.php" class="btn btn-secondary">Login</a>
    </div>
</div>
<?php endif; ?>

<h2>Featured Events</h2>
<div class="event-grid">
    <?php if(mysqli_num_rows($result) > 0): ?>
        <?php while($event = mysqli_fetch_assoc($result)): ?>
            <div class="event-card">
                <div class="event-card-body">
                    <h3><?php echo htmlspecialchars($event['title']); ?></h3>
                    <p><?php echo htmlspecialchars(substr($event['description'], 0, 100)); ?>...</p>
                    
                    <div class="event-details">
                        <p><strong>📍 Venue:</strong> <?php echo htmlspecialchars($event['venue_name']); ?></p>
                        <p><strong>📅 Date:</strong> <?php echo htmlspecialchars($event['event_date']); ?></p>
                        <p><strong>⏰ Time:</strong> <?php echo htmlspecialchars($event['start_time']); ?> - <?php echo htmlspecialchars($event['end_time']); ?></p>
                        <p><strong>👤 Organizer:</strong> <?php echo htmlspecialchars($event['organizer']); ?></p>
                    </div>
                    
                    <div class="price-tag">💰 $<?php echo htmlspecialchars($event['ticket_price']); ?> per ticket</div>
                    
                    <?php if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
                        <a href="user/book_ticket.php?event_id=<?php echo $event['id']; ?>" class="btn-book">Book Tickets</a>
                    <?php else: ?>
                        <a href="auth/login.php" class="btn-book">Login to Book</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="no-events">
            <p>🎉 No events available at the moment. Check back soon!</p>
        </div>
    <?php endif; ?>
</div>

<?php include "includes/footer.php"; ?> 