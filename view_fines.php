<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_login();
require_once __DIR__ . '/includes/db_connect.php';

if (is_admin()) {
    $sql = "SELECT fines.fine_id, members.full_name, books.title, fines.amount, fines.status
            FROM fines
            INNER JOIN borrows ON fines.borrow_id = borrows.borrow_id
            INNER JOIN members ON borrows.member_id = members.member_id
            INNER JOIN books ON borrows.book_id = books.book_id
            ORDER BY fines.fine_id DESC";
    $result = $conn->query($sql);
} else {
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare(
        "SELECT fines.fine_id, members.full_name, books.title, fines.amount, fines.status
         FROM fines
         INNER JOIN borrows ON fines.borrow_id = borrows.borrow_id
         INNER JOIN members ON borrows.member_id = members.member_id
         INNER JOIN books ON borrows.book_id = books.book_id
         WHERE members.user_id = ?
         ORDER BY fines.fine_id DESC"
    );
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
}

$page_title = "Fine List";
include 'header.php';
?>

<div class="card">
<h2>Fine List</h2>

<div class="table-wrap">
<table>
<tr>
<th>ID</th><th>Member</th><th>Book</th><th>Amount</th><th>Status</th>
<?php if (is_admin()): ?><th>Pay</th><?php endif; ?>
</tr>

<?php while ($row = $result->fetch_assoc()): ?>
<tr>
<td><?php echo (int)$row['fine_id']; ?></td>
<td><?php echo h($row['full_name']); ?></td>
<td><?php echo h($row['title']); ?></td>
<td><?php echo h(number_format((float)$row['amount'], 2)); ?></td>
<td><span class="badge <?php echo $row['status'] === 'Paid' ? 'badge-ok' : 'badge-bad'; ?>"><?php echo h($row['status']); ?></span></td>
<?php if (is_admin()): ?>
<td>
<?php if ($row['status'] === 'Unpaid'): ?>
  <form method="POST" action="pay_fine.php" onsubmit="return confirm('Mark this fine as paid?');" style="display:inline;">
    <?php echo csrf_field(); ?>
    <input type="hidden" name="id" value="<?php echo (int)$row['fine_id']; ?>">
    <button type="submit" class="btn-sm">Pay</button>
  </form>
<?php else: ?>
  <span class="badge badge-ok">Paid</span>
<?php endif; ?>
</td>
<?php endif; ?>
</tr>
<?php endwhile; ?>
</table>
</div>
</div>

<?php include 'footer.php'; ?>
