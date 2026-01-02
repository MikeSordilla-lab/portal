<?php

/**
 * View Courses Page
 * Admin Module
 * 
 * List all courses with DataTables and filters
 */

$pageTitle = 'Manage Courses';
require_once __DIR__ . '/../../includes/admin-header.php';
require_once __DIR__ . '/../../config/database.php';

// Fetch departments for filter
try {
  $deptStmt = $pdo->query("SELECT id, department_code, department_name FROM departments ORDER BY department_name");
  $departments = $deptStmt->fetchAll();
} catch (PDOException $e) {
  error_log("Error fetching departments: " . $e->getMessage());
  $departments = [];
}

// Fetch all courses with department info
try {
  $stmt = $pdo->query("
        SELECT c.id, c.course_code, c.course_name, c.credits, c.description, c.created_at,
               d.department_code, d.department_name
        FROM courses c
        LEFT JOIN departments d ON c.department_id = d.id
        ORDER BY c.course_code ASC
    ");
  $courses = $stmt->fetchAll();
} catch (PDOException $e) {
  error_log("Error fetching courses: " . $e->getMessage());
  $courses = [];
}
?>

<div class="row mb-4">
  <div class="col-12 d-flex justify-content-between align-items-center flex-wrap">
    <div>
      <h1 class="mb-2">
        <i class="bi bi-book me-2"></i>Manage Courses
      </h1>
      <p class="text-muted mb-0"><?= count($courses) ?> course<?= count($courses) !== 1 ? 's' : '' ?> available</p>
    </div>
    <a href="add-course.php" class="btn btn-primary">
      <i class="bi bi-plus-lg me-1"></i>Add Course
    </a>
  </div>
</div>

<!-- Filters -->
<div class="card mb-4">
  <div class="card-body">
    <div class="row g-3">
      <div class="col-md-6">
        <label for="department-filter" class="form-label">Filter by Department</label>
        <select class="form-select" id="department-filter">
          <option value="">All Departments</option>
          <?php foreach ($departments as $dept): ?>
            <option value="<?= h($dept['department_name']) ?>">
              <?= h($dept['department_code']) ?> - <?= h($dept['department_name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-6 d-flex align-items-end">
        <button type="button" class="btn btn-outline-secondary w-100" id="clear-filters">
          <i class="bi bi-x-circle me-1"></i>Clear Filters
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Courses Table -->
<div class="card">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table id="courses-table" class="table table-admin table-hover mb-0" style="width:100%">
        <thead>
          <tr>
            <th>Course Code</th>
            <th>Course Name</th>
            <th>Department</th>
            <th>Credits</th>
            <th>Description</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($courses as $course): ?>
            <tr>
              <td><strong><?= h($course['course_code']) ?></strong></td>
              <td><?= h($course['course_name']) ?></td>
              <td><?= h($course['department_name'] ?? 'N/A') ?></td>
              <td><?= h($course['credits']) ?></td>
              <td>
                <?php if ($course['description']): ?>
                  <?= h(substr($course['description'], 0, 50)) ?><?= strlen($course['description']) > 50 ? '...' : '' ?>
                <?php else: ?>
                  <span class="text-muted">-</span>
                <?php endif; ?>
              </td>
              <td>
                <div class="btn-group">
                  <a href="edit-course.php?id=<?= $course['id'] ?>"
                    class="btn btn-sm btn-outline-primary" title="Edit">
                    <i class="bi bi-pencil"></i>
                  </a>
                  <a href="delete-course.php?id=<?= $course['id'] ?>"
                    class="btn btn-sm btn-outline-danger" title="Delete">
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
    // Initialize DataTable
    var table = $('#courses-table').DataTable({
      responsive: true,
      order: [
        [0, 'asc']
      ], // Sort by course code
      searchDelay: 500,
      columnDefs: [{
        orderable: false,
        targets: [5]
      }],
      language: {
        search: "",
        searchPlaceholder: "Search courses...",
        emptyTable: "No courses found",
        zeroRecords: "No matching courses found"
      }
    });

    // Department filter
    document.getElementById('department-filter').addEventListener('change', function() {
      table.column(2).search(this.value).draw();
    });

    // Clear filters
    document.getElementById('clear-filters').addEventListener('click', function() {
      document.getElementById('department-filter').value = '';
      table.search('').columns().search('').draw();
    });
  });
</script>

<?php require_once __DIR__ . '/../../includes/admin-footer.php'; ?>