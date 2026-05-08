<?php
require 'auth.php';
require 'dbconnect.php';

// Pick up search and filter values from the URL
$search   = trim($_GET['search']   ?? '');
$category = trim($_GET['category'] ?? '');
$date     = trim($_GET['date']     ?? '');

// Build query dynamically based on what was submitted
$sql    = "SELECT * FROM events WHERE 1=1";
$params = [];
$types  = '';

// Pick session user's name for personalized greeting
$full_name = $_SESSION['full_name'] ?? 'Student';

if (!empty($search)) {
    $sql     .= " AND (Name LIKE ? OR Venue LIKE ? OR Organiser LIKE ?)";
    $like     = "%$search%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $types   .= 'sss';
}

if (!empty($category)) {
    $sql     .= " AND Category = ?";
    $params[] = $category;
    $types   .= 's';
}

if (!empty($date)) {
    $sql     .= " AND EventDate = ?";
    $params[] = $date;
    $types   .= 's';
}

$sql .= " ORDER BY EventDate ASC";

$stmt = mysqli_prepare($conn, $sql);

// Only bind params if there are any
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}

mysqli_stmt_execute($stmt);
$events = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);

// Fetch distinct categories for the filter dropdown
$cat_result = mysqli_query($conn, "SELECT DISTINCT Category FROM events ORDER BY Category ASC");
$categories = mysqli_fetch_all($cat_result, MYSQLI_ASSOC);
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
    <title>Dashboard</title>
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
            <li class="nav-item"><a class="nav-link active" href="StudentDashboard.php">Home</a></li>
            <li class="nav-item"><a class="nav-link" href="StudentBookingandHistory.php">Reservations</a></li>
            <li class="nav-item"><a class="nav-link" href="StudentBookingandHistory.php">History</a></li>
            <li class="nav-item"><a class="nav-link" href="Account.php">Profile</a></li>
            <li class="nav-item"><a class="nav-link nav-logout" href="logout.php">Logout</a></li>
          </ul>
        </div>
      </div>
    </nav>
  </div>

