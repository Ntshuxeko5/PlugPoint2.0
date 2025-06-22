<?php
include '../includes/header_pages.php';
require_once '../includes/db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Date range filtering
$date_from = isset($_GET['date_from']) ? $conn->real_escape_string($_GET['date_from']) : date('Y-m-01');
$date_to = isset($_GET['date_to']) ? $conn->real_escape_string($_GET['date_to']) : date('Y-m-d');

$date_where = "WHERE DATE(created_at) BETWEEN '$date_from' AND '$date_to'";

// Get statistics
$total_users = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$total_products = $conn->query("SELECT COUNT(*) as count FROM products WHERE status = 'active'")->fetch_assoc()['count'];
$total_orders = $conn->query("SELECT COUNT(*) as count FROM orders $date_where")->fetch_assoc()['count'];
$total_revenue = $conn->query("SELECT SUM(total) as total FROM orders WHERE status != 'cancelled' AND DATE(created_at) BETWEEN '$date_from' AND '$date_to'")->fetch_assoc()['total'] ?? 0;

// User statistics
$buyers_count = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'buyer'")->fetch_assoc()['count'];
$sellers_count = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'seller'")->fetch_assoc()['count'];
$admins_count = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'admin'")->fetch_assoc()['count'];

// Order statistics
$pending_orders = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'pending' AND DATE(created_at) BETWEEN '$date_from' AND '$date_to'")->fetch_assoc()['count'];
$completed_orders = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'completed' AND DATE(created_at) BETWEEN '$date_from' AND '$date_to'")->fetch_assoc()['count'];

