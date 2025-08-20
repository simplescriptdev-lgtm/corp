<?php
session_start();
if (isset($_SESSION['user'])) {
    header("Location: secret.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Логін</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body class="login-page">
    <div class="login-box">
        <h2>Вхід</h2>
        <form method="post" action="login.php">
            <input type="text" name="username" placeholder="Логін" required>
            <input type="password" name="password" placeholder="Пароль" required>
            <button type="submit">Увійти</button>
        </form>
    </div>
</body>
</html>
