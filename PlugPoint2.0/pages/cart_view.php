<?php
include '../includes/header.php';
require_once '../includes/db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'buyer') {
    header('Location: login.php');
    exit();
}
$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
$products = [];
$total = 0;
if ($cart) {
    $ids = implode(',', array_map('intval', $cart));
    $result = $conn->query("SELECT * FROM products WHERE id IN ($ids)");
    while ($row = $result->fetch_assoc()) {
        $products[$row['id']] = $row;
        $total += $row['price'];
    }
}
?>
<div class="container py-5">
  <h2 class="mb-4" style="color: var(--primary-blue);">My Cart</h2>
  <div class="row">
    <?php if ($products):
      foreach ($cart as $pid):
        if (!isset($products[$pid])) continue;
        $p = $products[$pid]; ?>
      <div class="col-md-4 mb-4">
        <div class="card h-100">
          <img src="../assets/images/<?= htmlspecialchars($p['image']) ?>" class="card-img-top" alt="<?= htmlspecialchars($p['name']) ?>" style="height:220px;object-fit:cover;">
          <div class="card-body">
            <h5 class="card-title"><?= htmlspecialchars($p['name']) ?></h5>
            <p class="card-text">R<?= htmlspecialchars($p['price']) ?></p>
            <form method="POST" action="cart_remove.php">
              <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
              <button type="submit" class="btn btn-danger">Remove</button>
            </form>
          </div>
        </div>
      </div>
    <?php endforeach; else: ?>
      <div class="col-12"><div class="alert alert-info">Your cart is empty.</div></div>
    <?php endif; ?>
  </div>
  <?php if ($products): ?>
    <div class="mt-4 text-end">
      <h4>Total: R<?= $total ?></h4>
      <a href="checkout.php" class="btn btn-primary btn-lg">Proceed to Checkout</a>
    </div>
  <?php endif; ?>
</div>
<?php include '../includes/footer.php'; ?> 