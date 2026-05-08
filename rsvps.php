<?php
require 'auth.php';
require 'dbconnect.php';

$event_id = intval($_GET['event_id'] ?? 0);
$user_id  = $_SESSION['user_id'];

if (!$event_id) {
    header("Location: StudentDashboard.php");
    exit();
}

// Fetch the event
$stmt = mysqli_prepare($conn, "SELECT * FROM events WHERE Id = ?");
mysqli_stmt_bind_param($stmt, "i", $event_id);
mysqli_stmt_execute($stmt);
$event = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$event) {
    header("Location: StudentDashboard.php");
    exit();
}

$error   = '';
$success = '';

// Check if already RSVPd
$check = mysqli_prepare($conn, "SELECT Id FROM rsvp WHERE UserId = ? AND EventId = ?");
mysqli_stmt_bind_param($check, "ii", $user_id, $event_id);
mysqli_stmt_execute($check);
mysqli_stmt_store_result($check);
$already_rsvpd = mysqli_stmt_num_rows($check) > 0;

// Check if RSVP deadline has passed
$deadline_passed = !empty($event['RSVPDeadline']) && strtotime($event['RSVPDeadline']) < strtotime('today');

// Handle RSVP submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$already_rsvpd && !$deadline_passed) {
    $insert = mysqli_prepare($conn, "INSERT INTO rsvp (UserId, EventId) VALUES (?, ?)");
    mysqli_stmt_bind_param($insert, "ii", $user_id, $event_id);

    if (mysqli_stmt_execute($insert)) {
        $success = "You have successfully RSVPd for this event!";
        $already_rsvpd = true;
    } else {
        $error = "Something went wrong. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>RSVP — <?= htmlspecialchars($event['Name']) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="Style.css">
  <link rel="stylesheet" href="StudentChrome.css">
</head>
<body>

<!-- Navbar -->
<div id="navbar">
  <nav class="navbar navbar-expand-lg custom-navbar">
    <div class="container-fluid student-nav-inner">
      <a class="navbar-brand" href="StudentDashboard.php">
        <img src="28f0cfd287b28c114982f4015e69fcb2-lotus-colored-icon.webp" alt="EMS logo">
        <span>EMS Student</span>
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav">
          <li class="nav-item"><a class="nav-link" href="StudentDashboard.php">Home</a></li>
          <li class="nav-item"><a class="nav-link" href="StudentBookingandHistory.php">Reservations</a></li>
          <li class="nav-item"><a class="nav-link" href="StudentBookingandHistory.php">History</a></li>
          <li class="nav-item"><a class="nav-link" href="Account.php">Profile</a></li>
          <li class="nav-item"><a class="nav-link nav-logout" href="logout.php">Logout</a></li>
        </ul>
      </div>
    </div>
  </nav>
</div>

<div style="background:#f5f7fa;min-height:100vh;padding:40px 16px;">
<div class="container" style="max-width:680px">

  <!-- Back link -->
  <a href="StudentDashboard.php" style="font-size:14px;color:#4f8ef7;text-decoration:none;display:inline-block;margin-bottom:20px">
    ← Back to Events
  </a>

  <!-- Event details card -->
  <div style="background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 2px 16px rgba(0,0,0,0.08);margin-bottom:24px">

    <!-- Event image or gradient -->
    <div style="height:200px;background:linear-gradient(135deg,#0c1a38,#1a3a6b);display:flex;align-items:center;justify-content:center;overflow:hidden">
      <?php if (!empty($event['Image'])): ?>
        <img src="uploads/<?= htmlspecialchars($event['Image']) ?>"
             style="width:100%;height:100%;object-fit:cover">
      <?php else: ?>
        <span style="font-size:60px">🎵</span>
      <?php endif; ?>
    </div>

    <div style="padding:24px">
      <span style="background:#e8f0fe;color:#3b6fd4;font-size:12px;font-weight:600;padding:4px 12px;border-radius:20px">
        <?= htmlspecialchars($event['Category'] ?? 'Event') ?>
      </span>

      <h2 style="font-size:22px;font-weight:700;margin:12px 0 8px;color:#1a1a2e">
        <?= htmlspecialchars($event['Name']) ?>
      </h2>

      <p style="color:#666;font-size:14px;margin-bottom:20px">
        <?= htmlspecialchars($event['Description'] ?? '') ?>
      </p>

      <!-- Event meta details -->
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
        <div style="background:#f8f9fb;border-radius:10px;padding:12px 16px">
          <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.8px;color:#aaa;margin-bottom:4px">Date</div>
          <div style="font-size:14px;font-weight:600;color:#1a1a2e">
            <?= date("d M Y", strtotime($event['EventDate'])) ?>
          </div>
        </div>
        <div style="background:#f8f9fb;border-radius:10px;padding:12px 16px">
          <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.8px;color:#aaa;margin-bottom:4px">Time</div>
          <div style="font-size:14px;font-weight:600;color:#1a1a2e">
            <?= date("g:i A", strtotime($event['StartTime'])) ?> –
            <?= date("g:i A", strtotime($event['EndTime'])) ?>
          </div>
        </div>
        <div style="background:#f8f9fb;border-radius:10px;padding:12px 16px">
          <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.8px;color:#aaa;margin-bottom:4px">Venue</div>
          <div style="font-size:14px;font-weight:600;color:#1a1a2e">
            <?= htmlspecialchars($event['Venue']) ?>
          </div>
        </div>
        <div style="background:#f8f9fb;border-radius:10px;padding:12px 16px">
          <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.8px;color:#aaa;margin-bottom:4px">Organiser</div>
          <div style="font-size:14px;font-weight:600;color:#1a1a2e">
            <?= htmlspecialchars($event['Organiser'] ?? '—') ?>
          </div>
        </div>
        <?php if (!empty($event['RSVPDeadline'])): ?>
        <div style="background:#fff8e1;border-radius:10px;padding:12px 16px;grid-column:1/-1">
          <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.8px;color:#b8860b;margin-bottom:4px">RSVP Deadline</div>
          <div style="font-size:14px;font-weight:600;color:#b8860b">
            <?= date("d M Y", strtotime($event['RSVPDeadline'])) ?>
          </div>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- RSVP action card -->
  <div style="background:#fff;border-radius:16px;padding:24px;box-shadow:0 2px 16px rgba(0,0,0,0.08)">

    <?php if (!empty($success)): ?>
      <div class="alert alert-success" style="border-radius:10px">
        ✅ <?= $success ?>
        <br><a href="StudentBookingandHistory.php" style="color:#1a9e5c;font-weight:600">View my bookings →</a>
      </div>

    <?php elseif (!empty($error)): ?>
      <div class="alert alert-danger" style="border-radius:10px"><?= $error ?></div>

    <?php elseif ($already_rsvpd): ?>
      <div class="alert alert-info" style="border-radius:10px;background:#e8f0fe;color:#3b6fd4;border:none">
        ℹ️ You have already RSVPd for this event.
        <br><a href="StudentBookingandHistory.php" style="color:#3b6fd4;font-weight:600">View my bookings →</a>
      </div>

    <?php elseif ($deadline_passed): ?>
      <div class="alert alert-warning" style="border-radius:10px">
        ⚠️ The RSVP deadline for this event has passed.
      </div>

    <?php else: ?>
      <h4 style="font-size:16px;font-weight:700;margin-bottom:8px">Confirm Your RSVP</h4>
      <p style="font-size:14px;color:#666;margin-bottom:20px">
        You are about to RSVP for <strong><?= htmlspecialchars($event['Name']) ?></strong>.
        This will add it to your active bookings.
      </p>
      <form method="POST">
        <button type="submit"
          style="width:100%;padding:13px;background:#4f8ef7;color:#fff;border:none;border-radius:10px;font-size:15px;font-weight:700;cursor:pointer">
          Confirm RSVP
        </button>
      </form>
    <?php endif; ?>

  </div>

</div>
</div>

<footer class="site-footer">
  <div class="site-footer-inner">
    <div class="site-footer-brand">
      <img src="28f0cfd287b28c114982f4015e69fcb2-lotus-colored-icon.webp" alt="EMS logo">
      <div>
        <p class="site-footer-title">EMS Student Portal</p>
        <p class="site-footer-copy">&copy; 2026 Event Management System. All rights reserved.</p>
      </div>
    </div>
    <div class="site-footer-links">
      <a href="StudentDashboard.php">Events</a>
      <a href="StudentBookingandHistory.php">Bookings</a>
      <a href="Account.php">Profile</a>
    </div>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
