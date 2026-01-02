<?php

/**
 * Delete Schedule Entry Page
 * Admin Module
 */

$pageTitle = 'Delete Schedule Entry';
require_once __DIR__ . '/../../includes/admin-header.php';
require_once __DIR__ . '/../../config/database.php';

$scheduleId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$scheduleId) {
  $_SESSION['error'] = 'Invalid schedule ID.';
  header('Location: view-schedule.php');
  exit;
}

try {
  $stmt = $pdo->prepare("
        SELECT sc.*, s.student_id as sid, s.name as student_name, c.course_code, c.course_name
        FROM schedule sc
        JOIN students s ON sc.student_id = s.id
        JOIN courses c ON sc.course_id = c.id
        WHERE sc.id = :id
    ");
  $stmt->execute([':id' => $scheduleId]);
  $schedule = $stmt->fetch();

  if (!$schedule) {
    $_SESSION['error'] = 'Schedule entry not found.';
    header('Location: view-schedule.php');
    exit;
  }
} catch (PDOException $e) {
  $_SESSION['error'] = 'Error loading schedule entry.';
  header('Location: view-schedule.php');
  exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
    $errors[] = 'Invalid request.';
  } else {
    if (!isset($_POST['confirm_delete'])) {
      $errors[] = 'Please confirm deletion.';
    }

    if (empty($errors)) {
      try {
        $stmt = $pdo->prepare("DELETE FROM schedule WHERE id = :id");
        $stmt->execute([':id' => $scheduleId]);
        $_SESSION['success'] = 'Schedule entry deleted successfully.';
        header('Location: view-schedule.php');
        exit;
      } catch (PDOException $e) {
        $errors[] = 'An error occurred while deleting the schedule entry.';
      }
    }
  }
}

$daysOfWeek = [1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday'];
?>

<div class="row mb-4">
  <div class="col-12">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="view-schedule.php">Schedule</a></li>
        <li class="breadcrumb-item active">Delete Schedule Entry</li>
      </ol>
    </nav>
    <h1 class="mb-3 text-danger"><i class="bi bi-exclamation-triangle me-2"></i>Delete Schedule Entry</h1>
  </div>
</div>

<?php if (!empty($errors)): ?>
  <div class="alert alert-danger">
    <ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= h($e) ?></li><?php endforeach; ?></ul>
  </div>
<?php endif; ?>

<div class="row">
  <div class="col-lg-6">
    <div class="alert alert-danger mb-4">
      <h5 class="alert-heading"><i class="bi bi-exclamation-circle me-2"></i>Warning!</h5>
      <p class="mb-0">You are about to permanently delete this schedule entry.</p>
    </div>

    <div class="card mb-4">
      <div class="card-header"><i class="bi bi-calendar-week me-2"></i>Schedule Entry Information</div>
      <div class="card-body">
        <table class="table table-borderless mb-0">
          <tr>
            <th>Student:</th>
            <td><?= h($schedule['sid']) ?> - <?= h($schedule['student_name']) ?></td>
          </tr>
          <tr>
            <th>Course:</th>
            <td><?= h($schedule['course_code']) ?> - <?= h($schedule['course_name']) ?></td>
          </tr>
          <tr>
            <th>Day:</th>
            <td><?= getDayName($schedule['day_of_week']) ?></td>
          </tr>
          <tr>
            <th>Time:</th>
            <td><?= formatTime($schedule['start_time']) ?> - <?= formatTime($schedule['end_time']) ?></td>
          </tr>
          <tr>
            <th>Room:</th>
            <td><?= h($schedule['room_number'] ?: 'Not specified') ?></td>
          </tr>
          <tr>
            <th>Instructor:</th>
            <td><?= h($schedule['instructor_name'] ?: 'Not specified') ?></td>
          </tr>
        </table>
      </div>
    </div>

    <div class="card">
      <div class="card-body">
        <form method="POST" action="">
          <?= csrfInput() ?>
          <div class="form-check mb-4">
            <input class="form-check-input" type="checkbox" id="confirm_delete" name="confirm_delete" value="1">
            <label class="form-check-label" for="confirm_delete">
              I confirm that I want to delete this schedule entry permanently
            </label>
          </div>
          <div class="d-flex justify-content-between">
            <a href="view-schedule.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Cancel</a>
            <button type="submit" class="btn btn-danger" id="delete-btn" disabled>
              <i class="bi bi-trash me-1"></i>Delete Schedule Entry
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
  document.getElementById('confirm_delete').addEventListener('change', function() {
    document.getElementById('delete-btn').disabled = !this.checked;
  });
</script>

<?php require_once __DIR__ . '/../../includes/admin-footer.php'; ?>