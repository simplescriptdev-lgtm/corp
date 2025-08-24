<?php
require_once __DIR__.'/db.php'; $pdo = db();
function mUAH($v){ return '₴'.number_format((float)$v,2,',',' '); }

$in_total = (float)$pdo->query("SELECT COALESCE(SUM(amount),0) s FROM capital_inflows WHERE category='charity'")->fetch()['s'];
$part25 = (float)$pdo->query("SELECT COALESCE(SUM(amount),0) s FROM charity_outflows WHERE part='25'")->fetch()['s'];
$part75 = (float)$pdo->query("SELECT COALESCE(SUM(amount),0) s FROM charity_outflows WHERE part='75'")->fetch()['s'];
$spent_total = $part25 + $part75;
$remain = $in_total - $spent_total;

$hist = $pdo->query("SELECT * FROM charity_outflows ORDER BY created_at DESC,id DESC")->fetchAll();
?>
<div class="card2">
  <div class="title">Благодійний фонд</div>
  <table>
    <tbody>
      <tr><td>Надходження капіталу (усього)</td><td class="text-end"><strong><?= mUAH($in_total) ?></strong></td></tr>
      <tr><td>Представницькі витрати (25%)</td><td class="text-end"><?= mUAH($part25) ?></td></tr>
      <tr><td>Благодійні внески (75%)</td><td class="text-end"><?= mUAH($part75) ?></td></tr>
      <tr><td><strong>Залишок</strong></td><td class="text-end"><strong><?= mUAH($remain) ?></strong></td></tr>
    </tbody>
  </table>
</div>

<div class="actions">
  <button class="btn outline" onclick="document.getElementById('char25').showModal()">Додати 25%</button>
  <button class="btn primary" onclick="document.getElementById('char75').showModal()">Додати 75%</button>
</div>

<div class="card2">
  <div class="title">Історія</div>
  <table>
    <thead><tr><th>Дата</th><th>Частка</th><th class="text-end">Сума</th><th>Нотатка</th><th class="text-end">Дії</th></tr></thead>
    <tbody>
      <?php if(!$hist): ?><tr><td colspan="5" class="muted">Записів немає</td></tr><?php endif; ?>
      <?php foreach($hist as $r): $id=(int)$r['id']; ?>
      <tr>
        <td><?= htmlspecialchars($r['created_at']) ?></td>
        <td><?= htmlspecialchars($r['part']) ?>%</td>
        <td class="text-end"><?= mUAH($r['amount']) ?></td>
        <td><?= htmlspecialchars($r['note']) ?></td>
        <td class="text-end">
          <button class="btn outline" onclick="document.getElementById('chE<?= $id ?>').showModal()">Редагувати</button>
          <form method="post" action="insurance.php" style="display:inline" onsubmit="return confirm('Видалити запис?')">
            <input type="hidden" name="action" value="charity_outflow_delete"><input type="hidden" name="id" value="<?= $id ?>">
            <button class="btn danger">Видалити</button>
          </form>
        </td>
      </tr>
      <dialog id="chE<?= $id ?>"><form method="post" action="insurance.php" class="card">
        <h3>Редагувати</h3>
        <input type="hidden" name="action" value="charity_outflow_update"><input type="hidden" name="id" value="<?= $id ?>">
        <label>Сума (₴)</label><input type="number" step="0.01" name="amount" value="<?= (float)$r['amount'] ?>" required>
        <label>Нотатка</label><input name="note" value="<?= htmlspecialchars($r['note']) ?>">
        <div class="actions"><button type="button" class="btn" onclick="this.closest('dialog').close()">Скасувати</button><button class="btn primary">Зберегти</button></div>
      </form></dialog>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<dialog id="char25"><form method="post" action="insurance.php" class="card">
  <h3>Додати 25%</h3>
  <input type="hidden" name="action" value="charity_outflow_create"><input type="hidden" name="part" value="25">
  <label>Сума (₴)</label><input type="number" step="0.01" name="amount" required>
  <label>Нотатка</label><input name="note">
  <div class="actions"><button type="button" class="btn" onclick="document.getElementById('char25').close()">Скасувати</button><button class="btn primary">Зберегти</button></div>
</form></dialog>

<dialog id="char75"><form method="post" action="insurance.php" class="card">
  <h3>Додати 75%</h3>
  <input type="hidden" name="action" value="charity_outflow_create"><input type="hidden" name="part" value="75">
  <label>Сума (₴)</label><input type="number" step="0.01" name="amount" required>
  <label>Нотатка</label><input name="note">
  <div class="actions"><button type="button" class="btn" onclick="document.getElementById('char75').close()">Скасувати</button><button class="btn primary">Зберегти</button></div>
</form></dialog>
