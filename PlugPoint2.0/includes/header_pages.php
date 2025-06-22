<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PlugPoint2.0</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="../assets/css/style.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <script src="../assets/js/main.js" defer></script>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="index.php">PlugPoint</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item">
          <a class="nav-link" href="products.php" title="Marketplace"><i class="bi bi-shop"></i></a>
        </li>
        <?php if(isset($_SESSION['user_id'])): ?>
          <?php
            require_once '../includes/db.php';
            $uid = $_SESSION['user_id'];
            $notifs = $conn->query("SELECT * FROM notifications WHERE user_id = $uid ORDER BY is_read ASC, created_at DESC LIMIT 8");
            $unread_count = $conn->query("SELECT COUNT(*) as c FROM notifications WHERE user_id = $uid AND is_read = 0")->fetch_assoc()['c'];
            if (isset($_GET['readnotifs'])) {
              $conn->query("UPDATE notifications SET is_read = 1 WHERE user_id = $uid");
              header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
              exit();
            }
          ?>
          <li class="nav-item dropdown">
            <a class="nav-link position-relative" href="#" id="notifDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false" onclick="window.location='?readnotifs=1'">
              <i class="bi bi-bell"></i>
              <?php if ($unread_count > 0): ?>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                  <?= $unread_count ?>
                </span>
              <?php endif; ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notifDropdown" style="min-width:320px;">
              <li class="dropdown-header fw-bold">Notifications</li>
              <?php if ($notifs && $notifs->num_rows > 0): ?>
                <?php while ($n = $notifs->fetch_assoc()): ?>
                  <li>
                    <a class="dropdown-item small<?= $n['is_read'] ? '' : ' fw-bold' ?>" href="<?= htmlspecialchars($n['link']) ?>">
                      <?= htmlspecialchars($n['content']) ?>
                      <br><span class="text-muted small"><?= date('Y-m-d H:i', strtotime($n['created_at'])) ?></span>
                    </a>
                  </li>
                <?php endwhile; ?>
              <?php else: ?>
                <li><span class="dropdown-item text-muted small">No notifications</span></li>
              <?php endif; ?>
            </ul>
          </li>
          <?php if($_SESSION['role'] === 'buyer'): ?>
            <li class="nav-item">
              <a class="nav-link" href="wishlist_view.php" title="Wishlist"><i class="bi bi-heart"></i></a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="cart_view.php" title="Cart"><i class="bi bi-cart"></i></a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="orders.php" title="Orders"><i class="bi bi-box"></i></a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="dashboard.php" title="Dashboard"><i class="bi bi-speedometer2"></i></a>
            </li>
          <?php elseif($_SESSION['role'] === 'seller'): ?>
            <li class="nav-item">
              <a class="nav-link" href="seller_dashboard.php" title="Dashboard"><i class="bi bi-speedometer2"></i></a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="add_product.php" title="Add Product"><i class="bi bi-plus-square"></i></a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="subscription.php" title="Subscription"><i class="bi bi-credit-card"></i></a>
            </li>
          <?php elseif($_SESSION['role'] === 'admin'): ?>
            <li class="nav-item">
              <a class="nav-link" href="admin_dashboard.php" title="Admin Dashboard"><i class="bi bi-shield"></i></a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="admin_users.php" title="Manage Users"><i class="bi bi-people"></i></a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="admin_products.php" title="Manage Products"><i class="bi bi-box"></i></a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="admin_orders.php" title="View Orders"><i class="bi bi-cart"></i></a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="admin_reports.php" title="Reports"><i class="bi bi-graph-up"></i></a>
            </li>
          <?php endif; ?>
          <li class="nav-item">
            <a class="nav-link" href="profile.php" title="Profile"><i class="bi bi-person"></i></a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="logout.php" title="Logout"><i class="bi bi-box-arrow-right"></i></a>
          </li>
        <?php else: ?>
          <li class="nav-item">
            <a class="nav-link" href="login.php" title="Login"><i class="bi bi-box-arrow-in-right"></i></a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="register.php" title="Register"><i class="bi bi-person-plus"></i></a>
          </li>
        <?php endif; ?>
        <!-- Dark mode toggle - available to all users -->
        <li class="nav-item d-flex align-items-center">
          <button id="darkModeToggle" class="btn btn-primary ms-2" style="min-width:40px; font-size:1.2rem;" title="Toggle dark mode">🌙</button>
        </li>
      </ul>
    </div>
  </div>
</nav>
<!-- Page content starts here --> 