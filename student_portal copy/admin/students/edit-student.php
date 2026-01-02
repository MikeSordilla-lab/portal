<?php

/**
 * Edit Student Page
 * Admin Module
 * 
 * Update existing student record
 */

$pageTitle = 'Edit Student';
require_once __DIR__ . '/../../includes/admin-header.php';
require_once __DIR__ . '/../../config/database.php';

// Get student ID from URL
$studentId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$studentId) {
  $_SESSION['error'] = 'Invalid student ID.';
  header('Location: view-students.php');
  exit;
}

// Fetch departments for dropdown
try {
  $deptStmt = $pdo->query("SELECT id, department_code, department_name FROM departments ORDER BY department_name");
  $departments = $deptStmt->fetchAll();
} catch (PDOException $e) {
  error_log("Error fetching departments: " . $e->getMessage());
  $departments = [];
}

// Fetch current student data
try {
  $stmt = $pdo->prepare("SELECT * FROM students WHERE id = :id");
  $stmt->execute([':id' => $studentId]);
  $student = $stmt->fetch();

  if (!$student) {
    $_SESSION['error'] = 'Student not found.';
    header('Location: view-students.php');
    exit;
  }
} catch (PDOException $e) {
  error_log("Error fetching student: " . $e->getMessage());
  $_SESSION['error'] = 'Error loading student data.';
  header('Location: view-students.php');
  exit;
}

