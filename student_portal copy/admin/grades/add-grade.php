<?php

/**
 * Add Grade Page
 * Admin Module
 */

$pageTitle = 'Add Grade';
require_once __DIR__ . '/../../includes/admin-header.php';
require_once __DIR__ . '/../../config/database.php';

// Fetch students
try {
  $studentsStmt = $pdo->query("SELECT id, student_id, name FROM students ORDER BY name");
  $students = $studentsStmt->fetchAll();
} catch (PDOException $e) {
  $students = [];
}

// Fetch courses
try {
  $coursesStmt = $pdo->query("SELECT id, course_code, course_name FROM courses ORDER BY course_code");
  $courses = $coursesStmt->fetchAll();
} catch (PDOException $e) {
  $courses = [];
}

$errors = [];
$formData = [
  'student_id' => '',
  'course_id' => '',
  'grade' => '',
  'semester' => '1',
  'academic_year' => date('Y') . '-' . (date('Y') + 1)
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
    if (empty($formData['grade'])) $errors[] = 'Grade is required.';
    if (!in_array($formData['grade'], $gradeOptions)) $errors[] = 'Invalid grade selected.';
    if (empty($formData['academic_year'])) $errors[] = 'Academic year is required.';

    // Check for duplicate
    if (empty($errors)) {
      $stmt = $pdo->prepare("SELECT COUNT(*) FROM grades WHERE student_id = :sid AND course_id = :cid AND semester = :sem AND academic_year = :year");
      $stmt->execute([
        ':sid' => $formData['student_id'],
        ':cid' => $formData['course_id'],
        ':sem' => $formData['semester'],
        ':year' => $formData['academic_year']
      ]);
      if ($stmt->fetchColumn() > 0) {
        $errors[] = 'A grade already exists for this student, course, semester, and year.';
      }
    }

    if (empty($errors)) {
      try {
        $stmt = $pdo->prepare("
                    INSERT INTO grades (student_id, course_id, grade, semester, academic_year)
                    VALUES (:sid, :cid, :grade, :sem, :year)
                ");
        $stmt->execute([
          ':sid' => $formData['student_id'],
          ':cid' => $formData['course_id'],
          ':grade' => $formData['grade'],
          ':sem' => $formData['semester'],
          ':year' => $formData['academic_year']
        ]);
        $_SESSION['success'] = 'Grade added successfully.';
        header('Location: view-grades.php');
        exit;
      } catch (PDOException $e) {
        $errors[] = 'An error occurred while adding the grade.';
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
        <li class="breadcrumb-item active">Add Grade</li>
      </ol>
    </nav>
    <h1 class="mb-3"><i class="bi bi-card-checklist me-2"></i>Add Grade</h1>
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
            <option value="">-- Select Grade --</option>
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
            value="<?= h($formData['academic_year']) ?>" required placeholder="e.g., 2024-2025">
        </div>
      </div>

      <hr>

      <div class="d-flex justify-content-between">
        <a href="view-grades.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Cancel</a>
        <button type="submit" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Add Grade</button>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/../../includes/admin-footer.php'; ?>