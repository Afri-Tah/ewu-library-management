<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_login();

$current = basename($_SERVER['SCRIPT_NAME']);
function nav_active($file, $current) {
    return $file === $current ? ' class="active"' : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo isset($page_title) ? h($page_title) . ' | ' : ''; ?>EWU Library</title>
<link rel="icon" href="assets/images/logo.png">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<header class="site-header">
  <div class="header-inner">
    <a class="brand" href="<?php echo is_admin() ? 'admin_dashboard.php' : 'member_dashboard.php'; ?>">
      <img src="assets/images/logo.png" alt="EWU Logo" class="brand-logo">
      <span class="brand-text">EWU Library<small>East West University</small></span>
    </a>

    <nav class="main-nav">
      <a href="<?php echo is_admin() ? 'admin_dashboard.php' : 'member_dashboard.php'; ?>"<?php echo nav_active(is_admin() ? 'admin_dashboard.php' : 'member_dashboard.php', $current); ?>>Dashboard</a>
      <a href="view_books.php"<?php echo nav_active('view_books.php', $current); ?>>Books</a>
      <?php if (is_admin()): ?>
      <a href="view_members.php"<?php echo nav_active('view_members.php', $current); ?>>Members</a>
      <?php endif; ?>
      <a href="view_borrows.php"<?php echo nav_active('view_borrows.php', $current); ?>>Borrow</a>
      <a href="view_fines.php"<?php echo nav_active('view_fines.php', $current); ?>>Fines</a>
    </nav>

    <div class="user-box">
      <div class="who">
        <b><?php echo h($_SESSION['username']); ?></b>
        <span class="role-pill"><?php echo h($_SESSION['role']); ?></span>
      </div>
      <a href="logout.php" class="btn-logout">Logout</a>
    </div>
  </div>
</header>

<main class="page-content">
