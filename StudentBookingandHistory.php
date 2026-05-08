<?php
require 'auth.php';
require 'dbconnect.php';

$user_id = $_SESSION['user_id'];

// Active bookings — events from today onwards
$sql_active = "SELECT rsvp.*, events.Name, events.EventDate, events.StartTime, 
                events.EndTime, events.Venue, events.Image
               FROM rsvp 
               JOIN events ON rsvp.EventId = events.Id
               WHERE rsvp.UserId = ? AND events.EventDate >= CURDATE()
               ORDER BY events.EventDate ASC";
$stmt = mysqli_prepare($conn, $sql_active);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$active_bookings = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);

// Past bookings — events before today
$sql_history = "SELECT rsvp.*, events.Name, events.EventDate, events.StartTime,
                events.EndTime, events.Venue, events.Image
               FROM rsvp 
               JOIN events ON rsvp.EventId = events.Id
               WHERE rsvp.UserId = ? AND events.EventDate < CURDATE()
               ORDER BY events.EventDate DESC";
$stmt2 = mysqli_prepare($conn, $sql_history);
mysqli_stmt_bind_param($stmt2, "i", $user_id);
mysqli_stmt_execute($stmt2);
$past_bookings = mysqli_fetch_all(mysqli_stmt_get_result($stmt2), MYSQLI_ASSOC);
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="Style.css">
    <link rel="stylesheet" href="StudentChrome.css">
    <title>Booking and History</title>
  </head>
  <body>
    <!--The Navbar starts here-->
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
            <li class="nav-item"><a class="nav-link active" href="StudentBookingandHistory.php">Reservations</a></li>
            <li class="nav-item"><a class="nav-link" href="StudentBookingandHistory.php">History</a></li>
            <li class="nav-item"><a class="nav-link" href="Account.php">Profile</a></li>
            <li class="nav-item"><a class="nav-link nav-logout" href="logout.php">Logout</a></li>
          </ul>
        </div>
      </div>
    </nav>
  </div>
<!-- The Navbar ends here -->

<!-- The hero section starts here -->
    <div id="heroSection">
        <img src="download (1).jpg" alt="Hero Image">
        <div id="heroText">
            <h1>Welcome to the student dashboard</h1>
            <p>This is your central hub for managing your events, profile, and more.</p>
        </div>
    </div>
 <!-- The hero section ends here -->

<!-- Active Bookings and Event History section starts here -->
<section class="section" style="padding-top:0">
  <div class="container">
    <div class="sec-top mb-4">
      <div class="sec-titles">
        <div class="ey">Upcoming</div>
        <h2>My Active Bookings</h2>
        <p>Your confirmed and pending reservations</p>
      </div>
      <a href="StudentDashboard.php#searchForm" class="btn-p" style="font-size:14px;padding:10px 22px;gap:7px">
        <i class="ti ti-plus"></i>
        Book New Event
      </a>
    </div>

    <div class="tbl-wrap">
      <table class="ev-tbl">
        <thead>
          <tr>
            <th>Event</th>
            <th>Date &amp; Time</th>
            <th>Venue</th>
            <th>Tickets</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($active_bookings)): ?>
            <tr>
              <td colspan="6" style="text-align:center;padding:30px;color:#888">
                You have no active bookings. <a href="StudentDashboard.php#searchForm">Browse events</a>
              </td>
            </tr>

          <?php else: ?>
            <?php foreach ($active_bookings as $booking): ?>
              <tr>
                <td>
                  <div style="display:flex;align-items:center;gap:14px">
                    <!-- Image or emoji fallback -->
                    <?php if (!empty($booking['Image'])): ?>
                      <img src="uploads/<?= htmlspecialchars($booking['Image']) ?>"
                          class="ts" style="object-fit:cover;">
                    <?php else: ?>
                      <div class="ts" style="background:linear-gradient(135deg,#0c1a38,#0f3060)">🎵</div>
                    <?php endif; ?>
                    <div>
                      <div class="tcn"><?= htmlspecialchars($booking['Name']) ?></div>
                      <div class="tcs"><?= htmlspecialchars($booking['Venue']) ?></div>
                    </div>
                  </div>
                </td>
                <td>
                  <div style="font-size:13.5px;font-weight:500">
                    <?= date("d M Y", strtotime($booking['EventDate'])) ?>
                  </div>
                  <div style="font-size:12px;color:#888">
                    <?= date("g:i A", strtotime($booking['StartTime'])) ?> –
                    <?= date("g:i A", strtotime($booking['EndTime'])) ?>
                  </div>
                </td>
                <td style="font-size:13.5px"><?= htmlspecialchars($booking['Venue']) ?></td>
                <td><span class="pill p-b">1 Ticket</span></td>
                <td><span class="pill p-g">Confirmed</span></td>
                <td>
                  <!-- Cancel button -->
                  <a href="cancelRsvp.php?rsvp_id=<?= $booking['Id'] ?>"
                    class="btn-sm"
                    onclick="return confirm('Cancel this booking?')">
                    Cancel
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>

<!-- Event History section starts here -->
<section class="section" style="padding-top:0;padding-bottom:20px">
  <div class="container">
    <div class="sec-top mb-4">
      <div class="sec-titles">
        <div class="ey">Past</div>
        <h2>Event History</h2>
        <p>Events you've previously attended</p>
      </div>
    </div>

    <div class="tbl-wrap">
      <table class="ev-tbl">
        <thead>
          <tr>
            <th>Event</th>
            <th>Date</th>
            <th>Venue</th>
            <th>Tickets</th>
            <th>Status</th>
            <th>Certificate</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($past_bookings)): ?>
            <tr>
              <td colspan="6" style="text-align:center;padding:30px;color:#888">
                No past events yet.
              </td>
            </tr>

          <?php else: ?>
            <?php foreach ($past_bookings as $booking): ?>
              <tr>
                <td>
                  <div style="display:flex;align-items:center;gap:14px">
                    <?php if (!empty($booking['Image'])): ?>
                      <img src="uploads/<?= htmlspecialchars($booking['Image']) ?>"
                          class="ts" style="object-fit:cover;">
                    <?php else: ?>
                      <div class="ts" style="background:linear-gradient(135deg,#1a3520,#0f4a2a)">🎵</div>
                    <?php endif; ?>
                    <div>
                      <div class="tcn"><?= htmlspecialchars($booking['Name']) ?></div>
                      <div class="tcs"><?= htmlspecialchars($booking['Venue']) ?></div>
                    </div>
                  </div>
                </td>
                <td style="font-size:13.5px">
                  <?= date("d M Y", strtotime($booking['EventDate'])) ?>
                </td>
                <td style="font-size:13.5px"><?= htmlspecialchars($booking['Venue']) ?></td>
                <td><span class="pill p-b">1 Ticket</span></td>
                <td><span class="pill p-g">Attended</span></td>
                <td><span style="font-size:13px;color:#aaa">—</span></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <div class="pag">
      <button class="pb"><i class="ti ti-arrow-left" style="font-size:14px"></i></button>
      <button class="pb on">01</button>
      <button class="pb">02</button>
      <button class="pb"><i class="ti ti-arrow-right" style="font-size:14px"></i></button>
    </div>
  </div>
</section>

     <!-- The footer section starts here -->
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
  </body>
</html>
