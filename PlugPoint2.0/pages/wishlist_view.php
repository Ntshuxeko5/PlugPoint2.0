<?php
include '../includes/header.php';
require_once '../includes/db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'buyer') {
    header('Location: login.php');
    exit();
}
$buyer_id = $_SESSION['user_id'];
$wishlist = $conn->query("SELECT w.id as wishlist_id, p.* FROM wishlists w JOIN products p ON w.product_id = p.id WHERE w.buyer_id = $buyer_id ORDER BY w.created_at DESC");
?>
<div class="container py-5">
  <h2 class="mb-4" style="color: var(--primary-blue);">My Wishlist</h2>
  <div class="row">
    <?php if ($wishlist && $wishlist->num_rows > 0):
      while ($p = $wishlist->fetch_assoc()): ?>
      <div class="col-md-4 mb-4">
        <div class="card h-100">
          <img src="../assets/images/<?= htmlspecialchars($p['image']) ?>" class="card-img-top" alt="<?= htmlspecialchars($p['name']) ?>" style="height:220px;object-fit:cover;">
          <div class="card-body">
            <h5 class="card-title"><?= htmlspecialchars($p['name']) ?></h5>
            <p class="card-text">R<?= htmlspecialchars($p['price']) ?></p>
            <form method="POST" action="wishlist_remove.php">
              <input type="hidden" name="wishlist_id" value="<?= $p['wishlist_id'] ?>">
              <button type="submit" class="btn btn-danger">Remove</button>
            </form>
          </div>
        </div>
      </div>
    <?php endwhile; else: ?>
      <div class="col-12"><div class="alert alert-info">Your wishlist is empty.</div></div>
    <?php endif; ?>
  </div>
</div>
<?php include '../includes/footer.php'; ?> 