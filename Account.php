<?php
require 'auth.php';
require 'dbconnect.php';

$user_id = $_SESSION['user_id'];
$success = '';
$errors  = [];

// Fetch current user data
$sql  = "SELECT * FROM users WHERE Id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name   = trim($_POST['name']);
    $phone  = trim($_POST['phone']);
    $gender = $_POST['gender'];

    // Password change is optional
    $new_password = $_POST['new_password'];
    $confirm      = $_POST['confirm_password'];
    $current      = $_POST['current_password'];

    // Validate name
    if (empty($name)) $errors[] = "Full name is required.";

    // If they filled in any password field, validate all three
    if (!empty($new_password) || !empty($confirm) || !empty($current)) {
        if (!password_verify($current, $user['Password'])) {
            $errors[] = "Current password is incorrect.";
        }
        if (strlen($new_password) < 6) {
            $errors[] = "New password must be at least 6 characters.";
        }
        if ($new_password !== $confirm) {
            $errors[] = "New passwords do not match.";
        }
    }

    if (empty($errors)) {
        if (!empty($new_password)) {
            // Update with new password
            $hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $sql2   = "UPDATE users SET Name=?, Phone=?, Gender=?, Password=? WHERE Id=?";
            $stmt2  = mysqli_prepare($conn, $sql2);
            mysqli_stmt_bind_param($stmt2, "ssssi", $name, $phone, $gender, $hashed, $user_id);
        } else {
            // Update without changing password
            $sql2  = "UPDATE users SET Name=?, Phone=?, Gender=? WHERE Id=?";
            $stmt2 = mysqli_prepare($conn, $sql2);
            mysqli_stmt_bind_param($stmt2, "sssi", $name, $phone, $gender, $user_id);
        }

        if (mysqli_stmt_execute($stmt2)) {
            // Update session name in case it changed
            $_SESSION['full_name'] = $name;
            $success = "Profile updated successfully.";

            // Re-fetch updated user data
            $stmt3 = mysqli_prepare($conn, "SELECT * FROM users WHERE Id = ?");
            mysqli_stmt_bind_param($stmt3, "i", $user_id);
            mysqli_stmt_execute($stmt3);
            $user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt3));
        } else {
            $errors[] = "Something went wrong. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>My Profile</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="AccountStyles.css">
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
            <li class="nav-item"><a class="nav-link active" href="Account.php">Profile</a></li>
            <li class="nav-item"><a class="nav-link nav-logout" href="logout.php">Logout</a></li>
          </ul>
        </div>
      </div>
    </nav>
  </div>

  <div class="profile-page">
    <div class="container" style="max-width:780px">

      <!-- ── Profile hero card ── -->
      <div class="profile-hero">
        <!-- Shows initials as avatar -->
        <div class="profile-avatar">
          <?= strtoupper(substr($user['Name'], 0, 1)) ?>
        </div>
        <div>
          <p class="profile-hero-name"><?= htmlspecialchars($user['Name']) ?></p>
          <p class="profile-hero-meta"><?= htmlspecialchars($user['Email']) ?></p>
          <p class="profile-hero-meta">Reg No: <?= htmlspecialchars($user['RegNo']) ?></p>
        </div>
        <span class="profile-hero-badge"><?= htmlspecialchars($user['Role']) ?></span>
      </div>

      <!-- Success / error messages -->
      <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?= $success ?></div>
      <?php endif; ?>
      <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
          <?php foreach ($errors as $e): ?>
            <div>• <?= htmlspecialchars($e) ?></div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <!-- ── Read-only details ── -->
      <div class="profile-card">
        <div class="profile-card-title">
          <span>🪪</span> Account Details
        </div>
        <div class="info-grid">
          <div class="info-item">
            <label>Registration Number</label>
            <p><?= htmlspecialchars($user['RegNo']) ?></p>
          </div>
          <div class="info-item">
            <label>Email Address</label>
            <p><?= htmlspecialchars($user['Email']) ?></p>
          </div>
          <div class="info-item">
            <label>Gender</label>
            <p><?= htmlspecialchars($user['Gender'] ?: '—') ?></p>
          </div>
          <div class="info-item">
            <label>Member Since</label>
            <p><?= date("d M Y", strtotime($user['CreatedAt'])) ?></p>
          </div>
        </div>
      </div>

      <!-- ── Editable details ── -->
      <div class="profile-card">
        <div class="profile-card-title">
          <span>✏️</span> Edit Profile
        </div>

        <form method="POST" action="Account.php" class="profile-form">

          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label>Full Name</label>
              <input type="text" name="name" class="form-control"
                     value="<?= htmlspecialchars($user['Name']) ?>" required>
            </div>
            <div class="col-md-6">
              <label>Phone Number</label>
              <input type="text" name="phone" class="form-control"
                     value="<?= htmlspecialchars($user['Phone'] ?? '') ?>">
            </div>
          </div>

          <div class="mb-4">
            <label>Gender</label>
            <select name="gender" class="form-control">
              <option value="">-- Select --</option>
              <option value="Male"   <?= $user['Gender'] === 'Male'   ? 'selected' : '' ?>>Male</option>
              <option value="Female" <?= $user['Gender'] === 'Female' ? 'selected' : '' ?>>Female</option>
              <option value="Other"  <?= $user['Gender'] === 'Other'  ? 'selected' : '' ?>>Other</option>
            </select>
          </div>

          <button type="submit" class="btn-save">Save Changes</button>

        </form>
      </div>

      <!-- ── Change password ── -->
      <div class="profile-card">
        <div class="profile-card-title">
          <span>🔒</span> Change Password
        </div>
        <p class="pw-note">Leave these blank if you don't want to change your password.</p>

        <form method="POST" action="Account.php" class="profile-form">
          <!-- Hidden fields to carry over name/phone/gender unchanged -->
          <input type="hidden" name="name"   value="<?= htmlspecialchars($user['Name']) ?>">
          <input type="hidden" name="phone"  value="<?= htmlspecialchars($user['Phone'] ?? '') ?>">
          <input type="hidden" name="gender" value="<?= htmlspecialchars($user['Gender'] ?? '') ?>">

          <div class="mb-3">
            <label>Current Password</label>
            <input type="password" name="current_password" class="form-control">
          </div>
          <div class="row g-3 mb-4">
            <div class="col-md-6">
              <label>New Password</label>
              <input type="password" name="new_password" class="form-control">
            </div>
            <div class="col-md-6">
              <label>Confirm New Password</label>
              <input type="password" name="confirm_password" class="form-control">
            </div>
          </div>

          <button type="submit" class="btn-save">Update Password</button>

        </form>
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
