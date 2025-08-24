<?php
require_once __DIR__.'/db.php'; $pdo = db();
function mUAH($v){ return '₴'.number_format((float)$v,2,',',' '); }
$in_owner = (float)$pdo->query("SELECT COALESCE(SUM(amount),0) s FROM capital_inflows WHERE category='owner'")->fetch()['s'];
$in_bank  = (float)$pdo->query("SELECT COALESCE(SUM(amount),0) s FROM capital_inflows WHERE source='bank' AND category='owner'")->fetch()['s'];
$in_total = (float)$pdo->query("SELECT COALESCE(SUM(amount),0) s FROM capital_inflows WHERE category='owner'")->fetch()['s'];
$out_total= (float)$pdo->query("SELECT COALESCE(SUM(amount),0) s FROM owner_withdrawals")->fetch()['s'];
$remain = $in_total - $out_total;
$hist = $pdo->query("SELECT * FROM owner_withdrawals ORDER BY created_at DESC,id DESC")->fetchAll();
?>
<div class="card2">
  <div class="title">Капітал власника</div>
  <table>
    <tbody>
      <tr><td>Надходження від власника</td><td class="text-end"><strong><?= mUAH($in_owner) ?></strong></td></tr>
      <tr><td>Надходження від банків</td><td class="text-end"><?= mUAH($in_bank) ?></td></tr>
      <tr><td><strong>Загальне надходження</strong></td><td class="text-end"><strong><?= mUAH($in_total) ?></strong></td></tr>
    </tbody>
  </table>
</div>
<div class="card2">
  <div class="title">Баланс</div>
  <table>
    <tbody>
      <tr><td>Загальне надходження</td><td class="text-end"><?= mUAH($in_total) ?></td></tr>
      <tr><td>Виведено капіталу</td><td class="text-end"><?= mUAH($out_total) ?></td></tr>
      <tr><td><strong>Залишок капіталу</strong></td><td class="text-end"><strong><?= mUAH($remain) ?></strong></td></tr>
    </tbody>
  </table>
  <div class="actions"><button class="btn primary" onclick="document.getElementById('ownW').showModal()">Вивести капітал</button></div>
</div>
<div class="card2">
  <div class="title">Історія виведення</div>
  <table>
    <thead><tr><th>Дата</th><th class="text-end">Сума</th><th>Нотатка</th><th class="text-end">Дії</th></tr></thead>
    <tbody>
      <?php if(!$hist): ?><tr><td colspan="4" class="muted">Записів немає</td></tr><?php endif; ?>
      <?php foreach($hist as $r): $id=(int)$r['id']; ?>
      <tr>
        <td><?= htmlspecialchars($r['created_at']) ?></td>
        <td class="text-end"><?= mUAH($r['amount']) ?></td>
        <td><?= htmlspecialchars($r['note']) ?></td>
        <td class="text-end">
          <button class="btn outline" onclick="document.getElementById('ownE<?= $id ?>').showModal()">Редагувати</button>
          <form method="post" action="insurance.php" style="display:inline" onsubmit="return confirm('Видалити запис?')">
            <input type="hidden" name="action" value="owner_withdraw_delete"><input type="hidden" name="id" value="<?= $id ?>">
            <button class="btn danger">Видалити</button>
          </form>
        </td>
      </tr>
      <dialog id="ownE<?= $id ?>"><form method="post" action="insurance.php" class="card">
        <h3>Редагувати</h3>
        <input type="hidden" name="action" value="owner_withdraw_update"><input type="hidden" name="id" value="<?= $id ?>">
        <label>Сума (₴)</label><input type="number" step="0.01" name="amount" value="<?= (float)$r['amount'] ?>" required>
        <label>Нотатка</label><input name="note" value="<?= htmlspecialchars($r['note']) ?>">
        <div class="actions"><button type="button" class="btn" onclick="this.closest('dialog').close()">Скасувати</button><button class="btn primary">Зберегти</button></div>
      </form></dialog>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<dialog id="ownW"><form method="post" action="insurance.php" class="card">
  <h3>Вивести капітал</h3>
  <input type="hidden" name="action" value="owner_withdraw_create">
  <label>Сума (₴)</label><input type="number" step="0.01" name="amount" required>
  <label>Нотатка</label><input name="note">
  <div class="actions"><button type="button" class="btn" onclick="document.getElementById('ownW').close()">Скасувати</button><button class="btn primary">Зберегти</button></div>
</form></dialog>
