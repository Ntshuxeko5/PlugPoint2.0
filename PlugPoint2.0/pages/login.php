<?php include '../includes/header_pages.php'; ?>
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-md-5">
      <div class="card p-4 shadow-lg border border-primary">
        <h2 class="mb-4 text-center fw-bold" style="color: var(--primary-purple);">Login</h2>
        <form method="POST" action="../includes/auth.php">
          <div class="mb-3">
            <label for="email" class="form-label">Email address</label>
            <input type="email" class="form-control" id="email" name="email" required>
          </div>
          <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" name="password" required>
          </div>
          <button type="submit" class="btn btn-primary w-100">Login</button>
        </form>
        <div class="mt-3 text-center">
          <a href="register.php" class="text-decoration-none" style="color: var(--primary-blue);">Don't have an account? Register</a>
        </div>
      </div>
    </div>
  </div>
</div>
<?php include '../includes/footer.php'; ?> 