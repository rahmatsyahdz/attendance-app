<?php
session_start();
require_once __DIR__ . '/includes/functions.php';

// Clear all session data
$_SESSION = array();

// Destroy session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 42000, '/');
}

// Destroy session
session_destroy();

// Redirect to login page
header('Location: /index.php');
exit();
?>
