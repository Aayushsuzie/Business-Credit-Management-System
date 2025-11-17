<?php
session_start();

// Check if user is already logged in
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    header("location: " . $_SESSION["user_type"] . "_dashboard.php");
    exit;
}

require_once "config/database.php";

$username = $password = $user_type = "";
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
    
    if(empty(trim($_POST["user_type"]))){
        $login_err = "Please select user type.";
    } else{
        $user_type = trim($_POST["user_type"]);
    }
    
    if(empty($username_err) && empty($password_err) && empty($login_err)){
        // Verify user credentials using the new function
        $result = verifyUser($username, $password, $user_type);
        
        if($result['success']) {
            session_start();
            
            $_SESSION["loggedin"] = true;
            $_SESSION["id"] = $result['id'];
            $_SESSION["username"] = $result['username'];
            $_SESSION["user_type"] = $result['user_type'];
            
            header("location: " . $user_type . "_dashboard.php");
        } else {
            $login_err = "Invalid username or password.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - BCMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-container {
            background: white;
            padding: 2.5rem;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 450px;
        }
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .login-header h1 {
            color: #2d3748;
            font-size: 2.2rem;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }
        .login-header p {
            color: #718096;
            font-size: 1.1rem;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            color: #4a5568;
            font-weight: 500;
            margin-bottom: 0.5rem;
        }
        .form-control {
            padding: 0.75rem 1rem;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102,126,234,0.1);
        }
        .btn-login {
            width: 100%;
            padding: 0.8rem;
            font-size: 1.1rem;
            background: #667eea;
            border: none;
            border-radius: 8px;
            color: white;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .btn-login:hover {
            background: #5a67d8;
            transform: translateY(-1px);
        }
        .btn-register {
            width: 100%;
            padding: 0.8rem;
            font-size: 1.1rem;
            background: #48bb78;
            border: none;
            border-radius: 8px;
            color: white;
            font-weight: 500;
            transition: all 0.3s ease;
            margin-top: 1rem;
        }
        .btn-register:hover {
            background: #38a169;
            transform: translateY(-1px);
        }
        .alert {
            margin-bottom: 1.5rem;
            border-radius: 8px;
            padding: 1rem;
        }
        .user-type-selector {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .user-type-option {
            flex: 1;
            text-align: center;
            padding: 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .user-type-option:hover {
            border-color: #667eea;
            background: #f7fafc;
        }
        .user-type-option.selected {
            border-color: #667eea;
            background: #ebf4ff;
        }
        .user-type-option i {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            color: #4a5568;
        }
        .user-type-option.selected i {
            color: #667eea;
        }
        .divider {
            text-align: center;
            margin: 1.5rem 0;
            position: relative;
        }
        .divider::before {
            content: "";
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: #e2e8f0;
        }
        .divider span {
            background: white;
            padding: 0 1rem;
            color: #718096;
            position: relative;
        }
        .register-options {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }
        .register-option {
            flex: 1;
            text-align: center;
            padding: 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            text-decoration: none;
            color: #4a5568;
            transition: all 0.3s ease;
        }
        .register-option:hover {
            border-color: #48bb78;
            background: #f0fff4;
            color: #2f855a;
        }
        .register-option i {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            display: block;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>Welcome to BCMS</h1>
            <p>Business Commerce Management System</p>
        </div>
        
        <?php 
        if(!empty($login_err)){
            echo '<div class="alert alert-danger">' . $login_err . '</div>';
        }        
        ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="user-type-selector">
                <div class="user-type-option" data-type="admin">
                    <i class="fas fa-user-shield"></i>
                    <div>Admin</div>
                </div>
                <div class="user-type-option" data-type="industry">
                    <i class="fas fa-industry"></i>
                    <div>Industry</div>
                </div>
                <div class="user-type-option" data-type="wholesaler">
                    <i class="fas fa-store"></i>
                    <div>Wholesaler</div>
                </div>
            </div>
            <input type="hidden" name="user_type" id="user_type" value="">
            
            <div class="form-group">
                <label><i class="fas fa-user me-2"></i>Username</label>
                <input type="text" name="username" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $username; ?>" placeholder="Enter your username">
                <span class="invalid-feedback"><?php echo $username_err; ?></span>
            </div>    
            <div class="form-group">
                <label><i class="fas fa-lock me-2"></i>Password</label>
                <input type="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>" placeholder="Enter your password">
                <span class="invalid-feedback"><?php echo $password_err; ?></span>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-login">
                    <i class="fas fa-sign-in-alt me-2"></i>Login
                </button>
            </div>
        </form>

        <div class="divider">
            <span>Register as</span>
        </div>

        <div class="register-options">
            <a href="register.php?type=industry" class="register-option">
                <i class="fas fa-industry"></i>
                Industry User
            </a>
            <a href="register.php?type=wholesaler" class="register-option">
                <i class="fas fa-store"></i>
                Wholesaler
            </a>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const userTypeOptions = document.querySelectorAll('.user-type-option');
            const userTypeInput = document.getElementById('user_type');

            userTypeOptions.forEach(option => {
                option.addEventListener('click', function() {
                    // Remove selected class from all options
                    userTypeOptions.forEach(opt => opt.classList.remove('selected'));
                    // Add selected class to clicked option
                    this.classList.add('selected');
                    // Update hidden input value
                    userTypeInput.value = this.dataset.type;
                });
            });
        });
    </script>
</body>
</html> 