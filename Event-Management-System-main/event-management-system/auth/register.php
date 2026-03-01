<?php
require_once "../config/db.php";

$username = $password = $confirm_password = $email = "";
$username_err = $password_err = $confirm_password_err = $email_err = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){
    // Validate username
    if(empty(trim($_POST["username"]))){
        $username_err = "Please enter a username.";
    } elseif(!preg_match('/^[a-zA-Z0-9_]+$/', trim($_POST["username"]))){
        $username_err = "Username can only contain letters, numbers, and underscores.";
    } else {
        $sql = "SELECT id FROM users WHERE username = ?";
        if($stmt = mysqli_prepare($conn, $sql)){
            mysqli_stmt_bind_param($stmt, "s", $param_username);
            $param_username = trim($_POST["username"]);
            
            if(mysqli_stmt_execute($stmt)){
                mysqli_stmt_store_result($stmt);
                if(mysqli_stmt_num_rows($stmt) == 1){
                    $username_err = "This username is already taken.";
                } else{
                    $username = trim($_POST["username"]);
                }
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }
            mysqli_stmt_close($stmt);
        }
    }
    
    // Validate email
    if(empty(trim($_POST["email"]))){
        $email_err = "Please enter an email.";
    } elseif(!filter_var(trim($_POST["email"]), FILTER_VALIDATE_EMAIL)){
        $email_err = "Please enter a valid email address.";
    } else {
        $sql = "SELECT id FROM users WHERE email = ?";
        if($stmt = mysqli_prepare($conn, $sql)){
            mysqli_stmt_bind_param($stmt, "s", $param_email);
            $param_email = trim($_POST["email"]);
            
            if(mysqli_stmt_execute($stmt)){
                mysqli_stmt_store_result($stmt);
                if(mysqli_stmt_num_rows($stmt) == 1){
                    $email_err = "This email is already registered.";
                } else{
                    $email = trim($_POST["email"]);
                }
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }
            mysqli_stmt_close($stmt);
        }
    }
    
    // Validate password
    if(empty(trim($_POST["password"]))){
        $password_err = "Please enter a password.";     
    } elseif(strlen(trim($_POST["password"])) < 6){
        $password_err = "Password must have at least 6 characters.";
    } else{
        $password = trim($_POST["password"]);
    }
    
    // Validate confirm password
    if(empty(trim($_POST["confirm_password"]))){
        $confirm_password_err = "Please confirm password.";     
    } else{
        $confirm_password = trim($_POST["confirm_password"]);
        if(empty($password_err) && ($password != $confirm_password)){
            $confirm_password_err = "Password did not match.";
        }
    }
    
    // Check input errors before inserting in database
    if(empty($username_err) && empty($password_err) && empty($confirm_password_err) && empty($email_err)){
        $sql = "INSERT INTO users (username, password, email) VALUES (?, ?, ?)";
        if($stmt = mysqli_prepare($conn, $sql)){
            mysqli_stmt_bind_param($stmt, "sss", $param_username, $param_password, $param_email);
            
            $param_username = $username;
            $param_password = password_hash($password, PASSWORD_DEFAULT);
            $param_email = $email;
            
            if(mysqli_stmt_execute($stmt)){
                header("location: login.php");
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }
            mysqli_stmt_close($stmt);
        }
    }
    mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Event Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: #000000;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .register-container {
            background: rgba(20, 30, 60, 0.95);
            border-radius: 15px;
            box-shadow: 0 15px 45px rgba(0, 0, 0, 0.6);
            border: 1px solid rgba(74, 123, 167, 0.3);
            padding: 40px;
            width: 100%;
            max-width: 500px;
        }
        .register-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .register-header h2 {
            color: #ffffff;
            font-weight: 700;
            margin-bottom: 10px;
            font-size: 1.8rem;
        }
        .register-header p {
            color: #a0aec0;
        }
        .form-control {
            border-radius: 8px;
            padding: 12px 15px;
            border: 1px solid rgba(74, 123, 167, 0.4);
            background: rgba(255, 255, 255, 0.05);
            color: #ffffff;
            transition: all 0.3s ease;
            font-size: 1rem;
        }
        .form-control::placeholder {
            color: #718096;
        }
        .form-control:focus {
            border-color: #4a7ba7;
            background: rgba(255, 255, 255, 0.08);
            box-shadow: 0 0 0 3px rgba(74, 123, 167, 0.2);
            outline: none;
        }
        .form-label {
            color: #cbd5e0;
            font-weight: 600;
            margin-bottom: 8px;
            display: block;
        }
        .btn-register {
            background: linear-gradient(135deg, #4a7ba7 0%, #2d5a8c 100%);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 12px 20px;
            font-weight: 600;
            width: 100%;
            margin-top: 20px;
            transition: all 0.3s ease;
            cursor: pointer;
            font-size: 1rem;
        }
        .btn-register:hover {
            background: linear-gradient(135deg, #2d5a8c 0%, #1a3a60 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(74, 123, 167, 0.3);
        }
        .btn-register:active {
            transform: translateY(0);
        }
        .login-link {
            text-align: center;
            margin-top: 20px;
            color: #a0aec0;
        }
        .login-link a {
            color: #63b3ed;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }
        .login-link a:hover {
            color: #4a9fd8;
            text-decoration: underline;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .invalid-feedback {
            color: #fc8181;
            font-size: 0.85rem;
            margin-top: 5px;
            display: block;
        }
        .form-control.is-invalid {
            border-color: #f56565;
            background: rgba(245, 101, 101, 0.05);
        }
        .form-control.is-invalid:focus {
            box-shadow: 0 0 0 3px rgba(245, 101, 101, 0.2);
        }
        .input-group-text {
            background-color: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(74, 123, 167, 0.4);
            border-right: none;
            color: #cbd5e0;
        }
        .input-group .form-control {
            border-left: none;
        }
        .input-group .form-control:focus {
            border-left: none;
        }
        .form-text {
            display: block;
            margin-top: 5px;
            color: #a0aec0;
            font-size: 0.85rem;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-header">
            <h2><i class="fas fa-user-plus"></i> Create Account</h2>
            <p>Join our event management community</p>
        </div>
        
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label class="form-label">Username</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                    <input type="text" name="username" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $username; ?>" placeholder="Enter your username">
                </div>
                <small class="form-text text-muted">3-20 characters, letters, numbers, and underscore only (e.g., john_doe)</small>
                <span class="invalid-feedback"><?php echo $username_err; ?></span>
            </div>    

            <div class="form-group">
                <label class="form-label">Email</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                    <input type="email" name="email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $email; ?>" placeholder="Enter your email">
                </div>
                <small class="form-text text-muted">Valid email format (e.g., user@example.com)</small>
                <span class="invalid-feedback"><?php echo $email_err; ?></span>
            </div>

            <div class="form-group">
                <label class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>" placeholder="Enter your password">
                </div>
                <small class="form-text text-muted">Minimum 6 characters (e.g., Password123)</small>
                <span class="invalid-feedback"><?php echo $password_err; ?></span>
            </div>

            <div class="form-group">
                <label class="form-label">Confirm Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" name="confirm_password" class="form-control <?php echo (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?>" placeholder="Confirm your password">
                </div>
                <span class="invalid-feedback"><?php echo $confirm_password_err; ?></span>
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary btn-register">
                    <i class="fas fa-user-plus"></i> Register
                </button>
            </div>

            <div class="login-link">
                Already have an account? <a href="login.php">Login here</a>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 