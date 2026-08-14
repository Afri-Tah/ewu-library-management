<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_admin();
require_once __DIR__ . '/includes/db_connect.php';

$message = "";
$errors = [];

// Keep values so the form can be re-filled if something goes wrong.
$in = [
    'username'    => '',
    'email'       => '',
    'student_id'  => '',
    'full_name'   => '',
    'phone'       => '',
    'department'  => '',
];

if (isset($_POST['submit'])) {
    verify_csrf();

    foreach ($in as $key => $_) {
        $in[$key] = trim($_POST[$key] ?? '');
    }
    $password = $_POST['password'] ?? '';

    if ($in['username'] === '' || $in['email'] === '' || $password === '' ||
        $in['student_id'] === '' || $in['full_name'] === '') {
        $errors[] = "Please fill in all required fields (Username, Email, Password, Student ID, Full Name).";
    }
    if ($password !== '' && strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters.";
    }
    if ($in['email'] !== '' && !filter_var($in['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }

    if (!$errors) {
        // Create the login account and the member profile together, atomically:
        // if either insert fails, roll back so we never leave an orphaned user
        // account with no matching member (or vice versa).
        $conn->begin_transaction();
        try {
            $hash = password_hash($password, PASSWORD_BCRYPT);

            $stmt = $conn->prepare(
                "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'Member')"
            );
            $stmt->bind_param("sss", $in['username'], $in['email'], $hash);
            $stmt->execute();
            $new_user_id = $stmt->insert_id;
            $stmt->close();

            $stmt = $conn->prepare(
                "INSERT INTO members (user_id, student_id, full_name, phone, department) VALUES (?, ?, ?, ?, ?)"
            );
            $stmt->bind_param(
                "issss",
                $new_user_id, $in['student_id'], $in['full_name'], $in['phone'], $in['department']
            );
            $stmt->execute();
            $stmt->close();

            $conn->commit();
            $message = "Member \"" . $in['full_name'] . "\" added successfully. They can log in with username \""
                      . $in['username'] . "\" and the password you set.";

            // Clear the form on success.
            foreach ($in as $key => $_) { $in[$key] = ''; }
        } catch (mysqli_sql_exception $e) {
            $conn->rollback();
            if ($conn->errno === 1062) {
                $dupField = str_contains($e->getMessage(), 'username') ? 'username'
                          : (str_contains($e->getMessage(), 'email') ? 'email' : 'student ID');
                $errors[] = "That $dupField is already in use. Please choose a different one.";
            } else {
                error_log("Add member failed: " . $e->getMessage());
                $errors[] = "Could not add the member (DB error " . $conn->errno . "): " . $e->getMessage();
            }
        }
    }
}

$page_title = "Add Member";
include 'header.php';
?>

<div class="card">
<h2>Add Member</h2>
<p style="color:var(--text-muted); margin-top:-10px; font-size:0.9rem;">
  This creates a login account and a library member profile together in one step.
</p>

<?php if ($message): ?><div class="alert alert-success"><?php echo h($message); ?></div><?php endif; ?>
<?php foreach ($errors as $e): ?><div class="alert alert-error"><?php echo h($e); ?></div><?php endforeach; ?>

<form method="POST">
<?php echo csrf_field(); ?>

<label>Username</label>
<input type="text" name="username" value="<?php echo h($in['username']); ?>" placeholder="e.g. rafi.khan" required>

<label>Email</label>
<input type="email" name="email" value="<?php echo h($in['email']); ?>" placeholder="rafi@ewubd.edu" required>

<label>Password</label>
<input type="password" name="password" placeholder="At least 6 characters" required>

<label>Student ID</label>
<input type="text" name="student_id" value="<?php echo h($in['student_id']); ?>" placeholder="e.g. 2023-1-60-009" required>

<label>Full Name</label>
<input type="text" name="full_name" value="<?php echo h($in['full_name']); ?>" required>

<label>Phone</label>
<input type="text" name="phone" value="<?php echo h($in['phone']); ?>">

<label>Department</label>
<input type="text" name="department" value="<?php echo h($in['department']); ?>" placeholder="e.g. CSE">

<input type="submit" name="submit" value="Add Member">
</form>

<br>
<a class="btn btn-outline" href="view_members.php">View Members</a>
</div>

<?php include 'footer.php'; ?>
