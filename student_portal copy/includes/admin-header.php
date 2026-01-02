<?php

/**
 * Admin Header Include
 * College Student Portal
 * 
 * Admin session check, security headers, Bootstrap 5 CDN
 */

// Start session with secure settings
require_once __DIR__ . '/functions.php';
configureSession();
session_start();

// Require admin authentication
requireAdminAuth();

// Set security headers
setSecurityHeaders();

// Get admin info for display
$adminName = $_SESSION['admin_name'] ?? 'Administrator';
$adminId = $_SESSION['admin_id'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="robots" content="noindex, nofollow">
  <title><?= h($pageTitle ?? 'Admin Panel') ?> - College Student Portal</title>

  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">

  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

  <!-- DataTables CSS -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

  <!-- Custom Styles -->
  <link rel="stylesheet" href="<?= $cssPath ?? '../assets/css/admin-style.css' ?>">
</head>

<body>
  <div class="admin-wrapper">
    <!-- Sidebar Navigation -->
    <?php include __DIR__ . '/admin-nav.php'; ?>

    <!-- Main Content Area -->
    <div class="admin-content">
      <!-- Top Header -->
      <header class="admin-header">
        <button class="btn btn-link sidebar-toggle" id="sidebar-toggle">
          <i class="bi bi-list fs-4"></i>
        </button>
        <h1 class="admin-header-title"><?= h($pageTitle ?? 'Admin Panel') ?></h1>
        <div class="admin-user-info">
          <span class="text-muted small"><?= h($adminId) ?></span>
          <div class="dropdown">
            <a class="btn btn-link dropdown-toggle text-dark text-decoration-none" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="bi bi-person-circle me-1"></i><?= h($adminName) ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item text-danger" href="../logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
            </ul>
          </div>
        </div>
      </header>

      <!-- Main Content -->
      <main class="admin-main">
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