<!-- The hero section starts here -->
    <div id="heroSection">
        <img src="download (1).jpg" alt="Hero Image">
        <div id="heroText">
            <h1>Welcome, <?= htmlspecialchars($full_name) ?>!</h1>
            <p>This is your central hub for managing your events, profile, and more.</p>
        </div>
    </div>
 <!-- The hero section ends here -->

 <!--The event section starts here -->
    <!-- The events carousel starts here -->
    <section class="section" style="padding-top: 40px">
        <div class="container">
            <div class="sec-top">
                <div class="sec-titles">
                    <h2>Upcoming Events</h2>
                    <p>Don't miss these highlighted experiences</p>
                </div>
            <a href="#searchForm" class="sec-link">View all <i class="ti ti-arrow-right" style="font-size:15px"></i></a>
            </div>

        <div id="featCarousel" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-inner">
            <?php if (empty($events)): ?>
            <div class="carousel-item active">
                <div class="feat-card">
                <div class="feat-img"><span class="stage">📅</span></div>
                <div class="feat-body">
                    <h3>No upcoming events at the moment.</h3>
                    <p>Check back soon!</p>
                </div>
                </div>
            </div>

            <?php else: ?>
            <?php foreach ($events as $index => $event): ?>
                <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                <div class="feat-card">

                    <div class="feat-img">
                    <?php if (!empty($event['Image'])): ?>
                        <img src="uploads/<?= htmlspecialchars($event['Image']) ?>"
                            style="width:100%;height:100%;object-fit:cover;">
                    <?php else: ?>
                        <span class="stage">🎵</span>
                    <?php endif; ?>
                    </div>

                    <span class="feat-badge">Upcoming Event</span>

                    <div class="feat-body">
                    <div class="feat-meta">
                        <span class="d-chip"><?= date("d M · Y", strtotime($event['EventDate'])) ?></span>
                        <span class="t-chip">
                        <i class="ti ti-clock"></i>
                        <?= date("g:i A", strtotime($event['StartTime'])) ?> –
                        <?= date("g:i A", strtotime($event['EndTime'])) ?>
                        </span>
                    </div>
                    <h3><?= htmlspecialchars($event['Name']) ?></h3>
                    <div class="loc-row">
                        <i class="ti ti-map-pin" style="font-size:15px;color:var(--blue-l)"></i>
                        <?= htmlspecialchars($event['Venue']) ?>
                    </div>
                    </div>

                    <div class="car-ctrl">
                    <button class="ctrl ctrl-prev" type="button" data-bs-target="#featCarousel" data-bs-slide="prev" aria-label="Previous event">
                        <span aria-hidden="true">&lsaquo;</span>
                    </button>
                    <button class="ctrl ctrl-next" type="button" data-bs-target="#featCarousel" data-bs-slide="next" aria-label="Next event">
                        <span aria-hidden="true">&rsaquo;</span>
                    </button>
                    </div>

                </div>
                </div>
            <?php endforeach; ?>
            <?php endif; ?>

        </div>
        </div>
        </div>
    </section>
    <!-- The events carousel ends here -->

    <!-- The events display cards start here -->
     <!--Using the event data from the database to display the events in cards-->
        <!-- The events display cards start here -->

         <!-- Search and filter section -->
        <form method="GET" action="StudentDashboard.php" id="searchForm" novalidate>
            <div class="filter-bar">

                <!-- Search input -->
                <div class="filter-search">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                    fill="none" stroke="#aaa" stroke-width="2"
                    viewBox="0 0 24 24" style="position:absolute;left:14px;top:50%;transform:translateY(-50%)">
                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <input type="text"
                        name="search"
                        class="filter-input"
                        placeholder="Search events, venues..."
                        maxlength="60"
                        value="<?= htmlspecialchars($search) ?>">
                </div>

                <!-- Category dropdown -->
                <select name="category" class="filter-select" onchange="this.form.requestSubmit()">
                <option value="">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= htmlspecialchars($cat['Category']) ?>"
                    <?= $category === $cat['Category'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat['Category']) ?>
                    </option>
                <?php endforeach; ?>
                </select>

                <!-- Date picker -->
                <input type="date"
                    name="date"
                    class="filter-select"
                    value="<?= htmlspecialchars($date) ?>"
                    onchange="this.form.requestSubmit()">

                <!-- Search button -->
                <button type="submit" class="filter-btn">Search</button>

                <!-- Clear filters — only shows when filters are active -->
                <?php if (!empty($search) || !empty($category) || !empty($date)): ?>
                <a href="StudentDashboard.php" class="filter-clear">Clear</a>
                <?php endif; ?>

            </div>
            <p class="validation-msg" id="studentSearchError" aria-live="polite"></p>
        </form>
        <!-- The search and filter form ends here -->

        <!-- These are the filter results -->
        <div class="filter-results">
            <?php if (!empty($search) || !empty($category) || !empty($date)): ?>
                <span><?= count($events) ?> result<?= count($events) !== 1 ? 's' : '' ?> found</span>

                <!-- Show active filter tags -->
                <?php if (!empty($search)): ?>
                <span class="filter-tag">
                    "<?= htmlspecialchars($search) ?>"
                    <a href="?category=<?= urlencode($category) ?>&date=<?= urlencode($date) ?>">✕</a>
                </span>
                <?php endif; ?>

                <?php if (!empty($category)): ?>
                <span class="filter-tag">
                    <?= htmlspecialchars($category) ?>
                    <a href="?search=<?= urlencode($search) ?>&date=<?= urlencode($date) ?>">✕</a>
                </span>
                <?php endif; ?>

                <?php if (!empty($date)): ?>
                <span class="filter-tag">
                    <?= date("d M Y", strtotime($date)) ?>
                    <a href="?search=<?= urlencode($search) ?>&category=<?= urlencode($category) ?>">✕</a>
                </span>
                <?php endif; ?>

            <?php else: ?>
                <span><?= count($events) ?> upcoming event<?= count($events) !== 1 ? 's' : '' ?></span>
            <?php endif; ?>
        </div>
        <!-- The filter results end here -->

        <?php if (empty($events)): ?>
            <div class="no-results">
                <p style="font-size:40px">🔍</p>
                <h3>No events found</h3>
                <p>Try a different search term or clear your filters.</p>
                <a href="StudentDashboard.php" class="filter-btn" style="display:inline-block;margin-top:12px">
                Clear Filters
                </a>
            </div>

        <?php else: ?>
        <div class="events-grid">
            <?php foreach ($events as $event): ?>
            <div class="event-card">
                <div class="ec-img">
                <?php if (!empty($event['Image'])): ?>
                    <img src="uploads/<?= htmlspecialchars($event['Image']) ?>"
                        alt="<?= htmlspecialchars($event['Name']) ?>">
                <?php else: ?>
                    <div class="ec-img-fallback">🎵</div>
                <?php endif; ?>
                <span class="ec-badge"><?= htmlspecialchars($event['Category']) ?></span>
                </div>
                <div class="ec-body">
                <div class="ec-meta">
                    <span>📅 <?= date("d M Y", strtotime($event['EventDate'])) ?></span>
                    <span>🕐 <?= date("g:i A", strtotime($event['StartTime'])) ?></span>
                </div>
                <h3 class="ec-title"><?= htmlspecialchars($event['Name']) ?></h3>
                <p class="ec-venue">📍 <?= htmlspecialchars($event['Venue']) ?></p>
                <a href="rsvps.php?event_id=<?= $event['Id'] ?>" class="ec-btn">RSVP Now</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
<!-- The events display cards end here -->
    <!-- The events display cards end here -->
     <!--The event section ends here -->

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
    <script>
      document.getElementById('searchForm').addEventListener('submit', function (event) {
        const searchInput = this.elements.search;
        const categoryInput = this.elements.category;
        const dateInput = this.elements.date;
        const errorBox = document.getElementById('studentSearchError');
        const search = searchInput.value.trim();
        const hasFilter = categoryInput.value !== '' || dateInput.value !== '';
        const allowedSearch = /^[a-zA-Z0-9\s.,'&-]+$/;

        errorBox.textContent = '';
        searchInput.value = search;

        if (search === '' && !hasFilter) {
          event.preventDefault();
          errorBox.textContent = 'Enter a search term or choose a filter before searching.';
          searchInput.focus();
          return;
        }

        if (search !== '' && search.length < 2) {
          event.preventDefault();
          errorBox.textContent = 'Search term must be at least 2 characters.';
          searchInput.focus();
          return;
        }

        if (search !== '' && !allowedSearch.test(search)) {
          event.preventDefault();
          errorBox.textContent = 'Use only letters, numbers, spaces, commas, periods, apostrophes, ampersands, or hyphens.';
          searchInput.focus();
        }
      });
    </script>
  </body>
</html>
