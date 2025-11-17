<?php
// Database credentials
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'bcm_system_db');

// Create connection
$conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Create database if it doesn't exist
$sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME;
if (mysqli_query($conn, $sql)) {
    mysqli_select_db($conn, DB_NAME);
    
    // Create admin table
    $sql = "CREATE TABLE IF NOT EXISTS admin (
        id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        full_name VARCHAR(100) NOT NULL,
        role ENUM('super_admin', 'admin') NOT NULL DEFAULT 'admin',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        last_login DATETIME,
        status ENUM('active', 'inactive') NOT NULL DEFAULT 'active'
    )";
    if (!mysqli_query($conn, $sql)) {
        echo "Error creating admin table: " . mysqli_error($conn);
    }

    // Check if default admin exists, if not create it
    $sql = "SELECT id FROM admin WHERE username = 'admin'";
    $result = mysqli_query($conn, $sql);
    if (mysqli_num_rows($result) == 0) {
        $hashed_password = password_hash('password', PASSWORD_DEFAULT);
        $sql = "INSERT INTO admin (username, password, email, full_name, role) 
                VALUES ('admin', ?, 'admin@system.com', 'System Administrator', 'super_admin')";
        if($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $hashed_password);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }
    
    // Create industry table
    $sql = "CREATE TABLE IF NOT EXISTS industry (
        id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        company_name VARCHAR(100) NOT NULL,
        phone VARCHAR(20) NOT NULL,
        address TEXT NOT NULL,
        PAN_number VARCHAR(50) UNIQUE,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        last_login DATETIME,
        status ENUM('active', 'inactive', 'pending') NOT NULL DEFAULT 'pending',
        verification_status ENUM('verified', 'unverified') NOT NULL DEFAULT 'unverified'
    )";
    if (!mysqli_query($conn, $sql)) {
        echo "Error creating industry table: " . mysqli_error($conn);
    }
    
    // Create wholesaler table
    $sql = "CREATE TABLE IF NOT EXISTS wholesaler (
        id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        business_name VARCHAR(100) NOT NULL,
        phone VARCHAR(20) NOT NULL,
        address TEXT NOT NULL,
        PAN_number VARCHAR(20) NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )";

    if (!$conn->query($sql)) {
        die("Error creating wholesaler table: " . $conn->error);
    }

    // Add PAN_number column if it doesn't exist
    $check_column = "SHOW COLUMNS FROM wholesaler LIKE 'PAN_number'";
    $result = $conn->query($check_column);
    if ($result->num_rows == 0) {
        $alter_sql = "ALTER TABLE wholesaler ADD COLUMN PAN_number VARCHAR(20) NOT NULL AFTER address";
        if (!$conn->query($alter_sql)) {
            die("Error adding PAN_number column: " . $conn->error);
        }
    }

    // Create unverified_credit_transactions table
    $sql = "CREATE TABLE IF NOT EXISTS unverified_credit_transactions (
        id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
        industry_id INT NOT NULL,
        amount DECIMAL(10,2) NOT NULL,
        description TEXT,
        deadline DATE NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        status ENUM('pending', 'rejected') NOT NULL DEFAULT 'pending',
        FOREIGN KEY (industry_id) REFERENCES industry(id)
    )";
    if (!mysqli_query($conn, $sql)) {
        echo "Error creating unverified_credit_transactions table: " . mysqli_error($conn);
    }

    // Create verified_credit_transactions table
    $sql = "CREATE TABLE IF NOT EXISTS verified_credit_transactions (
        id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
        industry_id INT NOT NULL,
        amount DECIMAL(10,2) NOT NULL,
        description TEXT,
        deadline DATE NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        verified_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        verified_by INT NOT NULL,
        FOREIGN KEY (industry_id) REFERENCES industry(id),
        FOREIGN KEY (verified_by) REFERENCES admin(id)
    )";
    if (!mysqli_query($conn, $sql)) {
        echo "Error creating verified_credit_transactions table: " . mysqli_error($conn);
    }

    // Create unverified_payment_transactions table
    $sql = "CREATE TABLE IF NOT EXISTS unverified_payment_transactions (
        id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
        wholesaler_id INT NOT NULL,
        amount DECIMAL(10,2) NOT NULL,
        description TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        status ENUM('pending', 'rejected') NOT NULL DEFAULT 'pending',
        FOREIGN KEY (wholesaler_id) REFERENCES wholesaler(id)
    )";
    if (!mysqli_query($conn, $sql)) {
        echo "Error creating unverified_payment_transactions table: " . mysqli_error($conn);
    }

    // Create verified_payment_transactions table
    $sql = "CREATE TABLE IF NOT EXISTS verified_payment_transactions (
        id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
        wholesaler_id INT NOT NULL,
        amount DECIMAL(10,2) NOT NULL,
        description TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        verified_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        verified_by INT NOT NULL,
        FOREIGN KEY (wholesaler_id) REFERENCES wholesaler(id),
        FOREIGN KEY (verified_by) REFERENCES admin(id)
    )";
    if (!mysqli_query($conn, $sql)) {
        echo "Error creating verified_payment_transactions table: " . mysqli_error($conn);
    }

    // Create credit_scores table
    $sql = "CREATE TABLE IF NOT EXISTS credit_scores (
        id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
        wholesaler_id INT NOT NULL UNIQUE,
        score INT NOT NULL DEFAULT 0,
        last_updated DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_by INT NOT NULL,
        FOREIGN KEY (wholesaler_id) REFERENCES wholesaler(id),
        FOREIGN KEY (updated_by) REFERENCES admin(id)
    )";
    if (!mysqli_query($conn, $sql)) {
        echo "Error creating credit_scores table: " . mysqli_error($conn);
    }

    // Create credit_score_history table
    $sql = "CREATE TABLE IF NOT EXISTS credit_score_history (
        id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
        wholesaler_id INT NOT NULL,
        previous_score DECIMAL(5,2),
        new_score DECIMAL(5,2) NOT NULL,
        admin_id INT NOT NULL,
        reason TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (wholesaler_id) REFERENCES wholesaler(id) ON DELETE CASCADE,
        FOREIGN KEY (admin_id) REFERENCES admin(id) ON DELETE CASCADE
    )";

    if (!$conn->query($sql)) {
        die("Error creating credit_score_history table: " . $conn->error);
    }
} else {
    echo "Error creating database: " . mysqli_error($conn);
}

