<?php

/**
 * Shared Functions
 * College Student Portal
 * 
 * CSRF protection, validation, sanitization, GPA calculation
 */

// ============================================================================
// CSRF PROTECTION
// ============================================================================

/**
 * Generate CSRF token
 * Uses cryptographically secure random bytes
 */
function generateCsrfToken(): string
{
  if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
  }
  return $_SESSION['csrf_token'];
}

/**
 * Validate CSRF token
 * Uses hash_equals() to prevent timing attacks
 */
function validateCsrfToken(?string $token): bool
{
  if (empty($token) || empty($_SESSION['csrf_token'])) {
    return false;
  }
  return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Output CSRF hidden input field
 */
function csrfInput(): string
{
  return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(generateCsrfToken(), ENT_QUOTES, 'UTF-8') . '">';
}

// ============================================================================
// SESSION MANAGEMENT
// ============================================================================

/**
 * Configure secure session settings
 */
function configureSession(): void
{
  ini_set('session.cookie_httponly', 1);
  ini_set('session.cookie_samesite', 'Strict');
  ini_set('session.use_strict_mode', 1);

  session_set_cookie_params([
    'lifetime' => 1800,  // 30 minutes
    'path' => '/',
    'domain' => '',
    'secure' => isset($_SERVER['HTTPS']),
    'httponly' => true,
    'samesite' => 'Strict'
  ]);
}

/**
 * Check session timeout (30 minutes inactivity)
 * Returns true if session is valid, false if expired
 */
function checkSessionTimeout(): bool
{
  $timeout = 1800; // 30 minutes

  if (isset($_SESSION['last_activity'])) {
    if (time() - $_SESSION['last_activity'] > $timeout) {
      return false;
    }
  }

  $_SESSION['last_activity'] = time();
  return true;
}

/**
 * Destroy session completely
 */
function destroySession(): void
{
  $_SESSION = [];

  if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
      session_name(),
      '',
      time() - 42000,
      $params["path"],
      $params["domain"],
      $params["secure"],
      $params["httponly"]
    );
  }

  session_destroy();
}

/**
 * Require student authentication
 */
