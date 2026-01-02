<?php

/**
 * Logout Handler
 * College Student Portal
 * 
 * Destroys session, clears cookies, redirects to index
 */

// Include functions
require_once __DIR__ . '/includes/functions.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
  configureSession();
  session_start();
}

// Destroy session completely
destroySession();

// Redirect to landing page
header('Location: index.php');
exit;
