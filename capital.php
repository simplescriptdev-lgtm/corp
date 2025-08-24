<?php
require_once __DIR__.'/db.php'; $pdo = db();
function mUAH($v){ return '₴'.number_format((float)$v,2,',',' '); }

$hist = $pdo->query("SELECT * FROM capital_inflows ORDER BY created_at DESC, id DESC")->fetchAll();
$sum_owner = (float)$pdo->query("SELECT COALESCE(SUM(amount),0) s FROM capital_inflows WHERE source='owner'")->fetch()['s'];
$sum_bank  = (float)$pdo->query("SELECT COALESCE(SUM(amount),0) s FROM capital_inflows WHERE source='bank'")->fetch()['s'];
$total = $sum_owner + $sum_bank;
?>
<div class="card2">
  <div class="title">Рух капіталу — додати надходження</div>
  <form class="actions" method="post" action="insurance.php">
    <input type="hidden" name="action" value="cap_add">
    <select name="source">
      <option value="owner">Власник</option>
      <option value="bank">Банк</option>
    </select>
    <select name="category" required>
      <option value="" disabled selected>Куди зарахувати</option>
      <option value="owner">Капітал власника</option>
      <option value="operational">Операційний капітал</option>
      <option value="it">IT‑компанія</option>
      <option value="charity">Благодійний фонд</option>
      <option value="insurance">Страховий фонд</option>
    </select>
    <input type="number" step="0.01" name="amount" placeholder="Сума (₴)" required>
    <button class="btn primary">Додати</button>
  </form>
  <div class="grid">
    <div class="card2"><div class="muted">Від власника</div><div class="value"><?= mUAH($sum_owner) ?></div></div>
    <div class="card2"><div class="muted">Від банків</div><div class="value"><?= mUAH($sum_bank) ?></div></div>
    <div class="card2"><div class="muted">Загалом</div><div class="value"><?= mUAH($total) ?></div></div>
  </div>
</div>

<div class="card2">
  <div class="title">Історія надходжень</div>
  <table>
    <thead><tr><th>Дата</th><th>Джерело</th><th>Куди</th><th class="text-end">Сума</th><th class="text-end">Дії</th></tr></thead>
    <tbody>
      <?php if(!$hist): ?><tr><td colspan="5" class="muted">Записів немає</td></tr><?php endif; ?>
      <?php foreach($hist as $r): ?>
      <tr>
        <td><?= htmlspecialchars($r['created_at']) ?></td>
        <td><?= htmlspecialchars($r['source']) ?></td>
        <td><?= htmlspecialchars($r['category']) ?></td>
        <td class="text-end"><?= mUAH($r['amount']) ?></td>
        <td class="text-end">
          <form method="post" action="insurance.php" onsubmit="return confirm('Видалити запис?')">
            <input type="hidden" name="action" value="cap_delete"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
            <button class="btn danger">Видалити</button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
