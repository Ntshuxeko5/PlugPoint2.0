<?php
include '../includes/header_pages.php';
require_once '../includes/db.php';
if (!isset($_GET['id'])) {
    echo '<div class="container py-5"><div class="alert alert-danger">Product not found.</div></div>';
    include '../includes/footer.php';
    exit();
}
$id = intval($_GET['id']);
$p = $conn->query("SELECT p.*, u.name as seller_name FROM products p JOIN users u ON p.seller_id = u.id WHERE p.id = $id AND p.status = 'active'");
if (!$p || $p->num_rows === 0) {
    echo '<div class="container py-5"><div class="alert alert-danger">Product not found or inactive.</div></div>';
    include '../includes/footer.php';
    exit();
}
$product = $p->fetch_assoc();
?>
<div class="container py-5">
  <div class="row">
    <div class="col-md-6">
      <img src="../assets/images/<?= htmlspecialchars($product['image']) ?>" class="img-fluid rounded" alt="<?= htmlspecialchars($product['name']) ?>">
    </div>
    <div class="col-md-6">
      <h2 style="color: var(--primary-blue);"><?= htmlspecialchars($product['name']) ?></h2>
      <h4 class="mb-3">R<?= htmlspecialchars($product['price']) ?></h4>
      <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>
      <p><strong>Seller:</strong> <?= htmlspecialchars($product['seller_name']) ?></p>
      <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'buyer' && $_SESSION['user_id'] != $product['seller_id']): ?>
        <a href="messages.php?user=<?= $product['seller_id'] ?>&product=<?= $product['id'] ?>" class="btn btn-outline-primary mb-3"><i class="bi bi-envelope"></i> Message Seller</a>
      <?php endif; ?>
      <?php
      // Fetch reviews and average rating
      $reviews_res = $conn->query("SELECT r.*, u.name FROM product_reviews r JOIN users u ON r.user_id = u.id WHERE r.product_id = $id ORDER BY r.created_at DESC");
      $avg_res = $conn->query("SELECT AVG(rating) as avg_rating, COUNT(*) as cnt FROM product_reviews WHERE product_id = $id");
      $avg = $avg_res ? $avg_res->fetch_assoc() : ['avg_rating'=>null,'cnt'=>0];
      $avg_rating = $avg['avg_rating'] ? round($avg['avg_rating'],1) : null;
      $review_count = $avg['cnt'];
      ?>
      <div class="mb-2">
        <?php if ($avg_rating): ?>
          <span class="fw-bold">Rating:</span>
          <span class="text-warning">
            <?php for ($i=1; $i<=5; $i++): ?>
              <i class="bi<?= $i <= round($avg_rating) ? ' bi-star-fill' : ' bi-star' ?>"></i>
            <?php endfor; ?>
          </span>
          <span class="ms-2">(<?= $avg_rating ?>/5, <?= $review_count ?> review<?= $review_count==1?'':'s' ?>)</span>
        <?php else: ?>
          <span class="text-muted">No reviews yet</span>
        <?php endif; ?>
      </div>
      <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'buyer'): ?>
        <?php
        // Check if user purchased this product
        $buyer_id = $_SESSION['user_id'];
        $bought = $conn->query("SELECT 1 FROM orders WHERE buyer_id=$buyer_id AND product_id=$id AND status IN ('paid','shipped','completed') LIMIT 1")->num_rows > 0;
        // Check if user already reviewed
        $already = $conn->query("SELECT 1 FROM product_reviews WHERE user_id=$buyer_id AND product_id=$id")->num_rows > 0;
        ?>
        <?php if ($bought && !$already): ?>
          <form method="POST" class="mb-3">
            <div class="mb-2">
              <label class="form-label">Leave a Review</label>
              <select name="rating" class="form-select w-auto d-inline" required>
                <option value="">Rating</option>
                <?php for ($i=5; $i>=1; $i--): ?>
                  <option value="<?= $i ?>"><?= $i ?> Star<?= $i==1?'':'s' ?></option>
                <?php endfor; ?>
              </select>
            </div>
            <div class="mb-2">
              <textarea name="review" class="form-control" rows="2" placeholder="Write your review..." required></textarea>
            </div>
            <button type="submit" class="btn btn-primary btn-sm">Submit Review</button>
          </form>
        <?php elseif ($bought && $already): ?>
          <div class="alert alert-info py-2">You have already reviewed this product.</div>
        <?php endif; ?>
      <?php endif; ?>
      <?php
      // Handle review submission
      if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id']) && $_SESSION['role'] === 'buyer' && $bought && !$already) {
        $rating = intval($_POST['rating']);
        $review = $conn->real_escape_string($_POST['review']);
        if ($rating >= 1 && $rating <= 5 && $review) {
          $conn->query("INSERT INTO product_reviews (product_id, user_id, rating, review) VALUES ($id, $buyer_id, $rating, '$review')");
          echo '<div class="alert alert-success py-2">Thank you for your review!</div>';
          echo '<script>setTimeout(()=>location.reload(), 1000);</script>';
        }
      }
      ?>
    </div>
  </div>
</div>
<div class="container py-3">
  <h5 class="mb-3">Product Reviews</h5>
  <?php if ($reviews_res && $reviews_res->num_rows > 0): ?>
    <?php while ($r = $reviews_res->fetch_assoc()): ?>
      <div class="border rounded p-3 mb-2 bg-light-subtle">
        <div class="d-flex align-items-center mb-1">
          <span class="fw-bold me-2"><?= htmlspecialchars($r['name']) ?></span>
          <span class="text-warning">
            <?php for ($i=1; $i<=5; $i++): ?>
              <i class="bi<?= $i <= $r['rating'] ? ' bi-star-fill' : ' bi-star' ?>"></i>
            <?php endfor; ?>
          </span>
          <span class="ms-2 small text-muted"><?= date('Y-m-d', strtotime($r['created_at'])) ?></span>
        </div>
        <div><?= nl2br(htmlspecialchars($r['review'])) ?></div>
      </div>
    <?php endwhile; ?>
  <?php else: ?>
    <div class="text-muted">No reviews yet.</div>
  <?php endif; ?>
</div>
<?php
// Related Products
$cat = $conn->real_escape_string($product['category']);
$related = $conn->query("SELECT * FROM products WHERE category='$cat' AND id != $id AND status='active' ORDER BY RAND() LIMIT 4");
if ($related && $related->num_rows > 0): ?>
<div class="container py-4">
  <h4 class="mb-3">Related Products</h4>
  <div class="row">
    <?php while ($r = $related->fetch_assoc()): ?>
      <div class="col-md-3 mb-3">
        <div class="card h-100 text-center">
          <div style="height:140px; background:#23263a; display:flex; align-items:center; justify-content:center;">
            <?php if ($r['image']): ?>
              <img src="../assets/images/<?= htmlspecialchars($r['image']) ?>" alt="<?= htmlspecialchars($r['name']) ?>" style="max-height:120px; max-width:100%; object-fit:contain;">
            <?php else: ?>
              <span class="text-muted">No Image</span>
            <?php endif; ?>
          </div>
          <div class="card-body p-2">
            <h6 class="fw-bold mb-1" style="color:var(--primary-blue); font-size:1rem;"><?= htmlspecialchars($r['name']) ?></h6>
            <p class="mb-1">R <?= htmlspecialchars($r['price']) ?></p>
            <a href="product_detail.php?id=<?= $r['id'] ?>" class="btn btn-outline-primary btn-sm">View</a>
          </div>
        </div>
      </div>
    <?php endwhile; ?>
  </div>
</div>
<?php endif; ?>
<?php include '../includes/footer.php'; ?> 