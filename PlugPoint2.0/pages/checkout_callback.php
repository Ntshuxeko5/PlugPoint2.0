<?php
session_start();
require_once '../includes/db.php';
$paystack_secret = 'sk_test_2b93a799c6998dd2977e6d23920241ed3c4a7932'; // Your test secret key

if (!isset($_GET['reference'])) {
    echo '<div class="container py-5"><div class="alert alert-danger">No transaction reference supplied.</div></div>';
    exit();
}
$reference = $_GET['reference'];

// Verify transaction with Paystack
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://api.paystack.co/transaction/verify/' . $reference);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $paystack_secret
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($ch);
$res = json_decode($result, true);
curl_close($ch);

if (isset($res['status']) && $res['status'] && isset($res['data']['status']) && $res['data']['status'] === 'success') {
    $metadata = $res['data']['metadata'];
    $buyer_id = intval($metadata['buyer_id']);
    $cart = $metadata['cart'];
    // Fetch product prices again for security
    require_once '../includes/db.php';
    $ids = implode(',', array_map('intval', $cart));
    $products = [];
    $result = $conn->query("SELECT * FROM products WHERE id IN ($ids)");
    while ($row = $result->fetch_assoc()) {
        $products[$row['id']] = $row;
    }
    foreach ($cart as $pid) {
        if (!isset($products[$pid])) continue;
        $p = $products[$pid];
        $conn->query("INSERT INTO orders (buyer_id, product_id, quantity, total, status) VALUES ($buyer_id, {$p['id']}, 1, {$p['price']}, 'paid')");
        // Notify seller
        $seller_id = $p['seller_id'];
        $notif_content = $conn->real_escape_string("New order for {$p['name']}");
        $notif_link = $conn->real_escape_string("/pages/seller_dashboard.php");
        $conn->query("INSERT INTO notifications (user_id, type, content, link) VALUES ($seller_id, 'order', '$notif_content', '$notif_link')");
    }
    unset($_SESSION['cart']);
    header('Location: orders.php');
    exit();
} else {
    echo '<div class="container py-5"><div class="alert alert-danger">Payment verification failed. <a href="cart_view.php">Return to Cart</a></div></div>';
    exit();
} 