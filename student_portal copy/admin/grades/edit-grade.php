<?php

/**
 * Edit Grade Page
 * Admin Module
 */

$pageTitle = 'Edit Grade';
require_once __DIR__ . '/../../includes/admin-header.php';
require_once __DIR__ . '/../../config/database.php';

$gradeId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$gradeId) {
  $_SESSION['error'] = 'Invalid grade ID.';
  header('Location: view-grades.php');
  exit;
}

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

// Fetch grade
try {
  $stmt = $pdo->prepare("SELECT * FROM grades WHERE id = :id");
  $stmt->execute([':id' => $gradeId]);
  $grade = $stmt->fetch();

  if (!$grade) {
    $_SESSION['error'] = 'Grade not found.';
    header('Location: view-grades.php');
    exit;
  }
} catch (PDOException $e) {
  $_SESSION['error'] = 'Error loading grade.';
  header('Location: view-grades.php');
  exit;
}

$errors = [];
$formData = [
  'student_id' => $grade['student_id'],
  'course_id' => $grade['course_id'],
  'grade' => $grade['grade'],
  'semester' => $grade['semester'],
  'academic_year' => $grade['academic_year']
];

$gradeOptions = ['A+', 'A', 'A-', 'B+', 'B', 'B-', 'C+', 'C', 'C-', 'D+', 'D', 'D-', 'F', 'I', 'W'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
    $errors[] = 'Invalid request.';
  } else {
    $formData = [
      'student_id' => $_POST['student_id'] ?? '',
      'course_id' => $_POST['course_id'] ?? '',
      'grade' => $_POST['grade'] ?? '',
      'semester' => $_POST['semester'] ?? '1',
      'academic_year' => trim($_POST['academic_year'] ?? '')
    ];

    if (empty($formData['student_id'])) $errors[] = 'Student is required.';
    if (empty($formData['course_id'])) $errors[] = 'Course is required.';
    if (empty($formData['grade']) || !in_array($formData['grade'], $gradeOptions)) $errors[] = 'Valid grade is required.';
    if (empty($formData['academic_year'])) $errors[] = 'Academic year is required.';

    if (empty($errors)) {
      try {
        $stmt = $pdo->prepare("
                    UPDATE grades SET student_id = :sid, course_id = :cid, grade = :grade,
                    semester = :sem, academic_year = :year WHERE id = :id
                ");
        $stmt->execute([
          ':sid' => $formData['student_id'],
          ':cid' => $formData['course_id'],
          ':grade' => $formData['grade'],
          ':sem' => $formData['semester'],
          ':year' => $formData['academic_year'],
          ':id' => $gradeId
        ]);
        $_SESSION['success'] = 'Grade updated successfully.';
        header('Location: view-grades.php');
        exit;
      } catch (PDOException $e) {
        $errors[] = 'An error occurred while updating the grade.';
      }
    }
  }
}
?>

<div class="row mb-4">
  <div class="col-12">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="view-grades.php">Grades</a></li>
        <li class="breadcrumb-item active">Edit Grade</li>
      </ol>
    </nav>
    <h1 class="mb-3"><i class="bi bi-pencil-square me-2"></i>Edit Grade</h1>
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
          <label for="grade" class="form-label required-field">Grade</label>
          <select class="form-select" id="grade" name="grade" required>
            <?php foreach ($gradeOptions as $g): ?>
              <option value="<?= $g ?>" <?= $formData['grade'] === $g ? 'selected' : '' ?>><?= $g ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-4">
          <label for="semester" class="form-label required-field">Semester</label>
          <select class="form-select" id="semester" name="semester" required>
            <?php for ($i = 1; $i <= 8; $i++): ?>
              <option value="<?= $i ?>" <?= $formData['semester'] == $i ? 'selected' : '' ?>>Semester <?= $i ?></option>
            <?php endfor; ?>
          </select>
        </div>
        <div class="col-md-4">
          <label for="academic_year" class="form-label required-field">Academic Year</label>
          <input type="text" class="form-control" id="academic_year" name="academic_year"
            value="<?= h($formData['academic_year']) ?>" required>
        </div>
      </div>

      <hr>

      <div class="d-flex justify-content-between">
        <a href="view-grades.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Cancel</a>
        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Save Changes</button>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/../../includes/admin-footer.php'; ?>