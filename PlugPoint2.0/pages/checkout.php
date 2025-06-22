<?php
include '../includes/header_pages.php';
require_once '../includes/db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'buyer') {
    header('Location: login.php');
    exit();
}
$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
if (!$cart) {
    echo '<div class="container py-5"><div class="alert alert-info">Your cart is empty. <a href="products.php">Shop now</a>.</div></div>';
    include '../includes/footer.php';
    exit();
}
$products = [];
$total = 0;
$ids = implode(',', array_map('intval', $cart));
$result = $conn->query("SELECT * FROM products WHERE id IN ($ids)");
while ($row = $result->fetch_assoc()) {
    $products[$row['id']] = $row;
    $total += $row['price'];
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $buyer_id = $_SESSION['user_id'];
    $email = $conn->query("SELECT email FROM users WHERE id = $buyer_id")->fetch_assoc()['email'];
    $paystack_secret = 'sk_test_2b93a799c6998dd2977e6d23920241ed3c4a7932'; // Your test secret key
    $amount = intval($total * 100); // Paystack expects amount in kobo
    $callback_url = 'http://' . $_SERVER['HTTP_HOST'] . '/pages/checkout_callback.php';
    $fields = [
        'email' => $email,
        'amount' => $amount,
        'callback_url' => $callback_url,
        'metadata' => [
            'buyer_id' => $buyer_id,
            'cart' => $cart
        ]
    ];
    $ch = curl_init('https://api.paystack.co/transaction/initialize');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $paystack_secret,
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    $res = json_decode($result, true);
    curl_close($ch);
    if (isset($res['status']) && $res['status'] && isset($res['data']['authorization_url'])) {
        header('Location: ' . $res['data']['authorization_url']);
        exit();
    } else {
        echo '<div class="container py-5"><div class="alert alert-danger">Could not initialize payment. Please try again.</div></div>';
        include '../includes/footer.php';
        exit();
    }
}
?>
<div class="container py-5">
  <h2 class="mb-4" style="color: var(--primary-blue);">Checkout</h2>
  <form method="POST" action="checkout.php">
    <table class="table table-dark table-striped">
      <thead>
        <tr>
          <th>Product</th>
          <th>Price</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($cart as $pid):
          if (!isset($products[$pid])) continue;
          $p = $products[$pid]; ?>
        <tr>
          <td><?= htmlspecialchars($p['name']) ?></td>
          <td>R<?= htmlspecialchars($p['price']) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
      <tfoot>
        <tr>
          <th>Total</th>
          <th>R<?= $total ?></th>
        </tr>
      </tfoot>
    </table>
    <div class="text-end">
      <button type="submit" class="btn btn-primary btn-lg">Confirm & Pay</button>
    </div>
  </form>
</div>
<?php include '../includes/footer.php'; ?> 