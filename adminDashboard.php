<?php
require 'adminAuth.php';
require_once 'dbconnect.php';

// ── Stats ────────────────────────────────────────────────
$total    = $conn->query("SELECT COUNT(*) AS c FROM events")->fetch_assoc()['c'];
$upcoming = $conn->query("SELECT COUNT(*) AS c FROM events WHERE EventDate >= CURDATE()")->fetch_assoc()['c'];
$past     = $conn->query("SELECT COUNT(*) AS c FROM events WHERE EventDate < CURDATE()")->fetch_assoc()['c'];
$today    = $conn->query("SELECT COUNT(*) AS c FROM events WHERE EventDate = CURDATE()")->fetch_assoc()['c'];

// ── Search / filter ──────────────────────────────────────
$search = trim($_GET['search'] ?? '');
$filter = trim($_GET['filter'] ?? '');

$where  = [];
$params = [];
$types  = '';

if ($search !== '') {
    $where[]  = "(Name LIKE ? OR Organiser LIKE ? OR Venue LIKE ?)";
    $like     = "%$search%";
    $params   = array_merge($params, [$like, $like, $like]);
    $types   .= 'sss';
}
if ($filter !== '') {
    $where[]  = "Category = ?";
    $params[] = $filter;
    $types   .= 's';
}

$whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';
$sql = "SELECT * FROM events $whereSQL ORDER BY EventDate DESC";

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$events = $stmt->get_result();

// ── Categories for filter dropdown ───────────────────────
$cats = $conn->query("SELECT DISTINCT Category FROM events WHERE Category IS NOT NULL ORDER BY Category");

// ── Flash message ─────────────────────────────────────────
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