// Function to verify user credentials
function verifyUser($username, $password, $userType) {
    global $conn;
    
    switch($userType) {
        case 'admin':
            $table = 'admin';
            break;
        case 'industry':
            $table = 'industry';
            break;
        case 'wholesaler':
            $table = 'wholesaler';
            break;
    }
    
    $sql = "SELECT id, username, password FROM $table WHERE username = ?";
    
    if($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "s", $username);
        
        if(mysqli_stmt_execute($stmt)) {
            mysqli_stmt_store_result($stmt);
            
            if(mysqli_stmt_num_rows($stmt) == 1) {
                mysqli_stmt_bind_result($stmt, $id, $username, $hashed_password);
                if(mysqli_stmt_fetch($stmt)) {
                    if(password_verify($password, $hashed_password)) {
                        // Update last login
                        $update_sql = "UPDATE $table SET last_login = CURRENT_TIMESTAMP WHERE id = ?";
                        if($update_stmt = mysqli_prepare($conn, $update_sql)) {
                            mysqli_stmt_bind_param($update_stmt, "i", $id);
                            mysqli_stmt_execute($update_stmt);
                            mysqli_stmt_close($update_stmt);
                        }
                        
                        return array(
                            'success' => true,
                            'id' => $id,
                            'username' => $username,
                            'user_type' => $userType
                        );
                    }
                }
            }
        }
        mysqli_stmt_close($stmt);
    }
    
    return array('success' => false);
}

