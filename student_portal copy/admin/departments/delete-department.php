<?php

/**
 * Delete Department Page
 * Admin Module
 */

$pageTitle = 'Delete Department';
require_once __DIR__ . '/../../includes/admin-header.php';
require_once __DIR__ . '/../../config/database.php';

$departmentId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$departmentId) {
  $_SESSION['error'] = 'Invalid department ID.';
  header('Location: view-departments.php');
  exit;
}

try {
  $stmt = $pdo->prepare("SELECT * FROM departments WHERE id = :id");
  $stmt->execute([':id' => $departmentId]);
  $department = $stmt->fetch();

  if (!$department) {
    $_SESSION['error'] = 'Department not found.';
    header('Location: view-departments.php');
    exit;
  }

  // Count related records
  $studentsStmt = $pdo->prepare("SELECT COUNT(*) FROM students WHERE department_id = :id");
  $studentsStmt->execute([':id' => $departmentId]);
  $studentCount = $studentsStmt->fetchColumn();

  $coursesStmt = $pdo->prepare("SELECT COUNT(*) FROM courses WHERE department_id = :id");
  $coursesStmt->execute([':id' => $departmentId]);
  $courseCount = $coursesStmt->fetchColumn();
} catch (PDOException $e) {
  $_SESSION['error'] = 'Error loading department.';
  header('Location: view-departments.php');
  exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
    $errors[] = 'Invalid request.';
  } else {
    $confirmCode = trim($_POST['confirm_code'] ?? '');

    if ($confirmCode !== $department['department_code']) {
      $errors[] = 'Department code confirmation does not match.';
    }

    if (empty($errors)) {
      try {
        $stmt = $pdo->prepare("DELETE FROM departments WHERE id = :id");
        $stmt->execute([':id' => $departmentId]);
        $_SESSION['success'] = 'Department deleted successfully.';
        header('Location: view-departments.php');
        exit;
      } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'foreign key constraint') !== false) {
          $errors[] = 'Cannot delete department with associated students or courses.';
        } else {
          $errors[] = 'An error occurred while deleting the department.';
        }
      }
    }
  }
}
?>

<div class="row mb-4">
  <div class="col-12">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="view-departments.php">Departments</a></li>
        <li class="breadcrumb-item active">Delete Department</li>
      </ol>
    </nav>
    <h1 class="mb-3 text-danger"><i class="bi bi-exclamation-triangle me-2"></i>Delete Department</h1>
  </div>
</div>

<?php if (!empty($errors)): ?>
  <div class="alert alert-danger">
    <ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= h($e) ?></li><?php endforeach; ?></ul>
  </div>
<?php endif; ?>

<div class="row">
  <div class="col-lg-8">
    <div class="alert alert-danger mb-4">
      <h5 class="alert-heading"><i class="bi bi-exclamation-circle me-2"></i>Warning!</h5>
      <p class="mb-0">You are about to permanently delete this department.</p>
    </div>

    <div class="card mb-4">
      <div class="card-header"><i class="bi bi-building me-2"></i>Department Information</div>
      <div class="card-body">
        <h4 class="mb-1"><?= h($department['department_code']) ?> - <?= h($department['department_name']) ?></h4>
        <?php if ($department['description']): ?>
          <p class="text-muted mb-0"><?= h($department['description']) ?></p>
        <?php endif; ?>
      </div>
    </div>

    <?php if ($studentCount > 0 || $courseCount > 0): ?>
      <div class="card mb-4 border-warning">
        <div class="card-header bg-warning text-dark"><i class="bi bi-database me-2"></i>Associated Data</div>
        <div class="card-body">
          <p class="mb-2">This department has:</p>
          <ul class="mb-2">
            <?php if ($studentCount > 0): ?><li><strong><?= $studentCount ?></strong> student(s)</li><?php endif; ?>
            <?php if ($courseCount > 0): ?><li><strong><?= $courseCount ?></strong> course(s)</li><?php endif; ?>
          </ul>
          <p class="text-danger mb-0"><strong>Note:</strong> Students and courses will have their department set to NULL if you proceed.</p>
        </div>
      </div>
    <?php endif; ?>

    <div class="card">
      <div class="card-header"><i class="bi bi-shield-check me-2"></i>Confirm Deletion</div>
      <div class="card-body">
        <form method="POST" action="">
          <?= csrfInput() ?>
          <p>Type <strong><?= h($department['department_code']) ?></strong> to confirm:</p>
          <div class="mb-4">
            <input type="text" class="form-control" id="confirm_code" name="confirm_code"
              required autocomplete="off">
          </div>
          <div class="d-flex justify-content-between">
            <a href="view-departments.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Cancel</a>
            <button type="submit" class="btn btn-danger" id="delete-btn" disabled>
              <i class="bi bi-trash me-1"></i>Delete Department
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
  document.getElementById('confirm_code').addEventListener('input', function() {
    document.getElementById('delete-btn').disabled = this.value !== '<?= addslashes($department['department_code']) ?>';
  });
</script>

<?php require_once __DIR__ . '/../../includes/admin-footer.php'; ?>