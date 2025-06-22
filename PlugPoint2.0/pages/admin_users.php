<?php
include '../includes/header_pages.php';
require_once '../includes/db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Handle user actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && isset($_POST['user_id'])) {
        $user_id = intval($_POST['user_id']);
        $action = $_POST['action'];
        
        if ($action === 'update_role' && isset($_POST['role'])) {
            $role = $conn->real_escape_string($_POST['role']);
            $conn->query("UPDATE users SET role = '$role' WHERE id = $user_id");
        } elseif ($action === 'suspend') {
            $conn->query("UPDATE users SET status = 'suspended' WHERE id = $user_id");
        } elseif ($action === 'activate') {
            $conn->query("UPDATE users SET status = 'active' WHERE id = $user_id");
        }
        header('Location: admin_users.php');
        exit();
    }
}

// Search and filtering
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$role_filter = isset($_GET['role']) ? $conn->real_escape_string($_GET['role']) : '';

$where = [];
if ($search) {
    $where[] = "(name LIKE '%$search%' OR email LIKE '%$search%')";
}
if ($role_filter) {
    $where[] = "role = '$role_filter'";
}
$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$users = $conn->query("SELECT * FROM users $where_sql ORDER BY created_at DESC");
?>
<div class="container py-5">
  <h2 class="mb-4" style="color: var(--primary-purple);">User Management</h2>
  
  <!-- Search and Filter -->
  <div class="card mb-4">
    <div class="card-body">
      <form method="GET" class="row g-3">
        <div class="col-md-4">
          <input type="text" class="form-control" name="search" placeholder="Search by name or email..." value="<?= htmlspecialchars($search) ?>">
        </div>
        <div class="col-md-3">
          <select class="form-select" name="role">
            <option value="">All Roles</option>
            <option value="buyer"<?= $role_filter === 'buyer' ? ' selected' : '' ?>>Buyer</option>
            <option value="seller"<?= $role_filter === 'seller' ? ' selected' : '' ?>>Seller</option>
            <option value="admin"<?= $role_filter === 'admin' ? ' selected' : '' ?>>Admin</option>
          </select>
        </div>
        <div class="col-md-2">
          <button type="submit" class="btn btn-primary w-100">Filter</button>
        </div>
        <div class="col-md-2">
          <a href="admin_users.php" class="btn btn-outline-secondary w-100">Clear</a>
        </div>
      </form>
    </div>
  </div>

  <!-- Users Table -->
  <div class="card">
    <div class="card-header">
      <h4>All Users</h4>
    </div>
    <div class="card-body">
      <table class="table table-dark table-striped">
        <thead>
          <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Status</th>
            <th>Joined</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($users && $users->num_rows > 0):
            while ($user = $users->fetch_assoc()): ?>
          <tr>
            <td><?= $user['id'] ?></td>
            <td><?= htmlspecialchars($user['name']) ?></td>
            <td><?= htmlspecialchars($user['email']) ?></td>
            <td>
              <form method="POST" class="d-inline">
                <input type="hidden" name="action" value="update_role">
                <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                <select name="role" class="form-select form-select-sm" onchange="this.form.submit()">
                  <option value="buyer"<?= $user['role'] === 'buyer' ? ' selected' : '' ?>>Buyer</option>
                  <option value="seller"<?= $user['role'] === 'seller' ? ' selected' : '' ?>>Seller</option>
                  <option value="admin"<?= $user['role'] === 'admin' ? ' selected' : '' ?>>Admin</option>
                </select>
              </form>
            </td>
            <td>
              <span class="badge bg-<?= ($user['status'] ?? 'active') === 'active' ? 'success' : 'danger' ?>">
                <?= ucfirst($user['status'] ?? 'active') ?>
              </span>
            </td>
            <td><?= date('Y-m-d', strtotime($user['created_at'])) ?></td>
            <td>
              <?php if (($user['status'] ?? 'active') === 'active'): ?>
                <form method="POST" class="d-inline">
                  <input type="hidden" name="action" value="suspend">
                  <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                  <button type="submit" class="btn btn-warning btn-sm" onclick="return confirm('Suspend this user?')">Suspend</button>
                </form>
              <?php else: ?>
                <form method="POST" class="d-inline">
                  <input type="hidden" name="action" value="activate">
                  <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                  <button type="submit" class="btn btn-success btn-sm">Activate</button>
                </form>
              <?php endif; ?>
            </td>
          </tr>
          <?php endwhile; else: ?>
          <tr><td colspan="7" class="text-center">No users found.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php include '../includes/footer.php'; ?> 