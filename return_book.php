<?php
require_once __DIR__ . '/includes/auth.php';
require_admin();
require_once __DIR__ . '/includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['id'])) {
    header("Location: view_borrows.php");
    exit();
}
verify_csrf();

$borrow_id = (int)$_POST['id'];

$conn->begin_transaction();
try {
    $stmt = $conn->prepare("SELECT book_id, due_date, status FROM borrows WHERE borrow_id = ? FOR UPDATE");
    $stmt->bind_param("i", $borrow_id);
    $stmt->execute();
    $borrow = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$borrow || $borrow['status'] !== 'Borrowed') {
        $conn->rollback();
        die("This borrow record is invalid or already returned.");
    }

    $book_id     = $borrow['book_id'];
    $due_date    = $borrow['due_date'];
    $return_date = date("Y-m-d");

    $stmt = $conn->prepare("UPDATE borrows SET return_date = ?, status = 'Returned' WHERE borrow_id = ?");
    $stmt->bind_param("si", $return_date, $borrow_id);
    $stmt->execute();
    $stmt->close();

    $stmt = $conn->prepare("UPDATE books SET available_copies = available_copies + 1 WHERE book_id = ?");
    $stmt->bind_param("i", $book_id);
    $stmt->execute();
    $stmt->close();

    if (strtotime($return_date) > strtotime($due_date)) {
        $days_late = (int) floor((strtotime($return_date) - strtotime($due_date)) / 86400);
        $amount = $days_late * 20;

        $stmt = $conn->prepare("INSERT INTO fines (borrow_id, amount, status) VALUES (?, ?, 'Unpaid')");
        $stmt->bind_param("id", $borrow_id, $amount);
        $stmt->execute();
        $stmt->close();
    }

    $conn->commit();
} catch (mysqli_sql_exception $e) {
    $conn->rollback();
    die("Could not process the return. Please try again.");
}

header("Location: view_borrows.php");
exit();
