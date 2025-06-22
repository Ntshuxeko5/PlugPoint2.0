<?php
session_start();
require_once '../includes/db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'buyer') {
    header('Location: login.php');
    exit();
}
$buyer_id = $_SESSION['user_id'];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $product_id = intval($_POST['product_id']);
    // Prevent duplicates
    $exists = $conn->query("SELECT id FROM wishlists WHERE buyer_id = $buyer_id AND product_id = $product_id");
    if ($exists && $exists->num_rows > 0) {
        header("Location: product_detail.php?id=$product_id&wishlist=exists");
        exit();
    }
    $conn->query("INSERT INTO wishlists (buyer_id, product_id) VALUES ($buyer_id, $product_id)");
    header("Location: product_detail.php?id=$product_id&wishlist=added");
    exit();
}
header('Location: products.php');
exit(); 