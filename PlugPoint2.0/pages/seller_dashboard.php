<?php include '../includes/header_pages.php'; ?>
<?php
require_once '../includes/db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header('Location: login.php');
    exit();
}
$seller_id = $_SESSION['user_id'];

// Stats
$product_count = $conn->query("SELECT COUNT(*) as c FROM products WHERE seller_id = $seller_id")->fetch_assoc()['c'];
$active_products = $conn->query("SELECT COUNT(*) as c FROM products WHERE seller_id = $seller_id AND status = 'active'")->fetch_assoc()['c'];
$pending_products = $conn->query("SELECT COUNT(*) as c FROM products WHERE seller_id = $seller_id AND status = 'pending'")->fetch_assoc()['c'];
$total_sales = $conn->query("SELECT SUM(total) as s FROM orders WHERE product_id IN (SELECT id FROM products WHERE seller_id = $seller_id) AND status IN ('paid','shipped','completed')")->fetch_assoc()['s'] ?? 0;
$total_orders = $conn->query("SELECT COUNT(*) as c FROM orders WHERE product_id IN (SELECT id FROM products WHERE seller_id = $seller_id)")->fetch_assoc()['c'];
$avg_rating = $conn->query("
    SELECT AVG(pr.rating) as avg_rating 
    FROM product_reviews pr 
    JOIN products p ON pr.product_id = p.id 
    WHERE p.seller_id = $seller_id
")->fetch_assoc()['avg_rating'] ?? 0;

// Subscription status
$sub = $conn->query("SELECT * FROM subscriptions WHERE seller_id = $seller_id AND status = 'active' AND end_date >= CURDATE() LIMIT 1");
$current = $sub && $sub->num_rows > 0 ? $sub->fetch_assoc() : null;

// Recent orders
$recent_orders = $conn->query("
    SELECT o.*, u.name as buyer_name, p.name as product_name, p.image 
    FROM orders o 
    JOIN users u ON o.buyer_id = u.id 
    JOIN products p ON o.product_id = p.id 
    WHERE p.seller_id = $seller_id 
    ORDER BY o.created_at DESC 
    LIMIT 10
");

// Top selling products
$top_products = $conn->query("
    SELECT p.name, p.image, COUNT(o.id) as order_count, SUM(o.total) as total_revenue
    FROM products p 
    LEFT JOIN orders o ON p.id = o.product_id 
    WHERE p.seller_id = $seller_id 
    GROUP BY p.id 
    ORDER BY order_count DESC 
    LIMIT 5
");

// Products by status
$products = $conn->query("SELECT * FROM products WHERE seller_id = $seller_id ORDER BY created_at DESC LIMIT 5");

// Monthly sales data for chart
$monthly_sales = $conn->query("
    SELECT DATE_FORMAT(o.created_at, '%Y-%m') as month, SUM(o.total) as revenue
    FROM orders o 
    JOIN products p ON o.product_id = p.id 
    WHERE p.seller_id = $seller_id 
    AND o.status IN ('paid', 'shipped', 'completed')
    AND o.created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(o.created_at, '%Y-%m')
    ORDER BY month DESC
");
?>

<div class="container py-5">
  <h2 class="mb-4" style="color: var(--primary-purple);">Seller Dashboard</h2>
  
  <!-- Subscription Status -->
  <?php if ($current): ?>
    <div class="alert alert-success">
      <h5><i class="bi bi-check-circle"></i> Active Subscription</h5>
      <p class="mb-0">You have an active <?= htmlspecialchars($current['plan']) ?> subscription until <?= date('F j, Y', strtotime($current['end_date'])) ?></p>
    </div>
  <?php else: ?>
    <div class="alert alert-warning">
      <h5><i class="bi bi-exclamation-triangle"></i> No Active Subscription</h5>
      <p class="mb-0">You need an active subscription to list products. <a href="subscription.php" class="alert-link">Subscribe now</a></p>
    </div>
  <?php endif; ?>

  <!-- Statistics Cards -->
  <div class="row mb-4">
    <div class="col-md-2">
      <div class="card text-center">
        <div class="card-body">
          <h3 class="text-primary"><?= $product_count ?></h3>
          <p class="card-text small">Total Products</p>
        </div>
      </div>
    </div>
    <div class="col-md-2">
      <div class="card text-center">
        <div class="card-body">
          <h3 class="text-success"><?= $active_products ?></h3>
          <p class="card-text small">Active Products</p>
        </div>
      </div>
    </div>
    <div class="col-md-2">
      <div class="card text-center">
        <div class="card-body">
          <h3 class="text-warning"><?= $pending_products ?></h3>
          <p class="card-text small">Pending Approval</p>
        </div>
      </div>
    </div>
    <div class="col-md-2">
      <div class="card text-center">
        <div class="card-body">
          <h3 class="text-info"><?= $total_orders ?></h3>
          <p class="card-text small">Total Orders</p>
        </div>
      </div>
    </div>
    <div class="col-md-2">
      <div class="card text-center">
        <div class="card-body">
          <h3 class="text-success">R<?= number_format($total_sales, 0) ?></h3>
          <p class="card-text small">Total Sales</p>
        </div>
      </div>
    </div>
    <div class="col-md-2">
      <div class="card text-center">
        <div class="card-body">
          <h3 class="text-warning"><?= number_format($avg_rating, 1) ?></h3>
          <p class="card-text small">Avg Rating</p>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <!-- Recent Orders -->
    <div class="col-md-8 mb-4">
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0">Recent Orders</h5>
          <a href="orders.php" class="btn btn-sm btn-outline-primary">View All</a>
        </div>
        <div class="card-body">
          <?php if ($recent_orders && $recent_orders->num_rows > 0): ?>
            <div class="table-responsive">
              <table class="table table-dark table-striped">
                <thead>
                  <tr>
                    <th>Product</th>
                    <th>Buyer</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Date</th>
                  </tr>
                </thead>
                <tbody>
                  <?php while ($order = $recent_orders->fetch_assoc()): ?>
                  <tr>
                    <td>
                      <div class="d-flex align-items-center">
                        <img src="../assets/images/<?= htmlspecialchars($order['image']) ?>" alt="" style="width:40px;height:40px;object-fit:cover;" class="me-2">
                        <span><?= htmlspecialchars($order['product_name']) ?></span>
                      </div>
                    </td>
                    <td><?= htmlspecialchars($order['buyer_name']) ?></td>
                    <td>R<?= $order['total'] ?></td>
                    <td>
                      <span class="badge bg-<?= $order['status'] === 'completed' ? 'success' : ($order['status'] === 'pending' ? 'warning' : 'info') ?>">
                        <?= ucfirst($order['status']) ?>
                      </span>
                    </td>
                    <td><?= date('M j, Y', strtotime($order['created_at'])) ?></td>
                  </tr>
                  <?php endwhile; ?>
                </tbody>
              </table>
            </div>
          <?php else: ?>
            <p class="text-muted text-center">No orders yet.</p>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Top Selling Products -->
    <div class="col-md-4 mb-4">
      <div class="card">
        <div class="card-header">
          <h5 class="mb-0">Top Selling Products</h5>
        </div>
        <div class="card-body">
          <?php if ($top_products && $top_products->num_rows > 0): ?>
            <?php while ($product = $top_products->fetch_assoc()): ?>
            <div class="d-flex align-items-center mb-3">
              <img src="../assets/images/<?= htmlspecialchars($product['image']) ?>" alt="" style="width:40px;height:40px;object-fit:cover;" class="me-3">
              <div class="flex-grow-1">
                <h6 class="mb-0"><?= htmlspecialchars($product['name']) ?></h6>
                <small class="text-muted"><?= $product['order_count'] ?> orders • R<?= number_format($product['total_revenue'], 0) ?></small>
              </div>
            </div>
            <?php endwhile; ?>
          <?php else: ?>
            <p class="text-muted">No sales data available.</p>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <!-- My Products -->
    <div class="col-md-6 mb-4">
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0">My Products</h5>
          <a href="add_product.php" class="btn btn-sm btn-outline-primary">Add Product</a>
        </div>
        <div class="card-body">
          <?php if ($products && $products->num_rows > 0): ?>
            <?php while ($product = $products->fetch_assoc()): ?>
            <div class="d-flex align-items-center mb-3">
              <img src="../assets/images/<?= htmlspecialchars($product['image']) ?>" alt="" style="width:50px;height:50px;object-fit:cover;" class="me-3">
              <div class="flex-grow-1">
                <h6 class="mb-0"><?= htmlspecialchars($product['name']) ?></h6>
                <small class="text-muted">R<?= $product['price'] ?> • <?= ucfirst($product['status']) ?></small>
              </div>
              <span class="badge bg-<?= $product['status'] === 'active' ? 'success' : ($product['status'] === 'pending' ? 'warning' : 'danger') ?>">
                <?= ucfirst($product['status']) ?>
              </span>
            </div>
            <?php endwhile; ?>
          <?php else: ?>
            <p class="text-muted">No products listed yet.</p>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Monthly Sales Chart -->
    <div class="col-md-6 mb-4">
      <div class="card">
        <div class="card-header">
          <h5 class="mb-0">Monthly Sales (Last 6 Months)</h5>
        </div>
        <div class="card-body">
          <?php if ($monthly_sales && $monthly_sales->num_rows > 0): ?>
            <div class="row">
              <?php while ($sale = $monthly_sales->fetch_assoc()): ?>
              <div class="col-6 mb-2">
                <div class="text-center">
                  <h6 class="mb-1"><?= date('M Y', strtotime($sale['month'] . '-01')) ?></h6>
                  <p class="mb-0 text-success">R<?= number_format($sale['revenue'], 0) ?></p>
                </div>
              </div>
              <?php endwhile; ?>
            </div>
          <?php else: ?>
            <p class="text-muted text-center">No sales data available.</p>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Quick Actions -->
  <div class="card">
    <div class="card-header">
      <h5 class="mb-0">Quick Actions</h5>
    </div>
    <div class="card-body">
      <div class="row">
        <div class="col-md-3">
          <a href="add_product.php" class="btn btn-primary w-100 mb-2">
            <i class="bi bi-plus-square"></i> Add Product
          </a>
        </div>
        <div class="col-md-3">
          <a href="subscription.php" class="btn btn-outline-primary w-100 mb-2">
            <i class="bi bi-credit-card"></i> Manage Subscription
          </a>
        </div>
        <div class="col-md-3">
          <a href="messages.php" class="btn btn-outline-primary w-100 mb-2">
            <i class="bi bi-chat"></i> View Messages
          </a>
        </div>
        <div class="col-md-3">
          <a href="profile.php" class="btn btn-outline-primary w-100 mb-2">
            <i class="bi bi-person"></i> Edit Profile
          </a>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include '../includes/footer.php'; ?> 