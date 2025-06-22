<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include '../includes/header_pages.php';
require_once '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'buyer') {
    header('Location: login.php');
    exit();
}

$buyer_id = $_SESSION['user_id'];

// Get buyer's wishlist
$wishlist = $conn->query("
    SELECT w.*, p.name as product_name, p.price, p.image, p.category, u.name as seller_name 
    FROM wishlists w 
    JOIN products p ON w.product_id = p.id 
    JOIN users u ON p.seller_id = u.id 
    WHERE w.buyer_id = $buyer_id AND p.status = 'active'
    ORDER BY w.created_at DESC 
    LIMIT 5
");

// Get buyer's recent orders
$recent_orders = $conn->query("
    SELECT o.*, p.name as product_name, p.image, p.price, u.name as seller_name 
    FROM orders o 
    JOIN products p ON o.product_id = p.id 
    JOIN users u ON p.seller_id = u.id 
    WHERE o.buyer_id = $buyer_id 
    ORDER BY o.created_at DESC 
    LIMIT 5
");

// Get featured products (active products with good ratings)
$featured_products = $conn->query("
    SELECT p.*, u.name as seller_name, 
           COALESCE(AVG(pr.rating), 0) as avg_rating,
           COUNT(pr.id) as review_count
    FROM products p 
    JOIN users u ON p.seller_id = u.id 
    LEFT JOIN product_reviews pr ON p.id = pr.product_id 
    WHERE p.status = 'active' 
    GROUP BY p.id 
    ORDER BY avg_rating DESC, p.created_at DESC 
    LIMIT 6
");

// Get buyer stats
$total_orders = $conn->query("SELECT COUNT(*) as count FROM orders WHERE buyer_id = $buyer_id")->fetch_assoc()['count'];
$total_spent = $conn->query("SELECT SUM(total) as total FROM orders WHERE buyer_id = $buyer_id AND status IN ('paid', 'shipped', 'completed')")->fetch_assoc()['total'] ?? 0;
$wishlist_count = $conn->query("SELECT COUNT(*) as count FROM wishlists WHERE buyer_id = $buyer_id")->fetch_assoc()['count'];
?>

<div class="container py-5">
  <h2 class="mb-4" style="color: var(--primary-blue);">Buyer Dashboard</h2>
  
  <!-- Stats Cards -->
  <div class="row mb-4">
    <div class="col-md-4">
      <div class="card text-center">
        <div class="card-body">
          <h3 class="text-primary"><?= $total_orders ?></h3>
          <p class="card-text">Total Orders</p>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card text-center">
        <div class="card-body">
          <h3 class="text-success">R<?= number_format($total_spent, 2) ?></h3>
          <p class="card-text">Total Spent</p>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card text-center">
        <div class="card-body">
          <h3 class="text-warning"><?= $wishlist_count ?></h3>
          <p class="card-text">Wishlist Items</p>
        </div>
      </div>
    </div>
  </div>

  <div class="row mb-4">
    <!-- Wishlist -->
    <div class="col-md-6">
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0">My Wishlist</h5>
          <a href="wishlist_view.php" class="btn btn-sm btn-outline-primary">View All</a>
        </div>
        <div class="card-body">
          <?php if ($wishlist && $wishlist->num_rows > 0): ?>
            <?php while ($item = $wishlist->fetch_assoc()): ?>
            <div class="d-flex align-items-center mb-3">
              <img src="../assets/images/<?= htmlspecialchars($item['image']) ?>" alt="" style="width:50px;height:50px;object-fit:cover;" class="me-3">
              <div class="flex-grow-1">
                <h6 class="mb-0"><?= htmlspecialchars($item['product_name']) ?></h6>
                <small class="text-muted">R<?= $item['price'] ?> • <?= htmlspecialchars($item['seller_name']) ?></small>
              </div>
              <a href="product_detail.php?id=<?= $item['product_id'] ?>" class="btn btn-sm btn-primary">View</a>
            </div>
            <?php endwhile; ?>
          <?php else: ?>
            <p class="text-muted mb-0">No items in wishlist.</p>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Recent Orders -->
    <div class="col-md-6">
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0">Recent Orders</h5>
          <a href="orders.php" class="btn btn-sm btn-outline-primary">View All</a>
        </div>
        <div class="card-body">
          <?php if ($recent_orders && $recent_orders->num_rows > 0): ?>
            <?php while ($order = $recent_orders->fetch_assoc()): ?>
            <div class="d-flex align-items-center mb-3">
              <img src="../assets/images/<?= htmlspecialchars($order['image']) ?>" alt="" style="width:50px;height:50px;object-fit:cover;" class="me-3">
              <div class="flex-grow-1">
                <h6 class="mb-0"><?= htmlspecialchars($order['product_name']) ?></h6>
                <small class="text-muted">R<?= $order['total'] ?> • <?= ucfirst($order['status']) ?></small>
              </div>
              <span class="badge bg-<?= $order['status'] === 'completed' ? 'success' : ($order['status'] === 'pending' ? 'warning' : 'info') ?>">
                <?= ucfirst($order['status']) ?>
              </span>
            </div>
            <?php endwhile; ?>
          <?php else: ?>
            <p class="text-muted mb-0">No recent orders.</p>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Featured Products -->
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h4 class="mb-0">Featured Products</h4>
      <a href="products.php" class="btn btn-outline-primary">View All Products</a>
    </div>
    <div class="card-body">
      <div class="row">
        <?php if ($featured_products && $featured_products->num_rows > 0):
          while ($product = $featured_products->fetch_assoc()): ?>
        <div class="col-md-4 mb-3">
          <div class="card h-100">
            <img src="../assets/images/<?= htmlspecialchars($product['image']) ?>" class="card-img-top" alt="<?= htmlspecialchars($product['name']) ?>" style="height:200px;object-fit:cover;">
            <div class="card-body">
              <h6 class="card-title"><?= htmlspecialchars($product['name']) ?></h6>
              <p class="card-text mb-1">R<?= htmlspecialchars($product['price']) ?></p>
              <div class="mb-2">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                  <i class="bi bi-star<?= $i <= $product['avg_rating'] ? '-fill' : '' ?>" style="color: #ffc107;"></i>
                <?php endfor; ?>
                <small class="text-muted">(<?= $product['review_count'] ?> reviews)</small>
              </div>
              <p class="card-text"><small class="text-muted">Seller: <?= htmlspecialchars($product['seller_name']) ?></small></p>
              <div class="d-flex gap-2">
                <a href="product_detail.php?id=<?= $product['id'] ?>" class="btn btn-primary btn-sm">View</a>
                <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'buyer'): ?>
                  <form method="POST" action="wishlist.php" class="d-inline">
                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                    <button type="submit" class="btn btn-outline-primary btn-sm">Add to Wishlist</button>
                  </form>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
        <?php endwhile; else: ?>
          <div class="col-12 text-center">
            <p class="text-muted">No featured products available.</p>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php include '../includes/footer.php'; ?> 