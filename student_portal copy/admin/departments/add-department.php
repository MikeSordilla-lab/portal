<?php

/**
 * Add Department Page
 * Admin Module
 */

$pageTitle = 'Add Department';
require_once __DIR__ . '/../../includes/admin-header.php';
require_once __DIR__ . '/../../config/database.php';

$errors = [];
$formData = [
  'department_code' => '',
  'department_name' => '',
  'description' => ''
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

    // Check duplicate
    if (empty($errors)) {
      $stmt = $pdo->prepare("SELECT COUNT(*) FROM departments WHERE department_code = :code");
      $stmt->execute([':code' => $formData['department_code']]);
      if ($stmt->fetchColumn() > 0) $errors[] = 'Department code already exists.';
    }

    if (empty($errors)) {
      try {
        $stmt = $pdo->prepare("
                    INSERT INTO departments (department_code, department_name, description)
                    VALUES (:code, :name, :desc)
                ");
        $stmt->execute([
          ':code' => $formData['department_code'],
          ':name' => $formData['department_name'],
          ':desc' => $formData['description'] ?: null
        ]);
        $_SESSION['success'] = 'Department added successfully.';
        header('Location: view-departments.php');
        exit;
      } catch (PDOException $e) {
        $errors[] = 'An error occurred while adding the department.';
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
        <li class="breadcrumb-item active">Add Department</li>
      </ol>
    </nav>
    <h1 class="mb-3"><i class="bi bi-building me-2"></i>Add Department</h1>
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
            value="<?= h($formData['department_code']) ?>" required pattern="[A-Za-z0-9]+"
            placeholder="e.g., CS">
          <div class="form-text">Short code, alphanumeric only</div>
        </div>
        <div class="col-md-8">
          <label for="department_name" class="form-label required-field">Department Name</label>
          <input type="text" class="form-control" id="department_name" name="department_name"
            value="<?= h($formData['department_name']) ?>" required
            placeholder="e.g., Computer Science">
        </div>
        <div class="col-12">
          <label for="description" class="form-label">Description</label>
          <textarea class="form-control" id="description" name="description" rows="3"><?= h($formData['description']) ?></textarea>
        </div>
      </div>

      <hr>

      <div class="d-flex justify-content-between">
        <a href="view-departments.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Cancel</a>
        <button type="submit" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Add Department</button>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/../../includes/admin-footer.php'; ?>