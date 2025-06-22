<?php
include '../includes/header.php';
require_once '../includes/db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Handle product actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && isset($_POST['product_id'])) {
        $product_id = intval($_POST['product_id']);
        $action = $_POST['action'];
        
        if ($action === 'approve') {
            $conn->query("UPDATE products SET status = 'active' WHERE id = $product_id");
        } elseif ($action === 'reject') {
            $conn->query("UPDATE products SET status = 'rejected' WHERE id = $product_id");
        } elseif ($action === 'delete') {
            $conn->query("DELETE FROM products WHERE id = $product_id");
        }
        header('Location: admin_products.php');
        exit();
    }
}

// Search and filtering
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$category_filter = isset($_GET['category']) ? $conn->real_escape_string($_GET['category']) : '';
$status_filter = isset($_GET['status']) ? $conn->real_escape_string($_GET['status']) : '';

$where = [];
if ($search) {
    $where[] = "(p.name LIKE '%$search%' OR p.description LIKE '%$search%')";
}
if ($category_filter) {
    $where[] = "p.category = '$category_filter'";
}
if ($status_filter) {
    $where[] = "p.status = '$status_filter'";
}
$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$products = $conn->query("SELECT p.*, u.name as seller_name FROM products p JOIN users u ON p.seller_id = u.id $where_sql ORDER BY p.created_at DESC");
?>
<div class="container py-5">
  <h2 class="mb-4" style="color: var(--primary-purple);">Product Management</h2>
  
  <!-- Search and Filter -->
  <div class="card mb-4">
    <div class="card-body">
      <form method="GET" class="row g-3">
        <div class="col-md-3">
          <input type="text" class="form-control" name="search" placeholder="Search products..." value="<?= htmlspecialchars($search) ?>">
        </div>
        <div class="col-md-2">
          <select class="form-select" name="category">
            <option value="">All Categories</option>
            <option value="Electronics"<?= $category_filter === 'Electronics' ? ' selected' : '' ?>>Electronics</option>
            <option value="Fashion"<?= $category_filter === 'Fashion' ? ' selected' : '' ?>>Fashion</option>
            <option value="Home & Garden"<?= $category_filter === 'Home & Garden' ? ' selected' : '' ?>>Home & Garden</option>
            <option value="Sports & Outdoors"<?= $category_filter === 'Sports & Outdoors' ? ' selected' : '' ?>>Sports & Outdoors</option>
            <option value="Health & Beauty"<?= $category_filter === 'Health & Beauty' ? ' selected' : '' ?>>Health & Beauty</option>
            <option value="Toys & Games"<?= $category_filter === 'Toys & Games' ? ' selected' : '' ?>>Toys & Games</option>
            <option value="Automotive"<?= $category_filter === 'Automotive' ? ' selected' : '' ?>>Automotive</option>
            <option value="Books & Media"<?= $category_filter === 'Books & Media' ? ' selected' : '' ?>>Books & Media</option>
            <option value="Collectibles"<?= $category_filter === 'Collectibles' ? ' selected' : '' ?>>Collectibles</option>
            <option value="Other"<?= $category_filter === 'Other' ? ' selected' : '' ?>>Other</option>
          </select>
        </div>
        <div class="col-md-2">
          <select class="form-select" name="status">
            <option value="">All Status</option>
            <option value="active"<?= $status_filter === 'active' ? ' selected' : '' ?>>Active</option>
            <option value="pending"<?= $status_filter === 'pending' ? ' selected' : '' ?>>Pending</option>
            <option value="rejected"<?= $status_filter === 'rejected' ? ' selected' : '' ?>>Rejected</option>
          </select>
        </div>
        <div class="col-md-2">
          <button type="submit" class="btn btn-primary w-100">Filter</button>
        </div>
        <div class="col-md-2">
          <a href="admin_products.php" class="btn btn-outline-secondary w-100">Clear</a>
        </div>
      </form>
    </div>
  </div>

  <!-- Products Table -->
  <div class="card">
    <div class="card-header">
      <h4>All Products</h4>
    </div>
    <div class="card-body">
      <table class="table table-dark table-striped">
        <thead>
          <tr>
            <th>ID</th>
            <th>Image</th>
            <th>Name</th>
            <th>Seller</th>
            <th>Category</th>
            <th>Price</th>
            <th>Status</th>
            <th>Created</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($products && $products->num_rows > 0):
            while ($product = $products->fetch_assoc()): ?>
          <tr>
            <td><?= $product['id'] ?></td>
            <td>
              <img src="../assets/images/<?= htmlspecialchars($product['image']) ?>" alt="" style="width:50px;height:50px;object-fit:cover;">
            </td>
            <td><?= htmlspecialchars($product['name']) ?></td>
            <td><?= htmlspecialchars($product['seller_name']) ?></td>
            <td><span class="badge bg-secondary"><?= htmlspecialchars($product['category']) ?></span></td>
            <td>R<?= $product['price'] ?></td>
            <td>
              <span class="badge bg-<?= $product['status'] === 'active' ? 'success' : ($product['status'] === 'pending' ? 'warning' : 'danger') ?>">
                <?= ucfirst($product['status']) ?>
              </span>
            </td>
            <td><?= date('Y-m-d', strtotime($product['created_at'])) ?></td>
            <td>
              <?php if ($product['status'] === 'pending'): ?>
                <form method="POST" class="d-inline">
                  <input type="hidden" name="action" value="approve">
                  <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                  <button type="submit" class="btn btn-success btn-sm">Approve</button>
                </form>
                <form method="POST" class="d-inline">
                  <input type="hidden" name="action" value="reject">
                  <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                  <button type="submit" class="btn btn-warning btn-sm">Reject</button>
                </form>
              <?php endif; ?>
              <form method="POST" class="d-inline">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Delete this product?')">Delete</button>
              </form>
            </td>
          </tr>
          <?php endwhile; else: ?>
          <tr><td colspan="9" class="text-center">No products found.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php include '../includes/footer.php'; ?> 