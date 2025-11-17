<?php
require_once "includes/header.php";
require_once "config/database.php";  // Add database connection

// Verify user is admin
if($_SESSION["user_type"] !== "admin") {
    header("location: index.php");
    exit;
}

// Handle transaction verification
if($_SERVER["REQUEST_METHOD"] == "POST") {
    if(isset($_POST['verify_credit'])) {
        $transaction_id = $_POST['transaction_id'];
        $action = $_POST['action'];
        
        if($action === 'accept') {
            // Move to verified credit transactions
            $sql = "INSERT INTO verified_credit_transactions 
                    (industry_id, amount, description, deadline, verified_by) 
                    SELECT industry_id, amount, description, deadline, ? 
                    FROM unverified_credit_transactions 
                    WHERE id = ?";
            if($stmt = mysqli_prepare($conn, $sql)) {
                mysqli_stmt_bind_param($stmt, "ii", $_SESSION['id'], $transaction_id);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
        }
        // Delete from unverified (whether accepted or rejected)
        $sql = "DELETE FROM unverified_credit_transactions WHERE id = ?";
        if($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "i", $transaction_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }
    
    if(isset($_POST['verify_payment'])) {
        $transaction_id = $_POST['transaction_id'];
        $action = $_POST['action'];
        
        if($action === 'accept') {
            // Move to verified payment transactions
            $sql = "INSERT INTO verified_payment_transactions 
                    (wholesaler_id, amount, description, verified_by) 
                    SELECT wholesaler_id, amount, description, ? 
                    FROM unverified_payment_transactions 
                    WHERE id = ?";
            if($stmt = mysqli_prepare($conn, $sql)) {
                mysqli_stmt_bind_param($stmt, "ii", $_SESSION['id'], $transaction_id);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
        }
        // Delete from unverified (whether accepted or rejected)
        $sql = "DELETE FROM unverified_payment_transactions WHERE id = ?";
        if($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "i", $transaction_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }
    
    if(isset($_POST['update_credit_score'])) {
        $wholesaler_id = $_POST['wholesaler_id'];
        $score = $_POST['score'];
        
        // Update or insert credit score
        $sql = "INSERT INTO credit_scores (wholesaler_id, score, updated_by) 
                VALUES (?, ?, ?) 
                ON DUPLICATE KEY UPDATE 
                score = ?, 
                last_updated = CURRENT_TIMESTAMP,
                updated_by = ?";
        if($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "iiiii", $wholesaler_id, $score, $_SESSION['id'], $score, $_SESSION['id']);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }
}
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h2>Admin Dashboard</h2>
            <p class="text-muted">Manage transactions and credit scores</p>
        </div>
    </div>

    <div class="row">
        <!-- Unverified Credit Transactions -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Unverified Credit Transactions</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Industry</th>
                                    <th>Amount</th>
                                    <th>Description</th>
                                    <th>Deadline</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql = "SELECT u.*, i.username as industry_name 
                                       FROM unverified_credit_transactions u 
                                       JOIN industry i ON u.industry_id = i.id 
                                       ORDER BY u.created_at DESC";
                                $result = mysqli_query($conn, $sql);
                                while($row = mysqli_fetch_assoc($result)) {
                                    echo "<tr>";
                                    echo "<td>" . date('Y-m-d', strtotime($row['created_at'])) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['industry_name']) . "</td>";
                                    echo "<td>₹" . number_format($row['amount'], 2) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['description']) . "</td>";
                                    echo "<td>" . date('Y-m-d', strtotime($row['deadline'])) . "</td>";
                                    echo "<td>
                                            <form method='post' style='display: inline;'>
                                                <input type='hidden' name='transaction_id' value='" . $row['id'] . "'>
                                                <input type='hidden' name='action' value='accept'>
                                                <button type='submit' name='verify_credit' class='btn btn-sm btn-success'>Accept</button>
                                            </form>
                                            <form method='post' style='display: inline;'>
                                                <input type='hidden' name='transaction_id' value='" . $row['id'] . "'>
                                                <input type='hidden' name='action' value='reject'>
                                                <button type='submit' name='verify_credit' class='btn btn-sm btn-danger'>Reject</button>
                                            </form>
                                          </td>";
                                    echo "</tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Unverified Payment Transactions -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Unverified Payment Transactions</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Wholesaler</th>
                                    <th>Amount</th>
                                    <th>Description</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql = "SELECT u.*, w.username as wholesaler_name 
                                       FROM unverified_payment_transactions u 
                                       JOIN wholesaler w ON u.wholesaler_id = w.id 
                                       ORDER BY u.created_at DESC";
                                $result = mysqli_query($conn, $sql);
                                while($row = mysqli_fetch_assoc($result)) {
                                    echo "<tr>";
                                    echo "<td>" . date('Y-m-d', strtotime($row['created_at'])) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['wholesaler_name']) . "</td>";
                                    echo "<td>₹" . number_format($row['amount'], 2) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['description']) . "</td>";
                                    echo "<td>
                                            <form method='post' style='display: inline;'>
                                                <input type='hidden' name='transaction_id' value='" . $row['id'] . "'>
                                                <input type='hidden' name='action' value='accept'>
                                                <button type='submit' name='verify_payment' class='btn btn-sm btn-success'>Accept</button>
                                            </form>
                                            <form method='post' style='display: inline;'>
                                                <input type='hidden' name='transaction_id' value='" . $row['id'] . "'>
                                                <input type='hidden' name='action' value='reject'>
                                                <button type='submit' name='verify_payment' class='btn btn-sm btn-danger'>Reject</button>
                                            </form>
                                          </td>";
                                    echo "</tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Credit Score Management -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Manage Wholesaler Credit Scores</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Wholesaler</th>
                                    <th>Current Score</th>
                                    <th>Last Updated</th>
                                    <th>Updated By</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql = "SELECT w.id, w.username, 
                                       COALESCE(cs.score, 0) as score, 
                                       cs.last_updated,
                                       a.username as updated_by
                                       FROM wholesaler w
                                       LEFT JOIN credit_scores cs ON w.id = cs.wholesaler_id
                                       LEFT JOIN admin a ON cs.updated_by = a.id
                                       ORDER BY w.username";
                                $result = mysqli_query($conn, $sql);
                                while($row = mysqli_fetch_assoc($result)) {
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($row['username']) . "</td>";
                                    echo "<td>" . $row['score'] . "</td>";
                                    echo "<td>" . ($row['last_updated'] ? date('Y-m-d', strtotime($row['last_updated'])) : 'Never') . "</td>";
                                    echo "<td>" . ($row['updated_by'] ? htmlspecialchars($row['updated_by']) : '-') . "</td>";
                                    echo "<td>
                                            <button type='button' class='btn btn-sm btn-primary' 
                                                    data-bs-toggle='modal' 
                                                    data-bs-target='#updateScoreModal' 
                                                    data-wholesaler-id='" . $row['id'] . "'
                                                    data-wholesaler-name='" . htmlspecialchars($row['username']) . "'
                                                    data-current-score='" . $row['score'] . "'>
                                                Update Score
                                            </button>
                                          </td>";
                                    echo "</tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Update Score Modal -->
<div class="modal fade" id="updateScoreModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Credit Score</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <input type="hidden" name="wholesaler_id" id="wholesaler_id">
                    <div class="mb-3">
                        <label class="form-label">Wholesaler</label>
                        <input type="text" class="form-control" id="wholesaler_name" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Credit Score</label>
                        <input type="number" name="score" class="form-control" id="credit_score" min="0" max="100" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="update_credit_score" class="btn btn-primary">Update Score</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const updateScoreModal = document.getElementById('updateScoreModal');
    if (updateScoreModal) {
        updateScoreModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const wholesalerId = button.getAttribute('data-wholesaler-id');
            const wholesalerName = button.getAttribute('data-wholesaler-name');
            const currentScore = button.getAttribute('data-current-score');
            
            updateScoreModal.querySelector('#wholesaler_id').value = wholesalerId;
            updateScoreModal.querySelector('#wholesaler_name').value = wholesalerName;
            updateScoreModal.querySelector('#credit_score').value = currentScore;
        });
    }
});
</script>

<?php require_once "includes/footer.php"; ?> 