function requireStudentAuth(): void
{
  if (!isset($_SESSION['student_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'student') {
    header('Location: ../student-login.php');
    exit;
  }

  if (!checkSessionTimeout()) {
    destroySession();
    session_start();
    $_SESSION['error'] = 'Session expired. Please log in again.';
    header('Location: ../student-login.php');
    exit;
  }
}

/**
 * Require admin authentication
 */
function requireAdminAuth(): void
{
  if (!isset($_SESSION['admin_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../admin-login.php');
    exit;
  }

  if (!checkSessionTimeout()) {
    destroySession();
    session_start();
    $_SESSION['error'] = 'Session expired. Please log in again.';
    header('Location: ../admin-login.php');
    exit;
  }
}

// ============================================================================
// LOGIN SECURITY (Account Lockout)
// ============================================================================

/**
 * Track failed login attempts
 * Lock account after 5 failed attempts for 15 minutes
 */
function trackLoginAttempt(PDO $pdo, string $identifier, string $type): array
{
  $lockoutTime = 900; // 15 minutes
  $maxAttempts = 5;

  $table = ($type === 'student') ? 'students' : 'admins';
  $idColumn = ($type === 'student') ? 'student_id' : 'admin_id';

  // Check if we have login attempts tracking in session
  $key = 'login_attempts_' . $type . '_' . $identifier;

  if (!isset($_SESSION[$key])) {
    $_SESSION[$key] = [
      'count' => 0,
      'lockout_until' => 0
    ];
  }

  // Check if currently locked out
  if ($_SESSION[$key]['lockout_until'] > time()) {
    $remaining = ceil(($_SESSION[$key]['lockout_until'] - time()) / 60);
    return [
      'locked' => true,
      'message' => "Account locked. Try again in $remaining minute(s)."
    ];
  }

  // Reset if lockout has expired
  if ($_SESSION[$key]['lockout_until'] <= time() && $_SESSION[$key]['count'] >= $maxAttempts) {
    $_SESSION[$key] = [
      'count' => 0,
      'lockout_until' => 0
    ];
  }

  return [
    'locked' => false,
    'attempts' => $_SESSION[$key]['count']
  ];
}

/**
 * Record a failed login attempt
 */
function recordFailedAttempt(string $identifier, string $type): void
{
  $lockoutTime = 900; // 15 minutes
  $maxAttempts = 5;

  $key = 'login_attempts_' . $type . '_' . $identifier;

  if (!isset($_SESSION[$key])) {
    $_SESSION[$key] = [
      'count' => 0,
      'lockout_until' => 0
    ];
  }

  $_SESSION[$key]['count']++;

  if ($_SESSION[$key]['count'] >= $maxAttempts) {
    $_SESSION[$key]['lockout_until'] = time() + $lockoutTime;
  }
}

/**
 * Clear login attempts after successful login
 */
function clearLoginAttempts(string $identifier, string $type): void
{
  $key = 'login_attempts_' . $type . '_' . $identifier;
  unset($_SESSION[$key]);
}

// ============================================================================
// INPUT VALIDATION
// ============================================================================

/**
 * Sanitize output for HTML
 */
function h($string): string
{
  return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Validate email format
 */
function validateEmail(string $email): bool
{
  return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate phone number (10-15 digits)
 */
function validatePhone(string $phone): bool
{
  $phone = preg_replace('/[^0-9]/', '', $phone);
  return strlen($phone) >= 10 && strlen($phone) <= 15;
}

/**
 * Validate date format (YYYY-MM-DD)
 */
function validateDate(string $date): bool
{
  $d = DateTime::createFromFormat('Y-m-d', $date);
  return $d && $d->format('Y-m-d') === $date;
}

/**
 * Validate student ID format (YYYYNNNNNN - 10 chars)
 */
function validateStudentId(string $studentId): bool
{
  return preg_match('/^\d{4}[A-Za-z]{2}\d{3,4}$/', $studentId) === 1;
}

/**
 * Validate password (minimum 8 characters)
 */
function validatePassword(string $password): bool
{
  return strlen($password) >= 8;
}

/**
 * Validate gender value
 */
function validateGender(string $gender): bool
{
  return in_array($gender, ['Male', 'Female', 'Other']);
}

/**
 * Validate semester (1-8)
 */
function validateSemester(int $semester): bool
{
  return $semester >= 1 && $semester <= 8;
}

/**
 * Validate credits (1-6)
 */
function validateCredits(int $credits): bool
{
  return $credits >= 1 && $credits <= 6;
}

/**
 * Validate grade value
 */
function validateGrade(string $grade): bool
{
  $validGrades = ['A', 'A-', 'B+', 'B', 'B-', 'C+', 'C', 'C-', 'D+', 'D', 'F'];
  return in_array($grade, $validGrades);
}

/**
 * Validate day of week (1-7)
 */
function validateDayOfWeek(int $day): bool
{
  return $day >= 1 && $day <= 7;
}

/**
 * Validate time format (HH:MM)
 */
function validateTime(string $time): bool
{
  return preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $time) === 1;
}

// ============================================================================
// GPA CALCULATION
// ============================================================================

/**
 * Grade point values
 */
function getGradePoints(): array
{
  return [
    'A' => 4.0,
    'A-' => 3.7,
    'B+' => 3.3,
    'B' => 3.0,
    'B-' => 2.7,
    'C+' => 2.3,
    'C' => 2.0,
    'C-' => 1.7,
    'D+' => 1.3,
    'D' => 1.0,
    'F' => 0.0
  ];
}

/**
 * Calculate GPA from array of grades
 * Each grade should have 'grade' and 'credits' keys
 */
function calculateGPA(array $grades): float
{
  $gradePoints = getGradePoints();
  $totalPoints = 0;
  $totalCredits = 0;

  foreach ($grades as $grade) {
    if (!isset($grade['grade']) || !isset($grade['credits'])) {
      continue;
    }

    $points = $gradePoints[$grade['grade']] ?? 0;
    $credits = (int) $grade['credits'];

    $totalPoints += $points * $credits;
    $totalCredits += $credits;
  }

  return $totalCredits > 0 ? round($totalPoints / $totalCredits, 2) : 0.00;
}

// ============================================================================
// FILE UPLOAD VALIDATION
// ============================================================================

/**
 * Validate image upload (7-step verification per constitution)
 * Returns new filename on success, false on failure
 */
function validateImageUpload(array $file): array
{
  $allowedMimes = ['image/jpeg', 'image/png'];
  $maxSize = 2097152; // 2MB
  $result = ['success' => false, 'message' => '', 'filename' => ''];

  // Step 1: Check for upload errors
  if ($file['error'] !== UPLOAD_ERR_OK) {
    $result['message'] = 'File upload failed.';
    return $result;
  }

  // Step 2: Check file size
  if ($file['size'] > $maxSize) {
    $result['message'] = 'File size exceeds 2MB limit.';
    return $result;
  }

  // Step 3: Check file size (not empty)
  if ($file['size'] === 0) {
    $result['message'] = 'Uploaded file is empty.';
    return $result;
  }

  // Step 4: Verify MIME type via mime_content_type()
  $mime = mime_content_type($file['tmp_name']);
  if (!in_array($mime, $allowedMimes)) {
    $result['message'] = 'Only JPG and PNG files are allowed.';
    return $result;
  }

  // Step 5: Verify it's actually an image via getimagesize()
  $imageInfo = getimagesize($file['tmp_name']);
  if ($imageInfo === false) {
    $result['message'] = 'Invalid image file.';
    return $result;
  }

  // Step 6: Verify image type matches MIME
  if (!in_array($imageInfo[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG])) {
    $result['message'] = 'Only JPG and PNG files are allowed.';
    return $result;
  }

  // Step 7: Generate safe filename
  $ext = ($mime === 'image/png') ? '.png' : '.jpg';
  $newName = uniqid() . '_' . time() . $ext;

  $result['success'] = true;
  $result['filename'] = $newName;
  return $result;
}

// ============================================================================
// AUDIT LOGGING
// ============================================================================

/**
 * Log an audit event
 */
function logAudit(string $action, string $details, ?int $adminId = null): void
{
  $logFile = __DIR__ . '/../logs/audit.log';
  $logDir = dirname($logFile);

  if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
  }

  $timestamp = date('Y-m-d H:i:s');
  $adminInfo = $adminId ? "Admin ID: $adminId" : 'System';
  $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';

  $logEntry = "[$timestamp] [$adminInfo] [$ip] $action: $details" . PHP_EOL;

  file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
}

// ============================================================================
// HELPER FUNCTIONS
// ============================================================================

/**
 * Get day name from day number (1-7)
 */
function getDayName(int $day): string
{
  $days = [
    1 => 'Monday',
    2 => 'Tuesday',
    3 => 'Wednesday',
    4 => 'Thursday',
    5 => 'Friday',
    6 => 'Saturday',
    7 => 'Sunday'
  ];
  return $days[$day] ?? 'Unknown';
}

/**
 * Format time for display (24h to 12h)
 */
function formatTime(string $time): string
{
  return date('g:i A', strtotime($time));
}

/**
 * Set security headers
 */
function setSecurityHeaders(): void
{
  header('X-Frame-Options: DENY');
  header('X-Content-Type-Options: nosniff');
  header('X-XSS-Protection: 1; mode=block');
  header('Referrer-Policy: strict-origin-when-cross-origin');
}

/**
 * Redirect with message
 */
function redirectWithMessage(string $url, string $message, string $type = 'success'): void
{
  $_SESSION[$type] = $message;
  header("Location: $url");
  exit;
}

/**
 * Get and clear session message
 */
function getFlashMessage(string $type = 'success'): ?string
{
  if (isset($_SESSION[$type])) {
    $message = $_SESSION[$type];
    unset($_SESSION[$type]);
    return $message;
  }
  return null;
}

/**
 * Display alert message (Bootstrap 5)
 */
function displayAlert(?string $message, string $type = 'success'): string
{
  if (!$message) return '';

  $alertType = match ($type) {
    'error' => 'danger',
    'warning' => 'warning',
    'info' => 'info',
    default => 'success'
  };

  return '<div class="alert alert-' . $alertType . ' alert-dismissible fade show" role="alert">' .
    h($message) .
    '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' .
    '</div>';
}
