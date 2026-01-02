<?php

/**
 * Student Header Include
 * College Student Portal
 * 
 * Session check, security headers, Bootstrap 5 CDN
 */

// Start session with secure settings
require_once __DIR__ . '/functions.php';
configureSession();
session_start();

// Require student authentication
requireStudentAuth();

// Set security headers
setSecurityHeaders();

// Get student info for display
$studentName = $_SESSION['student_name'] ?? 'Student';
$studentId = $_SESSION['student_id'] ?? '';
$profilePic = $_SESSION['profile_picture'] ?? 'default.jpg';
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="robots" content="noindex, nofollow">
  <title><?= h($pageTitle ?? 'Student Portal') ?> - College Student Portal</title>

  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">

  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

  <!-- Custom Styles -->
  <link rel="stylesheet" href="../assets/css/student-style.css">
</head>

<body>
  <!-- Navigation -->
  <?php include __DIR__ . '/student-nav.php'; ?>

  <!-- Main Content Container -->
  <main class="container py-4">
    <!-- Alert Container for Flash Messages -->
    <div id="alert-container">
      <?php
      $successMsg = getFlashMessage('success');
      $errorMsg = getFlashMessage('error');
      $warningMsg = getFlashMessage('warning');

      if ($successMsg) echo displayAlert($successMsg, 'success');
      if ($errorMsg) echo displayAlert($errorMsg, 'error');
      if ($warningMsg) echo displayAlert($warningMsg, 'warning');
      ?>
    </div>