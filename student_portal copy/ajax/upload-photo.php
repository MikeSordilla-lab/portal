<?php

/**
 * Photo Upload AJAX Handler
 * College Student Portal
 * 
 * Handles profile picture uploads for students
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

session_start();

// Ensure student is logged in
if (!isStudentLoggedIn()) {
  echo json_encode([
    'success' => false,
    'message' => 'Not authenticated'
  ]);
  exit;
}

// Validate CSRF token
if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
  echo json_encode([
    'success' => false,
    'message' => 'Invalid request'
  ]);
  exit;
}

// Check if file was uploaded
if (!isset($_FILES['profile_picture']) || $_FILES['profile_picture']['error'] !== UPLOAD_ERR_OK) {
  $errorMessage = 'No file uploaded';
  if (isset($_FILES['profile_picture']['error'])) {
    switch ($_FILES['profile_picture']['error']) {
      case UPLOAD_ERR_INI_SIZE:
      case UPLOAD_ERR_FORM_SIZE:
        $errorMessage = 'File is too large';
        break;
      case UPLOAD_ERR_PARTIAL:
        $errorMessage = 'File was only partially uploaded';
        break;
      case UPLOAD_ERR_NO_FILE:
        $errorMessage = 'No file was uploaded';
        break;
    }
  }
  echo json_encode([
    'success' => false,
    'message' => $errorMessage
  ]);
  exit;
}

// Validate the uploaded file
$file = $_FILES['profile_picture'];
$validationResult = validateProfilePicture($file);

if ($validationResult !== true) {
  echo json_encode([
    'success' => false,
    'message' => $validationResult
  ]);
  exit;
}

// Create upload directory if it doesn't exist
$uploadDir = __DIR__ . '/../uploads/profile_pictures/';
if (!is_dir($uploadDir)) {
  mkdir($uploadDir, 0755, true);
}

// Generate unique filename
$extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$newFilename = $_SESSION['student_id'] . '_' . time() . '.' . $extension;
$targetPath = $uploadDir . $newFilename;

// Move the uploaded file
if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
  echo json_encode([
    'success' => false,
    'message' => 'Failed to save file'
  ]);
  exit;
}

// Get current profile picture
try {
  $stmt = $pdo->prepare("SELECT profile_picture FROM students WHERE id = :id");
  $stmt->execute([':id' => $_SESSION['student_db_id']]);
  $student = $stmt->fetch();

  $oldPicture = $student['profile_picture'];

  // Update database
  $stmt = $pdo->prepare("UPDATE students SET profile_picture = :picture WHERE id = :id");
  $stmt->execute([
    ':picture' => $newFilename,
    ':id' => $_SESSION['student_db_id']
  ]);

  // Delete old picture if it exists and isn't the default
  if ($oldPicture && $oldPicture !== 'default.jpg') {
    $oldPath = $uploadDir . $oldPicture;
    if (file_exists($oldPath)) {
      unlink($oldPath);
    }
  }

  echo json_encode([
    'success' => true,
    'message' => 'Profile picture updated successfully',
    'filename' => $newFilename
  ]);
} catch (PDOException $e) {
  error_log("Photo upload error: " . $e->getMessage());

  // Remove uploaded file if database update failed
  if (file_exists($targetPath)) {
    unlink($targetPath);
  }

  echo json_encode([
    'success' => false,
    'message' => 'Database error'
  ]);
}
