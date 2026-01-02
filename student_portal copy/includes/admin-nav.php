<?php

/**
 * Admin Sidebar Navigation
 * College Student Portal
 * 
 * Dashboard + all management sections
 */

// Get current page for active state
$currentPage = basename($_SERVER['PHP_SELF']);
$currentDir = basename(dirname($_SERVER['PHP_SELF']));

// Determine navigation depth for links
$isSubDir = in_array($currentDir, ['students', 'courses', 'grades', 'schedules', 'departments']);
$prefix = $isSubDir ? '../' : '';
?>
<aside class="admin-sidebar">
  <div class="sidebar-header">
    <i class="bi bi-shield-lock-fill fs-1 text-white"></i>
    <h2 class="sidebar-title">Admin Panel</h2>
  </div>

  <nav>
    <ul class="sidebar-nav">
      <!-- Dashboard -->
      <li class="sidebar-nav-item">
        <a class="sidebar-nav-link <?= $currentPage === 'dashboard.php' && $currentDir === 'admin' ? 'active' : '' ?>"
          href="<?= $prefix ?>dashboard.php">
          <i class="bi bi-speedometer2"></i>
          Dashboard
        </a>
      </li>

      <!-- Student Management Section -->
      <li class="sidebar-section">Student Management</li>
      <li class="sidebar-nav-item">
        <a class="sidebar-nav-link <?= $currentDir === 'students' ? 'active' : '' ?>"
          href="<?= $prefix ?>students/view-students.php">
          <i class="bi bi-people"></i>
          Students
        </a>
      </li>

      <!-- Academic Management Section -->
      <li class="sidebar-section">Academic Management</li>
      <li class="sidebar-nav-item">
        <a class="sidebar-nav-link <?= $currentDir === 'courses' ? 'active' : '' ?>"
          href="<?= $prefix ?>courses/view-courses.php">
          <i class="bi bi-book"></i>
          Courses
        </a>
      </li>
      <li class="sidebar-nav-item">
        <a class="sidebar-nav-link <?= $currentDir === 'grades' ? 'active' : '' ?>"
          href="<?= $prefix ?>grades/view-grades.php">
          <i class="bi bi-award"></i>
          Grades
        </a>
      </li>
      <li class="sidebar-nav-item">
        <a class="sidebar-nav-link <?= $currentDir === 'schedules' ? 'active' : '' ?>"
          href="<?= $prefix ?>schedules/view-schedules.php">
          <i class="bi bi-calendar-week"></i>
          Schedules
        </a>
      </li>

      <!-- Administration Section -->
      <li class="sidebar-section">Administration</li>
      <li class="sidebar-nav-item">
        <a class="sidebar-nav-link <?= $currentDir === 'departments' ? 'active' : '' ?>"
          href="<?= $prefix ?>departments/view-departments.php">
          <i class="bi bi-building"></i>
          Departments
        </a>
      </li>

      <!-- Logout -->
      <li class="sidebar-section">Account</li>
      <li class="sidebar-nav-item">
        <a class="sidebar-nav-link" href="<?= $isSubDir ? '../../' : '../' ?>logout.php">
          <i class="bi bi-box-arrow-right"></i>
          Logout
        </a>
      </li>
    </ul>
  </nav>
</aside>