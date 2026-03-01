<?php
require_once "config/db.php";

// Function to set admin password
function setAdminPassword($username, $password) {
    global $conn;
    
    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Update or insert the admin user
    $sql = "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'admin') 
            ON DUPLICATE KEY UPDATE password = ?, role = 'admin'";
    
    if($stmt = mysqli_prepare($conn, $sql)){
        $email = $username . "@admin.com";
        mysqli_stmt_bind_param($stmt, "ssss", $username, $email, $hashed_password, $hashed_password);
        
        if(mysqli_stmt_execute($stmt)){
            return true;
        }
    }
    return false;
}

// Check if form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
    if(!empty($_POST["username"]) && !empty($_POST["password"])){
        $username = trim($_POST["username"]);
        $password = trim($_POST["password"]);
        
        if(setAdminPassword($username, $password)){
            $success = "Admin user '$username' has been set successfully!<br>Password: " . htmlspecialchars($password);
        } else {
            $error = "Error setting admin user. Please try again.";
        }
    } else {
        $error = "Please fill in all fields.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Set Admin Password</title>
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
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .container {
            width: 100%;
            max-width: 400px;
        }
        
        .form-box {
            background: rgba(255, 255, 255, 0.98);
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.4);
            border: 2px solid #051353;
        }
        
        h1 {
            color: #051353;
            margin-bottom: 10px;
            font-size: 1.8rem;
            text-align: center;
        }
        
        .form-text {
            color: #666;
            text-align: center;
            margin-bottom: 25px;
            font-size: 0.9rem;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
        }
        
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 2px solid #051353;
            border-radius: 8px;
            font-size: 1rem;
            background: rgba(5, 19, 83, 0.05);
            transition: all 0.3s ease;
        }
        
        input[type="text"]:focus,
        input[type="password"]:focus {
            outline: none;
            background: rgba(5, 19, 83, 0.1);
            box-shadow: 0 0 10px rgba(5, 19, 83, 0.3);
        }
        
        button {
            width: 100%;
            padding: 12px;
            background: #051353;
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        button:hover {
            background: #0a2366;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(5, 19, 83, 0.4);
        }
        
        .success {
            background: #c6f6d5;
            border: 2px solid #22543d;
            color: #22543d;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .error {
            background: #fed7d7;
            border: 2px solid #742a2a;
            color: #742a2a;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-box">
            <h1>🔐 Set Admin Password</h1>
            <p class="form-text">Create or update your admin account credentials</p>
            
            <?php if(isset($success)): ?>
                <div class="success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <?php if(isset($error)): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="username">Admin Username:</label>
                    <input type="text" id="username" name="username" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Admin Password:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit">Set Admin Account</button>
            </form>
        </div>
    </div>
</body>
</html>
