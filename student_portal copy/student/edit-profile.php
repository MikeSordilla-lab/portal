<?php

/**
 * Edit Profile Page
 * College Student Portal
 * 
 * Update editable profile fields
 */

$pageTitle = 'Edit Profile';
require_once __DIR__ . '/../includes/student-header.php';
require_once __DIR__ . '/../config/database.php';

// Get student's database ID
$studentDbId = $_SESSION['student_db_id'];

// Fetch current profile
try {
  $stmt = $pdo->prepare("SELECT * FROM students WHERE id = :id");
  $stmt->execute([':id' => $studentDbId]);
  $student = $stmt->fetch();

  if (!$student) {
    $_SESSION['error'] = 'Profile not found.';
    header('Location: dashboard.php');
    exit;
  }
} catch (PDOException $e) {
  error_log("Profile error: " . $e->getMessage());
  $_SESSION['error'] = 'Error loading profile.';
  header('Location: dashboard.php');
  exit;
}

$errors = [];

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Validate CSRF token
  if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
    $errors[] = 'Invalid request. Please refresh the page and try again.';
  } else {
    // Get and sanitize input
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $dateOfBirth = trim($_POST['date_of_birth'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    $address = trim($_POST['address'] ?? '');

    // Validation
    if (empty($email)) {
      $errors[] = 'Email is required.';
    } elseif (!validateEmail($email)) {
      $errors[] = 'Please enter a valid email address.';
    }

    if (!empty($phone) && !validatePhone($phone)) {
      $errors[] = 'Phone number must be 10-15 digits.';
    }

    if (!empty($dateOfBirth) && !validateDate($dateOfBirth)) {
      $errors[] = 'Please enter a valid date of birth.';
    }

    if (!empty($gender) && !validateGender($gender)) {
      $errors[] = 'Please select a valid gender.';
    }

    if (strlen($address) > 500) {
      $errors[] = 'Address must be 500 characters or less.';
    }

    // Update if no errors
    if (empty($errors)) {
      try {
        $stmt = $pdo->prepare("
                    UPDATE students 
                    SET email = :email, 
                        phone = :phone, 
                        date_of_birth = :dob, 
                        gender = :gender, 
                        address = :address
                    WHERE id = :id
                ");
        $stmt->execute([
          ':email' => $email,
          ':phone' => $phone ?: null,
          ':dob' => $dateOfBirth ?: null,
          ':gender' => $gender ?: null,
          ':address' => $address ?: null,
          ':id' => $studentDbId
        ]);

        // Update session email
        $_SESSION['student_email'] = $email;

        $_SESSION['success'] = 'Profile updated successfully.';
        header('Location: profile.php');
        exit;
      } catch (PDOException $e) {
        error_log("Profile update error: " . $e->getMessage());
        $errors[] = 'An error occurred while updating your profile.';
      }
    }

    // Update student array with submitted values for redisplay
    $student['email'] = $email;
    $student['phone'] = $phone;
    $student['date_of_birth'] = $dateOfBirth;
    $student['gender'] = $gender;
    $student['address'] = $address;
  }
}
?>

<div class="row mb-4">
  <div class="col-12">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="profile.php">Profile</a></li>
        <li class="breadcrumb-item active" aria-current="page">Edit Profile</li>
      </ol>
    </nav>
    <h1 class="mb-3">
      <i class="bi bi-pencil-square me-2"></i>Edit Profile
    </h1>
    <p class="text-muted">Update your personal information below.</p>
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
    <div class="card">
      <div class="card-body">
        <form method="POST" action="" class="needs-validation" novalidate>
          <?= csrfInput() ?>

          <!-- Email -->
          <div class="mb-3">
            <label for="email" class="form-label required-field">Email Address</label>
            <input type="email" class="form-control" id="email" name="email"
              value="<?= h($student['email']) ?>" required>
            <div class="invalid-feedback">Please enter a valid email address.</div>
          </div>

          <!-- Phone -->
          <div class="mb-3">
            <label for="phone" class="form-label">Phone Number</label>
            <input type="tel" class="form-control" id="phone" name="phone"
              value="<?= h($student['phone']) ?>"
              pattern="[0-9]{10,15}"
              placeholder="e.g., 5551234567">
            <div class="form-text">10-15 digits only</div>
            <div class="invalid-feedback">Please enter a valid phone number (10-15 digits).</div>
          </div>

          <!-- Date of Birth -->
          <div class="mb-3">
            <label for="date_of_birth" class="form-label">Date of Birth</label>
            <input type="date" class="form-control" id="date_of_birth" name="date_of_birth"
              value="<?= h($student['date_of_birth']) ?>">
          </div>

          <!-- Gender -->
          <div class="mb-3">
            <label for="gender" class="form-label">Gender</label>
            <select class="form-select" id="gender" name="gender">
              <option value="">-- Select --</option>
              <option value="Male" <?= $student['gender'] === 'Male' ? 'selected' : '' ?>>Male</option>
              <option value="Female" <?= $student['gender'] === 'Female' ? 'selected' : '' ?>>Female</option>
              <option value="Other" <?= $student['gender'] === 'Other' ? 'selected' : '' ?>>Other</option>
            </select>
          </div>

          <!-- Address -->
          <div class="mb-3">
            <label for="address" class="form-label">Address</label>
            <textarea class="form-control" id="address" name="address" rows="3"
              maxlength="500"><?= h($student['address']) ?></textarea>
            <div class="form-text">Maximum 500 characters</div>
          </div>

          <hr>

          <div class="d-flex justify-content-between">
            <a href="profile.php" class="btn btn-outline-secondary">
              <i class="bi bi-arrow-left me-1"></i>Cancel
            </a>
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-check-lg me-1"></i>Save Changes
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="col-lg-4">
    <div class="card">
      <div class="card-header">
        <i class="bi bi-camera me-2"></i>Profile Picture
      </div>
      <div class="card-body text-center">
        <img src="../uploads/profile_pictures/<?= h($student['profile_picture'] ?: 'default.jpg') ?>"
          alt="Profile Picture"
          id="profile-picture-preview"
          class="profile-picture mb-3"
          onerror="this.src='../assets/images/default-avatar.png'">

        <form id="photo-upload-form" enctype="multipart/form-data">
          <?= csrfInput() ?>
          <div class="mb-3">
            <input type="file" class="form-control" id="profile-picture-input"
              name="profile_picture" accept="image/jpeg,image/png">
            <div class="form-text">JPG or PNG, max 2MB</div>
          </div>
        </form>
      </div>
    </div>

    <div class="card mt-3">
      <div class="card-header">
        <i class="bi bi-info-circle me-2"></i>Note
      </div>
      <div class="card-body">
        <p class="small text-muted mb-0">
          The following fields cannot be changed from here:
        </p>
        <ul class="small text-muted mb-0">
          <li>Student ID</li>
          <li>Full Name</li>
          <li>Department</li>
          <li>Current Semester</li>
          <li>Enrollment Date</li>
        </ul>
        <p class="small text-muted mt-2 mb-0">
          Contact the administration to update these fields.
        </p>
      </div>
    </div>
  </div>
</div>

<script>
  // Photo upload preview and AJAX
  document.getElementById('profile-picture-input').addEventListener('change', function(event) {
    const file = event.target.files[0];
    if (!file) return;

    // Validate file type
    if (!['image/jpeg', 'image/png'].includes(file.type)) {
      alert('Only JPG and PNG files are allowed');
      this.value = '';
      return;
    }

    // Validate file size (2MB)
    if (file.size > 2097152) {
      alert('File size exceeds 2MB limit');
      this.value = '';
      return;
    }

    // Preview
    const reader = new FileReader();
    reader.onload = function(e) {
      document.getElementById('profile-picture-preview').src = e.target.result;
    };
    reader.readAsDataURL(file);

    // Upload via AJAX
    const formData = new FormData();
    formData.append('csrf_token', document.querySelector('[name="csrf_token"]').value);
    formData.append('profile_picture', file);

    fetch('../ajax/upload-photo.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          alert(data.message);
        } else {
          alert(data.message || 'Upload failed');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while uploading the photo');
      });
  });
</script>

<?php require_once __DIR__ . '/../includes/student-footer.php'; ?>