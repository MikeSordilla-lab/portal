<?php

/**
 * View Departments Page
 * Admin Module
 */

$pageTitle = 'Manage Departments';
require_once __DIR__ . '/../../includes/admin-header.php';
require_once __DIR__ . '/../../config/database.php';

// Fetch all departments with counts
try {
  $stmt = $pdo->query("
        SELECT d.id, d.department_code, d.department_name, d.description, d.created_at,
               (SELECT COUNT(*) FROM students WHERE department_id = d.id) as student_count,
               (SELECT COUNT(*) FROM courses WHERE department_id = d.id) as course_count
        FROM departments d
        ORDER BY d.department_name ASC
    ");
  $departments = $stmt->fetchAll();
} catch (PDOException $e) {
  error_log("Error fetching departments: " . $e->getMessage());
  $departments = [];
}
?>

<div class="row mb-4">
  <div class="col-12 d-flex justify-content-between align-items-center flex-wrap">
    <div>
      <h1 class="mb-2"><i class="bi bi-building me-2"></i>Manage Departments</h1>
      <p class="text-muted mb-0"><?= count($departments) ?> department<?= count($departments) !== 1 ? 's' : '' ?></p>
    </div>
    <a href="add-department.php" class="btn btn-primary">
      <i class="bi bi-plus-lg me-1"></i>Add Department
    </a>
  </div>
</div>

<!-- Departments Table -->
<div class="card">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table id="departments-table" class="table table-admin table-hover mb-0" style="width:100%">
        <thead>
          <tr>
            <th>Code</th>
            <th>Name</th>
            <th>Description</th>
            <th>Students</th>
            <th>Courses</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($departments as $dept): ?>
            <tr>
              <td><strong><?= h($dept['department_code']) ?></strong></td>
              <td><?= h($dept['department_name']) ?></td>
              <td>
                <?php if ($dept['description']): ?>
                  <?= h(substr($dept['description'], 0, 50)) ?><?= strlen($dept['description']) > 50 ? '...' : '' ?>
                <?php else: ?>
                  <span class="text-muted">-</span>
                <?php endif; ?>
              </td>
              <td><?= $dept['student_count'] ?></td>
              <td><?= $dept['course_count'] ?></td>
              <td>
                <div class="btn-group">
                  <a href="edit-department.php?id=<?= $dept['id'] ?>" class="btn btn-sm btn-outline-primary" title="Edit">
                    <i class="bi bi-pencil"></i>
                  </a>
                  <a href="delete-department.php?id=<?= $dept['id'] ?>" class="btn btn-sm btn-outline-danger" title="Delete">
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
    $('#departments-table').DataTable({
      responsive: true,
      order: [
        [1, 'asc']
      ],
      searchDelay: 500,
      columnDefs: [{
        orderable: false,
        targets: [5]
      }],
      language: {
        search: "",
        searchPlaceholder: "Search departments...",
        emptyTable: "No departments found"
      }
    });
  });
</script>

<?php require_once __DIR__ . '/../../includes/admin-footer.php'; ?>