<?php

/**
 * Admin Login Page
 * College Student Portal
 * 
 * Form, CSRF, validation, lockout check
 */

// Include required files
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/database.php';

// Configure and start session
configureSession();
session_start();

// Set security headers
setSecurityHeaders();

// Redirect if already logged in as admin
if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin') {
  header('Location: admin/dashboard.php');
  exit;
}

$error = '';
$adminIdValue = '';

// Process login form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Validate CSRF token
  if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
    $error = 'Invalid request. Please refresh the page and try again.';
  } else {
    $adminId = trim($_POST['admin_id'] ?? '');
    $password = $_POST['password'] ?? '';
    $adminIdValue = $adminId;

    // Basic validation
    if (empty($adminId) || empty($password)) {
      $error = 'Please fill all fields.';
    } else {
      // Check lockout status
      $lockout = trackLoginAttempt($pdo, $adminId, 'admin');

      if ($lockout['locked']) {
        $error = $lockout['message'];
      } else {
        // Attempt login
        try {
          $stmt = $pdo->prepare("
                        SELECT id, admin_id, name, email, password
                        FROM admins
                        WHERE admin_id = :admin_id
                    ");
          $stmt->execute([':admin_id' => $adminId]);
          $admin = $stmt->fetch();

          if ($admin && password_verify($password, $admin['password'])) {
            // Successful login
            clearLoginAttempts($adminId, 'admin');

            // Regenerate session ID for security
            session_regenerate_id(true);

            // Set session variables
            $_SESSION['user_type'] = 'admin';
            $_SESSION['admin_db_id'] = $admin['id'];
            $_SESSION['admin_id'] = $admin['admin_id'];
            $_SESSION['admin_name'] = $admin['name'];
            $_SESSION['admin_email'] = $admin['email'];
            $_SESSION['last_activity'] = time();

            // Redirect to dashboard
            header('Location: admin/dashboard.php');
            exit;
          } else {
            // Failed login
            recordFailedAttempt($adminId, 'admin');
            $error = 'Invalid Admin ID or Password.';
          }
        } catch (PDOException $e) {
          error_log("Admin login error: " . $e->getMessage());
          $error = 'An error occurred. Please try again later.';
        }
      }
    }
  }
}

// Get any flash messages
$flashError = getFlashMessage('error');
if ($flashError) {
  $error = $flashError;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="robots" content="noindex, nofollow">
  <title>Admin Login - College Student Portal</title>

  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">

  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

  <!-- Custom Styles -->
  <link rel="stylesheet" href="assets/css/admin-style.css">

  <style>
    .admin-login-container {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
    }

    .admin-login-card {
      max-width: 400px;
      width: 100%;
      background: #ffffff;
      border-radius: 1rem;
      box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.175);
    }
  </style>
</head>

<body>
  <div class="admin-login-container">
    <div class="admin-login-card p-4">
      <div class="text-center mb-4">
        <i class="bi bi-shield-lock" style="font-size: 4rem; color: #2c3e50;"></i>
        <h2 class="mt-2">Admin Login</h2>
        <p class="text-muted">Enter your credentials to access the admin panel</p>
      </div>

      <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          <?= h($error) ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      <?php endif; ?>

      <form method="POST" action="" class="needs-validation" novalidate>
        <?= csrfInput() ?>

        <div class="mb-3">
          <label for="admin_id" class="form-label">Admin ID</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-person-gear"></i></span>
            <input type="text" class="form-control" id="admin_id" name="admin_id"
              value="<?= h($adminIdValue) ?>"
              placeholder="e.g., ADMIN001" required autofocus>
          </div>
          <div class="invalid-feedback">Please enter your Admin ID.</div>
        </div>

        <div class="mb-3">
          <label for="password" class="form-label">Password</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-lock"></i></span>
            <input type="password" class="form-control" id="password" name="password"
              placeholder="Enter your password" required>
            <button type="button" class="btn btn-outline-secondary password-toggle" tabindex="-1">
              <i class="bi bi-eye"></i>
            </button>
          </div>
          <div class="invalid-feedback">Please enter your password.</div>
        </div>

        <div class="d-grid gap-2 mt-4">
          <button type="submit" class="btn btn-dark btn-lg">
            <i class="bi bi-box-arrow-in-right me-2"></i>Login
          </button>
        </div>
      </form>

      <hr class="my-4">

      <div class="text-center">
        <a href="index.php" class="text-decoration-none">
          <i class="bi bi-arrow-left me-1"></i>Back to Home
        </a>
      </div>
    </div>
  </div>

  <!-- Bootstrap 5 JS Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>

  <script>
    // Password toggle
    document.querySelector('.password-toggle').addEventListener('click', function() {
      const input = document.getElementById('password');
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

    // Form validation
    (function() {
      'use strict';
      const forms = document.querySelectorAll('.needs-validation');
      Array.from(forms).forEach(function(form) {
        form.addEventListener('submit', function(event) {
          if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
          }
          form.classList.add('was-validated');
        }, false);
      });
    })();
  </script>
</body>

</html>