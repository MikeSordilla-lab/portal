<?php

/**
 * Change Password Page
 * College Student Portal
 * 
 * Verify current password and set new password
 */

$pageTitle = 'Change Password';
require_once __DIR__ . '/../includes/student-header.php';
require_once __DIR__ . '/../config/database.php';

// Get student's database ID
$studentDbId = $_SESSION['student_db_id'];

$errors = [];

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Validate CSRF token
  if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
    $errors[] = 'Invalid request. Please refresh the page and try again.';
  } else {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // Validation
    if (empty($currentPassword)) {
      $errors[] = 'Current password is required.';
    }

    if (empty($newPassword)) {
      $errors[] = 'New password is required.';
    } elseif (!validatePassword($newPassword)) {
      $errors[] = 'New password must be at least 8 characters.';
    }

    if ($newPassword !== $confirmPassword) {
      $errors[] = 'Passwords do not match.';
    }

    // Verify current password
    if (empty($errors)) {
      try {
        $stmt = $pdo->prepare("SELECT password FROM students WHERE id = :id");
        $stmt->execute([':id' => $studentDbId]);
        $student = $stmt->fetch();

        if (!$student || !password_verify($currentPassword, $student['password'])) {
          $errors[] = 'Current password is incorrect.';
        }
      } catch (PDOException $e) {
        error_log("Password check error: " . $e->getMessage());
        $errors[] = 'An error occurred. Please try again.';
      }
    }

    // Update password if no errors
    if (empty($errors)) {
      try {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("UPDATE students SET password = :password WHERE id = :id");
        $stmt->execute([
          ':password' => $hashedPassword,
          ':id' => $studentDbId
        ]);

        // Regenerate session ID for security
        session_regenerate_id(true);

        $_SESSION['success'] = 'Password changed successfully.';
        header('Location: profile.php');
        exit;
      } catch (PDOException $e) {
        error_log("Password update error: " . $e->getMessage());
        $errors[] = 'An error occurred while changing your password.';
      }
    }
  }
}
?>

<div class="row mb-4">
  <div class="col-12">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="profile.php">Profile</a></li>
        <li class="breadcrumb-item active" aria-current="page">Change Password</li>
      </ol>
    </nav>
    <h1 class="mb-3">
      <i class="bi bi-key me-2"></i>Change Password
    </h1>
    <p class="text-muted">Update your account password.</p>
  </div>
</div>

<?php if (!empty($errors)): ?>
  <div class="alert alert-danger">
    <ul class="mb-0">
      <?php foreach ($errors as $error): ?>
        <li><?= h($error) ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<div class="row">
  <div class="col-lg-6">
    <div class="card">
      <div class="card-body">
        <form method="POST" action="" class="needs-validation" novalidate>
          <?= csrfInput() ?>

          <!-- Current Password -->
          <div class="mb-3">
            <label for="current_password" class="form-label required-field">Current Password</label>
            <div class="input-group">
              <input type="password" class="form-control" id="current_password"
                name="current_password" required>
              <button type="button" class="btn btn-outline-secondary password-toggle">
                <i class="bi bi-eye"></i>
              </button>
            </div>
            <div class="invalid-feedback">Please enter your current password.</div>
          </div>

          <hr>

          <!-- New Password -->
          <div class="mb-3">
            <label for="new_password" class="form-label required-field">New Password</label>
            <div class="input-group">
              <input type="password" class="form-control" id="new_password"
                name="new_password" minlength="8" required>
              <button type="button" class="btn btn-outline-secondary password-toggle">
                <i class="bi bi-eye"></i>
              </button>
            </div>
            <div class="form-text">Minimum 8 characters</div>
            <div class="invalid-feedback">Password must be at least 8 characters.</div>
          </div>

          <!-- Confirm Password -->
          <div class="mb-3">
            <label for="confirm_password" class="form-label required-field">Confirm New Password</label>
            <div class="input-group">
              <input type="password" class="form-control" id="confirm_password"
                name="confirm_password" minlength="8" required>
              <button type="button" class="btn btn-outline-secondary password-toggle">
                <i class="bi bi-eye"></i>
              </button>
            </div>
            <div class="invalid-feedback">Please confirm your new password.</div>
          </div>

          <hr>

          <div class="d-flex justify-content-between">
            <a href="profile.php" class="btn btn-outline-secondary">
              <i class="bi bi-arrow-left me-1"></i>Cancel
            </a>
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-check-lg me-1"></i>Change Password
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="col-lg-6">
    <div class="card">
      <div class="card-header">
        <i class="bi bi-shield-check me-2"></i>Password Tips
      </div>
      <div class="card-body">
        <ul class="mb-0">
          <li>Use at least 8 characters</li>
          <li>Consider mixing letters, numbers, and symbols</li>
          <li>Avoid using common words or personal information</li>
          <li>Don't reuse passwords from other accounts</li>
          <li>Store your password securely</li>
        </ul>
      </div>
    </div>
  </div>
</div>

<script>
  // Password toggle functionality
  document.querySelectorAll('.password-toggle').forEach(function(btn) {
    btn.addEventListener('click', function() {
      const input = this.previousElementSibling;
      const icon = this.querySelector('i');

      if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
      } else {
        input.type = 'password';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
      }
    });
  });

  // Password match validation
  document.getElementById('confirm_password').addEventListener('input', function() {
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = this.value;

    if (confirmPassword && newPassword !== confirmPassword) {
      this.setCustomValidity('Passwords do not match');
    } else {
      this.setCustomValidity('');
    }
  });
</script>

<?php require_once __DIR__ . '/../includes/student-footer.php'; ?>