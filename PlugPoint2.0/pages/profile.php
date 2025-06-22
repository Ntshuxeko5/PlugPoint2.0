<?php
include '../includes/header_pages.php';
require_once '../includes/db.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
$user_id = $_SESSION['user_id'];
// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $conn->real_escape_string($_POST['name'] ?? $user['name']);
    $phone = $conn->real_escape_string($_POST['phone'] ?? $user['phone']);
    $address = $conn->real_escape_string($_POST['address'] ?? $user['address']);
    $avatar = '';
    // Handle avatar upload
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === 0) {
        $target_dir = '../assets/images/';
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        $img_name = uniqid('avatar_') . '_' . basename($_FILES['avatar']['name']);
        $target_file = $target_dir . $img_name;
        if (move_uploaded_file($_FILES['avatar']['tmp_name'], $target_file)) {
            $avatar = $img_name;
            $conn->query("UPDATE users SET avatar='$avatar' WHERE id=$user_id");
        }
    }
    $conn->query("UPDATE users SET name='$name', phone='$phone', address='$address' WHERE id=$user_id");
    $_SESSION['name'] = $name;
    echo '<div class="container py-3"><div class="alert alert-success">Profile updated successfully.</div></div>';
}
// Fetch user info
$user = $conn->query("SELECT * FROM users WHERE id=$user_id")->fetch_assoc();
?>
<div class="container py-5">
  <h2 class="mb-4" style="color: var(--primary-blue);">My Profile</h2>
  <div class="row">
    <div class="col-md-4 text-center">
      <div class="mb-3">
        <?php if (!empty($user['avatar'])): ?>
          <img src="../assets/images/<?= htmlspecialchars($user['avatar']) ?>" alt="Avatar" class="rounded-circle" style="width:140px;height:140px;object-fit:cover;">
        <?php else: ?>
          <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center" style="width:140px;height:140px;font-size:3rem;color:#fff;">
            <i class="bi bi-person"></i>
          </div>
        <?php endif; ?>
      </div>
      <form method="POST" enctype="multipart/form-data">
        <div class="mb-3">
          <input type="file" class="form-control" name="avatar" accept="image/*">
        </div>
        <button type="submit" class="btn btn-outline-primary w-100">Upload Avatar</button>
      </form>
    </div>
    <div class="col-md-8">
      <form method="POST" enctype="multipart/form-data">
        <div class="mb-3">
          <label class="form-label">Name</label>
          <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Email</label>
          <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" disabled>
        </div>
        <div class="mb-3">
          <label class="form-label">Phone</label>
          <input type="text" class="form-control" name="phone" value="<?= htmlspecialchars($user['phone']) ?>">
        </div>
        <div class="mb-3">
          <label class="form-label">Address</label>
          <input type="text" class="form-control" name="address" value="<?= htmlspecialchars($user['address']) ?>">
        </div>
        <button type="submit" class="btn btn-primary">Save Changes</button>
      </form>
    </div>
  </div>
</div>
<?php include '../includes/footer.php'; ?> 