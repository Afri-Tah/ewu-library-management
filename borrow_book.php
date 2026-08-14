<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_admin();
require_once __DIR__ . '/includes/db_connect.php';

$message = "";
$errors = [];

if (isset($_POST['borrow'])) {
    verify_csrf();

    $member_id = (int)($_POST['member_id'] ?? 0);
    $book_id   = (int)($_POST['book_id'] ?? 0);

    if ($member_id <= 0 || $book_id <= 0) {
        $errors[] = "Please select both a member and a book.";
    } else {
        $conn->begin_transaction();
        try {
            // Lock the row so two simultaneous borrows can't both pass the check.
            $stmt = $conn->prepare("SELECT available_copies FROM books WHERE book_id = ? FOR UPDATE");
            $stmt->bind_param("i", $book_id);
            $stmt->execute();
            $book = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$book || $book['available_copies'] <= 0) {
                $errors[] = "This book is not currently available.";
                $conn->rollback();
            } else {
                $borrow_date = date("Y-m-d");
                $due_date    = date("Y-m-d", strtotime("+7 days"));

                $stmt = $conn->prepare(
                    "INSERT INTO borrows (member_id, book_id, borrow_date, due_date, status)
                     VALUES (?, ?, ?, ?, 'Borrowed')"
                );
                $stmt->bind_param("iiss", $member_id, $book_id, $borrow_date, $due_date);
                $stmt->execute();
                $stmt->close();

                $stmt = $conn->prepare("UPDATE books SET available_copies = available_copies - 1 WHERE book_id = ?");
                $stmt->bind_param("i", $book_id);
                $stmt->execute();
                $stmt->close();

                $conn->commit();
                $message = "Book borrowed successfully.";
            }
        } catch (mysqli_sql_exception $e) {
            $conn->rollback();
            $errors[] = "Could not complete the borrow. Please try again.";
        }
    }
}

$members = $conn->query("SELECT member_id, full_name FROM members ORDER BY full_name");
$books   = $conn->query("SELECT book_id, title, available_copies FROM books WHERE available_copies > 0 ORDER BY title");
$page_title = "Borrow Book";
include 'header.php';
?>

<div class="card">
<h2>Borrow Book</h2>

<?php if ($message): ?><div class="alert alert-success"><?php echo h($message); ?></div><?php endif; ?>
<?php foreach ($errors as $e): ?><div class="alert alert-error"><?php echo h($e); ?></div><?php endforeach; ?>

<form method="POST">
<?php echo csrf_field(); ?>

Member<br>
<select name="member_id" required>
<option value="">Select Member</option>
<?php while ($row = $members->fetch_assoc()): ?>
<option value="<?php echo (int)$row['member_id']; ?>"><?php echo h($row['full_name']); ?></option>
<?php endwhile; ?>
</select>
<br><br>

Book<br>
<select name="book_id" required>
<option value="">Select Book</option>
<?php while ($row = $books->fetch_assoc()): ?>
<option value="<?php echo (int)$row['book_id']; ?>">
<?php echo h($row['title']); ?> (Available: <?php echo (int)$row['available_copies']; ?>)
</option>
<?php endwhile; ?>
</select>
<br><br>

<input type="submit" name="borrow" value="Borrow Book">
</form>

<br>
<a class="btn btn-outline" href="view_borrows.php">View Borrow Records</a>
</div>

<?php include 'footer.php'; ?>
