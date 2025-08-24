<?php
// insurance.php — UAH + FX endpoints with PRG redirects
declare(strict_types=1);
require __DIR__ . '/db.php';
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: index.php'); exit; }
$pdo = db();

function back($sub){ header('Location: dashboard.php?tab=insurance&sub='.$sub); exit; }
$action = $_POST['action'] ?? $_GET['action'] ?? '';

/* UAH */
if ($action==='source_create'){
  $name=trim($_POST['name']??''); $amount=(float)($_POST['amount']??0); $note=trim($_POST['note']??'');
  if ($name!==''){ $pdo->prepare("INSERT INTO insurance_sources(name,amount,note,created_at) VALUES(?,?,?,datetime('now'))")->execute([$name,$amount,$note?:null]); }
  back('uah');
}
if ($action==='source_update'){
  $id=(int)($_POST['id']??0); $name=trim($_POST['name']??''); $amount=(float)($_POST['amount']??0); $note=trim($_POST['note']??'');
  if ($id>0 && $name!==''){ $pdo->prepare("UPDATE insurance_sources SET name=?,amount=?,note=? WHERE id=?")->execute([$name,$amount,$note?:null,$id]); }
  back('uah');
}
if ($action==='source_delete'){
  $id=(int)($_POST['id']??0); if ($id>0){ $pdo->prepare("DELETE FROM insurance_sources WHERE id=?")->execute([$id]); }
  back('uah');
}
if ($action==='invest_create'){
  $sid=(int)($_POST['source_id']??0); $amount=(float)($_POST['amount']??0); $note=trim($_POST['note']??'');
  if ($sid>0 && $amount>0){ $pdo->prepare("INSERT INTO insurance_investments(source_id,amount,note,created_at) VALUES(?,?,?,datetime('now'))")->execute([$sid,$amount,$note?:null]); }
  back('uah');
}
if ($action==='invest_update'){
  $id=(int)($_POST['id']??0); $amount=(float)($_POST['amount']??0); $note=trim($_POST['note']??'');
  if ($id>0){ $pdo->prepare("UPDATE insurance_investments SET amount=?,note=? WHERE id=?")->execute([$amount,$note?:null,$id]); }
  back('uah');
}
if ($action==='invest_delete'){
  $id=(int)($_POST['id']??0); if ($id>0){ $pdo->prepare("DELETE FROM insurance_investments WHERE id=?")->execute([$id]); }
  back('uah');
}

/* FX */
if ($action==='fx_source_create'){
  $name=trim($_POST['name']??''); $currency=strtoupper(trim($_POST['currency']??''));
  $amount_currency=(float)($_POST['amount_currency']??0); $display_rate=($_POST['display_rate']??'')!==''?(float)$_POST['display_rate']:null; $note=trim($_POST['note']??'');
  if ($name!=='' && $currency!==''){
    $pdo->prepare("INSERT INTO insurance_fx_sources(name,currency,amount_currency,display_rate,note,created_at) VALUES(?,?,?,?,?,datetime('now'))")
        ->execute([$name,$currency,$amount_currency,$display_rate,$note?:null]);
  }
  back('fx');
}
if ($action==='fx_source_update'){
  $id=(int)($_POST['id']??0); $name=trim($_POST['name']??''); $currency=strtoupper(trim($_POST['currency']??''));
  $amount_currency=(float)($_POST['amount_currency']??0); $display_rate=($_POST['display_rate']??'')!==''?(float)$_POST['display_rate']:null; $note=trim($_POST['note']??'');
  if ($id>0 && $name!=='' && $currency!==''){
    $pdo->prepare("UPDATE insurance_fx_sources SET name=?,currency=?,amount_currency=?,display_rate=?,note=? WHERE id=?")
        ->execute([$name,$currency,$amount_currency,$display_rate,$note?:null,$id]);
  }
  back('fx');
}
if ($action==='fx_source_delete'){
  $id=(int)($_POST['id']??0); if ($id>0){ $pdo->prepare("DELETE FROM insurance_fx_sources WHERE id=?")->execute([$id]); }
  back('fx');
}
if ($action==='fx_invest_create'){
  $sid=(int)($_POST['source_id']??0); $uah=(float)($_POST['amount_uah']??0); $rate=(float)($_POST['rate']??0); $note=trim($_POST['note']??'');
  if ($sid>0 && $uah>0 && $rate>0){
    $qty = $uah / $rate;
    $pdo->prepare("INSERT INTO insurance_fx_investments(source_id,amount_uah,rate,quantity_currency,note,created_at) VALUES(?,?,?,?,?,datetime('now'))")
        ->execute([$sid,$uah,$rate,$qty,$note?:null]);
    $pdo->prepare("UPDATE insurance_fx_sources SET display_rate=? WHERE id=?")->execute([$rate,$sid]);
  }
  back('fx');
}
if ($action==='fx_invest_update'){
  $id=(int)($_POST['id']??0); $uah=(float)($_POST['amount_uah']??0); $rate=(float)($_POST['rate']??0); $note=trim($_POST['note']??'');
  if ($id>0 && $rate>0){
    $qty = $uah / $rate;
    $pdo->prepare("UPDATE insurance_fx_investments SET amount_uah=?,rate=?,quantity_currency=?,note=? WHERE id=?")
        ->execute([$uah,$rate,$qty,$note?:null,$id]);
  }
  back('fx');
}
if ($action==='fx_invest_delete'){
  $id=(int)($_POST['id']??0); if ($id>0){ $pdo->prepare("DELETE FROM insurance_fx_investments WHERE id=?")->execute([$id]); }
  back('fx');
}

