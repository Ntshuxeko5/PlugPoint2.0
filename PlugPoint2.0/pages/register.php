<?php include '../includes/header_pages.php'; ?>
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-md-6">
      <div class="card p-4 shadow-lg border border-primary">
        <h2 class="mb-4 text-center fw-bold" style="color: var(--primary-blue);">Register</h2>
        <form method="POST" action="../includes/auth.php">
          <div class="mb-3">
            <label for="name" class="form-label">Full Name</label>
            <input type="text" class="form-control" id="name" name="name" required>
          </div>
          <div class="mb-3">
            <label for="email" class="form-label">Email address</label>
            <input type="email" class="form-control" id="email" name="email" required>
          </div>
          <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" name="password" required>
          </div>
          <div class="mb-3">
            <label for="role" class="form-label">Register as</label>
            <select class="form-select" id="role" name="role" required>
              <option value="buyer">Buyer</option>
              <option value="seller">Seller</option>
              <option value="admin">Admin</option>
            </select>
          </div>
          <button type="submit" class="btn btn-primary w-100">Register</button>
        </form>
        <div class="mt-3 text-center">
          <a href="login.php" class="text-decoration-none" style="color: var(--primary-blue);">Already have an account? Login</a>
        </div>
      </div>
    </div>
  </div>
</div>
<?php include '../includes/footer.php'; ?> 