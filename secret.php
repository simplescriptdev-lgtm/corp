<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head><title>Secret</title></head>
<body>
<h2>Welcome, <?php echo $_SESSION['user']; ?>!</h2>
<a href="logout.php">Logout</a>
</body>
</html>