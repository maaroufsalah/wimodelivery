<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<?php 

$bootstrap = new Bootstrap();
$bootstrap->runBootstrap();

include get_file("files/sql/get/os_settings");
include get_file("files/sql/get/session");
include get_file("files/sql/get/functions");

$rowsRaw = $_GET['order_ids'] ?? '';
if (!empty($rowsRaw)) {

$rows = explode(",", $rowsRaw);
$do = $_GET['do'] ?? 'Manage';

$stmt = $con->prepare("SELECT * FROM orders WHERE or_id IN ($rowsRaw)");
$stmt->execute();
$orders_list = $stmt->fetchAll();

if (count($orders_list) > 0) {

if ($do == 'a4') {
?>

<style>
@page {
  size: A4;
  margin: 0;
}
@media print {
  body { margin:0; padding:0; }
  .print-button { display:none; }
}
.table {
  font-size: 9px;
  width: 100%;
  border-color: #373b3e;
}
body {
  font-family: Arial, sans-serif;
  margin: 0;
  padding: 0;
  display: flex;
  flex-direction: column;
  align-items: center;
  background: #f5f5f5;
}
.print-button {
  padding: 10px 20px;
  font-size: 16px;
  background: #000;
  color: #fff;
  border: none;
  border-radius: 5px;
  cursor: pointer;
}
.page {
  width: 210mm;
  height: 297mm;
  display: flex;
  flex-wrap: wrap;
  box-sizing: border-box;
  background: #fff;
  page-break-after: always;
}
.label {
  width: 50%;
  height: 148mm;
  box-sizing: border-box;
  padding: 4mm;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  border: 1px dashed #ccc;
}
.qr-container,
.expediteur,
.info-container,
.note,
.options,
.footer {
  border: 1px solid #ddd;
  border-radius: 8px;
  background: #fafafa;
  padding: 8px 10px;
  margin-bottom: 8px;
  box-shadow: inset 0 0 5px rgba(0,0,0,0.05);
}
.qr-container img { width: 50px; }
.expediteur { font-size: 12px; font-weight: bold; }
.info-container, .note, .options, .footer { font-size: 10px; }
.product-list {
  margin-top: 5px;
}
.product-item {
  display: flex;
  justify-content: space-between;
  border-bottom: 1px dashed #bbb;
  padding: 2px 0;
}
.option {
  display: inline-block;
  border: 1px solid #bbb;
  border-radius: 20px;
  padding: 3px 10px;
  margin: 2px;
  background: #f0f0f0;
}
.footer {
  text-align: center;
  font-size: 9px;
  color: #666;
  border: none;
  box-shadow: none;
}
</style>

<?php } ?>

<button class="print-button" onclick="window.print()">🖨️ Imprimer</button>

<?php
$labelPerPage = 4;
$currentIndex = 0;
foreach ($orders_list as $index => $order):
    if ($currentIndex % $labelPerPage == 0) {
        if ($currentIndex > 0) echo '</div>'; // End previous .page
        echo '<div class="page">'; // Start new .page
    }

    $tradeStmt = $con->prepare("SELECT * FROM users WHERE user_id = ? AND user_rank = 'user' LIMIT 1");
    $tradeStmt->execute([$order['or_trade']]);
    $trade = $tradeStmt->fetch() ?: [];

    $cityStmt = $con->prepare("SELECT * FROM city WHERE city_id = ? LIMIT 1");
    $cityStmt->execute([$order['or_city']]);
    $city = $cityStmt->fetch() ?: [];

    $whStmt = $con->prepare("SELECT * FROM warehouse WHERE wh_id = ? LIMIT 1");
    $whStmt->execute([$order['or_warehouse']]);
    $warehouse = $whStmt->fetch() ?: [];

    $stateStmt = $con->prepare("SELECT * FROM state WHERE state_id = ? LIMIT 1");
    $stateStmt->execute([$order['or_state_delivery']]);
    $state = $stateStmt->fetch() ?: [];

    $stmt_items = $con->prepare("SELECT * FROM order_items WHERE order_id = ?");
    $stmt_items->execute([$order['or_id']]);
    $items = $stmt_items->fetchAll();
?>

<!-- بداية بطاقة label -->
<div class="label">
<div class="table-responsive">
<table class="table table-bordered">


<tr>
<td class='text-center'>
<img src='uploads/<?=$set_logo;?>' class='' style='width:100px'/>
</td>

<td class='text-center'>
<h5 style='text-transform: uppercase;'><b><?= html_entity_decode($city['city_name'] ?? '-') ?></b></h5>
</td>
</tr>



<tr>
<td>
<div class="info">Code d'envoi :<br> <strong>#<?= html_entity_decode($order['or_id']) ?></strong></div>
</td>
<td>
<div class="info"><br> <strong><?= html_entity_decode($order['or_created']) ?></strong></div>
</td>
</tr>



<tr>
<td colspan="2">
<div>Expéditeur:</div>  
<div class=''><b><?= ($trade['user_name']) ?> - <?= ($trade['user_phone']) ?></b></div>
</td>
</tr>
<tr>
<td>
<div class="info">Client :<br> <strong><?= html_entity_decode($order['or_name']) ?></strong></div>
</td>
<td>
<div class="info">Téléphone Client :<br> <strong><?= html_entity_decode($order['or_phone']) ?></strong></div>
</td>
</tr>
<tr>
<td colspan="2">
<div class="info">Adresse :<br> <strong><?= html_entity_decode($order['or_address']) ?></strong></div>
</td>
</tr>
<tr>
<td>
<div class="info">Montant :<br> <strong><h5><b><?= html_entity_decode($order['or_total']) ?></b></h5></strong></div>
</td>
<td>
<div>Produits:<br>
<?php if ($items && count($items) > 0): ?>
<?php foreach ($items as $item): ?>
<?php
$name = html_entity_decode($item['product_name']);
$shortName = mb_substr($name, 0, 30, 'UTF-8');
if (mb_strlen($name, 'UTF-8') > 30) {
  $shortName .= '...';
}
?>
<div><span>x<?= htmlspecialchars($item['quantity']) ?> <?= htmlspecialchars($shortName) ?></span></div>
<?php endforeach; ?>
<?php else: ?>
<?php
$name = html_entity_decode($order['or_item']);
$shortName = mb_substr($name, 0, 30, 'UTF-8');
if (mb_strlen($name, 'UTF-8') > 30) {
  $shortName .= '...';
}
?>
<?php endif; ?>
</div>
</td>
</tr>
<tr>
<td>
<div class="info">Note :<br> <strong><?= html_entity_decode($order['or_note']) ?></strong></div>

</td>
<td colspan="2">
<div id="qr-<?= $order['or_id'] ?>" class="qr-code"></div>
</td>
</tr>
<tr>
<td>
<div class="mb-2">
<strong>Ouverture:</strong>
<span class="ms-1">
<i class="fa-regular <?= ($order['or_open_package'] == 1) ? 'fa-check-square' : 'fa-square' ?>"></i> Oui
</span>
<span class="ms-1">
<i class="fa-regular <?= ($order['or_open_package'] == 0) ? 'fa-check-square' : 'fa-square' ?>"></i> Non
</span>
</div>
<div class="mb-2">
<strong>Essayage:</strong>
<span class="ms-1">
<i class="fa-regular <?= ($order['or_try'] == 1) ? 'fa-check-square' : 'fa-square' ?>"></i> Oui
</span>
<span class="ms-1">
<i class="fa-regular <?= ($order['or_try'] == 0) ? 'fa-check-square' : 'fa-square' ?>"></i> Non
</span>
</div>
</td>
<td>
<div class="mb-2">
<strong>Échange:</strong>
<span class="ms-1">
<i class="fa-regular <?= ($order['or_change'] == 1) ? 'fa-check-square' : 'fa-square' ?>"></i> Oui
</span>
<span class="ms-1">
<i class="fa-regular <?= ($order['or_change'] == 0) ? 'fa-check-square' : 'fa-square' ?>"></i> Non
</span>
</div>


</td>
</tr>
<tr>
<td colspan="2">
<?= $set_name; ?> | Livraison e-commerce <br>
<?= $set_name; ?> SARL n'est pas responsable de vos achats.
</td>
</tr>
</table>
</div>
</div> <!-- نهاية البطاقة -->

<?php
$currentIndex++;
endforeach;
if ($currentIndex % $labelPerPage != 0) {
  echo '</div>'; // إغلاق الصفحة الأخيرة
}
?>

<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/qrcodejs/qrcode.min.js"></script>
<script>
<?php foreach ($orders_list as $order): ?>
new QRCode(document.getElementById("qr-<?= $order['or_id'] ?>"), {
  text: "<?= $order['or_id'] ?>",
  width: 60,
  height: 60
});
<?php endforeach; ?>
</script>

<?php
}} // end if count
?>
