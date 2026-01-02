<?php

/**
 * Check Unique AJAX Handler
 * College Student Portal
 * 
 * Checks if a student_id or admin_id is unique
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

session_start();

// Ensure admin is logged in
if (!isAdminLoggedIn()) {
  echo json_encode([
    'success' => false,
    'message' => 'Not authenticated'
  ]);
  exit;
}

// Validate CSRF token via header
$token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
if (!validateCsrfToken($token)) {
  echo json_encode([
    'success' => false,
    'message' => 'Invalid request'
  ]);
  exit;
}

$type = $_GET['type'] ?? '';
$value = trim($_GET['value'] ?? '');
$excludeId = $_GET['exclude_id'] ?? null;

if (empty($value)) {
  echo json_encode([
    'success' => false,
    'message' => 'Value is required'
  ]);
  exit;
}

try {
  $isUnique = false;

  switch ($type) {
    case 'student_id':
      $sql = "SELECT COUNT(*) FROM students WHERE student_id = :value";
      if ($excludeId) {
        $sql .= " AND id != :exclude_id";
      }
      $stmt = $pdo->prepare($sql);
      $params = [':value' => $value];
      if ($excludeId) {
        $params[':exclude_id'] = $excludeId;
      }
      $stmt->execute($params);
      $isUnique = ($stmt->fetchColumn() == 0);
      break;

    case 'admin_id':
      $sql = "SELECT COUNT(*) FROM admins WHERE admin_id = :value";
      if ($excludeId) {
        $sql .= " AND id != :exclude_id";
      }
      $stmt = $pdo->prepare($sql);
      $params = [':value' => $value];
      if ($excludeId) {
        $params[':exclude_id'] = $excludeId;
      }
      $stmt->execute($params);
      $isUnique = ($stmt->fetchColumn() == 0);
      break;

    case 'course_code':
      $sql = "SELECT COUNT(*) FROM courses WHERE course_code = :value";
      if ($excludeId) {
        $sql .= " AND id != :exclude_id";
      }
      $stmt = $pdo->prepare($sql);
      $params = [':value' => $value];
      if ($excludeId) {
        $params[':exclude_id'] = $excludeId;
      }
      $stmt->execute($params);
      $isUnique = ($stmt->fetchColumn() == 0);
      break;

    case 'department_code':
      $sql = "SELECT COUNT(*) FROM departments WHERE department_code = :value";
      if ($excludeId) {
        $sql .= " AND id != :exclude_id";
      }
      $stmt = $pdo->prepare($sql);
      $params = [':value' => $value];
      if ($excludeId) {
        $params[':exclude_id'] = $excludeId;
      }
      $stmt->execute($params);
      $isUnique = ($stmt->fetchColumn() == 0);
      break;

    default:
      echo json_encode([
        'success' => false,
        'message' => 'Invalid type'
      ]);
      exit;
  }

  echo json_encode([
    'success' => true,
    'unique' => $isUnique
  ]);
} catch (PDOException $e) {
  error_log("Check unique error: " . $e->getMessage());
  echo json_encode([
    'success' => false,
    'message' => 'Database error'
  ]);
}
