<?php include '../includes/header_pages.php'; ?>
<?php
require_once '../includes/db.php';

// Get featured products with ratings
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
    LIMIT 8
");

// Get category counts
$categories = $conn->query("
    SELECT category, COUNT(*) as count 
    FROM products 
    WHERE status = 'active' 
    GROUP BY category 
    ORDER BY count DESC 
    LIMIT 6
");

// Get top sellers
$top_sellers = $conn->query("
    SELECT u.name, u.id, COUNT(p.id) as product_count, 
           COALESCE(AVG(pr.rating), 0) as avg_rating
    FROM users u 
    LEFT JOIN products p ON u.id = p.seller_id AND p.status = 'active'
    LEFT JOIN product_reviews pr ON p.id = pr.product_id 
    WHERE u.role = 'seller' 
    GROUP BY u.id 
    HAVING product_count > 0
    ORDER BY avg_rating DESC, product_count DESC 
    LIMIT 4
");

// Get recent products
$recent_products = $conn->query("
    SELECT p.*, u.name as seller_name, 
           COALESCE(AVG(pr.rating), 0) as avg_rating,
           COUNT(pr.id) as review_count
    FROM products p 
    JOIN users u ON p.seller_id = u.id 
    LEFT JOIN product_reviews pr ON p.id = pr.product_id 
    WHERE p.status = 'active' 
    GROUP BY p.id 
    ORDER BY p.created_at DESC 
    LIMIT 4
");
?>

<!-- Hero Section -->
<div class="hero-gradient">
  <div class="container text-center text-white">
    <!-- Dark mode toggle for hero section -->
    <!-- <div class="text-end mb-3">
      <button id="heroDarkModeToggle" class="btn btn-outline-light" style="font-size:1.2rem;" title="Toggle dark mode">
        <i class="bi bi-moon"></i> Dark Mode
      </button>
    </div> -->
    <h1 class="display-4 fw-bold mb-4">Welcome to PlugPoint</h1>
    <p class="lead mb-4">Your trusted marketplace for quality products from verified sellers</p>
    <div class="row justify-content-center">
      <div class="col-md-6">
        <form action="products.php" method="GET" class="d-flex">
          <input type="text" class="form-control me-2" name="search" placeholder="Search for products...">
          <button type="submit" class="btn btn-light">Search</button>
        </form>
      </div>
    </div>
    <div class="mt-4">
      <a href="products.php" class="btn btn-outline-light me-3">Browse Products</a>
      <?php if (!isset($_SESSION['user_id'])): ?>
        <a href="register.php" class="btn btn-light">Start Selling</a>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Categories Section -->
<div class="container py-5">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h3 class="fw-bold">Shop by Category</h3>
      <p class="mb-0">Discover products in your favorite categories</p>
    </div>
    <a href="products.php" class="btn btn-outline-primary">View All</a>
  </div>
  <div class="row g-4">
    <?php if ($categories && $categories->num_rows > 0):
      while ($cat = $categories->fetch_assoc()): ?>
    <div class="col-md-4 col-lg-2">
      <a href="products.php?category=<?= urlencode($cat['category']) ?>" class="text-decoration-none">
        <div class="card text-center h-100">
          <div class="card-body">
            <i class="bi bi-box display-6 mb-3" style="color: var(--primary-blue);"></i>
            <h6 class="card-title"><?= htmlspecialchars($cat['category']) ?></h6>
            <p class="card-text small text-muted"><?= $cat['count'] ?> products</p>
          </div>
        </div>
      </a>
    </div>
    <?php endwhile; else: ?>
      <div class="col-12 text-center text-muted">No categories available.</div>
    <?php endif; ?>
  </div>
</div>

<!-- Featured Products -->
<div class="container py-5">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h3 class="fw-bold">Featured Products</h3>
      <p class="mb-0">Top-rated products from our community</p>
    </div>
    <a href="products.php" class="btn btn-outline-primary">View All</a>
  </div>
  <div class="row g-4">
    <?php if ($featured_products && $featured_products->num_rows > 0):
      while ($p = $featured_products->fetch_assoc()): ?>
    <div class="col-md-3">
      <div class="card h-100">
        <div style="height:200px; background:#23263a; display:flex; align-items:center; justify-content:center;">
          <?php if ($p['image']): ?>
            <img src="../assets/images/<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>" style="max-height:180px; max-width:100%; object-fit:contain;">
          <?php else: ?>
            <span class="text-muted">No Image</span>
          <?php endif; ?>
        </div>
        <div class="card-body">
          <h6 class="fw-bold mb-1" style="color:var(--primary-blue);"><?= htmlspecialchars($p['name']) ?></h6>
          <p class="mb-1">R<?= htmlspecialchars($p['price']) ?></p>
          
          <!-- Rating Stars -->
          <div class="mb-2">
            <?php for ($i = 1; $i <= 5; $i++): ?>
              <i class="bi bi-star<?= $i <= $p['avg_rating'] ? '-fill' : '' ?>" style="color: #ffc107; font-size: 0.8rem;"></i>
            <?php endfor; ?>
            <small class="text-muted">(<?= $p['review_count'] ?> reviews)</small>
          </div>
          
          <p class="card-text"><small class="text-muted">Seller: <?= htmlspecialchars($p['seller_name']) ?></small></p>
          <div class="d-flex gap-2">
            <a href="product_detail.php?id=<?= $p['id'] ?>" class="btn btn-primary btn-sm">View</a>
            <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'buyer'): ?>
              <form method="POST" action="cart.php" class="d-inline">
                <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                <button type="submit" class="btn btn-outline-primary btn-sm">Add to Cart</button>
              </form>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
    <?php endwhile; else: ?>
      <div class="col-12 text-center text-muted">No featured products available.</div>
    <?php endif; ?>
  </div>
</div>

<!-- Top Sellers Section -->
<div class="container py-5">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h3 class="fw-bold">Top Sellers</h3>
      <p class="mb-0">Meet our most trusted sellers</p>
    </div>
  </div>
  <div class="row g-4">
    <?php if ($top_sellers && $top_sellers->num_rows > 0):
      while ($seller = $top_sellers->fetch_assoc()): ?>
    <div class="col-md-3">
      <div class="card text-center h-100">
        <div class="card-body">
          <div class="mb-3">
            <i class="bi bi-person-circle display-4" style="color: var(--primary-purple);"></i>
          </div>
          <h6 class="card-title"><?= htmlspecialchars($seller['name']) ?></h6>
          <p class="card-text small text-muted"><?= $seller['product_count'] ?> products</p>
          
          <!-- Rating Stars -->
          <div class="mb-2">
            <?php for ($i = 1; $i <= 5; $i++): ?>
              <i class="bi bi-star<?= $i <= $seller['avg_rating'] ? '-fill' : '' ?>" style="color: #ffc107; font-size: 0.8rem;"></i>
            <?php endfor; ?>
            <small class="text-muted">(<?= number_format($seller['avg_rating'], 1) ?>)</small>
          </div>
          
          <a href="products.php?seller=<?= urlencode($seller['name']) ?>" class="btn btn-outline-primary btn-sm">View Products</a>
        </div>
      </div>
    </div>
    <?php endwhile; else: ?>
      <div class="col-12 text-center text-muted">No sellers available.</div>
    <?php endif; ?>
  </div>
</div>

<!-- Recent Products -->
<div class="container py-5">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h3 class="fw-bold">New Arrivals</h3>
      <p class="mb-0">Fresh products just added to our marketplace</p>
    </div>
    <a href="products.php?sort=newest" class="btn btn-outline-primary">View All</a>
  </div>
  <div class="row g-4">
    <?php if ($recent_products && $recent_products->num_rows > 0):
      while ($p = $recent_products->fetch_assoc()): ?>
    <div class="col-md-3">
      <div class="card h-100">
        <div style="height:200px; background:#23263a; display:flex; align-items:center; justify-content:center;">
          <?php if ($p['image']): ?>
            <img src="../assets/images/<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>" style="max-height:180px; max-width:100%; object-fit:contain;">
          <?php else: ?>
            <span class="text-muted">No Image</span>
          <?php endif; ?>
        </div>
        <div class="card-body">
          <h6 class="fw-bold mb-1" style="color:var(--primary-blue);"><?= htmlspecialchars($p['name']) ?></h6>
          <p class="mb-1">R<?= htmlspecialchars($p['price']) ?></p>
          
          <!-- Rating Stars -->
          <div class="mb-2">
            <?php for ($i = 1; $i <= 5; $i++): ?>
              <i class="bi bi-star<?= $i <= $p['avg_rating'] ? '-fill' : '' ?>" style="color: #ffc107; font-size: 0.8rem;"></i>
            <?php endfor; ?>
            <small class="text-muted">(<?= $p['review_count'] ?> reviews)</small>
          </div>
          
          <p class="card-text"><small class="text-muted">Seller: <?= htmlspecialchars($p['seller_name']) ?></small></p>
          <div class="d-flex gap-2">
            <a href="product_detail.php?id=<?= $p['id'] ?>" class="btn btn-primary btn-sm">View</a>
            <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'buyer'): ?>
              <form method="POST" action="cart.php" class="d-inline">
                <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                <button type="submit" class="btn btn-outline-primary btn-sm">Add to Cart</button>
              </form>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
    <?php endwhile; else: ?>
      <div class="col-12 text-center text-muted">No recent products available.</div>
    <?php endif; ?>
  </div>
</div>

<!-- Call to Action -->
<div class="container py-5">
  <div class="card text-center" style="background: linear-gradient(90deg, var(--primary-blue), var(--primary-purple));">
    <div class="card-body text-white">
      <h3 class="mb-3">Ready to Start Selling?</h3>
      <p class="mb-4">Join thousands of sellers and start earning today</p>
      <?php if (!isset($_SESSION['user_id'])): ?>
        <a href="register.php" class="btn btn-light me-3">Register as Seller</a>
        <a href="products.php" class="btn btn-outline-light">Browse Products</a>
      <?php else: ?>
        <a href="add_product.php" class="btn btn-light me-3">Add Product</a>
        <a href="products.php" class="btn btn-outline-light">Browse Products</a>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php include '../includes/footer.php'; ?> 