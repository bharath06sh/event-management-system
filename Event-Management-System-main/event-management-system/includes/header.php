<?php
require_once __DIR__ . "/../auth/auth_check.php";
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Management System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .navbar {
            background: #000000;
            padding: 0;
            box-shadow: 0 4px 20px rgba(74, 123, 167, 0.3);
            position: sticky;
            top: 0;
            z-index: 1000;
            border-bottom: 2px solid #4a7ba7;
        }

        .navbar .container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 20px;
            flex-wrap: wrap;
        }

        .navbar a {
            color: white;
            text-decoration: none;
            padding: 15px 20px;
            display: inline-flex;
            align-items: center;
            font-weight: 500;
            transition: all 0.3s ease;
            border-radius: 5px;
            margin: 5px;
            position: relative;
        }

        .navbar a:first-child {
            font-size: 1.3rem;
            font-weight: 700;
            margin-right: auto;
            padding: 15px 0;
        }

        .navbar a:first-child::before {
            content: "🎪 ";
            margin-right: 8px;
        }

        .navbar a:hover {
            background: rgba(255, 255, 255, 0.15);
            transform: translateY(-2px);
        }

        .navbar a:active {
            transform: translateY(0);
        }

        /* Auth Links Styling */
        .navbar a[href*="login.php"],
        .navbar a[href*="register.php"] {
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .navbar a[href*="register.php"] {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            border: none;
            font-weight: 600;
            padding: 12px 25px;
            margin: 8px 5px;
        }

        .navbar a[href*="register.php"]:hover {
            background: linear-gradient(135deg, #f5576c 0%, #f093fb 100%);
            box-shadow: 0 5px 15px rgba(245, 87, 108, 0.4);
        }

        .navbar a[href*="login.php"]:hover {
            background: rgba(255, 255, 255, 0.25);
            box-shadow: 0 5px 15px rgba(255, 255, 255, 0.2);
        }

        /* Admin/User Links */
        .navbar a[href*="dashboard.php"],
        .navbar a[href*="manage"],
        .navbar a[href*="create"],
        .navbar a[href*="book"],
        .navbar a[href*="view"] {
            background: rgba(255, 255, 255, 0.1);
            border-left: 3px solid rgba(255, 215, 0, 0.3);
        }

        .navbar a[href*="logout.php"] {
            background: rgba(255, 87, 108, 0.8);
            border: 1px solid rgba(255, 87, 108, 0.5);
        }

        .navbar a[href*="logout.php"]:hover {
            background: rgba(255, 87, 108, 1);
            box-shadow: 0 5px 15px rgba(255, 87, 108, 0.4);
        }

        @media (max-width: 768px) {
            .navbar .container {
                flex-direction: column;
                align-items: stretch;
            }

            .navbar a {
                width: 100%;
                text-align: center;
                padding: 12px 15px;
                border-radius: 0;
            }

            .navbar a:first-child {
                margin-right: 0;
                border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <a href="./index.php">Event Management</a>
            <?php if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
                <?php if(isset($_SESSION["role"]) && $_SESSION["role"] === "admin"): ?>
                    <a href="/Event-Management-System-main/event-management-system/admin/dashboard.php">📊 Admin Dashboard</a>
                    <a href="/Event-Management-System-main/event-management-system/admin/manage_users.php">👥 Manage Users</a>
                    <a href="/Event-Management-System-main/event-management-system/admin/manage_venues.php">🏢 Manage Venues</a>
                    <a href="/Event-Management-System-main/event-management-system/admin/manage_tickets.php">🎫 Manage Tickets</a>
                <?php else: ?>
                    <a href="/Event-Management-System-main/event-management-system/user/dashboard.php">📈 Dashboard</a>
                    <a href="/Event-Management-System-main/event-management-system/user/create_event.php">✨ Create Event</a>
                    <a href="/Event-Management-System-main/event-management-system/user/book_venue.php">🏛️ Book Venue</a>
                    <a href="/Event-Management-System-main/event-management-system/user/view_events.php">🎭 View Events</a>
                <?php endif; ?>
                <a href="/Event-Management-System-main/event-management-system/auth/logout.php">🚪 Logout</a>
            <?php else: ?>
                <a href="./auth/login.php">🔐 Login</a>
                <a href="./auth/register.php">✍️ Register</a>
            <?php endif; ?>
        </div>
    </nav>
    <div class="container">