// Top selling products
$top_products = $conn->query("
    SELECT p.name, p.image, COUNT(o.id) as sales_count, SUM(o.quantity) as total_quantity
    FROM products p 
    LEFT JOIN orders o ON p.id = o.product_id 
    WHERE p.status = 'active' AND (o.status IS NULL OR o.status != 'cancelled')
    GROUP BY p.id 
    ORDER BY total_quantity DESC 
    LIMIT 5
");

// Recent orders
$recent_orders = $conn->query("
    SELECT o.*, u.name as buyer_name, p.name as product_name
    FROM orders o 
    JOIN users u ON o.buyer_id = u.id 
    JOIN products p ON o.product_id = p.id
    ORDER BY o.created_at DESC 
    LIMIT 10
");

// Category distribution
$category_stats = $conn->query("
    SELECT category, COUNT(*) as count 
    FROM products 
    WHERE status = 'active' 
    GROUP BY category 
    ORDER BY count DESC
");
?>
<div class="container py-5">
  <h2 class="mb-4" style="color: var(--primary-purple);">Admin Reports & Analytics</h2>
  
  <!-- Date Range Filter -->
  <div class="card mb-4">
    <div class="card-body">
      <form method="GET" class="row g-3">
        <div class="col-md-3">
          <label class="form-label">From Date</label>
          <input type="date" class="form-control" name="date_from" value="<?= htmlspecialchars($date_from) ?>">
        </div>
        <div class="col-md-3">
          <label class="form-label">To Date</label>
          <input type="date" class="form-control" name="date_to" value="<?= htmlspecialchars($date_to) ?>">
        </div>
        <div class="col-md-2">
          <label class="form-label">&nbsp;</label>
          <button type="submit" class="btn btn-primary w-100">Update</button>
        </div>
        <div class="col-md-2">
          <label class="form-label">&nbsp;</label>
          <a href="admin_reports.php" class="btn btn-outline-secondary w-100">Reset</a>
        </div>
      </form>
    </div>
  </div>

  <!-- Overview Statistics -->
  <div class="row mb-4">
    <div class="col-md-3">
      <div class="card text-center">
        <div class="card-body">
          <h3 class="text-primary"><?= number_format($total_users) ?></h3>
          <p class="card-text">Total Users</p>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card text-center">
        <div class="card-body">
          <h3 class="text-success"><?= number_format($total_products) ?></h3>
          <p class="card-text">Active Products</p>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card text-center">
        <div class="card-body">
          <h3 class="text-warning"><?= number_format($total_orders) ?></h3>
          <p class="card-text">Total Orders</p>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card text-center">
        <div class="card-body">
          <h3 class="text-info">R<?= number_format($total_revenue, 2) ?></h3>
          <p class="card-text">Total Revenue</p>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <!-- User Statistics -->
    <div class="col-md-6 mb-4">
      <div class="card">
        <div class="card-header">
          <h5>User Distribution</h5>
        </div>
        <div class="card-body">
          <div class="row text-center">
            <div class="col-4">
              <h4 class="text-primary"><?= $buyers_count ?></h4>
              <small>Buyers</small>
            </div>
            <div class="col-4">
              <h4 class="text-success"><?= $sellers_count ?></h4>
              <small>Sellers</small>
            </div>
            <div class="col-4">
              <h4 class="text-warning"><?= $admins_count ?></h4>
              <small>Admins</small>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Order Statistics -->
    <div class="col-md-6 mb-4">
      <div class="card">
        <div class="card-header">
          <h5>Order Status (Selected Period)</h5>
        </div>
        <div class="card-body">
          <div class="row text-center">
            <div class="col-6">
              <h4 class="text-warning"><?= $pending_orders ?></h4>
              <small>Pending</small>
            </div>
            <div class="col-6">
              <h4 class="text-success"><?= $completed_orders ?></h4>
              <small>Completed</small>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <!-- Top Selling Products -->
    <div class="col-md-6 mb-4">
      <div class="card">
        <div class="card-header">
          <h5>Top Selling Products</h5>
        </div>
        <div class="card-body">
          <?php if ($top_products && $top_products->num_rows > 0): ?>
            <?php while ($product = $top_products->fetch_assoc()): ?>
            <div class="d-flex align-items-center mb-3">
              <img src="../assets/images/<?= htmlspecialchars($product['image']) ?>" alt="" style="width:40px;height:40px;object-fit:cover;" class="me-3">
              <div class="flex-grow-1">
                <h6 class="mb-0"><?= htmlspecialchars($product['name']) ?></h6>
                <small class="text-muted"><?= $product['total_quantity'] ?? 0 ?> units sold</small>
              </div>
            </div>
            <?php endwhile; ?>
          <?php else: ?>
            <p class="text-muted">No sales data available.</p>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Category Distribution -->
    <div class="col-md-6 mb-4">
      <div class="card">
        <div class="card-header">
          <h5>Products by Category</h5>
        </div>
        <div class="card-body">
          <?php if ($category_stats && $category_stats->num_rows > 0): ?>
            <?php while ($category = $category_stats->fetch_assoc()): ?>
            <div class="d-flex justify-content-between align-items-center mb-2">
              <span><?= htmlspecialchars($category['category']) ?></span>
              <span class="badge bg-primary"><?= $category['count'] ?></span>
            </div>
            <?php endwhile; ?>
          <?php else: ?>
            <p class="text-muted">No category data available.</p>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Recent Orders -->
  <div class="card">
    <div class="card-header">
      <h5>Recent Orders</h5>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-dark table-striped">
          <thead>
            <tr>
              <th>Order ID</th>
              <th>Product</th>
              <th>Buyer</th>
              <th>Amount</th>
              <th>Status</th>
              <th>Date</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($recent_orders && $recent_orders->num_rows > 0):
              while ($order = $recent_orders->fetch_assoc()): ?>
            <tr>
              <td>#<?= $order['id'] ?></td>
              <td><?= htmlspecialchars($order['product_name']) ?></td>
              <td><?= htmlspecialchars($order['buyer_name']) ?></td>
              <td>R<?= $order['total'] ?></td>
              <td>
                <span class="badge bg-<?= $order['status'] === 'completed' ? 'success' : ($order['status'] === 'pending' ? 'warning' : 'info') ?>">
                  <?= ucfirst($order['status']) ?>
                </span>
              </td>
              <td><?= date('Y-m-d H:i', strtotime($order['created_at'])) ?></td>
            </tr>
            <?php endwhile; else: ?>
            <tr><td colspan="6" class="text-center">No recent orders.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<?php include '../includes/footer.php'; ?> 