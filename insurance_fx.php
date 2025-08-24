<?php
require_once __DIR__ . '/db.php';
$pdo = db();

function mUAH($v){ return '₴' . number_format((float)$v, 2, ',', ' '); }
function mCUR($v,$c){ $sym = ($c==='USD'?'$':($c==='EUR'?'€':$c.' ')); return $sym . number_format((float)$v, 4, ',', ' '); }

$sources = $pdo->query("SELECT id,name,currency,amount_currency,display_rate,note,created_at
                        FROM insurance_fx_sources ORDER BY id DESC")->fetchAll();

$invest = $pdo->query("SELECT i.id,i.source_id,i.amount_uah,i.rate,i.quantity_currency,i.note,i.created_at,
                              s.name AS source_name, s.currency
                       FROM insurance_fx_investments i
                       JOIN insurance_fx_sources s ON s.id=i.source_id
                       ORDER BY i.created_at DESC, i.id DESC")->fetchAll();

$sumQty = [];
foreach ($invest as $r) {
  $sid = (int)$r['source_id'];
  $sumQty[$sid] = ($sumQty[$sid] ?? 0) + (float)$r['quantity_currency'];
}

$total_eq = 0.0;
foreach ($sources as &$s) {
  $sid = (int)$s['id'];
  $base = (float)$s['amount_currency'];
  $add  = (float)($sumQty[$sid] ?? 0);
  $s['balance_qty'] = $base + $add;
  $rate = (float)($s['display_rate'] ?? 0);
  $s['eq_uah'] = $rate>0 ? $s['balance_qty'] * $rate : 0;
  $total_eq += $s['eq_uah'];
}
unset($s);
$spent = 0.0; $remain = $total_eq - $spent;
?>

<div class="card2">
  <div class="title">Підсумок (40% валютний еквівалент)</div>
  <div class="grid">
    <div class="card2"><div class="muted">Грошовий еквівалент (40%)</div><div class="value"><?= mUAH($total_eq) ?></div></div>
    <div class="card2"><div class="muted">Витрачено</div><div class="value"><?= mUAH($spent) ?></div></div>
    <div class="card2"><div class="muted">Загальний капітал</div><div class="value"><?= mUAH($remain) ?></div></div>
  </div>
</div>

<div class="actions">
  <button class="btn outline" onclick="document.getElementById('fxCreate').showModal()">Створити джерело доходу</button>
  <button class="btn primary" onclick="document.getElementById('fxInvest').showModal()">Інвестувати капітал</button>
</div>

<div class="card2">
  <div class="title">Джерела доходу (валюта)</div>
  <div class="table-wrap">
    <table>
      <thead>
        <tr><th>Назва</th><th>Валюта</th><th class="text-end">Початково</th><th class="text-end">Додано</th><th class="text-end">Баланс</th><th class="text-end">Еквівалент (₴)</th><th class="text-end">Дії</th></tr>
      </thead>
      <tbody>
        <?php if (!$sources): ?>
          <tr><td colspan="7" class="muted">Порожньо</td></tr>
        <?php else: foreach ($sources as $s):
          $sid=(int)$s['id']; $base=(float)$s['amount_currency']; $add=(float)($sumQty[$sid]??0); $bal=$base+$add; $eq=(float)$s['eq_uah']; ?>
          <tr>
            <td><?= htmlspecialchars($s['name']) ?></td>
            <td><?= htmlspecialchars($s['currency']) ?></td>
            <td class="text-end"><?= mCUR($base,$s['currency']) ?></td>
            <td class="text-end"><?= mCUR($add,$s['currency']) ?></td>
            <td class="text-end"><strong><?= mCUR($bal,$s['currency']) ?></strong></td>
            <td class="text-end"><?= mUAH($eq) ?></td>
            <td class="text-end table-actions">
              <button class="btn outline" onclick="document.getElementById('fxEditSrc<?= $sid ?>').showModal()">Редагувати</button>
              <form method="post" action="insurance.php" style="display:inline" onsubmit="return confirm('Видалити джерело?');">
                <input type="hidden" name="action" value="fx_source_delete"><input type="hidden" name="id" value="<?= $sid ?>">
                <button class="btn danger">Видалити</button>
              </form>
            </td>
          </tr>
          <dialog id="fxEditSrc<?= $sid ?>">
            <form method="post" action="insurance.php" class="card" style="min-width:360px">
              <h3>Редагувати джерело</h3>
              <input type="hidden" name="action" value="fx_source_update"><input type="hidden" name="id" value="<?= $sid ?>">
              <label>Назва</label><input name="name" value="<?= htmlspecialchars($s['name']) ?>" required>
              <label>Валюта</label><input name="currency" value="<?= htmlspecialchars($s['currency']) ?>" required>
              <label>Початкова кількість (валюта)</label><input type="number" step="0.0001" name="amount_currency" value="<?= $base ?>" required>
              <label>Курс для відображення (₴ за 1)</label><input type="number" step="0.0001" name="display_rate" value="<?= (float)($s['display_rate'] ?? 0) ?>">
              <label>Нотатка</label><input name="note" value="<?= htmlspecialchars($s['note']) ?>">
              <div class="actions"><button type="button" class="btn" onclick="this.closest('dialog').close()">Скасувати</button><button class="btn primary">Зберегти</button></div>
            </form>
          </dialog>
        <?php endforeach; endif; ?>
        <?php if ($sources): ?>
          <tr>
            <td class="text-end" colspan="5"><strong>Загальна сума у джерелах</strong></td>
            <td class="text-end"><strong><?= mUAH($total_eq) ?></strong></td>
            <td></td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="card2">
  <div class="title">Історія інвестицій</div>
  <table>
    <thead><tr><th>Дата/час</th><th class="text-end">Сума (₴ → валюта)</th><th>Джерело</th><th>Валюта</th><th class="text-end">Курс</th><th class="text-end">К-сть</th><th>Нотатка</th><th class="text-end">Дії</th></tr></thead>
    <tbody>
      <?php if (!$invest): ?>
        <tr><td colspan="8" class="muted">Записів немає</td></tr>
      <?php else: foreach ($invest as $h): $hid=(int)$h['id']; ?>
        <tr>
          <td><?= htmlspecialchars($h['created_at']) ?></td>
          <td class="text-end"><?= mUAH($h['amount_uah']) ?> → <?= mCUR($h['quantity_currency'],$h['currency']) ?></td>
          <td><?= htmlspecialchars($h['source_name']) ?></td>
          <td><?= htmlspecialchars($h['currency']) ?></td>
          <td class="text-end"><?= number_format((float)$h['rate'],4,',',' ') ?></td>
          <td class="text-end"><?= number_format((float)$h['quantity_currency'],4,',',' ') ?></td>
          <td><?= htmlspecialchars($h['note']) ?></td>
          <td class="text-end table-actions">
            <button class="btn outline" onclick="document.getElementById('fxEditInv<?= $hid ?>').showModal()">Редагувати</button>
            <form method="post" action="insurance.php" style="display:inline" onsubmit="return confirm('Видалити запис?');">
              <input type="hidden" name="action" value="fx_invest_delete"><input type="hidden" name="id" value="<?= $hid ?>">
              <button class="btn danger">Видалити</button>
            </form>
          </td>
        </tr>
        <dialog id="fxEditInv<?= $hid ?>">
          <form method="post" action="insurance.php" class="card" style="min-width:360px">
            <h3>Редагувати інвестицію</h3>
            <input type="hidden" name="action" value="fx_invest_update"><input type="hidden" name="id" value="<?= $hid ?>">
            <label>Сума (₴)</label><input type="number" step="0.01" name="amount_uah" value="<?= (float)$h['amount_uah'] ?>" required>
            <label>Курс (₴ за 1)</label><input type="number" step="0.0001" name="rate" value="<?= (float)$h['rate'] ?>" required>
            <label>Нотатка</label><input name="note" value="<?= htmlspecialchars($h['note']) ?>">
            <div class="actions"><button type="button" class="btn" onclick="this.closest('dialog').close()">Скасувати</button><button class="btn primary">Зберегти</button></div>
          </form>
        </dialog>
      <?php endforeach; endif; ?>
    </tbody>
  </table>
</div>

<!-- Dialogs -->
<dialog id="fxCreate">
  <form method="post" action="insurance.php" class="card" style="min-width:360px">
    <h3>Створити джерело доходу</h3>
    <input type="hidden" name="action" value="fx_source_create">
    <label>Назва</label><input name="name" required>
    <label>Валюта</label><input name="currency" placeholder="USD / EUR / ..." required>
    <label>Сума (у валюті, початкова)</label><input type="number" step="0.0001" name="amount_currency" required>
    <label>Курс для відображення (₴ за 1)</label><input type="number" step="0.0001" name="display_rate" placeholder="необов'язково">
    <label>Нотатка</label><input name="note">
    <div class="actions"><button type="button" class="btn" onclick="document.getElementById('fxCreate').close()">Скасувати</button><button class="btn primary">Створити</button></div>
  </form>
</dialog>

<dialog id="fxInvest">
  <form method="post" action="insurance.php" class="card" style="min-width:360px">
    <h3>Інвестувати капітал</h3>
    <input type="hidden" name="action" value="fx_invest_create">
    <label>Сума (₴)</label><input type="number" step="0.01" name="amount_uah" required>
    <label>Джерело</label>
    <select name="source_id" required>
      <option value="" disabled selected>Оберіть…</option>
      <?php foreach ($sources as $s): ?><option value="<?= (int)$s['id'] ?>"><?= htmlspecialchars($s['name'].' ('.$s['currency'].')') ?></option><?php endforeach; ?>
    </select>
    <label>Курс (₴ за 1)</label><input type="number" step="0.0001" name="rate" required>
    <label>Нотатка</label><input name="note">
    <div class="actions"><button type="button" class="btn" onclick="document.getElementById('fxInvest').close()">Скасувати</button><button class="btn primary">Зберегти</button></div>
  </form>
</dialog>
