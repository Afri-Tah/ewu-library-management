<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_admin();
require_once __DIR__ . '/includes/db_connect.php';

$page_title = "Admin Dashboard";
include 'header.php';

$books   = $conn->query("SELECT COUNT(*) AS total FROM books")->fetch_assoc();
$members = $conn->query("SELECT COUNT(*) AS total FROM members")->fetch_assoc();
$borrow  = $conn->query("SELECT COUNT(*) AS total FROM borrows WHERE status='Borrowed'")->fetch_assoc();
$fines   = $conn->query("SELECT COUNT(*) AS total FROM fines WHERE status='Unpaid'")->fetch_assoc();
?>

<div class="page-title-row">
  <h2 style="border:none;">Administrator Dashboard</h2>
</div>

<div class="stat-grid">
  <div class="stat-card books">
    <div class="icon">&#128218;</div>
    <div>
      <div class="num"><?php echo (int)$books['total']; ?></div>
      <div class="label">Total Books</div>
    </div>
  </div>

  <div class="stat-card members">
    <div class="icon">&#128101;</div>
    <div>
      <div class="num"><?php echo (int)$members['total']; ?></div>
      <div class="label">Total Members</div>
    </div>
  </div>

  <div class="stat-card borrowed">
    <div class="icon">&#128218;</div>
    <div>
      <div class="num"><?php echo (int)$borrow['total']; ?></div>
      <div class="label">Borrowed Books</div>
    </div>
  </div>

  <div class="stat-card fines">
    <div class="icon">&#9888;</div>
    <div>
      <div class="num"><?php echo (int)$fines['total']; ?></div>
      <div class="label">Unpaid Fines</div>
    </div>
  </div>
</div>

<div class="card" style="margin-top:24px;">
  <h2>Quick Actions</h2>
  <div class="landing-actions" style="justify-content:flex-start;">
    <a class="btn btn-primary" href="add_book.php">+ Add Book</a>
    <a class="btn btn-outline" href="add_member.php">+ Add Member</a>
    <a class="btn btn-outline" href="borrow_book.php">Borrow Book</a>
    <a class="btn btn-outline" href="view_fines.php">Manage Fines</a>
  </div>
</div>

<?php include 'footer.php'; ?>
