<?php
require_once "includes/header.php";
require_once "config/database.php";  // Add database connection

// Verify user is industry
if($_SESSION["user_type"] !== "industry") {
    header("location: index.php");
    exit;
}

// Handle credit transaction submission
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_credit'])) {
    $amount = floatval($_POST['amount']);
    $description = trim($_POST['description']);
    $deadline = $_POST['deadline'];
    $industry_id = $_SESSION['id'];

    $sql = "INSERT INTO unverified_credit_transactions (industry_id, amount, description, deadline) VALUES (?, ?, ?, ?)";
    if($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "idss", $industry_id, $amount, $description, $deadline);
        if(mysqli_stmt_execute($stmt)) {
            $success_message = "Credit transaction submitted successfully!";
        } else {
            $error_message = "Error submitting credit transaction.";
        }
        mysqli_stmt_close($stmt);
    }
}
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h2>Industry Dashboard</h2>
            <p class="text-muted">Submit Credit Transactions</p>
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
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Submit Credit Transaction</h5>
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
                        <div class="mb-3">
                            <label class="form-label">Deadline</label>
                            <input type="date" name="deadline" class="form-control" required>
                        </div>
                        <button type="submit" name="submit_credit" class="btn btn-primary w-100">Submit Transaction</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once "includes/footer.php"; ?> 