<?php

/**
 * Student Profile Page
 * College Student Portal
 * 
 * View personal information and profile picture
 */

$pageTitle = 'My Profile';
require_once __DIR__ . '/../includes/student-header.php';
require_once __DIR__ . '/../config/database.php';

// Get student's database ID
$studentDbId = $_SESSION['student_db_id'];

// Fetch complete student profile
try {
  $stmt = $pdo->prepare("
        SELECT s.*, d.department_name, d.department_code
        FROM students s
        LEFT JOIN departments d ON s.department_id = d.id
        WHERE s.id = :id
    ");
  $stmt->execute([':id' => $studentDbId]);
  $student = $stmt->fetch();

  if (!$student) {
    $_SESSION['error'] = 'Profile not found.';
    header('Location: dashboard.php');
    exit;
  }
} catch (PDOException $e) {
  error_log("Profile error: " . $e->getMessage());
  $_SESSION['error'] = 'Error loading profile.';
  header('Location: dashboard.php');
  exit;
}
?>

<div class="row mb-4">
  <div class="col-12">
    <h1 class="mb-3">
      <i class="bi bi-person-circle me-2"></i>My Profile
    </h1>
  </div>
</div>

<div class="row">
  <!-- Profile Picture and Quick Info -->
  <div class="col-lg-4 mb-4">
    <div class="card text-center">
      <div class="card-body">
        <img src="../uploads/profile_pictures/<?= h($student['profile_picture'] ?: 'default.jpg') ?>"
          alt="Profile Picture"
          class="profile-picture mb-3"
          onerror="this.src='../assets/images/default-avatar.png'">

        <h4 class="mb-1"><?= h($student['name']) ?></h4>
        <p class="text-muted mb-2"><?= h($student['student_id']) ?></p>

        <span class="badge bg-primary"><?= h($student['department_code'] ?? 'N/A') ?></span>
        <span class="badge bg-secondary">Semester <?= h($student['current_semester'] ?? 'N/A') ?></span>
      </div>
      <div class="card-footer bg-transparent">
        <a href="edit-profile.php" class="btn btn-primary btn-sm">
          <i class="bi bi-pencil me-1"></i>Edit Profile
        </a>
        <a href="change-password.php" class="btn btn-outline-secondary btn-sm">
          <i class="bi bi-key me-1"></i>Change Password
        </a>
      </div>
    </div>
  </div>

  <!-- Profile Details -->
  <div class="col-lg-8 mb-4">
    <div class="card">
      <div class="card-header">
        <i class="bi bi-info-circle me-2"></i>Personal Information
      </div>
      <div class="card-body">
        <div class="row g-4">
          <!-- Student ID (Read-only) -->
          <div class="col-md-6">
            <div class="profile-info-label">Student ID</div>
            <div class="fs-5"><?= h($student['student_id']) ?></div>
            <small class="text-muted"><i class="bi bi-lock me-1"></i>Cannot be changed</small>
          </div>

          <!-- Full Name (Read-only) -->
          <div class="col-md-6">
            <div class="profile-info-label">Full Name</div>
            <div class="fs-5"><?= h($student['name']) ?></div>
            <small class="text-muted"><i class="bi bi-lock me-1"></i>Contact admin to change</small>
          </div>

          <!-- Email (Editable) -->
          <div class="col-md-6">
            <div class="profile-info-label">Email Address</div>
            <div class="fs-5"><?= h($student['email']) ?></div>
            <small class="text-success"><i class="bi bi-pencil me-1"></i>Editable</small>
          </div>

          <!-- Phone (Editable) -->
          <div class="col-md-6">
            <div class="profile-info-label">Phone Number</div>
            <div class="fs-5"><?= h($student['phone'] ?: 'Not provided') ?></div>
            <small class="text-success"><i class="bi bi-pencil me-1"></i>Editable</small>
          </div>

          <!-- Date of Birth (Editable) -->
          <div class="col-md-6">
            <div class="profile-info-label">Date of Birth</div>
            <div class="fs-5">
              <?php if ($student['date_of_birth']): ?>
                <?= date('F j, Y', strtotime($student['date_of_birth'])) ?>
              <?php else: ?>
                Not provided
              <?php endif; ?>
            </div>
            <small class="text-success"><i class="bi bi-pencil me-1"></i>Editable</small>
          </div>

          <!-- Gender (Editable) -->
          <div class="col-md-6">
            <div class="profile-info-label">Gender</div>
            <div class="fs-5"><?= h($student['gender'] ?: 'Not provided') ?></div>
            <small class="text-success"><i class="bi bi-pencil me-1"></i>Editable</small>
          </div>

          <!-- Address (Editable) -->
          <div class="col-12">
            <div class="profile-info-label">Address</div>
            <div class="fs-5"><?= h($student['address'] ?: 'Not provided') ?></div>
            <small class="text-success"><i class="bi bi-pencil me-1"></i>Editable</small>
          </div>
        </div>
      </div>
    </div>

    <!-- Academic Information (Read-only) -->
    <div class="card mt-4">
      <div class="card-header">
        <i class="bi bi-mortarboard me-2"></i>Academic Information
      </div>
      <div class="card-body">
        <div class="row g-4">
          <!-- Department (Read-only) -->
          <div class="col-md-6">
            <div class="profile-info-label">Department</div>
            <div class="fs-5"><?= h($student['department_name'] ?? 'Not assigned') ?></div>
            <small class="text-muted"><i class="bi bi-lock me-1"></i>Cannot be changed</small>
          </div>

          <!-- Current Semester (Read-only) -->
          <div class="col-md-6">
            <div class="profile-info-label">Current Semester</div>
            <div class="fs-5">Semester <?= h($student['current_semester'] ?? 'N/A') ?></div>
            <small class="text-muted"><i class="bi bi-lock me-1"></i>Cannot be changed</small>
          </div>

          <!-- Enrollment Date (Read-only) -->
          <div class="col-md-6">
            <div class="profile-info-label">Enrollment Date</div>
            <div class="fs-5">
              <?php if ($student['enrollment_date']): ?>
                <?= date('F j, Y', strtotime($student['enrollment_date'])) ?>
              <?php else: ?>
                Not recorded
              <?php endif; ?>
            </div>
            <small class="text-muted"><i class="bi bi-lock me-1"></i>Cannot be changed</small>
          </div>

          <!-- Account Created -->
          <div class="col-md-6">
            <div class="profile-info-label">Account Created</div>
            <div class="fs-5">
              <?= date('F j, Y', strtotime($student['created_at'])) ?>
            </div>
            <small class="text-muted">System generated</small>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/student-footer.php'; ?>