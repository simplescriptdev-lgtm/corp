<?php
require_once __DIR__ . '/db.php';
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: index.php'); exit; }

$tab = $_GET['tab'] ?? 'capital';
$sub = $_GET['sub'] ?? '';
?><!doctype html>
<html lang="uk">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Дашборд</title>
  <link rel="stylesheet" href="assets/main.css">
</head>
<body>
<div class="page">
  <aside class="sidebar">
    <ul class="menu">
      <li><a class="<?= $tab==='capital'?'active':'' ?>" href="dashboard.php?tab=capital">Рух капіталу</a></li>
      <li><a class="<?= $tab==='owner'?'active':'' ?>" href="dashboard.php?tab=owner">Капітал власника</a></li>
      <li><a class="<?= $tab==='operational'?'active':'' ?>" href="dashboard.php?tab=operational">Операційний капітал</a></li>
      <li><a class="<?= $tab==='it'?'active':'' ?>" href="dashboard.php?tab=it">IT‑компанія</a></li>
      <li><a class="<?= $tab==='charity'?'active':'' ?>" href="dashboard.php?tab=charity">Благодійний фонд</a></li>
      <li>
        <a class="<?= $tab==='insurance'?'active':'' ?>" href="dashboard.php?tab=insurance&sub=saldo">Страховий фонд</a>
        <?php if ($tab==='insurance'): ?>
        <ul class="submenu">
          <li><a class="<?= $sub==='saldo'?'active':'' ?>" href="dashboard.php?tab=insurance&sub=saldo">Сальдо</a></li>
          <li><a class="<?= $sub==='uah'?'active':'' ?>" href="dashboard.php?tab=insurance&sub=uah">40% гривневий</a></li>
          <li><a class="<?= $sub==='fx'?'active':'' ?>" href="dashboard.php?tab=insurance&sub=fx">40% валютний</a></li>
          <li><a class="<?= $sub==='metal'?'active':'' ?>" href="dashboard.php?tab=insurance&sub=metal">20% метали</a></li>
          <li><a class="<?= $sub==='profit'?'active':'' ?>" href="dashboard.php?tab=insurance&sub=profit">Розподіл прибутку</a></li>
        </ul>
        <?php endif; ?>
      </li>
    </ul>
  </aside>
  <main class="content">
    <?php
    if ($tab==='capital') {
      if (file_exists(__DIR__.'/capital.php')) include __DIR__.'/capital.php'; else echo '<div class="section"><h2>Рух капіталу</h2></div>';
    } elseif ($tab==='owner') {
      if (file_exists(__DIR__.'/owner.php')) include __DIR__.'/owner.php'; else echo '<div class="section"><h2>Капітал власника</h2></div>';
    } elseif ($tab==='operational') {
      if (file_exists(__DIR__.'/operational.php')) include __DIR__.'/operational.php'; else echo '<div class="section"><h2>Операційний капітал</h2></div>';
    } elseif ($tab==='it') {
      if (file_exists(__DIR__.'/it.php')) include __DIR__.'/it.php'; else echo '<div class="section"><h2>IT‑компанія</h2></div>';
    } elseif ($tab==='charity') {
      if (file_exists(__DIR__.'/charity.php')) include __DIR__.'/charity.php'; else echo '<div class="section"><h2>Благодійний фонд</h2></div>';
    } elseif ($tab==='insurance') {
      echo '<div class="section"><h2>Страховий фонд</h2></div>';
      if ($sub==='fx') {
        include __DIR__.'/insurance_fx.php';
      } elseif ($sub==='uah') {
        if (file_exists(__DIR__.'/insurance_uah.php')) include __DIR__.'/insurance_uah.php'; else echo '<div class="card2">Вкладка "40% гривневий" — підключіть <code>insurance_uah.php</code>.</div>';
      } elseif ($sub==='saldo') {
        if (file_exists(__DIR__.'/insurance_saldo.php')) include __DIR__.'/insurance_saldo.php'; else echo '<div class="card2">Вкладка "Сальдо" — підключіть <code>insurance_saldo.php</code>.</div>';
      } elseif ($sub==='metal') {
        if (file_exists(__DIR__.'/insurance_metal.php')) include __DIR__.'/insurance_metal.php'; else echo '<div class="card2">Вкладка "Метали" — підключіть <code>insurance_metal.php</code>.</div>';
      } elseif ($sub==='profit') {
        if (file_exists(__DIR__.'/insurance_profit.php')) include __DIR__.'/insurance_profit.php'; else echo '<div class="card2">Вкладка "Розподіл прибутку" — підключіть <code>insurance_profit.php</code>.</div>';
      } else {
        echo '<div class="card2">Оберіть підвкладку ліворуч.</div>';
      }
    } else {
      echo '<div class="section"><h2>Дашборд</h2></div>';
    }
    ?>
  </main>
</div>
</body>
</html>
