<?php
session_start();
require_once "../config/db.php";

if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    header("location: " . ($_SESSION["role"] == "admin" ? "../admin/dashboard.php" : "../user/dashboard.php"));
    exit;
}

$username = $password = "";
$username_err = $password_err = $login_err = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){
    if(empty(trim($_POST["username"]))){
        $username_err = "Please enter username.";
    } else{
        $username = trim($_POST["username"]);
    }
    
    if(empty(trim($_POST["password"]))){
        $password_err = "Please enter your password.";
    } else{
        $password = trim($_POST["password"]);
    }
    
    if(empty($username_err) && empty($password_err)){
        $sql = "SELECT id, username, password, role FROM users WHERE username = ?";
        
        if($stmt = mysqli_prepare($conn, $sql)){
            mysqli_stmt_bind_param($stmt, "s", $param_username);
            $param_username = $username;
            
            if(mysqli_stmt_execute($stmt)){
                mysqli_stmt_store_result($stmt);
                
                if(mysqli_stmt_num_rows($stmt) == 1){                    
                    mysqli_stmt_bind_result($stmt, $id, $username, $hashed_password, $role);
                    if(mysqli_stmt_fetch($stmt)){
                        // Ensure admin login works by verifying the role and redirecting correctly
                        if(password_verify($password, $hashed_password)){
                            session_start();

                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["username"] = $username;
                            $_SESSION["role"] = $role;

                            // Redirect based on role
                            if ($role == "admin") {
                                header("location: ../admin/dashboard.php");
                            } else {
                                header("location: ../user/dashboard.php");
                            }
                            exit;
                        } else {
                            $login_err = "Invalid username or password.";
                        }
                    }
                } else{
                    $login_err = "Invalid username or password.";
                }
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
    <title>Login - Event Management System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
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
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .wrapper {
            width: 100%;
            max-width: 400px;
            padding: 40px;
            background: rgba(20, 30, 60, 0.95);
            border-radius: 15px;
            box-shadow: 0 15px 45px rgba(0, 0, 0, 0.6);
            border: 1px solid rgba(74, 123, 167, 0.3);
        }

        h2 {
            color: #ffffff;
            font-weight: 700;
            margin-bottom: 10px;
            text-align: center;
            font-size: 1.8rem;
        }

        p {
            color: #a0aec0;
            text-align: center;
            margin-bottom: 25px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            color: #cbd5e0;
            font-weight: 600;
            display: block;
            margin-bottom: 8px;
        }

        .form-control {
            border-radius: 8px;
            padding: 12px 15px;
            border: 1px solid rgba(74, 123, 167, 0.4);
            background: rgba(255, 255, 255, 0.05);
            color: #ffffff;
            transition: all 0.3s;
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

        .form-control.is-invalid {
            border-color: #f56565;
            background: rgba(245, 101, 101, 0.05);
        }

        .btn-primary {
            background: linear-gradient(135deg, #4a7ba7 0%, #2d5a8c 100%);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 12px 20px;
            font-weight: 600;
            width: 100%;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 1rem;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #2d5a8c 0%, #1a3a60 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(74, 123, 167, 0.3);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        .alert-danger {
            background: rgba(245, 101, 101, 0.15);
            color: #fc8181;
            border: 1px solid rgba(245, 101, 101, 0.3);
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: 500;
        }

        .invalid-feedback {
            color: #fc8181;
            font-size: 0.85rem;
            margin-top: 5px;
            display: block;
        }

        .form-group p {
            text-align: center;
            margin-top: 20px;
            color: #a0aec0;
            font-size: 0.95rem;
        }

        a {
            color: #63b3ed;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }

        a:hover {
            color: #4a9fd8;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <h2>Login</h2>
        <p>Please fill in your credentials to login.</p>

        <?php 
        if(!empty($login_err)){
            echo '<div class="alert alert-danger">' . $login_err . '</div>';
        }        
        ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $username; ?>">
                <span class="invalid-feedback"><?php echo $username_err; ?></span>
            </div>    
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>">
                <span class="invalid-feedback"><?php echo $password_err; ?></span>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Login">
            </div>
            <p>Don't have an account? <a href="register.php">Sign up now</a>.</p>
        </form>
    </div>
</body>
</html>