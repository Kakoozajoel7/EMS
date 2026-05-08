<?php
require 'auth.php';
require 'dbconnect.php';

$rsvp_id = intval($_GET['rsvp_id'] ?? 0);
$user_id = $_SESSION['user_id'];

if ($rsvp_id) {
    $stmt = mysqli_prepare($conn, "DELETE FROM rsvp WHERE Id = ? AND UserId = ?");
    mysqli_stmt_bind_param($stmt, "ii", $rsvp_id, $user_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

header("Location: StudentBookingandHistory.php");
exit();
