<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_login(); // Book list is no longer public — must be logged in (Admin or Member).
require_once __DIR__ . '/includes/db_connect.php';

$sql = "SELECT books.book_id, books.title, books.isbn, books.publication_year, books.edition,
               books.total_copies, books.available_copies, books.shelf_no,
               authors.author_name, categories.category_name, publishers.publisher_name
        FROM books
        INNER JOIN authors ON books.author_id = authors.author_id
        INNER JOIN categories ON books.category_id = categories.category_id
        INNER JOIN publishers ON books.publisher_id = publishers.publisher_id
        ORDER BY books.book_id";
$result = $conn->query($sql);

$page_title = "Books";
include 'header.php';
?>

<div class="card">
<h2>Book List</h2>

<?php if (!empty($_GET['deleted'])): ?>
<div class="alert alert-success">Book deleted successfully.</div>
<?php endif; ?>
<?php if (!empty($_GET['error'])): ?>
<div class="alert alert-error"><?php echo h($_GET['error']); ?></div>
<?php endif; ?>

<div class="table-wrap">
<table>
<tr>
<th>ID</th><th>Title</th><th>ISBN</th><th>Year</th><th>Edition</th>
<th>Total</th><th>Available</th><th>Shelf</th><th>Author</th><th>Category</th><th>Publisher</th>
<?php if (is_admin()): ?><th>Edit</th><th>Delete</th><?php endif; ?>
</tr>

<?php while ($row = $result->fetch_assoc()): ?>
<tr>
<td><?php echo (int)$row['book_id']; ?></td>
<td><?php echo h($row['title']); ?></td>
<td><?php echo h($row['isbn']); ?></td>
<td><?php echo h($row['publication_year']); ?></td>
<td><?php echo h($row['edition']); ?></td>
<td><?php echo (int)$row['total_copies']; ?></td>
<td><?php echo (int)$row['available_copies']; ?></td>
<td><?php echo h($row['shelf_no']); ?></td>
<td><?php echo h($row['author_name']); ?></td>
<td><?php echo h($row['category_name']); ?></td>
<td><?php echo h($row['publisher_name']); ?></td>
<?php if (is_admin()): ?>
<td><a class="btn btn-sm btn-outline" href="edit_book.php?id=<?php echo (int)$row['book_id']; ?>">Edit</a></td>
<td>
  <form method="POST" action="delete_book.php" onsubmit="return confirm('Delete this book?');" style="display:inline;">
    <?php echo csrf_field(); ?>
    <input type="hidden" name="id" value="<?php echo (int)$row['book_id']; ?>">
    <button type="submit" class="btn-sm">Delete</button>
  </form>
</td>
<?php endif; ?>
</tr>
<?php endwhile; ?>
</table>
</div>

<br>
<?php if (is_admin()): ?>
<a class="btn btn-primary" href="add_book.php">+ Add New Book</a>
<?php endif; ?>
</div>

<?php include 'footer.php'; ?>
