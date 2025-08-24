<?php
require_once __DIR__ . '/db.php';
session_start();
$pdo = db();

if (isset($_SESSION['user_id'])) { header('Location: dashboard.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = trim($_POST['username'] ?? '');
    $p = (string)($_POST['password'] ?? '');
    if ($u === '' || $p === '') {
        $error = 'Введіть логін і пароль';
    } else {
        $st = $pdo->prepare('SELECT id, username, password_hash FROM users WHERE username=? LIMIT 1');
        $st->execute([$u]);
        $user = $st->fetch();
        if ($user && password_verify($p, $user['password_hash'])) {
            $_SESSION['user_id'] = (int)$user['id'];
            $_SESSION['username'] = $user['username'];
            header('Location: dashboard.php'); exit;
        } else {
            $error = 'Невірний логін або пароль';
        }
    }
}
?><!doctype html>
<html lang="uk">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Вхід</title>
  <link rel="stylesheet" href="assets/main.css">
</head>
<body class="login">
  <div class="wrap">
    <form class="card" method="post" action="index.php" autocomplete="off">
      <h1>Вхід до системи</h1>
      <?php if ($error): ?><div class="alert error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
      <label>Логін</label>
      <input type="text" name="username" required autofocus>
      <label>Пароль</label>
      <input type="password" name="password" required>
      <button class="btn primary" type="submit">Увійти</button>
      <div class="hint">За замовчуванням: <b>admin</b> / <b>123456</b></div>
    </form>
  </div>
</body>
</html>