$errors = [];
$formData = [
  'student_id' => $student['student_id'],
  'name' => $student['name'],
  'email' => $student['email'],
  'phone' => $student['phone'],
  'date_of_birth' => $student['date_of_birth'],
  'gender' => $student['gender'],
  'address' => $student['address'],
  'department_id' => $student['department_id'],
  'current_semester' => $student['current_semester'],
  'enrollment_date' => $student['enrollment_date']
];

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Validate CSRF token
  if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
    $errors[] = 'Invalid request. Please refresh the page and try again.';
  } else {
    // Get and sanitize input
    $formData = [
      'student_id' => trim($_POST['student_id'] ?? ''),
      'name' => trim($_POST['name'] ?? ''),
      'email' => trim($_POST['email'] ?? ''),
      'phone' => trim($_POST['phone'] ?? ''),
      'date_of_birth' => trim($_POST['date_of_birth'] ?? ''),
      'gender' => trim($_POST['gender'] ?? ''),
      'address' => trim($_POST['address'] ?? ''),
      'department_id' => $_POST['department_id'] ?? '',
      'current_semester' => $_POST['current_semester'] ?? '1',
      'enrollment_date' => trim($_POST['enrollment_date'] ?? '')
    ];
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // Validation
    if (empty($formData['student_id'])) {
      $errors[] = 'Student ID is required.';
    } elseif (!validateStudentId($formData['student_id'])) {
      $errors[] = 'Student ID format is invalid.';
    }

    if (empty($formData['name'])) {
      $errors[] = 'Name is required.';
    }

    if (empty($formData['email'])) {
      $errors[] = 'Email is required.';
    } elseif (!validateEmail($formData['email'])) {
      $errors[] = 'Please enter a valid email address.';
    }

    // Password validation (only if provided)
    if (!empty($newPassword)) {
      if (!validatePassword($newPassword)) {
        $errors[] = 'Password must be at least 8 characters.';
      }
      if ($newPassword !== $confirmPassword) {
        $errors[] = 'Passwords do not match.';
      }
    }

    if (!empty($formData['phone']) && !validatePhone($formData['phone'])) {
      $errors[] = 'Phone number must be 10-15 digits.';
    }

    if (!empty($formData['date_of_birth']) && !validateDate($formData['date_of_birth'])) {
      $errors[] = 'Please enter a valid date of birth.';
    }

    if (!empty($formData['gender']) && !validateGender($formData['gender'])) {
      $errors[] = 'Please select a valid gender.';
    }

    if (empty($formData['department_id'])) {
      $errors[] = 'Department is required.';
    }

    // Check for duplicate student_id (excluding current record)
    if (empty($errors) && $formData['student_id'] !== $student['student_id']) {
      try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM students WHERE student_id = :student_id AND id != :id");
        $stmt->execute([':student_id' => $formData['student_id'], ':id' => $studentId]);
        if ($stmt->fetchColumn() > 0) {
          $errors[] = 'Student ID already exists.';
        }
      } catch (PDOException $e) {
        error_log("Error checking student ID: " . $e->getMessage());
      }
    }

    // Update if no errors
    if (empty($errors)) {
      try {
        $sql = "UPDATE students SET 
                        student_id = :student_id, 
                        name = :name, 
                        email = :email, 
                        phone = :phone, 
                        date_of_birth = :dob, 
                        gender = :gender, 
                        address = :address, 
                        department_id = :department_id, 
                        current_semester = :current_semester, 
                        enrollment_date = :enrollment_date";

        $params = [
          ':student_id' => $formData['student_id'],
          ':name' => $formData['name'],
          ':email' => $formData['email'],
          ':phone' => $formData['phone'] ?: null,
          ':dob' => $formData['date_of_birth'] ?: null,
          ':gender' => $formData['gender'] ?: null,
          ':address' => $formData['address'] ?: null,
          ':department_id' => $formData['department_id'],
          ':current_semester' => $formData['current_semester'],
          ':enrollment_date' => $formData['enrollment_date'] ?: null,
          ':id' => $studentId
        ];

        // Include password if provided
        if (!empty($newPassword)) {
          $sql .= ", password = :password";
          $params[':password'] = password_hash($newPassword, PASSWORD_DEFAULT);
        }

        $sql .= " WHERE id = :id";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        $_SESSION['success'] = 'Student updated successfully.';
        header('Location: view-students.php');
        exit;
      } catch (PDOException $e) {
        error_log("Error updating student: " . $e->getMessage());
        $errors[] = 'An error occurred while updating the student.';
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
        <li class="breadcrumb-item active" aria-current="page">Edit Student</li>
      </ol>
    </nav>
    <h1 class="mb-3">
      <i class="bi bi-pencil-square me-2"></i>Edit Student
    </h1>
    <p class="text-muted">Editing: <strong><?= h($student['name']) ?></strong> (<?= h($student['student_id']) ?>)</p>
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

<div class="card">
  <div class="card-body">
    <form method="POST" action="" class="needs-validation" novalidate>
      <?= csrfInput() ?>

      <!-- Account Information -->
      <h5 class="mb-3"><i class="bi bi-key me-2"></i>Account Information</h5>
      <div class="row g-3 mb-4">
        <div class="col-md-4">
          <label for="student_id" class="form-label required-field">Student ID</label>
          <input type="text" class="form-control" id="student_id" name="student_id"
            value="<?= h($formData['student_id']) ?>" required
            data-check-unique="student_id" data-exclude-id="<?= $studentId ?>"
            pattern="[A-Za-z0-9]+">
          <div class="form-text">Alphanumeric only</div>
          <div class="invalid-feedback">Please enter a valid Student ID.</div>
        </div>
        <div class="col-md-4">
          <label for="new_password" class="form-label">New Password</label>
          <input type="password" class="form-control" id="new_password" name="new_password"
            minlength="8">
          <div class="form-text">Leave blank to keep current password</div>
        </div>
        <div class="col-md-4">
          <label for="confirm_password" class="form-label">Confirm New Password</label>
          <input type="password" class="form-control" id="confirm_password" name="confirm_password"
            minlength="8">
        </div>
      </div>

      <hr>

      <!-- Personal Information -->
      <h5 class="mb-3"><i class="bi bi-person me-2"></i>Personal Information</h5>
      <div class="row g-3 mb-4">
        <div class="col-md-6">
          <label for="name" class="form-label required-field">Full Name</label>
          <input type="text" class="form-control" id="name" name="name"
            value="<?= h($formData['name']) ?>" required>
          <div class="invalid-feedback">Please enter the student's name.</div>
        </div>
        <div class="col-md-6">
          <label for="email" class="form-label required-field">Email Address</label>
          <input type="email" class="form-control" id="email" name="email"
            value="<?= h($formData['email']) ?>" required>
          <div class="invalid-feedback">Please enter a valid email address.</div>
        </div>
        <div class="col-md-4">
          <label for="phone" class="form-label">Phone Number</label>
          <input type="tel" class="form-control" id="phone" name="phone"
            value="<?= h($formData['phone']) ?>" pattern="[0-9]{10,15}">
          <div class="form-text">10-15 digits only</div>
        </div>
        <div class="col-md-4">
          <label for="date_of_birth" class="form-label">Date of Birth</label>
          <input type="date" class="form-control" id="date_of_birth" name="date_of_birth"
            value="<?= h($formData['date_of_birth']) ?>">
        </div>
        <div class="col-md-4">
          <label for="gender" class="form-label">Gender</label>
          <select class="form-select" id="gender" name="gender">
            <option value="">-- Select --</option>
            <option value="Male" <?= $formData['gender'] === 'Male' ? 'selected' : '' ?>>Male</option>
            <option value="Female" <?= $formData['gender'] === 'Female' ? 'selected' : '' ?>>Female</option>
            <option value="Other" <?= $formData['gender'] === 'Other' ? 'selected' : '' ?>>Other</option>
          </select>
        </div>
        <div class="col-12">
          <label for="address" class="form-label">Address</label>
          <textarea class="form-control" id="address" name="address" rows="2"
            maxlength="500"><?= h($formData['address']) ?></textarea>
        </div>
      </div>

      <hr>

      <!-- Academic Information -->
      <h5 class="mb-3"><i class="bi bi-mortarboard me-2"></i>Academic Information</h5>
      <div class="row g-3 mb-4">
        <div class="col-md-4">
          <label for="department_id" class="form-label required-field">Department</label>
          <select class="form-select" id="department_id" name="department_id" required>
            <option value="">-- Select Department --</option>
            <?php foreach ($departments as $dept): ?>
              <option value="<?= $dept['id'] ?>" <?= $formData['department_id'] == $dept['id'] ? 'selected' : '' ?>>
                <?= h($dept['department_code']) ?> - <?= h($dept['department_name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
          <div class="invalid-feedback">Please select a department.</div>
        </div>
        <div class="col-md-4">
          <label for="current_semester" class="form-label required-field">Current Semester</label>
          <select class="form-select" id="current_semester" name="current_semester" required>
            <?php for ($i = 1; $i <= 8; $i++): ?>
              <option value="<?= $i ?>" <?= $formData['current_semester'] == $i ? 'selected' : '' ?>>
                Semester <?= $i ?>
              </option>
            <?php endfor; ?>
          </select>
        </div>
        <div class="col-md-4">
          <label for="enrollment_date" class="form-label">Enrollment Date</label>
          <input type="date" class="form-control" id="enrollment_date" name="enrollment_date"
            value="<?= h($formData['enrollment_date']) ?>">
        </div>
      </div>

      <hr>

      <div class="d-flex justify-content-between">
        <a href="view-students.php" class="btn btn-outline-secondary">
          <i class="bi bi-arrow-left me-1"></i>Cancel
        </a>
        <button type="submit" class="btn btn-primary">
          <i class="bi bi-check-lg me-1"></i>Save Changes
        </button>
      </div>
    </form>
  </div>
</div>

<script>
  // Password confirmation validation
  document.getElementById('confirm_password').addEventListener('input', function() {
    const password = document.getElementById('new_password').value;
    if (this.value && password !== this.value) {
      this.setCustomValidity('Passwords do not match');
    } else {
      this.setCustomValidity('');
    }
  });

  document.getElementById('new_password').addEventListener('input', function() {
    const confirm = document.getElementById('confirm_password');
    if (confirm.value && this.value !== confirm.value) {
      confirm.setCustomValidity('Passwords do not match');
    } else {
      confirm.setCustomValidity('');
    }
  });
</script>

<?php require_once __DIR__ . '/../../includes/admin-footer.php'; ?>