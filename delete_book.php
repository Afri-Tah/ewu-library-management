<?php
require_once __DIR__ . '/includes/auth.php';
require_admin();
require_once __DIR__ . '/includes/db_connect.php';

// POST-only + CSRF-protected so a book can't be deleted via a bare link/image (CSRF).
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['id'])) {
    header("Location: view_books.php");
    exit();
}
verify_csrf();

$id = (int)$_POST['id'];

$stmt = $conn->prepare("DELETE FROM books WHERE book_id = ?");
$stmt->bind_param("i", $id);

try {
    $stmt->execute();
    header("Location: view_books.php?deleted=1");
} catch (mysqli_sql_exception $e) {
    // Error 1451 = foreign key constraint fails on delete (borrow history exists).
    $msg = ($conn->errno === 1451)
        ? "Could not delete this book — it has existing borrow records on file."
        : "Could not delete this book. Please try again.";
    header("Location: view_books.php?error=" . urlencode($msg));
}
exit();
