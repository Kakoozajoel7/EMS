<?php
if (php_sapi_name() !== 'cli') {
    die('Access denied. This script can only be run from the command line.');
}

require_once 'dbconnect.php';

// Set the new password (you can change 'admin123' to your desired password)
$psswd = 'Admin@123.'; // Example strong password
$pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/';
if(strlen($psswd) < 8 || !preg_match($pattern , $psswd)) {
    die('Invalid password. It must be at least 8 characters long and contain uppercase, lowercase, numbers, and special characters.');
}
$password = password_hash($psswd, PASSWORD_DEFAULT);
$sql_check = 'SELECT Id FROM admin WHERE Id = 1';
$result = mysqli_query($conn, $sql_check);
if (mysqli_num_rows($result) > 0) {
    $sql = 'UPDATE admin SET Password = ? WHERE Id = 1';
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $password);
    $stmt->execute();
    echo "Admin password updated successfully.";
} else {
    echo "Admin user not found. Please ensure an admin user with Id=1 exists.";
}