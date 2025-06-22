<?php
include '../includes/header.php';
require_once '../includes/db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Handle order status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && isset($_POST['order_id'])) {
        $order_id = intval($_POST['order_id']);
        $action = $_POST['action'];
        
        if ($action === 'update_status' && isset($_POST['status'])) {
            $status = $conn->real_escape_string($_POST['status']);
            $conn->query("UPDATE orders SET status = '$status' WHERE id = $order_id");
        }
        header('Location: admin_orders.php');
        exit();
    }
}

// Search and filtering
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? $conn->real_escape_string($_GET['status']) : '';
$date_from = isset($_GET['date_from']) ? $conn->real_escape_string($_GET['date_from']) : '';
$date_to = isset($_GET['date_to']) ? $conn->real_escape_string($_GET['date_to']) : '';

$where = [];
if ($search) {
    $where[] = "(u.name LIKE '%$search%' OR u.email LIKE '%$search%' OR p.name LIKE '%$search%')";
}
if ($status_filter) {
    $where[] = "o.status = '$status_filter'";
}
if ($date_from) {
    $where[] = "DATE(o.created_at) >= '$date_from'";
}
if ($date_to) {
    $where[] = "DATE(o.created_at) <= '$date_to'";
}
$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$orders = $conn->query("SELECT o.*, u.name as buyer_name, u.email as buyer_email, p.name as product_name, p.image as product_image FROM orders o JOIN users u ON o.buyer_id = u.id JOIN products p ON o.product_id = p.id $where_sql ORDER BY o.created_at DESC");
?>
<div class="container py-5">
  <h2 class="mb-4" style="color: var(--primary-purple);">Order Management</h2>
  
  <!-- Search and Filter -->
  <div class="card mb-4">
    <div class="card-body">
      <form method="GET" class="row g-3">
        <div class="col-md-2">
          <input type="text" class="form-control" name="search" placeholder="Search orders..." value="<?= htmlspecialchars($search) ?>">
        </div>
        <div class="col-md-2">
          <select class="form-select" name="status">
            <option value="">All Status</option>
            <option value="pending"<?= $status_filter === 'pending' ? ' selected' : '' ?>>Pending</option>
            <option value="paid"<?= $status_filter === 'paid' ? ' selected' : '' ?>>Paid</option>
            <option value="shipped"<?= $status_filter === 'shipped' ? ' selected' : '' ?>>Shipped</option>
            <option value="completed"<?= $status_filter === 'completed' ? ' selected' : '' ?>>Completed</option>
            <option value="cancelled"<?= $status_filter === 'cancelled' ? ' selected' : '' ?>>Cancelled</option>
          </select>
        </div>
        <div class="col-md-2">
          <input type="date" class="form-control" name="date_from" placeholder="From Date" value="<?= htmlspecialchars($date_from) ?>">
        </div>
        <div class="col-md-2">
          <input type="date" class="form-control" name="date_to" placeholder="To Date" value="<?= htmlspecialchars($date_to) ?>">
        </div>
        <div class="col-md-2">
          <button type="submit" class="btn btn-primary w-100">Filter</button>
        </div>
        <div class="col-md-2">
          <a href="admin_orders.php" class="btn btn-outline-secondary w-100">Clear</a>
        </div>
      </form>
    </div>
  </div>

  <!-- Orders Table -->
  <div class="card">
    <div class="card-header">
      <h4>All Orders</h4>
    </div>
    <div class="card-body">
      <table class="table table-dark table-striped">
        <thead>
          <tr>
            <th>Order ID</th>
            <th>Product</th>
            <th>Buyer</th>
            <th>Email</th>
            <th>Quantity</th>
            <th>Total</th>
            <th>Status</th>
            <th>Date</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($orders && $orders->num_rows > 0):
            while ($order = $orders->fetch_assoc()): ?>
          <tr>
            <td><?= $order['id'] ?></td>
            <td>
              <div class="d-flex align-items-center">
                <img src="../assets/images/<?= htmlspecialchars($order['product_image']) ?>" alt="" style="width:40px;height:40px;object-fit:cover;" class="me-2">
                <span><?= htmlspecialchars($order['product_name']) ?></span>
              </div>
            </td>
            <td><?= htmlspecialchars($order['buyer_name']) ?></td>
            <td><?= htmlspecialchars($order['buyer_email']) ?></td>
            <td><?= $order['quantity'] ?></td>
            <td>R<?= $order['total'] ?></td>
            <td>
              <form method="POST" class="d-inline">
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                  <option value="pending"<?= $order['status'] === 'pending' ? ' selected' : '' ?>>Pending</option>
                  <option value="paid"<?= $order['status'] === 'paid' ? ' selected' : '' ?>>Paid</option>
                  <option value="shipped"<?= $order['status'] === 'shipped' ? ' selected' : '' ?>>Shipped</option>
                  <option value="completed"<?= $order['status'] === 'completed' ? ' selected' : '' ?>>Completed</option>
                  <option value="cancelled"<?= $order['status'] === 'cancelled' ? ' selected' : '' ?>>Cancelled</option>
                </select>
              </form>
            </td>
            <td><?= date('Y-m-d H:i', strtotime($order['created_at'])) ?></td>
            <td>
              <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#orderModal<?= $order['id'] ?>">
                View Details
              </button>
            </td>
          </tr>
          
          <!-- Order Details Modal -->
          <div class="modal fade" id="orderModal<?= $order['id'] ?>" tabindex="-1">
            <div class="modal-dialog modal-lg">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title">Order Details - #<?= $order['id'] ?></h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                  <div class="row mb-3">
                    <div class="col-md-6">
                      <strong>Buyer:</strong> <?= htmlspecialchars($order['buyer_name']) ?><br>
                      <strong>Email:</strong> <?= htmlspecialchars($order['buyer_email']) ?><br>
                      <strong>Order Date:</strong> <?= date('Y-m-d H:i', strtotime($order['created_at'])) ?>
                    </div>
                    <div class="col-md-6">
                      <strong>Status:</strong> <?= ucfirst($order['status']) ?><br>
                      <strong>Total Amount:</strong> R<?= $order['total'] ?><br>
                      <strong>Quantity:</strong> <?= $order['quantity'] ?>
                    </div>
                  </div>
                  
                  <h6>Product Details:</h6>
                  <div class="row">
                    <div class="col-md-3">
                      <img src="../assets/images/<?= htmlspecialchars($order['product_image']) ?>" alt="" style="width:100px;height:100px;object-fit:cover;" class="img-fluid">
                    </div>
                    <div class="col-md-9">
                      <h6><?= htmlspecialchars($order['product_name']) ?></h6>
                      <p><strong>Price:</strong> R<?= $order['total'] / $order['quantity'] ?></p>
                      <p><strong>Quantity:</strong> <?= $order['quantity'] ?></p>
                      <p><strong>Total:</strong> R<?= $order['total'] ?></p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <?php endwhile; else: ?>
          <tr><td colspan="9" class="text-center">No orders found.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php include '../includes/footer.php'; ?> 