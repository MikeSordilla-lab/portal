<?php

/**
 * Admin Dashboard
 * College Student Portal
 * 
 * Summary stats and quick links to all management sections
 */

$pageTitle = 'Dashboard';
$cssPath = '../assets/css/admin-style.css';
$jsPath = '../assets/js/admin-script.js';
require_once __DIR__ . '/../includes/admin-header.php';
require_once __DIR__ . '/../config/database.php';

// Fetch dashboard statistics
try {
  // Total students
  $stmt = $pdo->query("SELECT COUNT(*) as count FROM students");
  $totalStudents = $stmt->fetch()['count'];

  // Total courses
  $stmt = $pdo->query("SELECT COUNT(*) as count FROM courses");
  $totalCourses = $stmt->fetch()['count'];

  // Total departments
  $stmt = $pdo->query("SELECT COUNT(*) as count FROM departments");
  $totalDepartments = $stmt->fetch()['count'];

  // Total grades recorded
  $stmt = $pdo->query("SELECT COUNT(*) as count FROM grades");
  $totalGrades = $stmt->fetch()['count'];

  // Recent students (last 5)
  $stmt = $pdo->query("
        SELECT s.student_id, s.name, d.department_name, s.created_at
        FROM students s
        LEFT JOIN departments d ON s.department_id = d.id
        ORDER BY s.created_at DESC
        LIMIT 5
    ");
  $recentStudents = $stmt->fetchAll();
} catch (PDOException $e) {
  error_log("Dashboard error: " . $e->getMessage());
  $totalStudents = 0;
  $totalCourses = 0;
  $totalDepartments = 0;
  $totalGrades = 0;
  $recentStudents = [];
}
?>

<h1 class="mb-4">
  <i class="bi bi-speedometer2 me-2"></i>Admin Dashboard
</h1>

<!-- Statistics Cards -->
<div class="row g-4 mb-4">
  <div class="col-sm-6 col-xl-3">
    <div class="card stat-card h-100">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <p class="stat-label mb-1">Total Students</p>
            <h2 class="stat-value mb-0"><?= number_format($totalStudents) ?></h2>
          </div>
          <div class="stat-icon">
            <i class="bi bi-people"></i>
          </div>
        </div>
      </div>
      <div class="card-footer bg-transparent">
        <a href="students/view-students.php" class="text-decoration-none small">
          View all <i class="bi bi-arrow-right"></i>
        </a>
      </div>
    </div>
  </div>

  <div class="col-sm-6 col-xl-3">
    <div class="card stat-card stat-success h-100">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <p class="stat-label mb-1">Total Courses</p>
            <h2 class="stat-value mb-0"><?= number_format($totalCourses) ?></h2>
          </div>
          <div class="stat-icon">
            <i class="bi bi-book"></i>
          </div>
        </div>
      </div>
      <div class="card-footer bg-transparent">
        <a href="courses/view-courses.php" class="text-decoration-none small">
          View all <i class="bi bi-arrow-right"></i>
        </a>
      </div>
    </div>
  </div>

  <div class="col-sm-6 col-xl-3">
    <div class="card stat-card stat-warning h-100">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <p class="stat-label mb-1">Departments</p>
            <h2 class="stat-value mb-0"><?= number_format($totalDepartments) ?></h2>
          </div>
          <div class="stat-icon">
            <i class="bi bi-building"></i>
          </div>
        </div>
      </div>
      <div class="card-footer bg-transparent">
        <a href="departments/view-departments.php" class="text-decoration-none small">
          View all <i class="bi bi-arrow-right"></i>
        </a>
      </div>
    </div>
  </div>

  <div class="col-sm-6 col-xl-3">
    <div class="card stat-card stat-info h-100">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <p class="stat-label mb-1">Grades Recorded</p>
            <h2 class="stat-value mb-0"><?= number_format($totalGrades) ?></h2>
          </div>
          <div class="stat-icon">
            <i class="bi bi-award"></i>
          </div>
        </div>
      </div>
      <div class="card-footer bg-transparent">
        <a href="grades/view-grades.php" class="text-decoration-none small">
          View all <i class="bi bi-arrow-right"></i>
        </a>
      </div>
    </div>
  </div>
</div>

<div class="row g-4">
  <!-- Quick Actions -->
  <div class="col-lg-6">
    <div class="card h-100">
      <div class="card-header card-header-secondary">
        <i class="bi bi-lightning me-2"></i>Quick Actions
      </div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-6">
            <a href="students/add-student.php" class="btn btn-outline-primary w-100 py-3">
              <i class="bi bi-person-plus d-block fs-4 mb-1"></i>
              Add Student
            </a>
          </div>
          <div class="col-6">
            <a href="courses/add-course.php" class="btn btn-outline-success w-100 py-3">
              <i class="bi bi-book d-block fs-4 mb-1"></i>
              Add Course
            </a>
          </div>
          <div class="col-6">
            <a href="grades/add-grade.php" class="btn btn-outline-info w-100 py-3">
              <i class="bi bi-award d-block fs-4 mb-1"></i>
              Add Grade
            </a>
          </div>
          <div class="col-6">
            <a href="schedules/add-schedule.php" class="btn btn-outline-warning w-100 py-3">
              <i class="bi bi-calendar-plus d-block fs-4 mb-1"></i>
              Add Schedule
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Recent Students -->
  <div class="col-lg-6">
    <div class="card h-100">
      <div class="card-header card-header-secondary">
        <i class="bi bi-clock-history me-2"></i>Recently Added Students
      </div>
      <div class="card-body p-0">
        <?php if (empty($recentStudents)): ?>
          <div class="text-center text-muted py-4">
            <i class="bi bi-inbox fs-1"></i>
            <p class="mb-0 mt-2">No students added yet.</p>
          </div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-hover mb-0">
              <thead>
                <tr>
                  <th>Student ID</th>
                  <th>Name</th>
                  <th>Department</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($recentStudents as $student): ?>
                  <tr>
                    <td><code><?= h($student['student_id']) ?></code></td>
                    <td><?= h($student['name']) ?></td>
                    <td>
                      <span class="badge bg-secondary">
                        <?= h($student['department_name'] ?? 'N/A') ?>
                      </span>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
      <div class="card-footer bg-transparent text-end">
        <a href="students/view-students.php" class="btn btn-sm btn-outline-primary">
          View All Students <i class="bi bi-arrow-right"></i>
        </a>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/admin-footer.php'; ?>