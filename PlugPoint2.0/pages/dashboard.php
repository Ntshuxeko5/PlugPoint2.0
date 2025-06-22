<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
  header('Location: login.php');
  exit();
}
if ($_SESSION['role'] === 'seller') {
  header('Location: seller_dashboard.php');
  exit();
} elseif ($_SESSION['role'] === 'admin') {
  header('Location: admin_dashboard.php');
  exit();
} else {
  header('Location: buyer_dashboard.php');
  exit();
} 