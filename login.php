<?php
session_start();
$db = new SQLite3('users.db');
$username = $_POST['username'];
$password = $_POST['password'];
$stmt = $db->prepare("SELECT * FROM users WHERE username=:u AND password=:p");
$stmt->bindValue(':u', $username, SQLITE3_TEXT);
$stmt->bindValue(':p', $password, SQLITE3_TEXT);
$result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
if ($result) {
    $_SESSION['user'] = $username;
    header("Location: secret.php");
    exit();
} else {
    echo "Invalid credentials. <a href='index.php'>Try again</a>";
}
?>