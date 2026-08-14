<?php
require_once __DIR__ . '/includes/auth.php';
require_admin(); // Original file had NO auth check at all — this was a critical hole.
require_once __DIR__ . '/includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['id'])) {
    header("Location: view_fines.php");
    exit();
}
verify_csrf();

$id = (int)$_POST['id'];

$stmt = $conn->prepare("UPDATE fines SET status = 'Paid' WHERE fine_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

header("Location: view_fines.php");
exit();
