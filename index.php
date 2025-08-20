<?php
session_start();
if (isset($_SESSION['user'])) {
    header("Location: secret.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
</head>
<body>
<h2>Login</h2>
<form method="post" action="login.php">
    <label>Username:</label><input type="text" name="username"><br>
    <label>Password:</label><input type="password" name="password"><br>
    <button type="submit">Login</</button>
</form>
</body>
</html>