<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'student_mgmt');

define('UPLOAD_DIR', 'uploads/');
define('MAX_FILE_SIZE', 5242880);
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif']);

ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0);

function getDBConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
    } catch (PDOException $e) {
        die("Database connection failed: " . $e->getMessage());
    }
}

function startSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function isLoggedIn() {
    startSession();
    return isset($_SESSION['user_id']) && isset($_SESSION['role']);
}

function isAdmin() {
    return isLoggedIn() && $_SESSION['role'] === 'admin';
}

function isStudent() {
    return isLoggedIn() && $_SESSION['role'] === 'student';
}

function isFaculty() {
    return isLoggedIn() && $_SESSION['role'] === 'faculty';
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validateCNIC($cnic) {
    return preg_match('/^\d{5}-\d{7}-\d{1}$/', $cnic);
}

function validatePhone($phone) {
    return preg_match('/^0[0-9]{10}$/', $phone);
}

function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: admin/dashboard.php');
        exit();
    }
}

function requireStudent() {
    requireLogin();
    if (!isStudent()) {
        header('Location: user/dashboard.php');
        exit();
    }
}

function requireFaculty() {
    requireLogin();
    if (!isFaculty()) {
        header('Location: faculty/dashboard.php');
        exit();
    }
}
?>

