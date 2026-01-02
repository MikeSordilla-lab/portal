<?php

/**
 * Database Configuration
 * College Student Portal
 * 
 * PDO with native prepared statements per constitution requirements
 */

$host = 'localhost';
$port = 3307;
$dbname = 'student_portal';
$username = 'root';
$password = '';  // Default XAMPP has no password

try {
  $pdo = new PDO(
    "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4",
    $username,
    $password,
    [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_EMULATE_PREPARES => false,  // Native prepared statements
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]
  );
} catch (PDOException $e) {
  // Log error but don't expose details
  error_log("Database connection failed: " . $e->getMessage());
  die("Database connection failed. Please try again later.");
}
