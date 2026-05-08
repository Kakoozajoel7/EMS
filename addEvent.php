<?php
require 'adminAuth.php';
require_once 'dbconnect.php';

$error      = '';
$categories = ['Conference', 'Workshop', 'Seminar', 'Concert', 'Sport', 'Exhibition', 'Networking', 'Other'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name          = trim($_POST['Name']          ?? '');
    $category      = trim($_POST['Category']      ?? '');
    $description   = trim($_POST['Description']   ?? '');
    $event_date    = trim($_POST['EventDate']      ?? '');
    $start_time    = trim($_POST['StartTime']      ?? '');
    $end_time      = trim($_POST['EndTime']        ?? '');
    $venue         = trim($_POST['Venue']          ?? '');
    $organiser     = trim($_POST['Organiser']      ?? '');
    $rsvp_deadline = trim($_POST['RSVPDeadline']   ?? '');
    $created_by    = $_SESSION['admin_id'];

    if ($name === '' || $event_date === '') {
        $error = 'Event name and date are required.';
    } else {
        // ── Handle image upload ───────────────────────────────
        $image_filename = null;
        if (!empty($_FILES['Image']['name'])) {
            $upload_dir = 'uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            $ext      = strtolower(pathinfo($_FILES['Image']['name'], PATHINFO_EXTENSION));
            $allowed  = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (!in_array($ext, $allowed)) {
                $error = 'Image must be JPG, PNG, GIF, or WebP.';
            } elseif ($_FILES['Image']['size'] > 2 * 1024 * 1024) {
                $error = 'Image must be under 2 MB.';
            } else {
                $image_filename = uniqid('evt_') . '.' . $ext;
                if (!move_uploaded_file($_FILES['Image']['tmp_name'], $upload_dir . $image_filename)) {
                    $error = 'Failed to upload image.';
                    $image_filename = null;
                }
            }
        }

        if (!$error) {
            // DB columns: Id, Name, Category, Description, EventDate, StartTime,
            //             EndTime, Venue, Organiser, RSVPDeadline, CreatedBy, Image
            $stmt = $conn->prepare(
                "INSERT INTO events
                   (Name, Category, Description, EventDate, StartTime, EndTime,
                    Venue, Organiser, RSVPDeadline, CreatedBy, Image)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->bind_param(
                'sssssssssss',
                $name, $category, $description, $event_date,
                $start_time, $end_time, $venue, $organiser,
                $rsvp_deadline, $created_by, $image_filename
            );
            // Note: CreatedBy is INT — fix bind type
            // Re-bind with correct types
            $stmt->close();
            $stmt = $conn->prepare(
                "INSERT INTO events
                   (Name, Category, Description, EventDate, StartTime, EndTime,
                    Venue, Organiser, RSVPDeadline, CreatedBy, Image)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->bind_param(
                'sssssssssis',
                $name, $category, $description, $event_date,
                $start_time, $end_time, $venue, $organiser,
                $rsvp_deadline, $created_by, $image_filename
            );

            if ($stmt->execute()) {
                $_SESSION['flash'] = ['type' => 'success', 'msg' => "Event \"$name\" created successfully."];
                header('Location: adminDashboard.php');
                exit;
            } else {
                $error = 'Failed to create event: ' . $stmt->error;
            }
            $stmt->close();
        }
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>EMS — Add Event</title>
  <link rel="stylesheet" href="AdminStyles.css"/>
  <style>
    .image-preview-wrap { margin-top: 10px; }
    .image-preview-wrap img {
      max-width: 220px; max-height: 140px;
      border-radius: 8px; border: 2px solid var(--border);
      object-fit: cover; display: none;
    }
    .file-hint { font-size: 0.78rem; color: var(--muted); margin-top: 4px; }
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
    <h1 class="page-title">New <span>Event</span></h1>
    <a href="adminDashboard.php" class="btn btn-secondary">&larr; Back</a>
  </div>

  <?php if ($error): ?>
    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <div class="form-wrap">
    <!-- enctype required for file uploads -->
    <form method="POST" enctype="multipart/form-data">

      <div class="form-grid">

        <div class="form-group full">
          <label for="Name">Event Name *</label>
          <input type="text" id="Name" name="Name" placeholder="e.g. Annual Tech Summit"
            value="<?= htmlspecialchars($_POST['Name'] ?? '') ?>" required/>
        </div>

        <div class="form-group">
          <label for="Category">Category</label>
          <select id="Category" name="Category">
            <option value="">— Select —</option>
            <?php foreach ($categories as $cat): ?>
              <option value="<?= $cat ?>" <?= ($_POST['Category'] ?? '') === $cat ? 'selected' : '' ?>>
                <?= $cat ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group">
          <label for="Organiser">Organiser</label>
          <input type="text" id="Organiser" name="Organiser" placeholder="e.g. Joel Kakooza"
            value="<?= htmlspecialchars($_POST['Organiser'] ?? '') ?>"/>
        </div>

        <div class="form-group">
          <label for="EventDate">Event Date *</label>
          <input type="date" id="EventDate" name="EventDate"
            value="<?= htmlspecialchars($_POST['EventDate'] ?? '') ?>" required/>
        </div>

        <div class="form-group">
          <label for="RSVPDeadline">RSVP Deadline</label>
          <input type="date" id="RSVPDeadline" name="RSVPDeadline"
            value="<?= htmlspecialchars($_POST['RSVPDeadline'] ?? '') ?>"/>
        </div>

        <div class="form-group">
          <label for="StartTime">Start Time</label>
          <input type="time" id="StartTime" name="StartTime"
            value="<?= htmlspecialchars($_POST['StartTime'] ?? '') ?>"/>
        </div>

        <div class="form-group">
          <label for="EndTime">End Time</label>
          <input type="time" id="EndTime" name="EndTime"
            value="<?= htmlspecialchars($_POST['EndTime'] ?? '') ?>"/>
        </div>

        <div class="form-group full">
          <label for="Venue">Venue</label>
          <input type="text" id="Venue" name="Venue" placeholder="e.g. Kampala Serena Hotel"
            value="<?= htmlspecialchars($_POST['Venue'] ?? '') ?>"/>
        </div>

        <!-- ── Image Upload ─────────────────────────────────── -->
        <div class="form-group full">
          <label for="Image">Event Image</label>
          <input type="file" id="Image" name="Image" accept="image/jpeg,image/png,image/gif,image/webp"
            onchange="previewImage(this)"/>
          <p class="file-hint">JPG, PNG, GIF or WebP · max 2 MB</p>
          <div class="image-preview-wrap">
            <img id="imagePreview" src="" alt="Preview"/>
          </div>
        </div>

        <div class="form-group full">
          <label for="Description">Description</label>
          <textarea id="Description" name="Description" placeholder="Brief description of the event…"><?= htmlspecialchars($_POST['Description'] ?? '') ?></textarea>
        </div>

      </div>

      <div class="form-actions">
        <button type="submit" class="btn btn-primary">Create Event</button>
        <a href="adminDashboard.php" class="btn btn-secondary">Cancel</a>
      </div>

    </form>
  </div>
</div>

<script>
function previewImage(input) {
  const img = document.getElementById('imagePreview');
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = e => { img.src = e.target.result; img.style.display = 'block'; };
    reader.readAsDataURL(input.files[0]);
  } else {
    img.style.display = 'none';
  }
}
</script>
</body>
</html>
