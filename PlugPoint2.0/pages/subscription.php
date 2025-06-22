<?php
include '../includes/header.php';
require_once '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header('Location: login.php');
    exit();
}

$seller_id = $_SESSION['user_id'];
$sub = $conn->query("SELECT * FROM subscriptions WHERE seller_id = $seller_id AND status = 'active' ORDER BY end_date DESC LIMIT 1");
$current = $sub && $sub->num_rows > 0 ? $sub->fetch_assoc() : null;
?>
<div class="container py-5">
  <h2 class="mb-4" style="color: var(--primary-purple);">Manage Subscription</h2>
  <?php if ($current): ?>
    <div class="alert alert-success">Active Plan: <strong><?= $current['plan'] ?></strong> (Expires: <?= $current['end_date'] ?>)</div>
  <?php else: ?>
    <div class="alert alert-warning">You do not have an active subscription. Please choose a plan to start selling.</div>
  <?php endif; ?>
  <div class="row">
    <div class="col-md-4">
      <div class="card p-3 text-center">
        <h4>Basic</h4>
        <p>R299 / month</p>
        <form method="POST" action="../api/paystack.php">
          <input type="hidden" name="plan" value="Basic">
          <button type="submit" class="btn btn-primary w-100">Subscribe</button>
        </form>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card p-3 text-center">
        <h4>Pro</h4>
        <p>R599 / month</p>
        <form method="POST" action="../api/paystack.php">
          <input type="hidden" name="plan" value="Pro">
          <button type="submit" class="btn btn-primary w-100">Subscribe</button>
        </form>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card p-3 text-center">
        <h4>Enterprise</h4>
        <p>R999 / month</p>
        <form method="POST" action="../api/paystack.php">
          <input type="hidden" name="plan" value="Enterprise">
          <button type="submit" class="btn btn-primary w-100">Subscribe</button>
        </form>
      </div>
    </div>
  </div>
</div>
<?php include '../includes/footer.php'; ?> 