<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_login();
require_once __DIR__ . '/includes/db_connect.php';

$page_title = "My Dashboard";
include 'header.php';

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT member_id FROM members WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$member = $stmt->get_result()->fetch_assoc();
$stmt->close();

$rows = [];
$active_count = 0;
$returned_count = 0;

if ($member) {
    $id = $member['member_id'];
    $stmt = $conn->prepare(
        "SELECT books.title, borrows.borrow_date, borrows.due_date, borrows.status
         FROM borrows
         INNER JOIN books ON borrows.book_id = books.book_id
         WHERE borrows.member_id = ?
         ORDER BY borrows.borrow_date DESC"
    );
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $rows = $stmt->get_result();
}
?>

<div class="page-title-row">
  <h2 style="border:none;">My Borrowed Books</h2>
</div>

<?php if (!$member): ?>
<div class="card">
  <div class="alert alert-error">Your account is not yet linked to a member profile. Please contact the library admin.</div>
</div>
<?php else: ?>

<div class="card">
  <div class="table-wrap">
    <table>
      <tr><th>Book</th><th>Borrow Date</th><th>Due Date</th><th>Status</th><th>Fine Due</th></tr>
      <?php while ($row = $rows->fetch_assoc()):
          $status = effective_borrow_status($row['status'], $row['due_date']);
          $badge_class = $status === 'Returned' ? 'badge-ok' : ($status === 'Overdue' ? 'badge-bad' : 'badge-warn');
          $late = $status === 'Overdue' ? days_late($row['due_date']) : 0;
      ?>
      <tr>
        <td><?php echo h($row['title']); ?></td>
        <td><?php echo h($row['borrow_date']); ?></td>
        <td><?php echo h($row['due_date']); ?></td>
        <td><span class="badge <?php echo $badge_class; ?>"><?php echo h($status); ?></span></td>
        <td><?php echo $late > 0 ? h(number_format($late * FINE_PER_DAY, 2)) . ' (accruing)' : '—'; ?></td>
      </tr>
      <?php endwhile; ?>
    </table>
  </div>
</div>

<?php endif; ?>

<?php include 'footer.php'; ?>
