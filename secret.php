<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Дашборд</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body class="dashboard">
    <header>
        <h1>Дашборд</h1>
        <a href="logout.php" class="logout-btn">Вийти</a>
    </header>
    <div class="container">
        <nav class="sidebar">
            <ul>
                <li><a href="#">Головна</a></li>
                <li><a href="#">Операційний капітал</a></li>
                <li><a href="#">Звіти</a></li>
            </ul>
        </nav>
        <main class="content">
            <h2>Вітаю, <?= $_SESSION['user'] ?>!</h2>
            <p>Тут буде ваш контент.</p>
        </main>
    </div>
</body>
</html>
