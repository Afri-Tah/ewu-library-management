<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_admin();
require_once __DIR__ . '/includes/db_connect.php';

$message = "";
$errors = [];

if (isset($_POST['submit'])) {
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
            "INSERT INTO books
             (title, isbn, publication_year, edition, total_copies, available_copies, shelf_no, author_id, category_id, publisher_id)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param(
            "ssssiisiii",
            $title, $isbn, $publication_year, $edition,
            $total_copies, $available_copies, $shelf_no,
            $author_id, $category_id, $publisher_id
        );

        if ($stmt->execute()) {
            $message = "Book added successfully.";
        } else {
            $errors[] = ($conn->errno === 1062)
                ? "A book with that ISBN already exists."
                : "Could not add the book. Please try again.";
        }
        $stmt->close();
    }
}

$authors    = $conn->query("SELECT author_id, author_name FROM authors ORDER BY author_name");
$categories = $conn->query("SELECT category_id, category_name FROM categories ORDER BY category_name");
$publishers = $conn->query("SELECT publisher_id, publisher_name FROM publishers ORDER BY publisher_name");

$page_title = "Add Book";
include 'header.php';
?>

<div class="card">
<h2>Add New Book</h2>

<?php if ($message): ?><div class="alert alert-success"><?php echo h($message); ?></div><?php endif; ?>
<?php foreach ($errors as $e): ?><div class="alert alert-error"><?php echo h($e); ?></div><?php endforeach; ?>

<form method="POST">
<?php echo csrf_field(); ?>

<label>Title</label><br>
<input type="text" name="title" required>
<br><br>

<label>ISBN</label><br>
<input type="text" name="isbn" required>
<br><br>

<label>Publication Year</label><br>
<input type="number" name="publication_year" min="1000" max="2100" required>
<br><br>

<label>Edition</label><br>
<input type="text" name="edition">
<br><br>

<label>Total Copies</label><br>
<input type="number" name="total_copies" min="0" required>
<br><br>

<label>Available Copies</label><br>
<input type="number" name="available_copies" min="0" required>
<br><br>

<label>Shelf Number</label><br>
<input type="text" name="shelf_no" required>
<br><br>

<label>Author</label><br>
<select name="author_id" required>
<option value="">-- Select Author --</option>
<?php while ($row = $authors->fetch_assoc()): ?>
<option value="<?php echo (int)$row['author_id']; ?>"><?php echo h($row['author_name']); ?></option>
<?php endwhile; ?>
</select>
<br><br>

<label>Category</label><br>
<select name="category_id" required>
<option value="">-- Select Category --</option>
<?php while ($row = $categories->fetch_assoc()): ?>
<option value="<?php echo (int)$row['category_id']; ?>"><?php echo h($row['category_name']); ?></option>
<?php endwhile; ?>
</select>
<br><br>

<label>Publisher</label><br>
<select name="publisher_id" required>
<option value="">-- Select Publisher --</option>
<?php while ($row = $publishers->fetch_assoc()): ?>
<option value="<?php echo (int)$row['publisher_id']; ?>"><?php echo h($row['publisher_name']); ?></option>
<?php endwhile; ?>
</select>
<br><br>

<input type="submit" name="submit" value="Add Book">
</form>

<br>
<a class="btn btn-outline" href="view_books.php">View All Books</a>
</div>

<?php include 'footer.php'; ?>
