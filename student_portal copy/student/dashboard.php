<?php

/**
 * Student Dashboard
 * College Student Portal
 * 
 * Welcome page with quick links to grades, schedule, and profile
 */

$pageTitle = 'Dashboard';
require_once __DIR__ . '/../includes/student-header.php';
require_once __DIR__ . '/../config/database.php';

// Get student's database ID
$studentDbId = $_SESSION['student_db_id'];

// Fetch some stats for the dashboard
try {
  // Get total courses with grades
  $stmt = $pdo->prepare("SELECT COUNT(DISTINCT course_id) as total_courses FROM grades WHERE student_id = :id");
  $stmt->execute([':id' => $studentDbId]);
  $totalCourses = $stmt->fetch()['total_courses'];

  // Get current semester schedule count
  $currentSemester = $_SESSION['current_semester'] ?? 1;
  $stmt = $pdo->prepare("SELECT COUNT(DISTINCT course_id) as schedule_count FROM schedule WHERE student_id = :id AND semester = :sem");
  $stmt->execute([':id' => $studentDbId, ':sem' => $currentSemester]);
  $scheduleCount = $stmt->fetch()['schedule_count'];

  // Calculate cumulative GPA
  $stmt = $pdo->prepare("
        SELECT g.grade, c.credits 
        FROM grades g 
        JOIN courses c ON g.course_id = c.id 
        WHERE g.student_id = :id
    ");
  $stmt->execute([':id' => $studentDbId]);
  $grades = $stmt->fetchAll();
  $cumulativeGPA = calculateGPA($grades);
} catch (PDOException $e) {
  error_log("Dashboard error: " . $e->getMessage());
  $totalCourses = 0;
  $scheduleCount = 0;
  $cumulativeGPA = 0.00;
}
?>

<div class="row mb-4">
  <div class="col-12">
    <h1 class="mb-3">
      <i class="bi bi-speedometer2 me-2"></i>Dashboard
    </h1>
    <p class="lead text-muted">
      Welcome back, <strong><?= h($studentName) ?></strong>!
      Here's an overview of your academic information.
    </p>
  </div>
</div>

<!-- Quick Stats -->
<div class="row g-4 mb-4">
  <div class="col-md-4">
    <div class="card dashboard-card h-100 text-center">
      <div class="card-body">
        <div class="card-icon">
          <i class="bi bi-mortarboard"></i>
        </div>
        <h3 class="gpa-display mt-3"><?= number_format($cumulativeGPA, 2) ?></h3>
        <p class="gpa-label mb-0">Cumulative GPA</p>
      </div>
      <div class="card-footer bg-transparent border-0">
        <a href="grades.php" class="btn btn-outline-primary btn-sm">View Grades</a>
      </div>
    </div>
  </div>

  <div class="col-md-4">
    <div class="card dashboard-card h-100 text-center">
      <div class="card-body">
        <div class="card-icon">
          <i class="bi bi-book"></i>
        </div>
        <h3 class="gpa-display mt-3"><?= $totalCourses ?></h3>
        <p class="gpa-label mb-0">Courses Completed</p>
      </div>
      <div class="card-footer bg-transparent border-0">
        <a href="grades.php" class="btn btn-outline-primary btn-sm">View All</a>
      </div>
    </div>
  </div>

  <div class="col-md-4">
    <div class="card dashboard-card h-100 text-center">
      <div class="card-body">
        <div class="card-icon">
          <i class="bi bi-calendar-week"></i>
        </div>
        <h3 class="gpa-display mt-3"><?= $scheduleCount ?></h3>
        <p class="gpa-label mb-0">Current Classes</p>
      </div>
      <div class="card-footer bg-transparent border-0">
        <a href="schedule.php" class="btn btn-outline-primary btn-sm">View Schedule</a>
      </div>
    </div>
  </div>
</div>

<!-- Quick Links -->
<div class="row g-4">
  <div class="col-md-6">
    <div class="card h-100">
      <div class="card-header">
        <i class="bi bi-lightning me-2"></i>Quick Actions
      </div>
      <div class="card-body">
        <div class="list-group list-group-flush">
          <a href="grades.php" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
            <span><i class="bi bi-award me-2 text-primary"></i>View My Grades</span>
            <i class="bi bi-chevron-right"></i>
          </a>
          <a href="schedule.php" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
            <span><i class="bi bi-calendar-week me-2 text-success"></i>View Class Schedule</span>
            <i class="bi bi-chevron-right"></i>
          </a>
          <a href="profile.php" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
            <span><i class="bi bi-person-circle me-2 text-info"></i>View Profile</span>
            <i class="bi bi-chevron-right"></i>
          </a>
          <a href="edit-profile.php" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
            <span><i class="bi bi-pencil-square me-2 text-warning"></i>Edit Profile</span>
            <i class="bi bi-chevron-right"></i>
          </a>
          <a href="change-password.php" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
            <span><i class="bi bi-key me-2 text-danger"></i>Change Password</span>
            <i class="bi bi-chevron-right"></i>
          </a>
        </div>
      </div>
    </div>
  </div>

  <div class="col-md-6">
    <div class="card h-100">
      <div class="card-header">
        <i class="bi bi-info-circle me-2"></i>Student Information
      </div>
      <div class="card-body">
        <table class="table table-borderless mb-0">
          <tbody>
            <tr>
              <td class="text-muted" style="width: 40%;">Student ID</td>
              <td><strong><?= h($_SESSION['student_id']) ?></strong></td>
            </tr>
            <tr>
              <td class="text-muted">Name</td>
              <td><?= h($_SESSION['student_name']) ?></td>
            </tr>
            <tr>
              <td class="text-muted">Email</td>
              <td><?= h($_SESSION['student_email']) ?></td>
            </tr>
            <tr>
              <td class="text-muted">Department</td>
              <td><?= h($_SESSION['department_name'] ?? 'N/A') ?></td>
            </tr>
            <tr>
              <td class="text-muted">Current Semester</td>
              <td>Semester <?= h($_SESSION['current_semester'] ?? 'N/A') ?></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/student-footer.php'; ?>