<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'buyer') {
    header('Location: login.php');
    exit();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $product_id = intval($_POST['product_id']);
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    // Prevent duplicates
    if (!in_array($product_id, $_SESSION['cart'])) {
        $_SESSION['cart'][] = $product_id;
        $msg = 'added';
    } else {
        $msg = 'exists';
    }
    header("Location: product_detail.php?id=$product_id&cart=$msg");
    exit();
}
header('Location: products.php');
exit(); 