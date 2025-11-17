<?php
session_start();

// Check if user is logged in and is a wholesaler
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["user_type"] !== "wholesaler") {
    header("location: index.php");
    exit;
}

require_once "config/database.php";

if($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST["name"]);
    $description = trim($_POST["description"]);
    $price = floatval($_POST["price"]);
    $stock = intval($_POST["stock"]);
    $wholesaler_id = $_SESSION["id"];
    
    // Validate input
    if(empty($name) || $price <= 0 || $stock < 0) {
        header("location: wholesaler_dashboard.php?error=invalid_input");
        exit;
    }
    
    // Insert product
    $sql = "INSERT INTO products (wholesaler_id, name, description, price, stock) VALUES (?, ?, ?, ?, ?)";
    
    if($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "issdi", $wholesaler_id, $name, $description, $price, $stock);
        
        if(mysqli_stmt_execute($stmt)) {
            header("location: wholesaler_dashboard.php?success=product_added");
        } else {
            header("location: wholesaler_dashboard.php?error=db_error");
        }
        
        mysqli_stmt_close($stmt);
    }
    
    mysqli_close($conn);
} else {
    header("location: wholesaler_dashboard.php");
}
?> 