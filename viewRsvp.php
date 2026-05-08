<?php
require 'adminAuth.php';
require_once 'dbconnect.php';

// ── Handle delete ─────────────────────────────────────────
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $del_id = intval($_GET['delete']);
    $d = $conn->prepare("DELETE FROM rsvp WHERE Id = ?");
    $d->bind_param('i', $del_id);
    if ($d->execute() && $d->affected_rows > 0) {
        $_SESSION['flash'] = ['type' => 'success', 'msg' => 'RSVP cancelled successfully.'];
    } else {
        $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Could not cancel RSVP.'];
    }
    $d->close();
    header('Location: rsvps.php');
    exit;
}

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

// ── Filters ───────────────────────────────────────────────
$filter_event   = intval($_GET['event_id']  ?? 0);
$filter_student = intval($_GET['user_id']   ?? 0);
$search_name    = trim($_GET['search']      ?? '');

// ── Build query ───────────────────────────────────────────
// DB: rsvp (Id, UserId, EventId, RSVPDate)
//     users (Id, Name, Email, Phone, RegNo, Gender)
//     events (Id, Name, Category, EventDate, Venue ...)
$where  = [];
$params = [];
$types  = '';

if ($filter_event) {
    $where[]  = 'r.EventId = ?';
    $params[] = $filter_event;
    $types   .= 'i';
}
if ($filter_student) {
    $where[]  = 'r.UserId = ?';
    $params[] = $filter_student;
    $types   .= 'i';
}
if ($search_name !== '') {
    $where[]  = '(u.Name LIKE ? OR u.Email LIKE ?)';
    $like     = "%$search_name%";
    $params   = array_merge($params, [$like, $like]);
    $types   .= 'ss';
}

$whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$sql = "
    SELECT
        r.Id        AS rsvp_id,
        r.RSVPDate  AS rsvp_date,
        u.Id        AS user_id,
        u.Name      AS full_name,
        u.Email     AS email,
        u.Phone     AS phone,
        e.Id        AS event_id,
        e.Name      AS event_name,
        e.EventDate AS event_date,
        e.Venue     AS venue,
        e.Category  AS category
    FROM rsvp r
    JOIN users  u ON r.UserId  = u.Id
    JOIN events e ON r.EventId = e.Id
    $whereSQL
    ORDER BY r.RSVPDate DESC
";

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$rsvps = $stmt->get_result();

// ── Stats ─────────────────────────────────────────────────
$total_rsvps       = $conn->query("SELECT COUNT(*) AS c FROM rsvp")->fetch_assoc()['c'];
$total_students    = $conn->query("SELECT COUNT(*) AS c FROM users")->fetch_assoc()['c'];
$events_with_rsvps = $conn->query("SELECT COUNT(DISTINCT EventId) AS c FROM rsvp")->fetch_assoc()['c'];
$today_rsvps       = $conn->query("SELECT COUNT(*) AS c FROM rsvp WHERE DATE(RSVPDate) = CURDATE()")->fetch_assoc()['c'];

// ── Dropdown data ─────────────────────────────────────────
$all_events   = $conn->query("SELECT Id, Name FROM events ORDER BY EventDate DESC");
$all_students = $conn->query("SELECT Id, Name FROM users ORDER BY Name ASC");

