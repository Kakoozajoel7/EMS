<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header(($_SESSION['role'] ?? '') === 'admin' ? "Location: adminDashboard.php" : "Location: StudentDashboard.php");
    exit();
}

require 'dbconnect.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = "Please enter both email and password.";
    } else {

        // Check admin table first
        $sql  = "SELECT * FROM admin WHERE Email = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $admin  = mysqli_fetch_assoc($result);

        if ($admin && $password === $admin['Password']) {
            // Admin login — set session
            $_SESSION['user_id']    = $admin['Id'];
            $_SESSION['admin_id']   = $admin['Id'];
            $_SESSION['full_name']  = $admin['Name'];
            $_SESSION['admin_name'] = $admin['Name'];
            $_SESSION['email']      = $admin['Email'];
            $_SESSION['admin_email'] = $admin['Email'];
            $_SESSION['role']       = 'admin';
            $_SESSION['last_activity'] = time();
            header("Location: adminDashboard.php");
            exit();
        }

        // Check users table
        $sql2  = "SELECT * FROM users WHERE Email = ?";
        $stmt2 = mysqli_prepare($conn, $sql2);
        mysqli_stmt_bind_param($stmt2, "s", $email);
        mysqli_stmt_execute($stmt2);
        $result2 = mysqli_stmt_get_result($stmt2);
        $user    = mysqli_fetch_assoc($result2);

        if ($user && password_verify($password, $user['Password'])) {
            // Student login — set session
            $_SESSION['user_id']   = $user['Id'];
            $_SESSION['full_name'] = $user['Name'];
            $_SESSION['email']     = $user['Email'];
            $_SESSION['role']      = $user['Role'];
            $_SESSION['last_activity'] = time();
            header("Location: StudentDashboard.php");
            exit();
        }

        // Neither matched
        $error = "Incorrect email or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="Style2.css">
</head>
<body>
<div class="auth-wrapper">
  <div class="auth-card">

    <div class="auth-logo">
      <img src="28f0cfd287b28c114982f4015e69fcb2-lotus-colored-icon.webp" width="40" alt="Logo">
      <h2>Welcome Back</h2>
      <p>Log in to your account</p>
    </div>

    <?php if (isset($_GET['registered'])): ?>
      <div class="alert alert-success">Account created! You can now log in.</div>
    <?php endif; ?>

    <?php if (isset($_GET['timeout'])): ?>
      <div class="alert alert-warning">Your session expired after 20 minutes of inactivity. Please log in again.</div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="index.php">
      <div class="mb-3">
        <label>Email Address</label>
        <input type="text" name="email" class="form-control" inputmode="email" autocomplete="email"
               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
      </div>
      <div class="mb-3">
        <label>Password</label>
        <input type="password" name="password" class="form-control" required>
      </div>
      <button type="submit" class="btn-auth">Log In</button>
    </form>

    <p class="auth-switch">
      Don't have an account? <a href="register.php">Register</a>
    </p>

  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
