<?php include '../includes/header_pages.php'; ?>
<?php
require_once '../includes/db.php';

// Handle filters
$category = isset($_GET['category']) ? $conn->real_escape_string($_GET['category']) : '';
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$min_price = isset($_GET['min_price']) ? floatval($_GET['min_price']) : 0;
$max_price = isset($_GET['max_price']) ? floatval($_GET['max_price']) : 999999;
$rating = isset($_GET['rating']) ? intval($_GET['rating']) : 0;
$seller = isset($_GET['seller']) ? $conn->real_escape_string($_GET['seller']) : '';
$sort = isset($_GET['sort']) ? $conn->real_escape_string($_GET['sort']) : 'newest';

$where = ["p.status = 'active'"];
if ($category && $category !== 'All') {
    $where[] = "p.category = '$category'";
}
if ($search) {
    $where[] = "(p.name LIKE '%$search%' OR p.description LIKE '%$search%')";
}
if ($min_price > 0) {
    $where[] = "p.price >= $min_price";
}
if ($max_price < 999999) {
    $where[] = "p.price <= $max_price";
}
if ($rating > 0) {
    $where[] = "COALESCE(AVG(pr.rating), 0) >= $rating";
}
if ($seller) {
    $where[] = "u.name LIKE '%$seller%'";
}

$where_sql = implode(' AND ', $where);

// Sorting
$order_by = "ORDER BY ";
switch ($sort) {
    case 'price_low':
        $order_by .= "p.price ASC";
        break;
    case 'price_high':
        $order_by .= "p.price DESC";
        break;
    case 'rating':
        $order_by .= "avg_rating DESC";
        break;
    case 'oldest':
        $order_by .= "p.created_at ASC";
        break;
    default:
        $order_by .= "p.created_at DESC";
}

// Pagination
$per_page = 12;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $per_page;

