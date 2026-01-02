<?php

/**
 * Student Navigation Include
 * College Student Portal
 * 
 * Links to dashboard, grades, schedule, profile
 */

// Get current page for active state
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<nav class="navbar navbar-expand-lg navbar-student">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center" href="dashboard.php">
      <i class="bi bi-mortarboard-fill me-2"></i>
      Student Portal
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#studentNav" aria-controls="studentNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="studentNav">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link <?= $currentPage === 'dashboard.php' ? 'active' : '' ?>" href="dashboard.php">
            <i class="bi bi-speedometer2 me-1"></i>Dashboard
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= $currentPage === 'grades.php' ? 'active' : '' ?>" href="grades.php">
            <i class="bi bi-award me-1"></i>Grades
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= $currentPage === 'schedule.php' ? 'active' : '' ?>" href="schedule.php">
            <i class="bi bi-calendar-week me-1"></i>Schedule
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= ($currentPage === 'profile.php' || $currentPage === 'edit-profile.php' || $currentPage === 'change-password.php') ? 'active' : '' ?>" href="profile.php">
            <i class="bi bi-person-circle me-1"></i>Profile
          </a>
        </li>
      </ul>

      <ul class="navbar-nav">
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <img src="../uploads/profile_pictures/<?= h($profilePic) ?>"
              alt="Profile"
              class="profile-picture-sm me-2"
              onerror="this.src='../assets/images/default-avatar.png'">
            <?= h($studentName) ?>
          </a>
          <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
            <li><span class="dropdown-item-text text-muted small"><?= h($studentId) ?></span></li>
            <li>
              <hr class="dropdown-divider">
            </li>
            <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person me-2"></i>My Profile</a></li>
            <li><a class="dropdown-item" href="change-password.php"><i class="bi bi-key me-2"></i>Change Password</a></li>
            <li>
              <hr class="dropdown-divider">
            </li>
            <li><a class="dropdown-item text-danger" href="../logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
          </ul>
        </li>
      </ul>
    </div>
  </div>
</nav>