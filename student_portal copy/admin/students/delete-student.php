<?php

/**
 * Delete Student Page
 * Admin Module
 * 
 * Confirmation and cascade delete for student record
 */

$pageTitle = 'Delete Student';
require_once __DIR__ . '/../../includes/admin-header.php';
require_once __DIR__ . '/../../config/database.php';

// Get student ID from URL
$studentId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$studentId) {
  $_SESSION['error'] = 'Invalid student ID.';
  header('Location: view-students.php');
  exit;
}

// Fetch student data
try {
  $stmt = $pdo->prepare("
        SELECT s.*, d.department_name
        FROM students s
        LEFT JOIN departments d ON s.department_id = d.id
        WHERE s.id = :id
    ");
  $stmt->execute([':id' => $studentId]);
  $student = $stmt->fetch();

  if (!$student) {
    $_SESSION['error'] = 'Student not found.';
    header('Location: view-students.php');
    exit;
  }

  // Count related records
  $gradesStmt = $pdo->prepare("SELECT COUNT(*) FROM grades WHERE student_id = :id");
  $gradesStmt->execute([':id' => $studentId]);
  $gradesCount = $gradesStmt->fetchColumn();

  $scheduleStmt = $pdo->prepare("SELECT COUNT(*) FROM schedule WHERE student_id = :id");
  $scheduleStmt->execute([':id' => $studentId]);
  $scheduleCount = $scheduleStmt->fetchColumn();
} catch (PDOException $e) {
  error_log("Error fetching student: " . $e->getMessage());
  $_SESSION['error'] = 'Error loading student data.';
  header('Location: view-students.php');
  exit;
}

$errors = [];

// Process deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Validate CSRF token
  if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
    $errors[] = 'Invalid request. Please refresh the page and try again.';
  } else {
    // Verify confirmation
    $confirmStudentId = trim($_POST['confirm_student_id'] ?? '');

    if ($confirmStudentId !== $student['student_id']) {
      $errors[] = 'Student ID confirmation does not match.';
    }

    if (empty($errors)) {
      try {
        // Delete student (grades and schedule will cascade delete)
        $stmt = $pdo->prepare("DELETE FROM students WHERE id = :id");
        $stmt->execute([':id' => $studentId]);

        // Delete profile picture if exists
        if ($student['profile_picture'] && $student['profile_picture'] !== 'default.jpg') {
          $picturePath = __DIR__ . '/../../uploads/profile_pictures/' . $student['profile_picture'];
          if (file_exists($picturePath)) {
            unlink($picturePath);
          }
        }

        $_SESSION['success'] = 'Student deleted successfully.';
        header('Location: view-students.php');
        exit;
      } catch (PDOException $e) {
        error_log("Error deleting student: " . $e->getMessage());
        $errors[] = 'An error occurred while deleting the student.';
      }
    }
  }
}
?>

<div class="row mb-4">
  <div class="col-12">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="view-students.php">Students</a></li>
        <li class="breadcrumb-item active" aria-current="page">Delete Student</li>
      </ol>
    </nav>
    <h1 class="mb-3 text-danger">
      <i class="bi bi-exclamation-triangle me-2"></i>Delete Student
    </h1>
  </div>
</div>

<?php if (!empty($errors)): ?>
  <div class="alert alert-danger">
    <ul class="mb-0">
      <?php foreach ($errors as $error): ?>
        <li><?= h($error) ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<div class="row">
  <div class="col-lg-8">
    <div class="alert alert-danger mb-4">
      <h5 class="alert-heading"><i class="bi bi-exclamation-circle me-2"></i>Warning: This action cannot be undone!</h5>
      <p class="mb-0">
        Deleting this student will permanently remove their account and all associated data from the system.
      </p>
    </div>

    <!-- Student Info Card -->
    <div class="card mb-4">
      <div class="card-header">
        <i class="bi bi-person me-2"></i>Student Information
      </div>
      <div class="card-body">
        <div class="row align-items-center">
          <div class="col-auto">
            <img src="../../uploads/profile_pictures/<?= h($student['profile_picture'] ?: 'default.jpg') ?>"
              alt="<?= h($student['name']) ?>"
              class="rounded-circle"
              width="80" height="80"
              onerror="this.src='../../assets/images/default-avatar.png'">
          </div>
          <div class="col">
            <h4 class="mb-1"><?= h($student['name']) ?></h4>
            <p class="text-muted mb-1"><?= h($student['student_id']) ?></p>
            <p class="mb-0">
              <?= h($student['email']) ?><br>
              <?= h($student['department_name'] ?? 'No department') ?> &bull; Semester <?= h($student['current_semester']) ?>
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Related Data Warning -->
    <?php if ($gradesCount > 0 || $scheduleCount > 0): ?>
      <div class="card mb-4 border-warning">
        <div class="card-header bg-warning text-dark">
          <i class="bi bi-database me-2"></i>Related Data Will Be Deleted
        </div>
        <div class="card-body">
          <p class="mb-2">The following data associated with this student will also be permanently deleted:</p>
          <ul class="mb-0">
            <?php if ($gradesCount > 0): ?>
              <li><strong><?= $gradesCount ?></strong> grade record<?= $gradesCount !== 1 ? 's' : '' ?></li>
            <?php endif; ?>
            <?php if ($scheduleCount > 0): ?>
              <li><strong><?= $scheduleCount ?></strong> schedule entr<?= $scheduleCount !== 1 ? 'ies' : 'y' ?></li>
            <?php endif; ?>
          </ul>
        </div>
      </div>
    <?php endif; ?>

    <!-- Confirmation Form -->
    <div class="card">
      <div class="card-header">
        <i class="bi bi-shield-check me-2"></i>Confirm Deletion
      </div>
      <div class="card-body">
        <form method="POST" action="">
          <?= csrfInput() ?>

          <p class="mb-3">
            To confirm deletion, please type the Student ID: <strong><?= h($student['student_id']) ?></strong>
          </p>

          <div class="mb-4">
            <label for="confirm_student_id" class="form-label required-field">Student ID Confirmation</label>
            <input type="text" class="form-control" id="confirm_student_id" name="confirm_student_id"
              required autocomplete="off" placeholder="Type the Student ID to confirm">
            <div class="form-text text-danger">This is a permanent action.</div>
          </div>

          <div class="d-flex justify-content-between">
            <a href="view-students.php" class="btn btn-outline-secondary">
              <i class="bi bi-arrow-left me-1"></i>Cancel
            </a>
            <button type="submit" class="btn btn-danger" id="delete-btn" disabled>
              <i class="bi bi-trash me-1"></i>Delete Student Permanently
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
  // Enable delete button only when correct student ID is typed
  document.getElementById('confirm_student_id').addEventListener('input', function() {
    const expectedId = '<?= addslashes($student['student_id']) ?>';
    const deleteBtn = document.getElementById('delete-btn');

    if (this.value === expectedId) {
      deleteBtn.disabled = false;
    } else {
      deleteBtn.disabled = true;
    }
  });
</script>

<?php require_once __DIR__ . '/../../includes/admin-footer.php'; ?>