// Function to register new user
function registerUser($userData, $userType) {
    global $conn;
    
    try {
        $table = '';
        $fields = '';
        $values = '';
        $params = array();
        $types = '';
        
        switch($userType) {
            case 'admin':
                $table = 'admin';
                $fields = 'username, password, email';
                $values = '?, ?, ?';
                $params = array($userData['username'], password_hash($userData['password'], PASSWORD_DEFAULT), $userData['email']);
                $types = 'sss';
                break;
                
            case 'industry':
                $table = 'industry';
                $fields = 'username, password, email, company_name, phone, address, PAN_number';
                $values = '?, ?, ?, ?, ?, ?, ?';
                $params = array(
                    $userData['username'],
                    password_hash($userData['password'], PASSWORD_DEFAULT),
                    $userData['email'],
                    $userData['company_name'],
                    $userData['phone'],
                    $userData['address'],
                    $userData['PAN_number']
                );
                $types = 'sssssss';
                break;
                
            case 'wholesaler':
                $table = 'wholesaler';
                $fields = 'username, password, email, business_name, phone, address, PAN_number';
                $values = '?, ?, ?, ?, ?, ?, ?';
                $params = array(
                    $userData['username'],
                    password_hash($userData['password'], PASSWORD_DEFAULT),
                    $userData['email'],
                    $userData['business_name'],
                    $userData['phone'],
                    $userData['address'],
                    $userData['PAN_number']
                );
                $types = 'sssssss';
                break;
        }
        
        $sql = "INSERT INTO $table ($fields) VALUES ($values)";
        $stmt = mysqli_prepare($conn, $sql);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, $types, ...$params);
            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                return true;
            } else {
                throw new Exception("Error executing statement: " . mysqli_error($conn));
            }
        } else {
            throw new Exception("Error preparing statement: " . mysqli_error($conn));
        }
    } catch (Exception $e) {
        error_log("Registration error: " . $e->getMessage());
        return false;
    }
}

// Function to update credit score and record history
function updateCreditScore($wholesaler_id, $new_score, $admin_id, $reason = '') {
    global $conn;
    
    try {
        // Start transaction
        $conn->begin_transaction();
        
        // Get current score
        $stmt = $conn->prepare("SELECT score FROM credit_scores WHERE wholesaler_id = ?");
        $stmt->bind_param("i", $wholesaler_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $current_score = $result->fetch_assoc()['score'] ?? null;
        
        // Update credit score
        if ($current_score === null) {
            // Insert new score
            $stmt = $conn->prepare("INSERT INTO credit_scores (wholesaler_id, score, updated_by) VALUES (?, ?, ?)");
            $stmt->bind_param("idi", $wholesaler_id, $new_score, $admin_id);
        } else {
            // Update existing score
            $stmt = $conn->prepare("UPDATE credit_scores SET score = ?, updated_by = ?, last_updated = CURRENT_TIMESTAMP WHERE wholesaler_id = ?");
            $stmt->bind_param("dii", $new_score, $admin_id, $wholesaler_id);
        }
        
        if (!$stmt->execute()) {
            throw new Exception("Error updating credit score: " . $stmt->error);
        }
        
        // Record in history
        $stmt = $conn->prepare("INSERT INTO credit_score_history (wholesaler_id, previous_score, new_score, admin_id, reason) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iddis", $wholesaler_id, $current_score, $new_score, $admin_id, $reason);
        
        if (!$stmt->execute()) {
            throw new Exception("Error recording credit score history: " . $stmt->error);
        }
        
        // Commit transaction
        $conn->commit();
        return true;
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        error_log("Error in updateCreditScore: " . $e->getMessage());
        return false;
    }
}

// Function to get credit score history for a wholesaler
function getCreditScoreHistory($wholesaler_id) {
    global $conn;
    
    $sql = "SELECT 
                h.*,
                a.username as admin_username,
                w.username as wholesaler_username
            FROM credit_score_history h
            JOIN admin a ON h.admin_id = a.id
            JOIN wholesaler w ON h.wholesaler_id = w.id
            WHERE h.wholesaler_id = ?
            ORDER BY h.created_at DESC";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $wholesaler_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $history = array();
    while ($row = $result->fetch_assoc()) {
        $history[] = $row;
    }
    
    return $history;
}
?> 