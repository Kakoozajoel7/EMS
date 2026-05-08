<?php
session_start();
$timeout_seconds = 20 * 60;

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_seconds) {
    session_unset();
    session_destroy();
    header("Location: index.php?timeout=1");
    exit();
}

$_SESSION['last_activity'] = time();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

if (($_SESSION['role'] ?? '') === 'admin') {
    header("Location: adminDashboard.php");
    exit();
}
