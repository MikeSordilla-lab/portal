<?php

/**
 * Edit Course Page
 * Admin Module
 */

$pageTitle = 'Edit Course';
require_once __DIR__ . '/../../includes/admin-header.php';
require_once __DIR__ . '/../../config/database.php';

$courseId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$courseId) {
  $_SESSION['error'] = 'Invalid course ID.';
  header('Location: view-courses.php');
  exit;
}

// Fetch departments
try {
  $deptStmt = $pdo->query("SELECT id, department_code, department_name FROM departments ORDER BY department_name");
  $departments = $deptStmt->fetchAll();
} catch (PDOException $e) {
  $departments = [];
}

// Fetch course data
try {
  $stmt = $pdo->prepare("SELECT * FROM courses WHERE id = :id");
  $stmt->execute([':id' => $courseId]);
  $course = $stmt->fetch();

  if (!$course) {
    $_SESSION['error'] = 'Course not found.';
    header('Location: view-courses.php');
    exit;
  }
} catch (PDOException $e) {
  $_SESSION['error'] = 'Error loading course data.';
  header('Location: view-courses.php');
  exit;
}

$errors = [];
$formData = [
  'course_code' => $course['course_code'],
  'course_name' => $course['course_name'],
  'credits' => $course['credits'],
  'department_id' => $course['department_id'],
  'description' => $course['description']
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
    $errors[] = 'Invalid request.';
  } else {
    $formData = [
      'course_code' => strtoupper(trim($_POST['course_code'] ?? '')),
      'course_name' => trim($_POST['course_name'] ?? ''),
      'credits' => (int)($_POST['credits'] ?? 3),
      'department_id' => $_POST['department_id'] ?? '',
      'description' => trim($_POST['description'] ?? '')
    ];

    if (empty($formData['course_code'])) $errors[] = 'Course code is required.';
    if (empty($formData['course_name'])) $errors[] = 'Course name is required.';
    if ($formData['credits'] < 1 || $formData['credits'] > 6) $errors[] = 'Credits must be 1-6.';
    if (empty($formData['department_id'])) $errors[] = 'Department is required.';

    // Check duplicate
    if (empty($errors) && $formData['course_code'] !== $course['course_code']) {
      $stmt = $pdo->prepare("SELECT COUNT(*) FROM courses WHERE course_code = :code AND id != :id");
      $stmt->execute([':code' => $formData['course_code'], ':id' => $courseId]);
      if ($stmt->fetchColumn() > 0) $errors[] = 'Course code already exists.';
    }

    if (empty($errors)) {
      try {
        $stmt = $pdo->prepare("
                    UPDATE courses SET course_code = :code, course_name = :name, credits = :credits,
                    department_id = :dept, description = :desc WHERE id = :id
                ");
        $stmt->execute([
          ':code' => $formData['course_code'],
          ':name' => $formData['course_name'],
          ':credits' => $formData['credits'],
          ':dept' => $formData['department_id'],
          ':desc' => $formData['description'] ?: null,
          ':id' => $courseId
        ]);
        $_SESSION['success'] = 'Course updated successfully.';
        header('Location: view-courses.php');
        exit;
      } catch (PDOException $e) {
        $errors[] = 'An error occurred while updating the course.';
      }
    }
  }
}
?>

<div class="row mb-4">
  <div class="col-12">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="view-courses.php">Courses</a></li>
        <li class="breadcrumb-item active">Edit Course</li>
      </ol>
    </nav>
    <h1 class="mb-3"><i class="bi bi-pencil-square me-2"></i>Edit Course</h1>
    <p class="text-muted">Editing: <strong><?= h($course['course_code']) ?></strong></p>
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
          <label for="course_code" class="form-label required-field">Course Code</label>
          <input type="text" class="form-control" id="course_code" name="course_code"
            value="<?= h($formData['course_code']) ?>" required pattern="[A-Za-z0-9]+">
        </div>
        <div class="col-md-8">
          <label for="course_name" class="form-label required-field">Course Name</label>
          <input type="text" class="form-control" id="course_name" name="course_name"
            value="<?= h($formData['course_name']) ?>" required>
        </div>
        <div class="col-md-4">
          <label for="credits" class="form-label required-field">Credits</label>
          <select class="form-select" id="credits" name="credits" required>
            <?php for ($i = 1; $i <= 6; $i++): ?>
              <option value="<?= $i ?>" <?= $formData['credits'] == $i ? 'selected' : '' ?>><?= $i ?></option>
            <?php endfor; ?>
          </select>
        </div>
        <div class="col-md-8">
          <label for="department_id" class="form-label required-field">Department</label>
          <select class="form-select" id="department_id" name="department_id" required>
            <option value="">-- Select --</option>
            <?php foreach ($departments as $dept): ?>
              <option value="<?= $dept['id'] ?>" <?= $formData['department_id'] == $dept['id'] ? 'selected' : '' ?>>
                <?= h($dept['department_code']) ?> - <?= h($dept['department_name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-12">
          <label for="description" class="form-label">Description</label>
          <textarea class="form-control" id="description" name="description" rows="3"><?= h($formData['description']) ?></textarea>
        </div>
      </div>

      <hr>

      <div class="d-flex justify-content-between">
        <a href="view-courses.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Cancel</a>
        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Save Changes</button>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/../../includes/admin-footer.php'; ?>