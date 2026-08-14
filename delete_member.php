<?php
require_once __DIR__ . '/includes/auth.php';
require_admin();
require_once __DIR__ . '/includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['id'])) {
    header("Location: view_members.php");
    exit();
}
verify_csrf();

$id = (int)$_POST['id'];

$stmt = $conn->prepare("DELETE FROM members WHERE member_id = ?");
$stmt->bind_param("i", $id);

try {
    $stmt->execute();
    header("Location: view_members.php?deleted=1");
} catch (mysqli_sql_exception $e) {
    // Error 1451 = foreign key constraint fails on delete (borrow history exists).
    $msg = ($conn->errno === 1451)
        ? "Could not delete this member — they still have borrow or fine records on file. Return/clear those first, or keep the member on record."
        : "Could not delete this member. Please try again.";
    header("Location: view_members.php?error=" . urlencode($msg));
}
exit();
