<?php

/**
 * Edit Department Page
 * Admin Module
 */

$pageTitle = 'Edit Department';
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
} catch (PDOException $e) {
  $_SESSION['error'] = 'Error loading department.';
  header('Location: view-departments.php');
  exit;
}

$errors = [];
$formData = [
  'department_code' => $department['department_code'],
  'department_name' => $department['department_name'],
  'description' => $department['description']
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
    $errors[] = 'Invalid request.';
  } else {
    $formData = [
      'department_code' => strtoupper(trim($_POST['department_code'] ?? '')),
      'department_name' => trim($_POST['department_name'] ?? ''),
      'description' => trim($_POST['description'] ?? '')
    ];

    if (empty($formData['department_code'])) $errors[] = 'Department code is required.';
    if (empty($formData['department_name'])) $errors[] = 'Department name is required.';

    // Check duplicate (excluding current)
    if (empty($errors) && $formData['department_code'] !== $department['department_code']) {
      $stmt = $pdo->prepare("SELECT COUNT(*) FROM departments WHERE department_code = :code AND id != :id");
      $stmt->execute([':code' => $formData['department_code'], ':id' => $departmentId]);
      if ($stmt->fetchColumn() > 0) $errors[] = 'Department code already exists.';
    }

    if (empty($errors)) {
      try {
        $stmt = $pdo->prepare("
                    UPDATE departments SET department_code = :code, department_name = :name, 
                    description = :desc WHERE id = :id
                ");
        $stmt->execute([
          ':code' => $formData['department_code'],
          ':name' => $formData['department_name'],
          ':desc' => $formData['description'] ?: null,
          ':id' => $departmentId
        ]);
        $_SESSION['success'] = 'Department updated successfully.';
        header('Location: view-departments.php');
        exit;
      } catch (PDOException $e) {
        $errors[] = 'An error occurred while updating the department.';
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
        <li class="breadcrumb-item active">Edit Department</li>
      </ol>
    </nav>
    <h1 class="mb-3"><i class="bi bi-pencil-square me-2"></i>Edit Department</h1>
    <p class="text-muted">Editing: <strong><?= h($department['department_code']) ?></strong></p>
  </div>
</div>

<?php if (!empty($errors)): ?>
  <div class="alert alert-danger">
    <ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= h($e) ?></li><?php endforeach; ?></ul>
  </div>
<?php endif; ?>

<div class="card">
  <div class="card-body">
    <form method="POST" action="" class="needs-validation" novalidate>
      <?= csrfInput() ?>

      <div class="row g-3 mb-4">
        <div class="col-md-4">
          <label for="department_code" class="form-label required-field">Department Code</label>
          <input type="text" class="form-control" id="department_code" name="department_code"
            value="<?= h($formData['department_code']) ?>" required pattern="[A-Za-z0-9]+">
        </div>
        <div class="col-md-8">
          <label for="department_name" class="form-label required-field">Department Name</label>
          <input type="text" class="form-control" id="department_name" name="department_name"
            value="<?= h($formData['department_name']) ?>" required>
        </div>
        <div class="col-12">
          <label for="description" class="form-label">Description</label>
          <textarea class="form-control" id="description" name="description" rows="3"><?= h($formData['description']) ?></textarea>
        </div>
      </div>

      <hr>

      <div class="d-flex justify-content-between">
        <a href="view-departments.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Cancel</a>
        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Save Changes</button>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/../../includes/admin-footer.php'; ?>