function badgeClass($category) {
    $map = [
        'conference' => 'badge-blue',   'workshop'   => 'badge-green',
        'seminar'    => 'badge-yellow', 'concert'    => 'badge-purple',
        'sport'      => 'badge-red',    'exhibition' => 'badge-blue',
        'networking' => 'badge-green',  'technology' => 'badge-blue',
        'art'        => 'badge-purple',
    ];
    return $map[strtolower($category ?? '')] ?? 'badge-blue';
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>EMS — RSVPs</title>
  <link rel="stylesheet" href="AdminStyles.css"/>
  <style>
    .filter-bar { display:flex; gap:10px; flex-wrap:wrap; align-items:center; }
    .filter-bar select, .filter-bar input { width:auto; min-width:160px; padding:7px 12px; font-size:0.88rem; }
    .rsvp-meta { font-size:0.78rem; color:var(--muted); margin-top:2px; }
    .student-cell { line-height:1.3; }
    .student-cell strong { display:block; }
  </style>
</head>
<body>

<div class="topbar">
  <span class="topbar-brand">EMS</span>
  <div class="topbar-user">
    <strong><?= htmlspecialchars($_SESSION['admin_name']) ?></strong>
    <a href="logout.php" class="btn-logout">Logout</a>
  </div>
</div>

<div class="page-wrap">

  <div class="page-header">
    <h1 class="page-title">RSVP <span>Records</span></h1>
    <a href="adminDashboard.php" class="btn btn-secondary">&larr; Dashboard</a>
  </div>

  <?php if ($flash): ?>
    <div class="alert alert-<?= $flash['type'] ?>"><?= htmlspecialchars($flash['msg']) ?></div>
  <?php endif; ?>

  <div class="stats-grid">
    <div class="stat-card"><div class="stat-label">Total RSVPs</div><div class="stat-value"><?= $total_rsvps ?></div></div>
    <div class="stat-card green"><div class="stat-label">Students</div><div class="stat-value"><?= $total_students ?></div></div>
    <div class="stat-card"><div class="stat-label">Events w/ RSVPs</div><div class="stat-value"><?= $events_with_rsvps ?></div></div>
    <div class="stat-card yellow"><div class="stat-label">RSVPs Today</div><div class="stat-value"><?= $today_rsvps ?></div></div>
  </div>

  <div class="card">
    <div class="card-head">
      <h2>All RSVPs</h2>
      <form method="GET" class="filter-bar" id="rsvpSearchForm" novalidate>
        <input class="search-box" type="text" name="search" placeholder="Search student…"
          maxlength="60"
          value="<?= htmlspecialchars($search_name) ?>" style="width:180px;"/>

        <select name="event_id" class="search-box" onchange="this.form.requestSubmit()">
          <option value="">All Events</option>
          <?php $all_events->data_seek(0); while ($ev = $all_events->fetch_assoc()): ?>
            <option value="<?= $ev['Id'] ?>" <?= $filter_event === $ev['Id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($ev['Name']) ?>
            </option>
          <?php endwhile; ?>
        </select>

        <select name="user_id" class="search-box" onchange="this.form.requestSubmit()">
          <option value="">All Students</option>
          <?php while ($u = $all_students->fetch_assoc()): ?>
            <option value="<?= $u['Id'] ?>" <?= $filter_student === $u['Id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($u['Name']) ?>
            </option>
          <?php endwhile; ?>
        </select>

        <button type="submit" class="btn btn-secondary" style="padding:7px 14px;">Filter</button>
        <?php if ($filter_event || $filter_student || $search_name): ?>
          <a href="rsvps.php" class="btn btn-secondary" style="padding:7px 14px;">Clear</a>
        <?php endif; ?>
        <span class="validation-msg" id="rsvpSearchError" aria-live="polite"></span>
      </form>
    </div>

    <?php if ($rsvps->num_rows === 0): ?>
      <div class="empty-state">
        <svg width="48" height="48" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857
               M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857
               m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
        </svg>
        <p>No RSVPs found matching your filters.</p>
      </div>
    <?php else: ?>
    <div style="overflow-x:auto;">
      <table>
        <thead>
          <tr>
            <th>#</th><th>Student</th><th>Event</th><th>Category</th>
            <th>Event Date</th><th>Venue</th><th>RSVP Date</th><th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php $i = 1; while ($row = $rsvps->fetch_assoc()): ?>
          <tr>
            <td style="color:var(--muted)"><?= $i++ ?></td>
            <td class="student-cell">
              <strong><?= htmlspecialchars($row['full_name']) ?></strong>
              <span class="rsvp-meta"><?= htmlspecialchars($row['email']) ?></span>
              <?php if ($row['phone']): ?>
                <span class="rsvp-meta"><?= htmlspecialchars($row['phone']) ?></span>
              <?php endif; ?>
            </td>
            <td><strong><?= htmlspecialchars($row['event_name']) ?></strong></td>
            <td>
              <?php if ($row['category']): ?>
                <span class="badge <?= badgeClass($row['category']) ?>"><?= htmlspecialchars($row['category']) ?></span>
              <?php else: ?>—<?php endif; ?>
            </td>
            <td><?= $row['event_date'] ? date('d M Y', strtotime($row['event_date'])) : '—' ?></td>
            <td><?= htmlspecialchars($row['venue'] ?? '—') ?></td>
            <td style="white-space:nowrap;">
              <?= date('d M Y', strtotime($row['rsvp_date'])) ?>
              <span class="rsvp-meta"><?= date('H:i', strtotime($row['rsvp_date'])) ?></span>
            </td>
            <td>
              <button class="btn btn-delete"
                onclick="confirmCancel(<?= $row['rsvp_id'] ?>,
                  '<?= htmlspecialchars(addslashes($row['full_name'])) ?>',
                  '<?= htmlspecialchars(addslashes($row['event_name'])) ?>')">
                Cancel
              </button>
            </td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>

</div>

<div id="cancelModal" class="modal-overlay" style="display:none;">
  <div class="modal-box">
    <h3>Cancel RSVP?</h3>
    <p id="cancelMsg">This will remove the student's RSVP.</p>
    <div class="modal-actions">
      <button class="btn btn-secondary"
        onclick="document.getElementById('cancelModal').style.display='none'">Keep It</button>
      <a id="confirmCancelBtn" href="#" class="btn btn-delete">Yes, Cancel</a>
    </div>
  </div>
</div>

<script>
function confirmCancel(rsvpId, student, event) {
  document.getElementById('cancelMsg').textContent =
    'Remove ' + student + '\'s RSVP for "' + event + '"?';
  document.getElementById('confirmCancelBtn').href = 'rsvps.php?delete=' + rsvpId;
  document.getElementById('cancelModal').style.display = 'flex';
}
document.getElementById('cancelModal').addEventListener('click', function(e) {
  if (e.target === this) this.style.display = 'none';
});

document.getElementById('rsvpSearchForm').addEventListener('submit', function (event) {
  const searchInput = this.elements.search;
  const eventInput = this.elements.event_id;
  const studentInput = this.elements.user_id;
  const errorBox = document.getElementById('rsvpSearchError');
  const search = searchInput.value.trim();
  const allowedSearch = /^[a-zA-Z0-9\s.@'-]+$/;

  errorBox.textContent = '';
  searchInput.value = search;

  if (search === '' && eventInput.value === '' && studentInput.value === '') {
    event.preventDefault();
    errorBox.textContent = 'Enter a student name or choose a filter.';
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
    errorBox.textContent = 'Use only letters, numbers, spaces, periods, @, apostrophes, or hyphens.';
    searchInput.focus();
  }
});
</script>
</body>
</html>
