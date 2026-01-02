<?php

/**
 * Add Schedule Entry Page
 * Admin Module
 */

$pageTitle = 'Add Schedule Entry';
require_once __DIR__ . '/../../includes/admin-header.php';
require_once __DIR__ . '/../../config/database.php';

// Fetch students and courses
try {
  $studentsStmt = $pdo->query("SELECT id, student_id, name FROM students ORDER BY name");
  $students = $studentsStmt->fetchAll();
  $coursesStmt = $pdo->query("SELECT id, course_code, course_name FROM courses ORDER BY course_code");
  $courses = $coursesStmt->fetchAll();
} catch (PDOException $e) {
  $students = [];
  $courses = [];
}

$errors = [];
$formData = [
  'student_id' => '',
  'course_id' => '',
  'day_of_week' => '1',
  'start_time' => '09:00',
  'end_time' => '10:00',
  'room_number' => '',
  'instructor_name' => '',
  'semester' => '1'
];

$daysOfWeek = [1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
    $errors[] = 'Invalid request.';
  } else {
    $formData = [
      'student_id' => $_POST['student_id'] ?? '',
      'course_id' => $_POST['course_id'] ?? '',
      'day_of_week' => $_POST['day_of_week'] ?? '1',
      'start_time' => $_POST['start_time'] ?? '',
      'end_time' => $_POST['end_time'] ?? '',
      'room_number' => trim($_POST['room_number'] ?? ''),
      'instructor_name' => trim($_POST['instructor_name'] ?? ''),
      'semester' => $_POST['semester'] ?? '1'
    ];

    if (empty($formData['student_id'])) $errors[] = 'Student is required.';
    if (empty($formData['course_id'])) $errors[] = 'Course is required.';
    if (empty($formData['start_time'])) $errors[] = 'Start time is required.';
    if (empty($formData['end_time'])) $errors[] = 'End time is required.';
    if ($formData['start_time'] >= $formData['end_time']) $errors[] = 'End time must be after start time.';

    if (empty($errors)) {
      try {
        $stmt = $pdo->prepare("
                    INSERT INTO schedule (student_id, course_id, day_of_week, start_time, end_time, 
                                         room_number, instructor_name, semester)
                    VALUES (:sid, :cid, :day, :start, :end, :room, :instructor, :sem)
                ");
        $stmt->execute([
          ':sid' => $formData['student_id'],
          ':cid' => $formData['course_id'],
          ':day' => $formData['day_of_week'],
          ':start' => $formData['start_time'],
          ':end' => $formData['end_time'],
          ':room' => $formData['room_number'] ?: null,
          ':instructor' => $formData['instructor_name'] ?: null,
          ':sem' => $formData['semester']
        ]);
        $_SESSION['success'] = 'Schedule entry added successfully.';
        header('Location: view-schedule.php');
        exit;
      } catch (PDOException $e) {
        $errors[] = 'An error occurred while adding the schedule entry.';
      }
    }
  }
}
?>

<div class="row mb-4">
  <div class="col-12">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="view-schedule.php">Schedule</a></li>
        <li class="breadcrumb-item active">Add Schedule Entry</li>
      </ol>
    </nav>
    <h1 class="mb-3"><i class="bi bi-calendar-plus me-2"></i>Add Schedule Entry</h1>
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
        <div class="col-md-6">
          <label for="student_id" class="form-label required-field">Student</label>
          <select class="form-select" id="student_id" name="student_id" required>
            <option value="">-- Select Student --</option>
            <?php foreach ($students as $s): ?>
              <option value="<?= $s['id'] ?>" <?= $formData['student_id'] == $s['id'] ? 'selected' : '' ?>>
                <?= h($s['student_id']) ?> - <?= h($s['name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-6">
          <label for="course_id" class="form-label required-field">Course</label>
          <select class="form-select" id="course_id" name="course_id" required>
            <option value="">-- Select Course --</option>
            <?php foreach ($courses as $c): ?>
              <option value="<?= $c['id'] ?>" <?= $formData['course_id'] == $c['id'] ? 'selected' : '' ?>>
                <?= h($c['course_code']) ?> - <?= h($c['course_name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-4">
          <label for="day_of_week" class="form-label required-field">Day of Week</label>
          <select class="form-select" id="day_of_week" name="day_of_week" required>
            <?php foreach ($daysOfWeek as $num => $day): ?>
              <option value="<?= $num ?>" <?= $formData['day_of_week'] == $num ? 'selected' : '' ?>><?= $day ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-4">
          <label for="start_time" class="form-label required-field">Start Time</label>
          <input type="time" class="form-control" id="start_time" name="start_time"
            value="<?= h($formData['start_time']) ?>" required>
        </div>
        <div class="col-md-4">
          <label for="end_time" class="form-label required-field">End Time</label>
          <input type="time" class="form-control" id="end_time" name="end_time"
            value="<?= h($formData['end_time']) ?>" required>
        </div>
        <div class="col-md-4">
          <label for="room_number" class="form-label">Room Number</label>
          <input type="text" class="form-control" id="room_number" name="room_number"
            value="<?= h($formData['room_number']) ?>" placeholder="e.g., A-101">
        </div>
        <div class="col-md-4">
          <label for="instructor_name" class="form-label">Instructor Name</label>
          <input type="text" class="form-control" id="instructor_name" name="instructor_name"
            value="<?= h($formData['instructor_name']) ?>">
        </div>
        <div class="col-md-4">
          <label for="semester" class="form-label required-field">Semester</label>
          <select class="form-select" id="semester" name="semester" required>
            <?php for ($i = 1; $i <= 8; $i++): ?>
              <option value="<?= $i ?>" <?= $formData['semester'] == $i ? 'selected' : '' ?>>Semester <?= $i ?></option>
            <?php endfor; ?>
          </select>
        </div>
      </div>

      <hr>

      <div class="d-flex justify-content-between">
        <a href="view-schedule.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Cancel</a>
        <button type="submit" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Add Schedule Entry</button>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/../../includes/admin-footer.php'; ?>