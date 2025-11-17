<?php
require_once "includes/header.php";

// Verify user is enterprise
if($_SESSION["user_type"] !== "enterprise") {
    header("location: index.php");
    exit;
}

// Create orders table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS orders (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    enterprise_id INT NOT NULL,
    wholesaler_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'approved', 'rejected', 'completed') NOT NULL DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (enterprise_id) REFERENCES users(id),
    FOREIGN KEY (wholesaler_id) REFERENCES users(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
)";

if (!mysqli_query($conn, $sql)) {
    echo "Error creating orders table: " . mysqli_error($conn);
}
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h2>Enterprise Dashboard</h2>
            <p class="text-muted">Manage your orders and browse products</p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3 mb-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Orders</h5>
                    <?php
                    $sql = "SELECT COUNT(*) as total FROM orders WHERE enterprise_id = " . $_SESSION['id'];
                    $result = mysqli_query($conn, $sql);
                    $row = mysqli_fetch_assoc($result);
                    ?>
                    <h2 class="card-text"><?php echo $row['total']; ?></h2>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Pending Orders</h5>
                    <?php
                    $sql = "SELECT COUNT(*) as total FROM orders WHERE enterprise_id = " . $_SESSION['id'] . " AND status = 'pending'";
                    $result = mysqli_query($conn, $sql);
                    $row = mysqli_fetch_assoc($result);
                    ?>
                    <h2 class="card-text"><?php echo $row['total']; ?></h2>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-4">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">Completed Orders</h5>
                    <?php
                    $sql = "SELECT COUNT(*) as total FROM orders WHERE enterprise_id = " . $_SESSION['id'] . " AND status = 'completed'";
                    $result = mysqli_query($conn, $sql);
                    $row = mysqli_fetch_assoc($result);
                    ?>
                    <h2 class="card-text"><?php echo $row['total']; ?></h2>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-4">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Spent</h5>
                    <?php
                    $sql = "SELECT SUM(total_price) as total FROM orders WHERE enterprise_id = " . $_SESSION['id'] . " AND status = 'completed'";
                    $result = mysqli_query($conn, $sql);
                    $row = mysqli_fetch_assoc($result);
                    ?>
                    <h2 class="card-text">$<?php echo number_format($row['total'] ?? 0, 2); ?></h2>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Available Products</h5>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#orderModal">
                        Place New Order
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Product Name</th>
                                    <th>Wholesaler</th>
                                    <th>Description</th>
                                    <th>Price</th>
                                    <th>Stock</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql = "SELECT p.*, u.username as wholesaler_name 
                                       FROM products p 
                                       JOIN users u ON p.wholesaler_id = u.id 
                                       WHERE p.stock > 0 
                                       ORDER BY p.created_at DESC";
                                $result = mysqli_query($conn, $sql);
                                while($row = mysqli_fetch_assoc($result)) {
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['wholesaler_name']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['description']) . "</td>";
                                    echo "<td>$" . number_format($row['price'], 2) . "</td>";
                                    echo "<td>" . $row['stock'] . "</td>";
                                    echo "<td>
                                            <button class='btn btn-sm btn-primary order-product' 
                                                    data-id='" . $row['id'] . "'
                                                    data-name='" . htmlspecialchars($row['name']) . "'
                                                    data-price='" . $row['price'] . "'
                                                    data-stock='" . $row['stock'] . "'
                                                    data-wholesaler='" . $row['wholesaler_id'] . "'>
                                                Order
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

    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Your Orders</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Product</th>
                                    <th>Wholesaler</th>
                                    <th>Quantity</th>
                                    <th>Total Price</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql = "SELECT o.*, p.name as product_name, u.username as wholesaler_name 
                                       FROM orders o 
                                       JOIN products p ON o.product_id = p.id 
                                       JOIN users u ON o.wholesaler_id = u.id 
                                       WHERE o.enterprise_id = " . $_SESSION['id'] . " 
                                       ORDER BY o.created_at DESC";
                                $result = mysqli_query($conn, $sql);
                                while($row = mysqli_fetch_assoc($result)) {
                                    echo "<tr>";
                                    echo "<td>" . $row['id'] . "</td>";
                                    echo "<td>" . htmlspecialchars($row['product_name']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['wholesaler_name']) . "</td>";
                                    echo "<td>" . $row['quantity'] . "</td>";
                                    echo "<td>$" . number_format($row['total_price'], 2) . "</td>";
                                    echo "<td><span class='badge bg-" . 
                                         ($row['status'] == 'completed' ? 'success' : 
                                          ($row['status'] == 'pending' ? 'warning' : 
                                           ($row['status'] == 'rejected' ? 'danger' : 'info'))) . 
                                         "'>" . ucfirst($row['status']) . "</span></td>";
                                    echo "<td>" . date('M d, Y H:i', strtotime($row['created_at'])) . "</td>";
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

<!-- Order Modal -->
<div class="modal fade" id="orderModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Place Order</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="place_order.php" method="post">
                <div class="modal-body">
                    <input type="hidden" name="product_id" id="product_id">
                    <input type="hidden" name="wholesaler_id" id="wholesaler_id">
                    <div class="mb-3">
                        <label class="form-label">Product</label>
                        <input type="text" class="form-control" id="product_name" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Price per unit</label>
                        <input type="text" class="form-control" id="product_price" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Available Stock</label>
                        <input type="text" class="form-control" id="product_stock" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Quantity</label>
                        <input type="number" class="form-control" name="quantity" min="1" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Place Order</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle order button clicks
    document.querySelectorAll('.order-product').forEach(button => {
        button.addEventListener('click', function() {
            const modal = document.getElementById('orderModal');
            document.getElementById('product_id').value = this.dataset.id;
            document.getElementById('wholesaler_id').value = this.dataset.wholesaler;
            document.getElementById('product_name').value = this.dataset.name;
            document.getElementById('product_price').value = '$' + parseFloat(this.dataset.price).toFixed(2);
            document.getElementById('product_stock').value = this.dataset.stock;
            
            // Set max quantity
            const quantityInput = modal.querySelector('input[name="quantity"]');
            quantityInput.max = this.dataset.stock;
            
            new bootstrap.Modal(modal).show();
        });
    });
});
</script>

<?php require_once "includes/footer.php"; ?> 