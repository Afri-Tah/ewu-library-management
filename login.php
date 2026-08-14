<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/db_connect.php';

if (is_logged_in()) {
    header("Location: " . (is_admin() ? "admin_dashboard.php" : "member_dashboard.php"));
    exit();
}

$error = "";

if (isset($_POST['login'])) {
    verify_csrf();

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = "Please enter both username and password.";
    } else {
        $stmt = $conn->prepare("SELECT user_id, username, password, role FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        // password_verify() also transparently supports the legacy bcrypt
        // hash formats ($2a$/$2b$/$2y$) produced by the seed data.
        if ($row && password_verify($password, $row['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id']  = $row['user_id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['role']     = $row['role'];

            header("Location: " . ($row['role'] === "Admin" ? "admin_dashboard.php" : "member_dashboard.php"));
            exit();
        } else {
            $error = "Invalid Username or Password";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Login | EWU Library</title>
<link rel="icon" href="assets/images/logo.png">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="login-shell">
  <div class="login-card">

    <div class="login-visual">
      <div>
        <img src="assets/images/logo.png" alt="East West University" class="visual-logo">
        <h1>EWU Library<br>Management System</h1>
        <p>Search the catalog, track your borrowed books, and manage fines &mdash; all in one place.</p>
      </div>
      <div>
        <span class="tagline">Excellence in Education</span>
      </div>
    </div>

    <div class="login-form-side">
      <h2>Welcome Back</h2>
      <p class="sub">Sign in with your library account to continue.</p>

      <?php if ($error !== ""): ?>
      <div class="alert alert-error"><?php echo h($error); ?></div>
      <?php endif; ?>

      <form method="POST">
        <?php echo csrf_field(); ?>

        <label for="username">Username</label>
        <input type="text" id="username" name="username" required autofocus>

        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>

        <input type="submit" name="login" value="Login">
      </form>
    </div>

  </div>
</div>

</body>
</html>
