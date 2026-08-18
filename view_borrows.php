<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_login();
require_once __DIR__ . '/includes/db_connect.php';

if (is_admin()) {
    $sql = "SELECT borrows.borrow_id, members.full_name, books.title, borrows.borrow_date,
                   borrows.due_date, borrows.return_date, borrows.status
            FROM borrows
            INNER JOIN members ON borrows.member_id = members.member_id
            INNER JOIN books ON borrows.book_id = books.book_id
            ORDER BY borrows.borrow_id DESC";
    $result = $conn->query($sql);
} else {
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare(
        "SELECT borrows.borrow_id, members.full_name, books.title, borrows.borrow_date,
                borrows.due_date, borrows.return_date, borrows.status
         FROM borrows
         INNER JOIN members ON borrows.member_id = members.member_id
         INNER JOIN books ON borrows.book_id = books.book_id
         WHERE members.user_id = ?
         ORDER BY borrows.borrow_id DESC"
    );
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
}

$page_title = "Borrow Records";
include 'header.php';
?>

<div class="card">
<h2>Borrow Records</h2>

<div class="table-wrap">
<table>
<tr>
<th>ID</th><th>Member</th><th>Book</th><th>Borrow Date</th><th>Due Date</th><th>Return Date</th><th>Status</th>
<?php if (is_admin()): ?><th>Return</th><?php endif; ?>
</tr>

<?php while ($row = $result->fetch_assoc()):
    $status = effective_borrow_status($row['status'], $row['due_date']);
    $badge_class = $status === 'Returned' ? 'badge-ok' : ($status === 'Overdue' ? 'badge-bad' : 'badge-warn');
?>
<tr>
<td><?php echo (int)$row['borrow_id']; ?></td>
<td><?php echo h($row['full_name']); ?></td>
<td><?php echo h($row['title']); ?></td>
<td><?php echo h($row['borrow_date']); ?></td>
<td><?php echo h($row['due_date']); ?></td>
<td><?php echo h($row['return_date']); ?></td>
<td><span class="badge <?php echo $badge_class; ?>"><?php echo h($status); ?></span></td>
<?php if (is_admin()): ?>
<td>
<?php if ($row['status'] === 'Borrowed'): ?>
  <form method="POST" action="return_book.php" onsubmit="return confirm('Mark this book as returned?');" style="display:inline;">
    <?php echo csrf_field(); ?>
    <input type="hidden" name="id" value="<?php echo (int)$row['borrow_id']; ?>">
    <button type="submit" class="btn-sm">Return</button>
  </form>
<?php else: ?>
  <span class="badge badge-ok">Returned</span>
<?php endif; ?>
</td>
<?php endif; ?>
</tr>
<?php endwhile; ?>
</table>
</div>

<br>
<?php if (is_admin()): ?>
<a class="btn btn-primary" href="borrow_book.php">Borrow New Book</a>
<?php endif; ?>
</div>

<?php include 'footer.php'; ?>
