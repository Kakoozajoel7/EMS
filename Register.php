<?php
session_start();
require 'dbconnect.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name     = trim($_POST['name']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm  = $_POST['confirm_password'];
    $reg_no   = trim($_POST['reg_no']);
    $phone    = trim($_POST['phone']);
    $gender   = $_POST['gender'];

    // Validation
    if (empty($name))                                    $errors[] = "Full name is required.";
    if (empty($email))                                   $errors[] = "Email is required.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL))      $errors[] = "Email is not valid.";
    if (empty($password))                                $errors[] = "Password is required.";
    if (strlen($password) < 6)                           $errors[] = "Password must be at least 6 characters.";
    if ($password !== $confirm)                          $errors[] = "Passwords do not match.";
    if (empty($reg_no))                                  $errors[] = "Registration number is required.";

    // Check if email already exists
    if (empty($errors)) {
        $check = mysqli_prepare($conn, "SELECT Id FROM users WHERE Email = ?");
        mysqli_stmt_bind_param($check, "s", $email);
        mysqli_stmt_execute($check);
        mysqli_stmt_store_result($check);
        if (mysqli_stmt_num_rows($check) > 0) {
            $errors[] = "An account with this email already exists.";
        }
        mysqli_stmt_close($check);
    }

    // Check if reg number already exists
    if (empty($errors)) {
        $check2 = mysqli_prepare($conn, "SELECT Id FROM users WHERE RegNo = ?");
        mysqli_stmt_bind_param($check2, "s", $reg_no);
        mysqli_stmt_execute($check2);
        mysqli_stmt_store_result($check2);
        if (mysqli_stmt_num_rows($check2) > 0) {
            $errors[] = "This registration number is already in use.";
        }
        mysqli_stmt_close($check2);
    }

    // Insert into DB
    if (empty($errors)) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $role   = 'student';

        $sql  = "INSERT INTO users (Name, Email, Password, Phone, RegNo, Gender, Role) 
                 VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sssssss",
            $name, $email, $hashed, $phone, $reg_no, $gender, $role
        );

        if (mysqli_stmt_execute($stmt)) {
            header("Location: index.php?registered=1");
            exit();
        } else {
            $errors[] = "Something went wrong. Please try again.";
        }
        mysqli_stmt_close($stmt);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Register</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="Style2.css">
</head>
<body>
<div class="auth-wrapper">
  <div class="auth-card">

    <div class="auth-logo">
      <img src="28f0cfd287b28c114982f4015e69fcb2-lotus-colored-icon.webp" width="40" alt="Logo">
      <h2>Create Account</h2>
      <p>Register to access university events</p>
    </div>

    <?php if (!empty($errors)): ?>
      <div class="alert alert-danger">
        <?php foreach ($errors as $e): ?>
          <div>• <?= htmlspecialchars($e) ?></div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="register.php">

      <div class="mb-3">
        <label>Full Name</label>
        <input type="text" name="name" class="form-control"
               value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
      </div>

      <div class="mb-3">
        <label>Email Address</label>
        <input type="email" name="email" class="form-control"
               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
      </div>

      <div class="mb-3">
        <label>Registration Number</label>
        <input type="text" name="reg_no" class="form-control"
               value="<?= htmlspecialchars($_POST['reg_no'] ?? '') ?>" required>
      </div>

      <div class="mb-3">
        <label>Phone Number</label>
        <input type="text" name="phone" class="form-control"
               value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
      </div>

      <div class="mb-3">
        <label>Gender</label>
        <select name="gender" class="form-control">
          <option value="">-- Select --</option>
          <option value="Male"   <?= (($_POST['gender'] ?? '') === 'Male')   ? 'selected' : '' ?>>Male</option>
          <option value="Female" <?= (($_POST['gender'] ?? '') === 'Female') ? 'selected' : '' ?>>Female</option>
          <option value="Other"  <?= (($_POST['gender'] ?? '') === 'Other')  ? 'selected' : '' ?>>Other</option>
        </select>
      </div>

      <div class="mb-3">
        <label>Password</label>
        <input type="password" name="password" class="form-control" required>
      </div>

      <div class="mb-3">
        <label>Confirm Password</label>
        <input type="password" name="confirm_password" class="form-control" required>
      </div>

      <button type="submit" class="btn-auth">Create Account</button>

    </form>

    <p class="auth-switch">
      Already have an account? <a href="index.php">Log in</a>
    </p>

  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
