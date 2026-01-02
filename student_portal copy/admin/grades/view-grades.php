<?php

/**
 * View Grades Page
 * Admin Module
 * 
 * List all grades with DataTables and filters
 */

$pageTitle = 'Manage Grades';
require_once __DIR__ . '/../../includes/admin-header.php';
require_once __DIR__ . '/../../config/database.php';

// Fetch students for filter
try {
  $studentsStmt = $pdo->query("SELECT id, student_id, name FROM students ORDER BY name");
  $students = $studentsStmt->fetchAll();
} catch (PDOException $e) {
  $students = [];
}

// Fetch courses for filter
try {
  $coursesStmt = $pdo->query("SELECT id, course_code, course_name FROM courses ORDER BY course_code");
  $courses = $coursesStmt->fetchAll();
} catch (PDOException $e) {
  $courses = [];
}

// Fetch all grades
try {
  $stmt = $pdo->query("
        SELECT g.id, g.grade, g.semester, g.academic_year, g.created_at,
               s.student_id, s.name as student_name,
               c.course_code, c.course_name
        FROM grades g
        JOIN students s ON g.student_id = s.id
        JOIN courses c ON g.course_id = c.id
        ORDER BY g.created_at DESC
    ");
  $grades = $stmt->fetchAll();
} catch (PDOException $e) {
  error_log("Error fetching grades: " . $e->getMessage());
  $grades = [];
}
?>

<div class="row mb-4">
  <div class="col-12 d-flex justify-content-between align-items-center flex-wrap">
    <div>
      <h1 class="mb-2"><i class="bi bi-card-checklist me-2"></i>Manage Grades</h1>
      <p class="text-muted mb-0"><?= count($grades) ?> grade record<?= count($grades) !== 1 ? 's' : '' ?></p>
    </div>
    <a href="add-grade.php" class="btn btn-primary">
      <i class="bi bi-plus-lg me-1"></i>Add Grade
    </a>
  </div>
</div>

<!-- Filters -->
<div class="card mb-4">
  <div class="card-body">
    <div class="row g-3">
      <div class="col-md-4">
        <label for="student-filter" class="form-label">Filter by Student</label>
        <select class="form-select" id="student-filter">
          <option value="">All Students</option>
          <?php foreach ($students as $s): ?>
            <option value="<?= h($s['student_id']) ?>"><?= h($s['student_id']) ?> - <?= h($s['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-4">
        <label for="course-filter" class="form-label">Filter by Course</label>
        <select class="form-select" id="course-filter">
          <option value="">All Courses</option>
          <?php foreach ($courses as $c): ?>
            <option value="<?= h($c['course_code']) ?>"><?= h($c['course_code']) ?> - <?= h($c['course_name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-4 d-flex align-items-end">
        <button type="button" class="btn btn-outline-secondary w-100" id="clear-filters">
          <i class="bi bi-x-circle me-1"></i>Clear Filters
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Grades Table -->
<div class="card">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table id="grades-table" class="table table-admin table-hover mb-0" style="width:100%">
        <thead>
          <tr>
            <th>Student ID</th>
            <th>Student Name</th>
            <th>Course Code</th>
            <th>Course Name</th>
            <th>Grade</th>
            <th>Semester</th>
            <th>Year</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($grades as $grade): ?>
            <tr>
              <td><?= h($grade['student_id']) ?></td>
              <td><strong><?= h($grade['student_name']) ?></strong></td>
              <td><?= h($grade['course_code']) ?></td>
              <td><?= h($grade['course_name']) ?></td>
              <td><span class="badge <?= getGradeClass($grade['grade']) ?>"><?= h($grade['grade']) ?></span></td>
              <td><?= h($grade['semester']) ?></td>
              <td><?= h($grade['academic_year']) ?></td>
              <td>
                <div class="btn-group">
                  <a href="edit-grade.php?id=<?= $grade['id'] ?>" class="btn btn-sm btn-outline-primary" title="Edit">
                    <i class="bi bi-pencil"></i>
                  </a>
                  <a href="delete-grade.php?id=<?= $grade['id'] ?>" class="btn btn-sm btn-outline-danger" title="Delete">
                    <i class="bi bi-trash"></i>
                  </a>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    var table = $('#grades-table').DataTable({
      responsive: true,
      order: [
        [0, 'asc']
      ],
      searchDelay: 500,
      columnDefs: [{
        orderable: false,
        targets: [7]
      }],
      language: {
        search: "",
        searchPlaceholder: "Search grades...",
        emptyTable: "No grades found"
      }
    });

    document.getElementById('student-filter').addEventListener('change', function() {
      table.column(0).search(this.value).draw();
    });

    document.getElementById('course-filter').addEventListener('change', function() {
      table.column(2).search(this.value).draw();
    });

    document.getElementById('clear-filters').addEventListener('click', function() {
      document.getElementById('student-filter').value = '';
      document.getElementById('course-filter').value = '';
      table.search('').columns().search('').draw();
    });
  });
</script>

<?php require_once __DIR__ . '/../../includes/admin-footer.php'; ?>