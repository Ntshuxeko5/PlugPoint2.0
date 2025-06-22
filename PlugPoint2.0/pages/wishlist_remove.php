<?php
session_start();
require_once '../includes/db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'buyer') {
    header('Location: login.php');
    exit();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['wishlist_id'])) {
    $wishlist_id = intval($_POST['wishlist_id']);
    $conn->query("DELETE FROM wishlists WHERE id = $wishlist_id AND buyer_id = " . $_SESSION['user_id']);
}
header('Location: wishlist_view.php');
exit(); 