<?php
/**
 * Helper Functions
 * Common utility functions used throughout the application
 */

// Start session if not already started
function initSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

// Check if user is logged in
function isLoggedIn() {
    initSession();
    return isset($_SESSION['user_id']) && isset($_SESSION['role']);
}

// Check if user is admin
function isAdmin() {
    initSession();
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Redirect to login if not authenticated
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /index.php');
        exit();
    }
}

// Redirect to login if not admin
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: /employee/dashboard.php');
        exit();
    }
}

// Sanitize input data
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Validate email
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Format date
function formatDate($date, $format = 'Y-m-d') {
    return date($format, strtotime($date));
}

// Format time
function formatTime($time, $format = 'H:i:s') {
    return date($format, strtotime($time));
}

// Get current user data
function getCurrentUser() {
    initSession();
    if (!isLoggedIn()) {
        return null;
    }
    
    require_once __DIR__ . '/../config/database.php';
    $db = getDB();
    
    $stmt = $db->prepare("SELECT u.*, d.name as department_name 
                          FROM users u 
                          LEFT JOIN departments d ON u.department_id = d.id 
                          WHERE u.id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

// Upload file handler
function uploadFile($file, $directory, $allowed_types = ['jpg', 'jpeg', 'png']) {
    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        return ['success' => false, 'message' => 'No file uploaded'];
    }
    
    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($file_ext, $allowed_types)) {
        return ['success' => false, 'message' => 'Invalid file type'];
    }
    
    $file_name = uniqid() . '_' . time() . '.' . $file_ext;
    $file_path = $directory . '/' . $file_name;
    
    if (!file_exists($directory)) {
        mkdir($directory, 0777, true);
    }
    
    if (move_uploaded_file($file['tmp_name'], $file_path)) {
        return ['success' => true, 'filename' => $file_name, 'path' => $file_path];
    }
    
    return ['success' => false, 'message' => 'Failed to upload file'];
}

// Calculate attendance status based on clock-in time
function calculateAttendanceStatus($clock_in_time, $late_threshold = '09:00:00') {
    if (strtotime($clock_in_time) > strtotime($late_threshold)) {
        return 'late';
    }
    return 'present';
}

// Get attendance by user and date
function getAttendanceByDate($user_id, $date) {
    require_once __DIR__ . '/../config/database.php';
    $db = getDB();
    
    $stmt = $db->prepare("SELECT * FROM attendance WHERE user_id = ? AND date = ?");
    $stmt->execute([$user_id, $date]);
    return $stmt->fetch();
}

// Check if user has clocked in today
function hasClockedInToday($user_id) {
    $attendance = getAttendanceByDate($user_id, date('Y-m-d'));
    return $attendance && $attendance['clock_in'] !== null;
}

// Check if user has clocked out today
function hasClockedOutToday($user_id) {
    $attendance = getAttendanceByDate($user_id, date('Y-m-d'));
    return $attendance && $attendance['clock_out'] !== null;
}

// Get user by ID
function getUserById($user_id) {
    require_once __DIR__ . '/../config/database.php';
    $db = getDB();
    
    $stmt = $db->prepare("SELECT u.*, d.name as department_name 
                          FROM users u 
                          LEFT JOIN departments d ON u.department_id = d.id 
                          WHERE u.id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetch();
}

// Show success message
function setFlashMessage($type, $message) {
    initSession();
    $_SESSION['flash_type'] = $type;
    $_SESSION['flash_message'] = $message;
}

// Get and clear flash message
function getFlashMessage() {
    initSession();
    if (isset($_SESSION['flash_message'])) {
        $message = [
            'type' => $_SESSION['flash_type'],
            'message' => $_SESSION['flash_message']
        ];
        unset($_SESSION['flash_type']);
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}

// Prevent XSS
function escape($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}
?>