function badgeClass($category) {
    $map = [
        'conference'  => 'badge-blue',
        'workshop'    => 'badge-green',
        'seminar'     => 'badge-yellow',
        'concert'     => 'badge-purple',
        'sport'       => 'badge-red',
        'technology'  => 'badge-blue',
        'art'         => 'badge-purple',
    ];
    return $map[strtolower($category ?? '')] ?? 'badge-blue';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>EMS — Dashboard</title>
  <link rel="stylesheet" href="AdminStyles.css"/>
</head>
<body>

<div class="topbar">
  <span class="topbar-brand">EMS</span>
  <div class="topbar-user">
    Signed in as <strong><?= htmlspecialchars($_SESSION['admin_name']) ?></strong>
    <a href="logout.php" class="btn-logout">Logout</a>
  </div>
</div>

<div class="page-wrap">

  <div class="page-header">
    <h1 class="page-title">Events <span>Dashboard</span></h1>
    <div class="header-actions">
      <a href="viewRsvp.php" class="btn btn-secondary">RSVP Records</a>
      <a href="addEvent.php" class="btn btn-primary">
        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
        </svg>
        New Event
      </a>
    </div>
  </div>

  <?php if ($flash): ?>
    <div class="alert alert-<?= $flash['type'] ?>"><?= htmlspecialchars($flash['msg']) ?></div>
  <?php endif; ?>

  <div class="stats-grid">
    <div class="stat-card"><div class="stat-label">Total Events</div><div class="stat-value"><?= $total ?></div></div>
    <div class="stat-card green"><div class="stat-label">Upcoming</div><div class="stat-value"><?= $upcoming ?></div></div>
    <div class="stat-card yellow"><div class="stat-label">Today</div><div class="stat-value"><?= $today ?></div></div>
    <div class="stat-card red"><div class="stat-label">Past</div><div class="stat-value"><?= $past ?></div></div>
  </div>

  <div class="card">
    <div class="card-head">
      <h2>All Events</h2>
      <form method="GET" id="adminSearchForm" class="admin-search-form" novalidate>
        <input class="search-box" type="text" name="search" placeholder="Search events…"
          maxlength="60"
          value="<?= htmlspecialchars($search) ?>"/>
        <select name="filter" class="search-box" style="width:160px;" onchange="this.form.requestSubmit()">
          <option value="">All Categories</option>
          <?php while ($cat = $cats->fetch_assoc()): ?>
            <option value="<?= htmlspecialchars($cat['Category']) ?>"
              <?= $filter === $cat['Category'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($cat['Category']) ?>
            </option>
          <?php endwhile; ?>
        </select>
        <button type="submit" class="btn btn-secondary" style="padding:7px 14px;">Filter</button>
        <?php if ($search || $filter): ?>
          <a href="adminDashboard.php" class="btn btn-secondary" style="padding:7px 14px;">Clear</a>
        <?php endif; ?>
        <span class="validation-msg" id="adminSearchError" aria-live="polite"></span>
      </form>
    </div>

    <?php if ($events->num_rows === 0): ?>
      <div class="empty-state">
        <svg width="48" height="48" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
        </svg>
        <p>No events found. <a href="addEvent.php" style="color:var(--accent)">Add the first one &rarr;</a></p>
      </div>
    <?php else: ?>
    <div style="overflow-x:auto;">
      <table>
        <thead>
          <tr>
            <th>#</th>
            <th>Image</th>
            <th>Event Name</th>
            <th>Category</th>
            <th>Date</th>
            <th>Time</th>
            <th>Venue</th>
            <th>Organiser</th>
            <th>RSVP By</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php $i = 1; while ($ev = $events->fetch_assoc()): ?>
          <tr>
            <td style="color:var(--muted)"><?= $i++ ?></td>
            <td>
              <?php if (!empty($ev['Image'])): ?>
                <img src="uploads/<?= htmlspecialchars($ev['Image']) ?>"
                     alt="<?= htmlspecialchars($ev['Name']) ?>"
                     style="width:52px;height:40px;object-fit:cover;border-radius:6px;"/>
              <?php else: ?>
                <div style="width:52px;height:40px;background:var(--border);border-radius:6px;
                            display:flex;align-items:center;justify-content:center;">
                  <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="var(--muted)">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0
                         012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0
                         00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                  </svg>
                </div>
              <?php endif; ?>
            </td>
            <td><strong><?= htmlspecialchars($ev['Name'] ?? '—') ?></strong></td>
            <td>
              <?php if ($ev['Category']): ?>
                <span class="badge <?= badgeClass($ev['Category']) ?>"><?= htmlspecialchars($ev['Category']) ?></span>
              <?php else: ?>—<?php endif; ?>
            </td>
            <td><?= $ev['EventDate'] ? date('d M Y', strtotime($ev['EventDate'])) : '—' ?></td>
            <td style="white-space:nowrap;">
              <?php
                $s = $ev['StartTime'] ? date('H:i', strtotime($ev['StartTime'])) : '';
                $e = $ev['EndTime']   ? date('H:i', strtotime($ev['EndTime']))   : '';
                echo ($s && $e) ? "$s – $e" : ($s ?: '—');
              ?>
            </td>
            <td><?= htmlspecialchars($ev['Venue'] ?? '—') ?></td>
            <td><?= htmlspecialchars($ev['Organiser'] ?? '—') ?></td>
            <td><?= $ev['RSVPDeadline'] ? date('d M Y', strtotime($ev['RSVPDeadline'])) : '—' ?></td>
            <td>
              <div class="actions">
                <a href="editEvent.php?id=<?= $ev['Id'] ?>" class="btn btn-edit">Edit</a>
                <button class="btn btn-delete"
                  onclick="confirmDelete(<?= $ev['Id'] ?>, '<?= htmlspecialchars(addslashes($ev['Name'])) ?>')">
                  Delete
                </button>
              </div>
            </td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>

</div>

<div id="deleteModal" class="modal-overlay" style="display:none;">
  <div class="modal-box">
    <h3>Delete Event?</h3>
    <p id="modalMsg">This action cannot be undone.</p>
    <div class="modal-actions">
      <button class="btn btn-secondary" onclick="document.getElementById('deleteModal').style.display='none'">Cancel</button>
      <a id="confirmDeleteBtn" href="#" class="btn btn-delete">Yes, Delete</a>
    </div>
  </div>
</div>

<script>
function confirmDelete(id, name) {
  document.getElementById('modalMsg').textContent = 'Delete "' + name + '"? This cannot be undone.';
  document.getElementById('confirmDeleteBtn').href = 'deleteEvent.php?id=' + id;
  document.getElementById('deleteModal').style.display = 'flex';
}
document.getElementById('deleteModal').addEventListener('click', function(e) {
  if (e.target === this) this.style.display = 'none';
});

document.getElementById('adminSearchForm').addEventListener('submit', function (event) {
  const searchInput = this.elements.search;
  const filterInput = this.elements.filter;
  const errorBox = document.getElementById('adminSearchError');
  const search = searchInput.value.trim();
  const allowedSearch = /^[a-zA-Z0-9\s.,'&-]+$/;

  errorBox.textContent = '';
  searchInput.value = search;

  if (search === '' && filterInput.value === '') {
    event.preventDefault();
    errorBox.textContent = 'Enter a search term or select a category.';
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
<?php $conn->close(); ?>
</body>
</html>
