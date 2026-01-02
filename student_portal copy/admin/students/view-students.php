<?php

/**
 * View Students Page
 * Admin Module
 * 
 * List all students with DataTables and filters
 */

$pageTitle = 'Manage Students';
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

// Fetch all students with department info
try {
  $stmt = $pdo->query("
        SELECT s.id, s.student_id, s.name, s.email, s.phone, s.current_semester, 
               s.created_at, s.profile_picture,
               d.department_code, d.department_name
        FROM students s
        LEFT JOIN departments d ON s.department_id = d.id
        ORDER BY s.name ASC
    ");
  $students = $stmt->fetchAll();
} catch (PDOException $e) {
  error_log("Error fetching students: " . $e->getMessage());
  $students = [];
}
?>

<div class="row mb-4">
  <div class="col-12 d-flex justify-content-between align-items-center flex-wrap">
    <div>
      <h1 class="mb-2">
        <i class="bi bi-people me-2"></i>Manage Students
      </h1>
      <p class="text-muted mb-0"><?= count($students) ?> student<?= count($students) !== 1 ? 's' : '' ?> registered</p>
    </div>
    <a href="add-student.php" class="btn btn-primary">
      <i class="bi bi-plus-lg me-1"></i>Add Student
    </a>
  </div>
</div>

<!-- Filters -->
<div class="card mb-4">
  <div class="card-body">
    <div class="row g-3">
      <div class="col-md-4">
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
      <div class="col-md-4">
        <label for="semester-filter" class="form-label">Filter by Semester</label>
        <select class="form-select" id="semester-filter">
          <option value="">All Semesters</option>
          <?php for ($i = 1; $i <= 8; $i++): ?>
            <option value="<?= $i ?>">Semester <?= $i ?></option>
          <?php endfor; ?>
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

<!-- Students Table -->
<div class="card">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table id="students-table" class="table table-admin table-hover mb-0" style="width:100%">
        <thead>
          <tr>
            <th>Photo</th>
            <th>Student ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Department</th>
            <th>Semester</th>
            <th>Phone</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($students as $student): ?>
            <tr>
              <td>
                <img src="../../uploads/profile_pictures/<?= h($student['profile_picture'] ?: 'default.jpg') ?>"
                  alt="<?= h($student['name']) ?>"
                  class="rounded-circle"
                  width="40" height="40"
                  onerror="this.src='../../assets/images/default-avatar.png'">
              </td>
              <td><?= h($student['student_id']) ?></td>
              <td><strong><?= h($student['name']) ?></strong></td>
              <td><?= h($student['email']) ?></td>
              <td><?= h($student['department_name'] ?? 'N/A') ?></td>
              <td><?= h($student['current_semester']) ?></td>
              <td><?= h($student['phone'] ?: '-') ?></td>
              <td>
                <div class="btn-group">
                  <a href="edit-student.php?id=<?= $student['id'] ?>"
                    class="btn btn-sm btn-outline-primary" title="Edit">
                    <i class="bi bi-pencil"></i>
                  </a>
                  <a href="delete-student.php?id=<?= $student['id'] ?>"
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
    var table = $('#students-table').DataTable({
      responsive: true,
      order: [
        [2, 'asc']
      ], // Sort by name
      searchDelay: 500,
      columnDefs: [{
          orderable: false,
          targets: [0, 7]
        }, // Disable sort on photo and actions
        {
          searchable: false,
          targets: [0]
        }
      ],
      language: {
        search: "",
        searchPlaceholder: "Search students...",
        emptyTable: "No students found",
        zeroRecords: "No matching students found"
      }
    });

    // Department filter
    document.getElementById('department-filter').addEventListener('change', function() {
      table.column(4).search(this.value).draw();
    });

    // Semester filter
    document.getElementById('semester-filter').addEventListener('change', function() {
      table.column(5).search(this.value ? '^' + this.value + '$' : '', true, false).draw();
    });

    // Clear filters
    document.getElementById('clear-filters').addEventListener('click', function() {
      document.getElementById('department-filter').value = '';
      document.getElementById('semester-filter').value = '';
      table.search('').columns().search('').draw();
    });
  });
</script>

<?php require_once __DIR__ . '/../../includes/admin-footer.php'; ?>