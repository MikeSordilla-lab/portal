<?php

/**
 * Student Grades Page
 * College Student Portal
 * 
 * View all grades grouped by semester with GPA calculations
 */

$pageTitle = 'My Grades';
require_once __DIR__ . '/../includes/student-header.php';
require_once __DIR__ . '/../config/database.php';

// Get student's database ID
$studentDbId = $_SESSION['student_db_id'];

// Fetch all grades grouped by semester
try {
  $stmt = $pdo->prepare("
        SELECT g.semester, g.semester_year, g.grade, 
               c.course_code, c.course_name, c.credits,
               d.department_name
        FROM grades g
        JOIN courses c ON g.course_id = c.id
        LEFT JOIN departments d ON c.department_id = d.id
        WHERE g.student_id = :student_id
        ORDER BY g.semester ASC, c.course_code ASC
    ");
  $stmt->execute([':student_id' => $studentDbId]);
  $allGrades = $stmt->fetchAll();

  // Group grades by semester
  $gradesBySemester = [];
  foreach ($allGrades as $grade) {
    $semKey = $grade['semester'];
    if (!isset($gradesBySemester[$semKey])) {
      $gradesBySemester[$semKey] = [
        'semester' => $grade['semester'],
        'semester_year' => $grade['semester_year'],
        'grades' => []
      ];
    }
    $gradesBySemester[$semKey]['grades'][] = $grade;
  }

  // Calculate cumulative GPA
  $cumulativeGPA = calculateGPA($allGrades);
} catch (PDOException $e) {
  error_log("Grades error: " . $e->getMessage());
  $gradesBySemester = [];
  $cumulativeGPA = 0.00;
}

/**
 * Get CSS class for grade badge
 */
function getGradeClass($grade)
{
  $letter = substr($grade, 0, 1);
  switch ($letter) {
    case 'A':
      return 'grade-a';
    case 'B':
      return 'grade-b';
    case 'C':
      return 'grade-c';
    case 'D':
      return 'grade-d';
    case 'F':
      return 'grade-f';
    default:
      return 'grade-c';
  }
}
?>

<div class="row mb-4">
  <div class="col-12">
    <h1 class="mb-3">
      <i class="bi bi-award me-2"></i>My Grades
    </h1>
    <p class="text-muted">View your academic grades and GPA by semester.</p>
  </div>
</div>

<?php if (empty($gradesBySemester)): ?>
  <!-- Empty State -->
  <div class="card">
    <div class="card-body">
      <div class="empty-state">
        <div class="empty-state-icon">
          <i class="bi bi-inbox"></i>
        </div>
        <h4>No Grades Found</h4>
        <p class="text-muted">You don't have any grades recorded yet. Grades will appear here once they are entered by your instructors.</p>
      </div>
    </div>
  </div>
<?php else: ?>
  <!-- Cumulative GPA Card -->
  <div class="row mb-4">
    <div class="col-12">
      <div class="card bg-primary text-white">
        <div class="card-body text-center py-4">
          <h6 class="text-uppercase opacity-75 mb-2">Cumulative GPA</h6>
          <h1 class="display-3 mb-0 fw-bold"><?= number_format($cumulativeGPA, 2) ?></h1>
          <p class="mb-0 opacity-75">
            Across <?= count($gradesBySemester) ?> semester<?= count($gradesBySemester) > 1 ? 's' : '' ?>
            &bull; <?= count($allGrades) ?> course<?= count($allGrades) > 1 ? 's' : '' ?>
          </p>
        </div>
      </div>
    </div>
  </div>

  <!-- Grades by Semester -->
  <?php foreach ($gradesBySemester as $semData):
    $semesterGPA = calculateGPA($semData['grades']);
    $semesterCredits = array_sum(array_column($semData['grades'], 'credits'));
  ?>
    <div class="card mb-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        <span>
          <i class="bi bi-calendar3 me-2"></i>
          Semester <?= h($semData['semester']) ?>
          <?php if ($semData['semester_year']): ?>
            <span class="opacity-75">- <?= h($semData['semester_year']) ?></span>
          <?php endif; ?>
        </span>
        <span class="badge bg-light text-dark">
          GPA: <strong><?= number_format($semesterGPA, 2) ?></strong>
        </span>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-student table-hover mb-0">
            <thead>
              <tr>
                <th>Course Code</th>
                <th>Course Name</th>
                <th class="text-center">Credits</th>
                <th class="text-center">Grade</th>
                <th class="text-center">Grade Points</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($semData['grades'] as $grade):
                $gradePoints = getGradePoints();
                $points = $gradePoints[$grade['grade']] ?? 0;
              ?>
                <tr>
                  <td><code><?= h($grade['course_code']) ?></code></td>
                  <td><?= h($grade['course_name']) ?></td>
                  <td class="text-center"><?= h($grade['credits']) ?></td>
                  <td class="text-center">
                    <span class="grade-badge <?= getGradeClass($grade['grade']) ?>">
                      <?= h($grade['grade']) ?>
                    </span>
                  </td>
                  <td class="text-center"><?= number_format($points, 1) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
            <tfoot>
              <tr class="table-light">
                <td colspan="2" class="text-end"><strong>Semester Totals:</strong></td>
                <td class="text-center"><strong><?= $semesterCredits ?></strong></td>
                <td colspan="2" class="text-center">
                  <strong>Semester GPA: <?= number_format($semesterGPA, 2) ?></strong>
                </td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
    </div>
  <?php endforeach; ?>

  <!-- GPA Scale Reference -->
  <div class="card">
    <div class="card-header card-header-secondary bg-light text-dark">
      <i class="bi bi-info-circle me-2"></i>Grade Point Scale
    </div>
    <div class="card-body">
      <div class="row text-center">
        <div class="col">
          <span class="grade-badge grade-a">A</span>
          <small class="d-block mt-1">4.0</small>
        </div>
        <div class="col">
          <span class="grade-badge grade-a">A-</span>
          <small class="d-block mt-1">3.7</small>
        </div>
        <div class="col">
          <span class="grade-badge grade-b">B+</span>
          <small class="d-block mt-1">3.3</small>
        </div>
        <div class="col">
          <span class="grade-badge grade-b">B</span>
          <small class="d-block mt-1">3.0</small>
        </div>
        <div class="col">
          <span class="grade-badge grade-b">B-</span>
          <small class="d-block mt-1">2.7</small>
        </div>
        <div class="col">
          <span class="grade-badge grade-c">C+</span>
          <small class="d-block mt-1">2.3</small>
        </div>
        <div class="col">
          <span class="grade-badge grade-c">C</span>
          <small class="d-block mt-1">2.0</small>
        </div>
        <div class="col">
          <span class="grade-badge grade-c">C-</span>
          <small class="d-block mt-1">1.7</small>
        </div>
        <div class="col">
          <span class="grade-badge grade-d">D+</span>
          <small class="d-block mt-1">1.3</small>
        </div>
        <div class="col">
          <span class="grade-badge grade-d">D</span>
          <small class="d-block mt-1">1.0</small>
        </div>
        <div class="col">
          <span class="grade-badge grade-f">F</span>
          <small class="d-block mt-1">0.0</small>
        </div>
      </div>
    </div>
  </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/student-footer.php'; ?>