// Get total count for pagination
$count_res = $conn->query("
    SELECT COUNT(DISTINCT p.id) as cnt 
    FROM products p 
    JOIN users u ON p.seller_id = u.id 
    LEFT JOIN product_reviews pr ON p.id = pr.product_id 
    WHERE $where_sql
    GROUP BY p.id
");
$total = $count_res ? $count_res->num_rows : 0;
$total_pages = ceil($total / $per_page);

// Get products with ratings
$products = $conn->query("
    SELECT p.*, u.name as seller_name, 
           COALESCE(AVG(pr.rating), 0) as avg_rating,
           COUNT(pr.id) as review_count
    FROM products p 
    JOIN users u ON p.seller_id = u.id 
    LEFT JOIN product_reviews pr ON p.id = pr.product_id 
    WHERE $where_sql
    GROUP BY p.id 
    $order_by 
    LIMIT $per_page OFFSET $offset
");

// Get unique sellers for filter
$sellers = $conn->query("
    SELECT DISTINCT u.name 
    FROM products p 
    JOIN users u ON p.seller_id = u.id 
    WHERE p.status = 'active' 
    ORDER BY u.name
");
?>

<div class="container py-5">
  <h2 class="mb-4" style="color: var(--primary-blue);">Products</h2>
  
  <!-- Search and Filter Form -->
  <div class="card mb-4">
    <div class="card-body">
      <form method="GET" class="row g-3">
        <div class="col-md-3">
          <input type="text" class="form-control" name="search" placeholder="Search products..." value="<?= htmlspecialchars($search) ?>">
        </div>
        <div class="col-md-2">
          <select class="form-select" name="category">
            <option value="">All Categories</option>
            <option value="Electronics"<?= $category === 'Electronics' ? ' selected' : '' ?>>Electronics</option>
            <option value="Fashion"<?= $category === 'Fashion' ? ' selected' : '' ?>>Fashion</option>
            <option value="Home & Garden"<?= $category === 'Home & Garden' ? ' selected' : '' ?>>Home & Garden</option>
            <option value="Sports & Outdoors"<?= $category === 'Sports & Outdoors' ? ' selected' : '' ?>>Sports & Outdoors</option>
            <option value="Health & Beauty"<?= $category === 'Health & Beauty' ? ' selected' : '' ?>>Health & Beauty</option>
            <option value="Toys & Games"<?= $category === 'Toys & Games' ? ' selected' : '' ?>>Toys & Games</option>
            <option value="Automotive"<?= $category === 'Automotive' ? ' selected' : '' ?>>Automotive</option>
            <option value="Books & Media"<?= $category === 'Books & Media' ? ' selected' : '' ?>>Books & Media</option>
            <option value="Collectibles"<?= $category === 'Collectibles' ? ' selected' : '' ?>>Collectibles</option>
            <option value="Other"<?= $category === 'Other' ? ' selected' : '' ?>>Other</option>
          </select>
        </div>
        <div class="col-md-2">
          <select class="form-select" name="seller">
            <option value="">All Sellers</option>
            <?php while ($seller_row = $sellers->fetch_assoc()): ?>
              <option value="<?= htmlspecialchars($seller_row['name']) ?>"<?= $seller === $seller_row['name'] ? ' selected' : '' ?>>
                <?= htmlspecialchars($seller_row['name']) ?>
              </option>
            <?php endwhile; ?>
          </select>
        </div>
        <div class="col-md-2">
          <select class="form-select" name="rating">
            <option value="0">Any Rating</option>
            <option value="4"<?= $rating === 4 ? ' selected' : '' ?>>4+ Stars</option>
            <option value="3"<?= $rating === 3 ? ' selected' : '' ?>>3+ Stars</option>
            <option value="2"<?= $rating === 2 ? ' selected' : '' ?>>2+ Stars</option>
            <option value="1"<?= $rating === 1 ? ' selected' : '' ?>>1+ Star</option>
          </select>
        </div>
        <div class="col-md-3">
          <div class="input-group">
            <input type="number" class="form-control" name="min_price" placeholder="Min Price" value="<?= $min_price > 0 ? $min_price : '' ?>">
            <span class="input-group-text">-</span>
            <input type="number" class="form-control" name="max_price" placeholder="Max Price" value="<?= $max_price < 999999 ? $max_price : '' ?>">
          </div>
        </div>
        <div class="col-md-2">
          <select class="form-select" name="sort">
            <option value="newest"<?= $sort === 'newest' ? ' selected' : '' ?>>Newest</option>
            <option value="oldest"<?= $sort === 'oldest' ? ' selected' : '' ?>>Oldest</option>
            <option value="price_low"<?= $sort === 'price_low' ? ' selected' : '' ?>>Price: Low to High</option>
            <option value="price_high"<?= $sort === 'price_high' ? ' selected' : '' ?>>Price: High to Low</option>
            <option value="rating"<?= $sort === 'rating' ? ' selected' : '' ?>>Highest Rated</option>
          </select>
        </div>
        <div class="col-md-2">
          <button type="submit" class="btn btn-primary w-100">Filter</button>
        </div>
        <div class="col-md-2">
          <a href="products.php" class="btn btn-outline-secondary w-100">Clear</a>
        </div>
      </form>
    </div>
  </div>

  <!-- Results Count -->
  <div class="d-flex justify-content-between align-items-center mb-3">
    <p class="mb-0">Showing <?= $total ?> products</p>
    <?php if ($search || $category || $min_price > 0 || $max_price < 999999 || $rating > 0 || $seller): ?>
      <small class="text-muted">
        Filters: 
        <?php 
        $active_filters = [];
        if ($search) $active_filters[] = "Search: '$search'";
        if ($category) $active_filters[] = "Category: $category";
        if ($min_price > 0) $active_filters[] = "Min Price: R$min_price";
        if ($max_price < 999999) $active_filters[] = "Max Price: R$max_price";
        if ($rating > 0) $active_filters[] = "Rating: $rating+ stars";
        if ($seller) $active_filters[] = "Seller: $seller";
        echo implode(', ', $active_filters);
        ?>
      </small>
    <?php endif; ?>
  </div>

  <!-- Products Grid -->
  <div class="row g-4">
    <?php if ($products && $products->num_rows > 0):
      while ($p = $products->fetch_assoc()): ?>
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
            
            <p class="card-text"><span class="badge bg-secondary"><?= htmlspecialchars($p['category']) ?></span></p>
            <p class="card-text"><small class="text-muted">Seller: <?= htmlspecialchars($p['seller_name']) ?></small></p>
            <a href="product_detail.php?id=<?= $p['id'] ?>" class="btn btn-primary">View Details</a>
            <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'buyer'): ?>
              <form method="POST" action="cart.php" class="d-inline">
                <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                <button type="submit" class="btn btn-outline-primary ms-2">Add to Cart</button>
              </form>
            <?php endif; ?>
          </div>
        </div>
      </div>
    <?php endwhile; else: ?>
      <div class="col-12">
        <div class="alert alert-info text-center">
          <h5>No products found</h5>
          <p>Try adjusting your search criteria or browse all products.</p>
          <a href="products.php" class="btn btn-primary">View All Products</a>
        </div>
      </div>
    <?php endif; ?>
  </div>
  
  <!-- Pagination -->
  <?php if ($total_pages > 1): ?>
    <nav aria-label="Page navigation" class="mt-4">
      <ul class="pagination justify-content-center">
        <li class="page-item<?= $page <= 1 ? ' disabled' : '' ?>">
          <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>" tabindex="-1">Previous</a>
        </li>
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
          <li class="page-item<?= $i == $page ? ' active' : '' ?>">
            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
          </li>
        <?php endfor; ?>
        <li class="page-item<?= $page >= $total_pages ? ' disabled' : '' ?>">
          <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">Next</a>
        </li>
      </ul>
    </nav>
  <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?> 