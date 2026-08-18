<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_login();
require_once __DIR__ . '/includes/db_connect.php';

// Finalized fines: rows already in `fines`, created at return time.
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

// Accruing fines: books that are overdue and still checked out. No row in
// `fines` exists for these yet (that only happens on return), so previously
// these were invisible everywhere — a member could owe a large amount and
// nobody would see a number until the book actually came back. Compute the
// live estimate here instead of waiting for the return.
if (is_admin()) {
    $accrue_sql = "SELECT borrows.borrow_id, members.full_name, books.title, borrows.due_date
                    FROM borrows
                    INNER JOIN members ON borrows.member_id = members.member_id
                    INNER JOIN books ON borrows.book_id = books.book_id
                    WHERE borrows.status = 'Borrowed' AND borrows.due_date < CURDATE()
                    ORDER BY borrows.due_date ASC";
    $accruing_result = $conn->query($accrue_sql);
} else {
    $stmt = $conn->prepare(
        "SELECT borrows.borrow_id, members.full_name, books.title, borrows.due_date
         FROM borrows
         INNER JOIN members ON borrows.member_id = members.member_id
         INNER JOIN books ON borrows.book_id = books.book_id
         WHERE members.user_id = ? AND borrows.status = 'Borrowed' AND borrows.due_date < CURDATE()
         ORDER BY borrows.due_date ASC"
    );
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $accruing_result = $stmt->get_result();
}
$accruing_rows = $accruing_result->fetch_all(MYSQLI_ASSOC);

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
<?php foreach ($accruing_rows as $row):
    $late = days_late($row['due_date']);
    $amount = $late * FINE_PER_DAY;
?>
<tr>
<td>—</td>
<td><?php echo h($row['full_name']); ?></td>
<td><?php echo h($row['title']); ?></td>
<td><?php echo h(number_format($amount, 2)); ?></td>
<td><span class="badge badge-bad">Accruing</span></td>
<?php if (is_admin()): ?>
<td><span title="Book is still checked out — fine finalizes and becomes payable once it's returned.">Not yet returned</span></td>
<?php endif; ?>
</tr>
<?php endforeach; ?>
</table>
</div>
<?php if (!empty($accruing_rows)): ?>
<p style="margin-top:8px; font-size:0.9em; color:#666;">
  "Accruing" rows are books still checked out past their due date — the fine grows daily (৳<?php echo (int)FINE_PER_DAY; ?>/day) and becomes a real, payable record once the book is returned.
</p>
<?php endif; ?>
</div>

<?php include 'footer.php'; ?>
