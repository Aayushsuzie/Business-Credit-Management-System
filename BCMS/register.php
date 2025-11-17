<?php
session_start();

// Check if user is already logged in
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    header("location: " . $_SESSION["user_type"] . "_dashboard.php");
    exit;
}

require_once "config/database.php";

$username = $password = $confirm_password = $email = $company_name = $phone = $address = $PAN_number = $business_name = $business_license = $tax_id = "";
$username_err = $password_err = $confirm_password_err = $email_err = $company_name_err = $phone_err = $address_err = $PAN_number_err = $business_name_err = $business_license_err = $tax_id_err = "";

// Get user type from URL parameter
$user_type = isset($_GET['type']) ? $_GET['type'] : '';
if (!in_array($user_type, ['industry', 'wholesaler'])) {
    header("location: index.php");
    exit;
}

if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register'])) {
    $user_type = $_POST['user_type'];
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $email = trim($_POST['email']);
    
    // Validate input
    $errors = array();
    
    if(empty($username)) {
        $errors[] = "Username is required";
    }
    if(empty($password)) {
        $errors[] = "Password is required";
    }
    if(empty($email)) {
        $errors[] = "Email is required";
    }
    
    // Additional validation based on user type
    if($user_type == "industry") {
        $company_name = trim($_POST['company_name']);
        $phone = trim($_POST['phone']);
        $address = trim($_POST['address']);
        $PAN_number = trim($_POST['PAN_number']);
        
        if(empty($company_name)) {
            $errors[] = "Company name is required";
        }
        if(empty($phone)) {
            $errors[] = "Phone number is required";
        }
        if(empty($address)) {
            $errors[] = "Address is required";
        }
        if(empty($PAN_number)) {
            $errors[] = "PAN number is required";
        }
    } elseif($user_type == "wholesaler") {
        $business_name = trim($_POST['business_name']);
        $phone = trim($_POST['phone']);
        $address = trim($_POST['address']);
        $PAN_number = trim($_POST['PAN_number']);
        
        if(empty($business_name)) {
            $errors[] = "Business name is required";
        }
        if(empty($phone)) {
            $errors[] = "Phone number is required";
        }
        if(empty($address)) {
            $errors[] = "Address is required";
        }
        if(empty($PAN_number)) {
            $errors[] = "PAN number is required";
        }
    }
    
    // If no errors, proceed with registration
    if(empty($errors)) {
        $userData = array(
            'username' => $username,
            'password' => $password,
            'email' => $email,
            'user_type' => $user_type
        );
        
        if($user_type == "industry") {
            $userData['company_name'] = $company_name;
            $userData['phone'] = $phone;
            $userData['address'] = $address;
            $userData['PAN_number'] = $PAN_number;
        } elseif($user_type == "wholesaler") {
            $userData['business_name'] = $business_name;
            $userData['phone'] = $phone;
            $userData['address'] = $address;
            $userData['PAN_number'] = $PAN_number;
        }
        
        if(registerUser($userData, $user_type)) {
            $_SESSION['register_success'] = true;
            header("location: index.php");
            exit;
        } else {
            $errors[] = "Registration failed. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - BCMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 2rem 0;
        }
        .register-container {
            background: white;
            padding: 2.5rem;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 600px;
        }
        .register-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .register-header h1 {
            color: #2d3748;
            font-size: 2.2rem;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }
        .register-header p {
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
        .back-to-login {
            text-align: center;
            margin-top: 1.5rem;
        }
        .back-to-login a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }
        .back-to-login a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-header">
            <h1>Create <?php echo ucfirst($user_type); ?> Account</h1>
            <p>Join BCMS as a <?php echo $user_type; ?> user</p>
        </div>
        
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?type=' . $user_type; ?>" method="post">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label><i class="fas fa-user me-2"></i>Username</label>
                        <input type="text" name="username" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $username; ?>" placeholder="Choose a username">
                        <span class="invalid-feedback"><?php echo $username_err; ?></span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label><i class="fas fa-envelope me-2"></i>Email</label>
                        <input type="email" name="email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $email; ?>" placeholder="Enter your email">
                        <span class="invalid-feedback"><?php echo $email_err; ?></span>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label><i class="fas fa-building me-2"></i><?php echo $user_type == 'industry' ? 'Company' : 'Business'; ?> Name</label>
                        <input type="text" name="<?php echo $user_type == 'industry' ? 'company_name' : 'business_name'; ?>" class="form-control <?php echo (!empty($company_name_err) || !empty($business_name_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $user_type == 'industry' ? $company_name : $business_name; ?>" placeholder="Enter <?php echo $user_type == 'industry' ? 'company' : 'business'; ?> name">
                        <span class="invalid-feedback"><?php echo $user_type == 'industry' ? $company_name_err : $business_name_err; ?></span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label><i class="fas fa-phone me-2"></i>Phone Number</label>
                        <input type="tel" name="phone" class="form-control <?php echo (!empty($phone_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $phone; ?>" placeholder="Enter phone number">
                        <span class="invalid-feedback"><?php echo $phone_err; ?></span>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label><i class="fas fa-id-card me-2"></i>PAN Number</label>
                        <input type="text" name="PAN_number" class="form-control <?php echo (!empty($PAN_number_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $PAN_number; ?>" placeholder="Enter PAN number">
                        <span class="invalid-feedback"><?php echo $PAN_number_err; ?></span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label><i class="fas fa-map-marker-alt me-2"></i>Address</label>
                        <textarea name="address" class="form-control <?php echo (!empty($address_err)) ? 'is-invalid' : ''; ?>" rows="3" placeholder="Enter your address"><?php echo $address; ?></textarea>
                        <span class="invalid-feedback"><?php echo $address_err; ?></span>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label><i class="fas fa-lock me-2"></i>Password</label>
                        <input type="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>" placeholder="Enter password">
                        <span class="invalid-feedback"><?php echo $password_err; ?></span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label><i class="fas fa-lock me-2"></i>Confirm Password</label>
                        <input type="password" name="confirm_password" class="form-control <?php echo (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?>" placeholder="Confirm password">
                        <span class="invalid-feedback"><?php echo $confirm_password_err; ?></span>
                    </div>
                </div>
            </div>

            <input type="hidden" name="user_type" value="<?php echo $user_type; ?>">
            
            <div class="form-group">
                <button type="submit" name="register" class="btn btn-register">
                    <i class="fas fa-user-plus me-2"></i>Create Account
                </button>
            </div>
            
            <div class="back-to-login">
                <p>Already have an account? <a href="index.php">Login here</a></p>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 