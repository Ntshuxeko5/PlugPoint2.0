<?php
include '../includes/header.php';
require_once '../includes/db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Fetch admin statistics
$total_users = $conn->query("SELECT COUNT(*) as c FROM users")->fetch_assoc()['c'];
$total_products = $conn->query("SELECT COUNT(*) as c FROM products")->fetch_assoc()['c'];
$total_orders = $conn->query("SELECT COUNT(*) as c FROM orders")->fetch_assoc()['c'];
$total_sales = $conn->query("SELECT SUM(total) as s FROM orders WHERE status IN ('paid','shipped','completed')")->fetch_assoc()['s'] ?? 0;

// Recent activity
$recent_users = $conn->query("SELECT * FROM users ORDER BY id DESC LIMIT 5");
$recent_orders = $conn->query("SELECT o.*, u.name as buyer_name, p.name as product_name FROM orders o JOIN users u ON o.buyer_id = u.id JOIN products p ON o.product_id = p.id ORDER BY o.created_at DESC LIMIT 5");
?>
<div class="container py-5">
  <h2 class="mb-4" style="color: var(--primary-purple);">Admin Dashboard</h2>
  
  <!-- Statistics Cards -->
  <div class="row mb-4">
    <div class="col-md-3">
      <div class="card text-center p-3">
        <h5>Total Users</h5>
        <span class="display-6"><?= $total_users ?></span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card text-center p-3">
        <h5>Total Products</h5>
        <span class="display-6"><?= $total_products ?></span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card text-center p-3">
        <h5>Total Orders</h5>
        <span class="display-6"><?= $total_orders ?></span>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card text-center p-3">
        <h5>Total Sales</h5>
        <span class="display-6">R<?= $total_sales ?: 0 ?></span>
      </div>
    </div>
  </div>

  <!-- Admin Actions -->
  <div class="row mb-4">
    <div class="col-12">
      <div class="card p-4">
        <h4>Admin Actions</h4>
        <div class="row">
          <div class="col-md-3">
            <a href="admin_users.php" class="btn btn-primary w-100 mb-2">
              <i class="bi bi-people"></i> Manage Users
            </a>
          </div>
          <div class="col-md-3">
            <a href="admin_products.php" class="btn btn-primary w-100 mb-2">
              <i class="bi bi-box"></i> Manage Products
            </a>
          </div>
          <div class="col-md-3">
            <a href="admin_orders.php" class="btn btn-primary w-100 mb-2">
              <i class="bi bi-cart"></i> View Orders
            </a>
          </div>
          <div class="col-md-3">
            <a href="admin_reports.php" class="btn btn-primary w-100 mb-2">
              <i class="bi bi-graph-up"></i> Reports
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Recent Activity -->
  <div class="row">
    <div class="col-md-6">
      <div class="card p-4">
        <h4>Recent Users</h4>
        <table class="table table-dark table-striped">
          <thead>
            <tr>
              <th>Name</th>
              <th>Email</th>
              <th>Role</th>
              <th>Joined</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($recent_users && $recent_users->num_rows > 0):
              while ($u = $recent_users->fetch_assoc()): ?>
            <tr>
              <td><?= htmlspecialchars($u['name']) ?></td>
              <td><?= htmlspecialchars($u['email']) ?></td>
              <td><span class="badge bg-<?= $u['role'] === 'admin' ? 'danger' : ($u['role'] === 'seller' ? 'warning' : 'info') ?>"><?= ucfirst($u['role']) ?></span></td>
              <td><?= date('Y-m-d', strtotime($u['created_at'])) ?></td>
            </tr>
            <?php endwhile; else: ?>
            <tr><td colspan="4">No users found.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card p-4">
        <h4>Recent Orders</h4>
        <table class="table table-dark table-striped">
          <thead>
            <tr>
              <th>Product</th>
              <th>Buyer</th>
              <th>Amount</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($recent_orders && $recent_orders->num_rows > 0):
              while ($o = $recent_orders->fetch_assoc()): ?>
            <tr>
              <td><?= htmlspecialchars($o['product_name']) ?></td>
              <td><?= htmlspecialchars($o['buyer_name']) ?></td>
              <td>R<?= $o['total'] ?></td>
              <td><span class="badge bg-<?= $o['status'] === 'paid' ? 'success' : 'secondary' ?>"><?= ucfirst($o['status']) ?></span></td>
            </tr>
            <?php endwhile; else: ?>
            <tr><td colspan="4">No orders found.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<?php include '../includes/footer.php'; ?> 