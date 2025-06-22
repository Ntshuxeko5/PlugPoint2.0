<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header('Location: ../pages/login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['plan'])) {
    $plan = $_POST['plan'];
    $seller_id = $_SESSION['user_id'];
    $email = $conn->query("SELECT email FROM users WHERE id = $seller_id")->fetch_assoc()['email'];
    $amounts = ['Basic' => 29900, 'Pro' => 59900, 'Enterprise' => 99900]; // in kobo (ZAR*100)
    $amount = $amounts[$plan] ?? 29900;
    $paystack_secret = 'sk_test_2b93a799c6998dd2977e6d23920241ed3c4a7932'; // Your test secret key

    // Initialize Paystack transaction
    $callback_url = 'http://' . $_SERVER['HTTP_HOST'] . '/pages/paystack_callback.php';
    $fields = [
        'email' => $email,
        'amount' => $amount,
        'callback_url' => $callback_url,
        'metadata' => [
            'seller_id' => $seller_id,
            'plan' => $plan
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
        // Optionally, store a pending subscription here
        header('Location: ' . $res['data']['authorization_url']);
        exit();
    } else {
        header('Location: ../pages/subscription.php?error=paystack');
        exit();
    }
}
header('Location: ../pages/subscription.php?error=1');
exit(); 