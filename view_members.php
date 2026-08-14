<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_admin(); // Member PII (phone/email) is admin-only, unlike the public book catalog.
require_once __DIR__ . '/includes/db_connect.php';

$sql = "SELECT members.member_id, members.student_id, members.full_name, members.phone,
               members.department, users.username, users.email
        FROM members
        INNER JOIN users ON members.user_id = users.user_id
        ORDER BY members.member_id";
$result = $conn->query($sql);

$page_title = "Members";
include 'header.php';
?>

<div class="card">
<h2>Member List</h2>

<?php if (!empty($_GET['deleted'])): ?>
<div class="alert alert-success">Member deleted successfully.</div>
<?php endif; ?>
<?php if (!empty($_GET['error'])): ?>
<div class="alert alert-error"><?php echo h($_GET['error']); ?></div>
<?php endif; ?>

<div class="table-wrap">
<table>
<tr>
<th>ID</th><th>Student ID</th><th>Name</th><th>Username</th><th>Email</th><th>Phone</th><th>Department</th>
<th>Edit</th><th>Delete</th>
</tr>

<?php while ($row = $result->fetch_assoc()): ?>
<tr>
<td><?php echo (int)$row['member_id']; ?></td>
<td><?php echo h($row['student_id']); ?></td>
<td><?php echo h($row['full_name']); ?></td>
<td><?php echo h($row['username']); ?></td>
<td><?php echo h($row['email']); ?></td>
<td><?php echo h($row['phone']); ?></td>
<td><?php echo h($row['department']); ?></td>
<td><a class="btn btn-sm btn-outline" href="edit_member.php?id=<?php echo (int)$row['member_id']; ?>">Edit</a></td>
<td>
  <form method="POST" action="delete_member.php" onsubmit="return confirm('Delete this member?');" style="display:inline;">
    <?php echo csrf_field(); ?>
    <input type="hidden" name="id" value="<?php echo (int)$row['member_id']; ?>">
    <button type="submit" class="btn-sm">Delete</button>
  </form>
</td>
</tr>
<?php endwhile; ?>
</table>
</div>

<br>
<a class="btn btn-primary" href="add_member.php">+ Add Member</a>
</div>

<?php include 'footer.php'; ?>
