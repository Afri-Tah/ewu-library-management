<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_admin();
require_once __DIR__ . '/includes/db_connect.php';

if (empty($_GET['id'])) {
    die("Invalid Book ID");
}
$id = (int)$_GET['id'];

$stmt = $conn->prepare("SELECT * FROM books WHERE book_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$row) {
    die("Book Not Found");
}

$errors = [];

if (isset($_POST['update'])) {
    verify_csrf();

    $title             = trim($_POST['title'] ?? '');
    $isbn              = trim($_POST['isbn'] ?? '');
    $publication_year  = trim($_POST['publication_year'] ?? '');
    $edition           = trim($_POST['edition'] ?? '');
    $total_copies      = (int)($_POST['total_copies'] ?? 0);
    $available_copies  = (int)($_POST['available_copies'] ?? 0);
    $shelf_no          = trim($_POST['shelf_no'] ?? '');
    $author_id         = (int)($_POST['author_id'] ?? 0);
    $category_id       = (int)($_POST['category_id'] ?? 0);
    $publisher_id      = (int)($_POST['publisher_id'] ?? 0);

    if ($title === '' || $isbn === '' || $author_id <= 0 || $category_id <= 0 || $publisher_id <= 0) {
        $errors[] = "Please fill in all required fields.";
    }
    if ($available_copies > $total_copies) {
        $errors[] = "Available copies cannot exceed total copies.";
    }

    if (!$errors) {
        $stmt = $conn->prepare(
            "UPDATE books SET title=?, isbn=?, publication_year=?, edition=?, total_copies=?,
             available_copies=?, shelf_no=?, author_id=?, category_id=?, publisher_id=?
             WHERE book_id=?"
        );
        $stmt->bind_param(
            "ssssiisiiii",
            $title, $isbn, $publication_year, $edition,
            $total_copies, $available_copies, $shelf_no,
            $author_id, $category_id, $publisher_id, $id
        );

        if ($stmt->execute()) {
            header("Location: view_books.php");
            exit();
        }
        $errors[] = "Update failed. Please try again.";
        $stmt->close();
    }

    // Keep the form pre-filled with the attempted values on error.
    $row = array_merge($row, compact(
        'title','isbn','publication_year','edition','total_copies',
        'available_copies','shelf_no','author_id','category_id','publisher_id'
    ));
}

$authors    = $conn->query("SELECT author_id, author_name FROM authors ORDER BY author_name");
$categories = $conn->query("SELECT category_id, category_name FROM categories ORDER BY category_name");
$publishers = $conn->query("SELECT publisher_id, publisher_name FROM publishers ORDER BY publisher_name");
$page_title = "Edit Book";
include 'header.php';
?>

<div class="card">
<h2>Edit Book</h2>

<?php foreach ($errors as $e): ?><div class="alert alert-error"><?php echo h($e); ?></div><?php endforeach; ?>

<form method="POST">
<?php echo csrf_field(); ?>

Title<br>
<input type="text" name="title" value="<?php echo h($row['title']); ?>" required>
<br><br>

ISBN<br>
<input type="text" name="isbn" value="<?php echo h($row['isbn']); ?>" required>
<br><br>

Publication Year<br>
<input type="number" name="publication_year" value="<?php echo h($row['publication_year']); ?>" required>
<br><br>

Edition<br>
<input type="text" name="edition" value="<?php echo h($row['edition']); ?>">
<br><br>

Total Copies<br>
<input type="number" name="total_copies" value="<?php echo h($row['total_copies']); ?>" min="0">
<br><br>

Available Copies<br>
<input type="number" name="available_copies" value="<?php echo h($row['available_copies']); ?>" min="0">
<br><br>

Shelf Number<br>
<input type="text" name="shelf_no" value="<?php echo h($row['shelf_no']); ?>">
<br><br>

Author<br>
<select name="author_id">
<?php while ($a = $authors->fetch_assoc()): ?>
<option value="<?php echo (int)$a['author_id']; ?>" <?php echo $a['author_id'] == $row['author_id'] ? 'selected' : ''; ?>>
<?php echo h($a['author_name']); ?>
</option>
<?php endwhile; ?>
</select>
<br><br>

Category<br>
<select name="category_id">
<?php while ($c = $categories->fetch_assoc()): ?>
<option value="<?php echo (int)$c['category_id']; ?>" <?php echo $c['category_id'] == $row['category_id'] ? 'selected' : ''; ?>>
<?php echo h($c['category_name']); ?>
</option>
<?php endwhile; ?>
</select>
<br><br>

Publisher<br>
<select name="publisher_id">
<?php while ($p = $publishers->fetch_assoc()): ?>
<option value="<?php echo (int)$p['publisher_id']; ?>" <?php echo $p['publisher_id'] == $row['publisher_id'] ? 'selected' : ''; ?>>
<?php echo h($p['publisher_name']); ?>
</option>
<?php endwhile; ?>
</select>
<br><br>

<input type="submit" name="update" value="Update Book">
</form>

<br>
<a class="btn btn-outline" href="view_books.php">Back to Books</a>
</div>

<?php include 'footer.php'; ?>