// fallback
back('fx');

<?php
// Extra endpoints for other sections (owner/operational/it/charity/capital)
if (!function_exists('redir')) { function redir($url){ header('Location: '.$url); exit; } }
$pdo = db();

// CAPITAL INFLOWS (add/edit/delete)
if (($action ?? '') === 'cap_add') {
    $source = trim($_POST['source'] ?? 'owner');
    $category = trim($_POST['category'] ?? '');
    $amount = (float)($_POST['amount'] ?? 0);
    if ($category !== '' && $amount > 0) {
        $pdo->prepare("INSERT INTO capital_inflows(source,category,amount,created_at) VALUES(?,?,?,datetime('now'))")
            ->execute([$source, $category, $amount]);
    }
    redir('dashboard.php?tab=capital');
}
if (($action ?? '') === 'cap_delete') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id>0) $pdo->prepare("DELETE FROM capital_inflows WHERE id=?")->execute([$id]);
    redir('dashboard.php?tab=capital');
}

// OWNER withdrawals
if (($action ?? '') === 'owner_withdraw_create') {
    $amount=(float)($_POST['amount']??0); $note=trim($_POST['note']??'');
    if ($amount>0) $pdo->prepare("INSERT INTO owner_withdrawals(amount,note,created_at) VALUES(?,?,datetime('now'))")->execute([$amount,$note?:null]);
    redir('dashboard.php?tab=owner');
}
if (($action ?? '') === 'owner_withdraw_update') {
    $id=(int)($_POST['id']??0); $amount=(float)($_POST['amount']??0); $note=trim($_POST['note']??'');
    if ($id>0) $pdo->prepare("UPDATE owner_withdrawals SET amount=?, note=? WHERE id=?")->execute([$amount,$note?:null,$id]);
    redir('dashboard.php?tab=owner');
}
if (($action ?? '') === 'owner_withdraw_delete') {
    $id=(int)($_POST['id']??0);
    if ($id>0) $pdo->prepare("DELETE FROM owner_withdrawals WHERE id=?")->execute([$id]);
    redir('dashboard.php?tab=owner');
}

// OPERATIONAL withdrawals
if (($action ?? '') === 'op_withdraw_create') {
    $amount=(float)($_POST['amount']??0); $note=trim($_POST['note']??'');
    if ($amount>0) $pdo->prepare("INSERT INTO operational_withdrawals(amount,note,created_at) VALUES(?,?,datetime('now'))")->execute([$amount,$note?:null]);
    redir('dashboard.php?tab=operational');
}
if (($action ?? '') === 'op_withdraw_update') {
    $id=(int)($_POST['id']??0); $amount=(float)($_POST['amount']??0); $note=trim($_POST['note']??'');
    if ($id>0) $pdo->prepare("UPDATE operational_withdrawals SET amount=?, note=? WHERE id=?")->execute([$amount,$note?:null,$id]);
    redir('dashboard.php?tab=operational');
}
if (($action ?? '') === 'op_withdraw_delete') {
    $id=(int)($_POST['id']??0);
    if ($id>0) $pdo->prepare("DELETE FROM operational_withdrawals WHERE id=?")->execute([$id]);
    redir('dashboard.php?tab=operational');
}

// IT withdrawals
if (($action ?? '') === 'it_withdraw_create') {
    $amount=(float)($_POST['amount']??0); $note=trim($_POST['note']??'');
    if ($amount>0) $pdo->prepare("INSERT INTO it_withdrawals(amount,note,created_at) VALUES(?,?,datetime('now'))")->execute([$amount,$note?:null]);
    redir('dashboard.php?tab=it');
}
if (($action ?? '') === 'it_withdraw_update') {
    $id=(int)($_POST['id']??0); $amount=(float)($_POST['amount']??0); $note=trim($_POST['note']??'');
    if ($id>0) $pdo->prepare("UPDATE it_withdrawals SET amount=?, note=? WHERE id=?")->execute([$amount,$note?:null,$id]);
    redir('dashboard.php?tab=it');
}
if (($action ?? '') === 'it_withdraw_delete') {
    $id=(int)($_POST['id']??0);
    if ($id>0) $pdo->prepare("DELETE FROM it_withdrawals WHERE id=?")->execute([$id]);
    redir('dashboard.php?tab=it');
}

// CHARITY outflows (25/75)
if (($action ?? '') === 'charity_outflow_create') {
    $part=trim($_POST['part']??'25'); $amount=(float)($_POST['amount']??0); $note=trim($_POST['note']??'');
    if ($amount>0) $pdo->prepare("INSERT INTO charity_outflows(part,amount,note,created_at) VALUES(?,?,?,datetime('now'))")->execute([$part,$amount,$note?:null]);
    redir('dashboard.php?tab=charity');
}
if (($action ?? '') === 'charity_outflow_update') {
    $id=(int)($_POST['id']??0); $amount=(float)($_POST['amount']??0); $note=trim($_POST['note']??'');
    if ($id>0) $pdo->prepare("UPDATE charity_outflows SET amount=?, note=? WHERE id=?")->execute([$amount,$note?:null,$id]);
    redir('dashboard.php?tab=charity');
}
if (($action ?? '') === 'charity_outflow_delete') {
    $id=(int)($_POST['id']??0);
    if ($id>0) $pdo->prepare("DELETE FROM charity_outflows WHERE id=?")->execute([$id]);
    redir('dashboard.php?tab=charity');
}
?>