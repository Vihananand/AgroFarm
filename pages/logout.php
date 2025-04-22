<?php
include_once '../includes/config.php';
include_once '../includes/auth_functions.php';

// Destroy the session
session_destroy();

// Redirect to login page
header("Location: " . SITE_URL . "/pages/login.php");
exit();
?> 