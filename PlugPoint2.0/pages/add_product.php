<?php
include '../includes/header.php';
require_once '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header('Location: login.php');
    exit();
}

$seller_id = $_SESSION['user_id'];
$sub = $conn->query("SELECT * FROM subscriptions WHERE seller_id = $seller_id AND status = 'active' AND end_date >= CURDATE() LIMIT 1");
if (!$sub || $sub->num_rows === 0) {
    echo '<div class="container py-5"><div class="alert alert-danger">You need an active subscription to list products. <a href="subscription.php">Subscribe now</a>.</div></div>';
    include '../includes/footer.php';
    exit();
}
?>
<div class="container py-5">
  <h2 class="mb-4" style="color: var(--primary-purple);">Add New Product</h2>
  <div class="card p-4">
    <form method="POST" action="add_product.php" enctype="multipart/form-data">
      <div class="mb-3">
        <label for="name" class="form-label">Product Name</label>
        <input type="text" class="form-control" id="name" name="name" required>
      </div>
      <div class="mb-3">
        <label for="description" class="form-label">Description</label>
        <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
      </div>
      <div class="mb-3">
        <label for="price" class="form-label">Price (ZAR)</label>
        <input type="number" class="form-control" id="price" name="price" min="1" step="0.01" required>
      </div>
      <div class="mb-3">
        <label for="category" class="form-label">Category</label>
        <select class="form-control" id="category" name="category" required>
          <option value="">Select Category</option>
          <option value="Electronics">Electronics</option>
          <option value="Fashion">Fashion</option>
          <option value="Home & Garden">Home & Garden</option>
          <option value="Sports & Outdoors">Sports & Outdoors</option>
          <option value="Health & Beauty">Health & Beauty</option>
          <option value="Toys & Games">Toys & Games</option>
          <option value="Automotive">Automotive</option>
          <option value="Books & Media">Books & Media</option>
          <option value="Collectibles">Collectibles</option>
          <option value="Other">Other</option>
        </select>
      </div>
      <div class="mb-3">
        <label for="image" class="form-label">Product Image</label>
        <input type="file" class="form-control" id="image" name="image" accept="image/*" required>
      </div>
      <button type="submit" class="btn btn-primary">Add Product</button>
    </form>
  </div>
</div>
<?php
// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $conn->real_escape_string($_POST['name']);
    $desc = $conn->real_escape_string($_POST['description']);
    $price = floatval($_POST['price']);
    $category = $conn->real_escape_string($_POST['category']);
    $img = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $target_dir = '../assets/images/';
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        $img_name = uniqid('prod_') . '_' . basename($_FILES['image']['name']);
        $target_file = $target_dir . $img_name;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            $img = $img_name;
        }
    }
    if ($img) {
        $conn->query("INSERT INTO products (seller_id, name, description, price, image, category, status) VALUES ($seller_id, '$name', '$desc', $price, '$img', '$category', 'active')");
        echo '<div class="container py-3"><div class="alert alert-success">Product added successfully!</div></div>';
    } else {
        echo '<div class="container py-3"><div class="alert alert-danger">Image upload failed.</div></div>';
    }
}
include '../includes/footer.php'; 