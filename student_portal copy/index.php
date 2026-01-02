<?php

/**
 * Main Landing Page
 * College Student Portal
 * 
 * Login selection: Student vs Admin
 */

// Include functions for session and security
require_once __DIR__ . '/includes/functions.php';
configureSession();
session_start();

// Set security headers
setSecurityHeaders();

// Redirect if already logged in
if (isset($_SESSION['user_type'])) {
  if ($_SESSION['user_type'] === 'student') {
    header('Location: student/dashboard.php');
    exit;
  } elseif ($_SESSION['user_type'] === 'admin') {
    header('Location: admin/dashboard.php');
    exit;
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="robots" content="noindex, nofollow">
  <title>College Student Portal - Welcome</title>

  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">

  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

  <!-- Custom Styles -->
  <link rel="stylesheet" href="assets/css/student-style.css">

  <style>
    .welcome-container {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      background: linear-gradient(135deg, #4a6fa5 0%, #6b8cae 100%);
    }

    .welcome-card {
      max-width: 800px;
      width: 100%;
      background: #ffffff;
      border-radius: 1rem;
      box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.175);
      overflow: hidden;
    }

    .welcome-header {
      background-color: #343a40;
      color: #ffffff;
      padding: 2rem;
      text-align: center;
    }

    .welcome-header i {
      font-size: 4rem;
      margin-bottom: 1rem;
    }

    .welcome-body {
      padding: 2rem;
    }

    .login-option {
      display: flex;
      flex-direction: column;
      align-items: center;
      padding: 2rem;
      border-radius: 0.75rem;
      transition: all 0.3s ease;
      text-decoration: none;
      color: inherit;
      border: 2px solid transparent;
    }

    .login-option:hover {
      transform: translateY(-5px);
      border-color: #4a6fa5;
      box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
    }

    .login-option.student-option {
      background-color: #e8f4f8;
    }

    .login-option.admin-option {
      background-color: #f8f4e8;
    }

    .login-option i {
      font-size: 3rem;
      margin-bottom: 1rem;
    }

    .login-option.student-option i {
      color: #4a6fa5;
    }

    .login-option.admin-option i {
      color: #d4a03a;
    }

    .login-option h3 {
      margin-bottom: 0.5rem;
    }

    .login-option p {
      color: #6c757d;
      margin-bottom: 0;
      font-size: 0.875rem;
    }
  </style>
</head>

<body>
  <div class="welcome-container">
    <div class="welcome-card">
      <div class="welcome-header">
        <i class="bi bi-mortarboard-fill"></i>
        <h1 class="mb-2">College Student Portal</h1>
        <p class="mb-0 opacity-75">Welcome to the Student Information System</p>
      </div>

      <div class="welcome-body">
        <h2 class="text-center mb-4">Select Login Type</h2>

        <div class="row g-4">
          <div class="col-md-6">
            <a href="student-login.php" class="login-option student-option">
              <i class="bi bi-person-badge"></i>
              <h3>Student Login</h3>
              <p>Access your grades, schedule, and profile</p>
            </a>
          </div>

          <div class="col-md-6">
            <a href="admin-login.php" class="login-option admin-option">
              <i class="bi bi-shield-lock"></i>
              <h3>Admin Login</h3>
              <p>Manage students, courses, and records</p>
            </a>
          </div>
        </div>

        <hr class="my-4">

        <div class="text-center text-muted small">
          <p class="mb-0">&copy; <?= date('Y') ?> College Student Portal. All rights reserved.</p>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap 5 JS Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
</body>

</html>