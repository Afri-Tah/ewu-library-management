<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_admin();
require_once __DIR__ . '/includes/db_connect.php';

if (empty($_GET['id'])) {
    die("Invalid Member");
}
$id = (int)$_GET['id'];

$stmt = $conn->prepare("SELECT * FROM members WHERE member_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$row) {
    die("Member Not Found");
}

$errors = [];

if (isset($_POST['update'])) {
    verify_csrf();

    $student_id = trim($_POST['student_id'] ?? '');
    $full_name  = trim($_POST['full_name'] ?? '');
    $phone      = trim($_POST['phone'] ?? '');
    $department = trim($_POST['department'] ?? '');

    if ($student_id === '' || $full_name === '') {
        $errors[] = "Student ID and Full Name are required.";
    }

    if (!$errors) {
        $stmt = $conn->prepare(
            "UPDATE members SET student_id=?, full_name=?, phone=?, department=? WHERE member_id=?"
        );
        $stmt->bind_param("ssssi", $student_id, $full_name, $phone, $department, $id);

        if ($stmt->execute()) {
            header("Location: view_members.php");
            exit();
        }
        $errors[] = ($conn->errno === 1062) ? "That Student ID is already in use." : "Update failed.";
        $stmt->close();
    }
    $row = array_merge($row, compact('student_id','full_name','phone','department'));
}
$page_title = "Edit Member";
include 'header.php';
?>

<div class="card">
<h2>Edit Member</h2>

<?php foreach ($errors as $e): ?><div class="alert alert-error"><?php echo h($e); ?></div><?php endforeach; ?>

<form method="POST">
<?php echo csrf_field(); ?>

Student ID<br>
<input type="text" name="student_id" value="<?php echo h($row['student_id']); ?>" required>
<br><br>

Full Name<br>
<input type="text" name="full_name" value="<?php echo h($row['full_name']); ?>" required>
<br><br>

Phone<br>
<input type="text" name="phone" value="<?php echo h($row['phone']); ?>">
<br><br>

Department<br>
<input type="text" name="department" value="<?php echo h($row['department']); ?>">
<br><br>

<input type="submit" name="update" value="Update Member">
</form>

<br>
<a class="btn btn-outline" href="view_members.php">Back to Members</a>
</div>

<?php include 'footer.php'; ?>
