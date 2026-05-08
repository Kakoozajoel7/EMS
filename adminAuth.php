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

if (($_SESSION['role'] ?? '') !== 'admin' || !isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}
