<?php

/**
 * Delete Course Page
 * Admin Module
 */

$pageTitle = 'Delete Course';
require_once __DIR__ . '/../../includes/admin-header.php';
require_once __DIR__ . '/../../config/database.php';

$courseId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$courseId) {
  $_SESSION['error'] = 'Invalid course ID.';
  header('Location: view-courses.php');
  exit;
}

try {
  $stmt = $pdo->prepare("SELECT * FROM courses WHERE id = :id");
  $stmt->execute([':id' => $courseId]);
  $course = $stmt->fetch();

  if (!$course) {
    $_SESSION['error'] = 'Course not found.';
    header('Location: view-courses.php');
    exit;
  }

  // Count related records
  $gradesStmt = $pdo->prepare("SELECT COUNT(*) FROM grades WHERE course_id = :id");
  $gradesStmt->execute([':id' => $courseId]);
  $gradesCount = $gradesStmt->fetchColumn();

  $scheduleStmt = $pdo->prepare("SELECT COUNT(*) FROM schedule WHERE course_id = :id");
  $scheduleStmt->execute([':id' => $courseId]);
  $scheduleCount = $scheduleStmt->fetchColumn();
} catch (PDOException $e) {
  $_SESSION['error'] = 'Error loading course data.';
  header('Location: view-courses.php');
  exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
    $errors[] = 'Invalid request.';
  } else {
    $confirmCode = trim($_POST['confirm_course_code'] ?? '');

    if ($confirmCode !== $course['course_code']) {
      $errors[] = 'Course code confirmation does not match.';
    }

    if (empty($errors)) {
      try {
        $stmt = $pdo->prepare("DELETE FROM courses WHERE id = :id");
        $stmt->execute([':id' => $courseId]);
        $_SESSION['success'] = 'Course deleted successfully.';
        header('Location: view-courses.php');
        exit;
      } catch (PDOException $e) {
        $errors[] = 'An error occurred while deleting the course.';
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
        <li class="breadcrumb-item active">Delete Course</li>
      </ol>
    </nav>
    <h1 class="mb-3 text-danger"><i class="bi bi-exclamation-triangle me-2"></i>Delete Course</h1>
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
      <p class="mb-0">Deleting this course will permanently remove it and all associated grades and schedule entries.</p>
    </div>

    <div class="card mb-4">
      <div class="card-header"><i class="bi bi-book me-2"></i>Course Information</div>
      <div class="card-body">
        <h4 class="mb-1"><?= h($course['course_code']) ?> - <?= h($course['course_name']) ?></h4>
        <p class="text-muted mb-0"><?= h($course['credits']) ?> credit<?= $course['credits'] != 1 ? 's' : '' ?></p>
      </div>
    </div>

    <?php if ($gradesCount > 0 || $scheduleCount > 0): ?>
      <div class="card mb-4 border-warning">
        <div class="card-header bg-warning text-dark"><i class="bi bi-database me-2"></i>Related Data</div>
        <div class="card-body">
          <ul class="mb-0">
            <?php if ($gradesCount > 0): ?><li><strong><?= $gradesCount ?></strong> grade record(s)</li><?php endif; ?>
            <?php if ($scheduleCount > 0): ?><li><strong><?= $scheduleCount ?></strong> schedule entry(s)</li><?php endif; ?>
          </ul>
        </div>
      </div>
    <?php endif; ?>

    <div class="card">
      <div class="card-header"><i class="bi bi-shield-check me-2"></i>Confirm Deletion</div>
      <div class="card-body">
        <form method="POST" action="">
          <?= csrfInput() ?>
          <p>Type <strong><?= h($course['course_code']) ?></strong> to confirm:</p>
          <div class="mb-4">
            <input type="text" class="form-control" id="confirm_course_code" name="confirm_course_code" required autocomplete="off">
          </div>
          <div class="d-flex justify-content-between">
            <a href="view-courses.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Cancel</a>
            <button type="submit" class="btn btn-danger" id="delete-btn" disabled><i class="bi bi-trash me-1"></i>Delete Course</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
  document.getElementById('confirm_course_code').addEventListener('input', function() {
    document.getElementById('delete-btn').disabled = this.value !== '<?= addslashes($course['course_code']) ?>';
  });
</script>

<?php require_once __DIR__ . '/../../includes/admin-footer.php'; ?>