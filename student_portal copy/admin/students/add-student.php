<?php

/**
 * Add Student Page
 * Admin Module
 * 
 * Create new student record
 */

$pageTitle = 'Add Student';
require_once __DIR__ . '/../../includes/admin-header.php';
require_once __DIR__ . '/../../config/database.php';

// Fetch departments for dropdown
try {
  $deptStmt = $pdo->query("SELECT id, department_code, department_name FROM departments ORDER BY department_name");
  $departments = $deptStmt->fetchAll();
} catch (PDOException $e) {
  error_log("Error fetching departments: " . $e->getMessage());
  $departments = [];
}

$errors = [];
$formData = [
  'student_id' => '',
  'name' => '',
  'email' => '',
  'phone' => '',
  'date_of_birth' => '',
  'gender' => '',
  'address' => '',
  'department_id' => '',
  'current_semester' => '1',
  'enrollment_date' => date('Y-m-d')
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
      'enrollment_date' => trim($_POST['enrollment_date'] ?? date('Y-m-d'))
    ];
    $password = $_POST['password'] ?? '';
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

    if (empty($password)) {
      $errors[] = 'Password is required.';
    } elseif (!validatePassword($password)) {
      $errors[] = 'Password must be at least 8 characters.';
    }

    if ($password !== $confirmPassword) {
      $errors[] = 'Passwords do not match.';
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

    // Check for duplicate student_id
    if (empty($errors)) {
      try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM students WHERE student_id = :student_id");
        $stmt->execute([':student_id' => $formData['student_id']]);
        if ($stmt->fetchColumn() > 0) {
          $errors[] = 'Student ID already exists.';
        }
      } catch (PDOException $e) {
        error_log("Error checking student ID: " . $e->getMessage());
      }
    }

    // Insert if no errors
    if (empty($errors)) {
      try {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("
                    INSERT INTO students (student_id, name, email, password, phone, date_of_birth, 
                                         gender, address, department_id, current_semester, enrollment_date)
                    VALUES (:student_id, :name, :email, :password, :phone, :dob, 
                            :gender, :address, :department_id, :current_semester, :enrollment_date)
                ");
        $stmt->execute([
          ':student_id' => $formData['student_id'],
          ':name' => $formData['name'],
          ':email' => $formData['email'],
          ':password' => $hashedPassword,
          ':phone' => $formData['phone'] ?: null,
          ':dob' => $formData['date_of_birth'] ?: null,
          ':gender' => $formData['gender'] ?: null,
          ':address' => $formData['address'] ?: null,
          ':department_id' => $formData['department_id'],
          ':current_semester' => $formData['current_semester'],
          ':enrollment_date' => $formData['enrollment_date']
        ]);

        $_SESSION['success'] = 'Student added successfully.';
        header('Location: view-students.php');
        exit;
      } catch (PDOException $e) {
        error_log("Error adding student: " . $e->getMessage());
        $errors[] = 'An error occurred while adding the student.';
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
        <li class="breadcrumb-item active" aria-current="page">Add Student</li>
      </ol>
    </nav>
    <h1 class="mb-3">
      <i class="bi bi-person-plus me-2"></i>Add Student
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
            data-check-unique="student_id" pattern="[A-Za-z0-9]+"
            placeholder="e.g., 2024CS001">
          <div class="form-text">Alphanumeric only</div>
          <div class="invalid-feedback">Please enter a valid Student ID.</div>
        </div>
        <div class="col-md-4">
          <label for="password" class="form-label required-field">Password</label>
          <input type="password" class="form-control" id="password" name="password"
            minlength="8" required>
          <div class="form-text">Minimum 8 characters</div>
          <div class="invalid-feedback">Password must be at least 8 characters.</div>
        </div>
        <div class="col-md-4">
          <label for="confirm_password" class="form-label required-field">Confirm Password</label>
          <input type="password" class="form-control" id="confirm_password" name="confirm_password"
            minlength="8" required>
          <div class="invalid-feedback">Passwords must match.</div>
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
            value="<?= h($formData['phone']) ?>" pattern="[0-9]{10,15}"
            placeholder="e.g., 5551234567">
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
          <i class="bi bi-plus-lg me-1"></i>Add Student
        </button>
      </div>
    </form>
  </div>
</div>

<script>
  // Password confirmation validation
  document.getElementById('confirm_password').addEventListener('input', function() {
    const password = document.getElementById('password').value;
    if (this.value && password !== this.value) {
      this.setCustomValidity('Passwords do not match');
    } else {
      this.setCustomValidity('');
    }
  });

  document.getElementById('password').addEventListener('input', function() {
    const confirm = document.getElementById('confirm_password');
    if (confirm.value && this.value !== confirm.value) {
      confirm.setCustomValidity('Passwords do not match');
    } else {
      confirm.setCustomValidity('');
    }
  });
</script>

<?php require_once __DIR__ . '/../../includes/admin-footer.php'; ?>