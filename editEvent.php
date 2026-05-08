<?php
require 'adminAuth.php';
require_once 'dbconnect.php';

$id = intval($_GET['id'] ?? 0);
if (!$id) {
    header('Location: adminDashboard.php');
    exit;
}

// Fetch existing event — DB columns are capitalized
$stmt = $conn->prepare("SELECT * FROM events WHERE Id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$ev = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$ev) {
    $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Event not found.'];
    header('Location: adminDashboard.php');
    exit;
}

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

    if ($name === '' || $event_date === '') {
        $error = 'Event name and date are required.';
    } else {
        // ── Handle image upload ───────────────────────────────
        $image_filename = $ev['Image']; // keep existing by default

        if (!empty($_FILES['Image']['name'])) {
            $upload_dir = 'uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            $ext     = strtolower(pathinfo($_FILES['Image']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (!in_array($ext, $allowed)) {
                $error = 'Image must be JPG, PNG, GIF, or WebP.';
            } elseif ($_FILES['Image']['size'] > 2 * 1024 * 1024) {
                $error = 'Image must be under 2 MB.';
            } else {
                $new_filename = uniqid('evt_') . '.' . $ext;
                if (move_uploaded_file($_FILES['Image']['tmp_name'], $upload_dir . $new_filename)) {
                    // Delete old image if it exists
                    if ($image_filename && file_exists($upload_dir . $image_filename)) {
                        unlink($upload_dir . $image_filename);
                    }
                    $image_filename = $new_filename;
                } else {
                    $error = 'Failed to upload image.';
                }
            }
        }

        // Remove image if admin checked "remove"
        if (isset($_POST['remove_image']) && $_POST['remove_image'] === '1') {
            if ($ev['Image'] && file_exists('uploads/' . $ev['Image'])) {
                unlink('uploads/' . $ev['Image']);
            }
            $image_filename = null;
        }

        if (!$error) {
            $stmt = $conn->prepare(
                "UPDATE events SET
                   Name=?, Category=?, Description=?, EventDate=?,
                   StartTime=?, EndTime=?, Venue=?, Organiser=?,
                   RSVPDeadline=?, Image=?
                 WHERE Id=?"
            );
            $stmt->bind_param(
                'ssssssssssi',
                $name, $category, $description, $event_date,
                $start_time, $end_time, $venue, $organiser,
                $rsvp_deadline, $image_filename, $id
            );

            if ($stmt->execute()) {
                $_SESSION['flash'] = ['type' => 'success', 'msg' => "Event \"$name\" updated successfully."];
                header('Location: adminDashboard.php');
                exit;
            } else {
                $error = 'Failed to update event: ' . $stmt->error;
            }
            $stmt->close();
        }
    }

    // Reflect POST data back on error (keep DB Image)
    $ev = array_merge($ev, $_POST);
    $ev['Image'] = $image_filename ?? $ev['Image']; // don't overwrite with POST junk
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>EMS — Edit Event</title>
  <link rel="stylesheet" href="AdminStyles.css"/>
  <style>
    .current-image img {
      max-width: 220px; max-height: 140px;
      border-radius: 8px; border: 2px solid var(--border); object-fit: cover;
    }
    .current-image { margin-bottom: 10px; }
    .image-preview-wrap img {
      max-width: 220px; max-height: 140px;
      border-radius: 8px; border: 2px dashed var(--accent);
      object-fit: cover; display: none; margin-top: 8px;
    }
    .file-hint { font-size: 0.78rem; color: var(--muted); margin-top: 4px; }
    .remove-label { font-size: 0.84rem; color: var(--muted); cursor: pointer; }
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
    <h1 class="page-title">Edit <span>Event</span></h1>
    <a href="adminDashboard.php" class="btn btn-secondary">&larr; Back</a>
  </div>

  <?php if ($error): ?>
    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <div class="form-wrap">
    <form method="POST" enctype="multipart/form-data">

      <div class="form-grid">

        <div class="form-group full">
          <label for="Name">Event Name *</label>
          <input type="text" id="Name" name="Name"
            value="<?= htmlspecialchars($ev['Name'] ?? '') ?>" required/>
        </div>

        <div class="form-group">
          <label for="Category">Category</label>
          <select id="Category" name="Category">
            <option value="">— Select —</option>
            <?php foreach ($categories as $cat): ?>
              <option value="<?= $cat ?>" <?= ($ev['Category'] ?? '') === $cat ? 'selected' : '' ?>>
                <?= $cat ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group">
          <label for="Organiser">Organiser</label>
          <input type="text" id="Organiser" name="Organiser"
            value="<?= htmlspecialchars($ev['Organiser'] ?? '') ?>"/>
        </div>

        <div class="form-group">
          <label for="EventDate">Event Date *</label>
          <input type="date" id="EventDate" name="EventDate"
            value="<?= htmlspecialchars($ev['EventDate'] ?? '') ?>" required/>
        </div>

        <div class="form-group">
          <label for="RSVPDeadline">RSVP Deadline</label>
          <input type="date" id="RSVPDeadline" name="RSVPDeadline"
            value="<?= htmlspecialchars($ev['RSVPDeadline'] ?? '') ?>"/>
        </div>

        <div class="form-group">
          <label for="StartTime">Start Time</label>
          <input type="time" id="StartTime" name="StartTime"
            value="<?= htmlspecialchars($ev['StartTime'] ?? '') ?>"/>
        </div>

        <div class="form-group">
          <label for="EndTime">End Time</label>
          <input type="time" id="EndTime" name="EndTime"
            value="<?= htmlspecialchars($ev['EndTime'] ?? '') ?>"/>
        </div>

        <div class="form-group full">
          <label for="Venue">Venue</label>
          <input type="text" id="Venue" name="Venue"
            value="<?= htmlspecialchars($ev['Venue'] ?? '') ?>"/>
        </div>

        <!-- ── Image Upload ─────────────────────────────────── -->
        <div class="form-group full">
          <label for="Image">Event Image</label>

          <?php if (!empty($ev['Image'])): ?>
            <div class="current-image">
              <p style="font-size:0.82rem;color:var(--muted);margin-bottom:6px;">Current image:</p>
              <img src="uploads/<?= htmlspecialchars($ev['Image']) ?>" alt="Current event image"/>
            </div>
            <label class="remove-label">
              <input type="checkbox" name="remove_image" value="1"/> Remove current image
            </label>
            <p class="file-hint" style="margin-top:6px;">Upload a new image below to replace it:</p>
          <?php endif; ?>

          <input type="file" id="Image" name="Image" accept="image/jpeg,image/png,image/gif,image/webp"
            onchange="previewImage(this)" style="margin-top:8px;"/>
          <p class="file-hint">JPG, PNG, GIF or WebP · max 2 MB</p>
          <div class="image-preview-wrap">
            <img id="imagePreview" src="" alt="New image preview"/>
          </div>
        </div>

        <div class="form-group full">
          <label for="Description">Description</label>
          <textarea id="Description" name="Description"><?= htmlspecialchars($ev['Description'] ?? '') ?></textarea>
        </div>

      </div>

      <div class="form-actions">
        <button type="submit" class="btn btn-primary">Save Changes</button>
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
