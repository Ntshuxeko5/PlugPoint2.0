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
    $seller_id = intval($metadata['seller_id']);
    $plan = $metadata['plan'];
    $start = date('Y-m-d');
    $end = date('Y-m-d', strtotime('+30 days'));
    // Expire any previous active subscriptions
    $conn->query("UPDATE subscriptions SET status='expired' WHERE seller_id=$seller_id AND status='active'");
    // Activate new subscription
    $conn->query("INSERT INTO subscriptions (seller_id, plan, status, start_date, end_date, paystack_ref) VALUES ($seller_id, '$plan', 'active', '$start', '$end', '$reference')");
    echo '<div class="container py-5"><div class="alert alert-success">Subscription activated successfully! <a href="subscription.php">Return to Subscription</a></div></div>';
    exit();
} else {
    echo '<div class="container py-5"><div class="alert alert-danger">Payment verification failed. <a href="subscription.php">Try again</a></div></div>';
    exit();
} 