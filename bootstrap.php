<?php
session_start();
require_once __DIR__ . '/config/database.php';

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$currentPage = basename($_SERVER['PHP_SELF'], '.php');

// Redirect to dashboard if already logged in and going to auth pages
if ($isLoggedIn && in_array($currentPage, ['login', 'daftar', 'lupa-password'])) {
    header('Location: dashboard.php');
    exit;
}
?>
