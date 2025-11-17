<?php
require_once "includes/header.php";
require_once "config/database.php";  // Add database connection

// Verify user is wholesaler
if($_SESSION["user_type"] !== "wholesaler") {
    header("location: index.php");
    exit;
}

// Handle payment transaction submission
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_payment'])) {
    $amount = floatval($_POST['amount']);
    $description = trim($_POST['description']);
    $wholesaler_id = $_SESSION['id'];

    $sql = "INSERT INTO unverified_payment_transactions (wholesaler_id, amount, description) VALUES (?, ?, ?)";
    if($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "ids", $wholesaler_id, $amount, $description);
        if(mysqli_stmt_execute($stmt)) {
            $success_message = "Payment transaction submitted successfully!";
        } else {
            $error_message = "Error submitting payment transaction.";
        }
        mysqli_stmt_close($stmt);
    }
}

// Get credit score
$credit_score = 0;
$sql = "SELECT score FROM credit_scores WHERE wholesaler_id = ?";
if($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $_SESSION['id']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if($row = mysqli_fetch_assoc($result)) {
        $credit_score = $row['score'];
    }
    mysqli_stmt_close($stmt);
}
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h2>Wholesaler Dashboard</h2>
            <p class="text-muted">Submit Payment Transactions</p>
        </div>
    </div>

    <?php if(isset($success_message)): ?>
        <div class="alert alert-success"><?php echo $success_message; ?></div>
    <?php endif; ?>
    <?php if(isset($error_message)): ?>
        <div class="alert alert-danger"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <div class="row justify-content-center">
        <div class="col-md-6">
            <!-- Credit Score Card -->
            <div class="card bg-primary text-white mb-4">
                <div class="card-body">
                    <h5 class="card-title">Credit Score</h5>
                    <h2 class="display-4"><?php echo $credit_score; ?></h2>
                    <p class="card-text">Your current credit score as assigned by admin</p>
                </div>
            </div>

            <!-- Payment Transaction Form -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Submit Payment Transaction</h5>
                </div>
                <div class="card-body">
                    <form method="post" action="">
                        <div class="mb-3">
                            <label class="form-label">Amount</label>
                            <input type="number" name="amount" class="form-control" step="0.01" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3" required></textarea>
                        </div>
                        <button type="submit" name="submit_payment" class="btn btn-primary w-100">Submit Payment</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once "includes/footer.php"; ?> 