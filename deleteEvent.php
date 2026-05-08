<?php
require 'adminAuth.php';
require_once 'dbconnect.php';

$id = intval($_GET['id'] ?? 0);

if ($id) {
    $s = $conn->prepare("SELECT Name, Image FROM events WHERE Id = ?");
    $s->bind_param('i', $id);
    $s->execute();
    $row = $s->get_result()->fetch_assoc();
    $s->close();

    if ($row) {
        $del = $conn->prepare("DELETE FROM events WHERE Id = ?");
        $del->bind_param('i', $id);
        if ($del->execute()) {
            // Delete image file if exists
            if ($row['Image'] && file_exists('uploads/' . $row['Image'])) {
                unlink('uploads/' . $row['Image']);
            }
            $_SESSION['flash'] = ['type' => 'success', 'msg' => "Event \"{$row['Name']}\" deleted."];
        } else {
            $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Delete failed: ' . $del->error];
        }
        $del->close();
    } else {
        $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Event not found.'];
    }
} else {
    $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Invalid event ID.'];
}

$conn->close();
header('Location: adminDashboard.php');
exit;
