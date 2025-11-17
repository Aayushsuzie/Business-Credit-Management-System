<?php
session_start();

// Check if user is logged in and is an enterprise
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["user_type"] !== "enterprise") {
    header("location: index.php");
    exit;
}

require_once "config/database.php";

if($_SERVER["REQUEST_METHOD"] == "POST") {
    $product_id = intval($_POST["product_id"]);
    $wholesaler_id = intval($_POST["wholesaler_id"]);
    $quantity = intval($_POST["quantity"]);
    $enterprise_id = $_SESSION["id"];
    
    // Validate input
    if($quantity <= 0) {
        header("location: enterprise_dashboard.php?error=invalid_quantity");
        exit;
    }
    
    // Check product availability
    $sql = "SELECT price, stock FROM products WHERE id = ? AND wholesaler_id = ? AND stock >= ?";
    
    if($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "iii", $product_id, $wholesaler_id, $quantity);
        
        if(mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            
            if($row = mysqli_fetch_assoc($result)) {
                $total_price = $row['price'] * $quantity;
                
                // Start transaction
                mysqli_begin_transaction($conn);
                
                try {
                    // Insert order
                    $sql = "INSERT INTO orders (enterprise_id, wholesaler_id, product_id, quantity, total_price) 
                           VALUES (?, ?, ?, ?, ?)";
                    
                    if($stmt2 = mysqli_prepare($conn, $sql)) {
                        mysqli_stmt_bind_param($stmt2, "iiidi", $enterprise_id, $wholesaler_id, $product_id, $quantity, $total_price);
                        
                        if(!mysqli_stmt_execute($stmt2)) {
                            throw new Exception("Error creating order");
                        }
                        
                        mysqli_stmt_close($stmt2);
                    }
                    
                    // Update product stock
                    $sql = "UPDATE products SET stock = stock - ? WHERE id = ? AND wholesaler_id = ?";
                    
                    if($stmt3 = mysqli_prepare($conn, $sql)) {
                        mysqli_stmt_bind_param($stmt3, "iii", $quantity, $product_id, $wholesaler_id);
                        
                        if(!mysqli_stmt_execute($stmt3)) {
                            throw new Exception("Error updating stock");
                        }
                        
                        mysqli_stmt_close($stmt3);
                    }
                    
                    // Commit transaction
                    mysqli_commit($conn);
                    header("location: enterprise_dashboard.php?success=order_placed");
                    
                } catch (Exception $e) {
                    // Rollback transaction on error
                    mysqli_rollback($conn);
                    header("location: enterprise_dashboard.php?error=order_failed");
                }
            } else {
                header("location: enterprise_dashboard.php?error=product_unavailable");
            }
        } else {
            header("location: enterprise_dashboard.php?error=db_error");
        }
        
        mysqli_stmt_close($stmt);
    }
    
    mysqli_close($conn);
} else {
    header("location: enterprise_dashboard.php");
}
?> 