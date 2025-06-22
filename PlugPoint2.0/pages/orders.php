<?php
include '../includes/header.php';
require_once '../includes/db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'buyer') {
    header('Location: login.php');
    exit();
}
$buyer_id = $_SESSION['user_id'];
$orders = $conn->query("SELECT o.*, p.name, p.image, p.price FROM orders o JOIN products p ON o.product_id = p.id WHERE o.buyer_id = $buyer_id ORDER BY o.created_at DESC");
?>
<div class="container py-5">
  <h2 class="mb-4" style="color: var(--primary-blue);">My Orders</h2>
  <div class="row">
    <?php if ($orders && $orders->num_rows > 0):
      while ($o = $orders->fetch_assoc()): ?>
      <div class="col-md-4 mb-4">
        <div class="card h-100">
          <img src="../assets/images/<?= htmlspecialchars($o['image']) ?>" class="card-img-top" alt="<?= htmlspecialchars($o['name']) ?>" style="height:220px;object-fit:cover;">
          <div class="card-body">
            <h5 class="card-title"><?= htmlspecialchars($o['name']) ?></h5>
            <p class="card-text">R<?= htmlspecialchars($o['price']) ?></p>
            <p class="card-text"><span class="badge bg-success">Status: <?= ucfirst($o['status']) ?></span></p>
            <p class="card-text"><small class="text-muted">Ordered on: <?= date('Y-m-d', strtotime($o['created_at'])) ?></small></p>
          </div>
        </div>
      </div>
    <?php endwhile; else: ?>
      <div class="col-12"><div class="alert alert-info">You have no orders yet.</div></div>
    <?php endif; ?>
  </div>
</div>
<?php include '../includes/footer.php'; ?> 