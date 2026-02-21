<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<?php 

$bootstrap = new Bootstrap();
$bootstrap->runBootstrap();

include get_file("files/sql/get/os_settings");
include get_file("files/sql/get/session");
include get_file("files/sql/get/functions");

$rowsRaw = $_GET['orders_ids'] ?? '';
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
--bs-table-color-type: initial;
--bs-table-bg-type: initial;
--bs-table-color-state: initial;
--bs-table-bg-state: initial;
--bs-table-color: var(--bs-emphasis-color);
--bs-table-bg: var(--bs-body-bg);
--bs-table-border-color: var(--bs-border-color);
--bs-table-accent-bg: transparent;
--bs-table-striped-color: var(--bs-emphasis-color);
--bs-table-striped-bg: rgba(var(--bs-emphasis-color-rgb), 0.05);
--bs-table-active-color: var(--bs-emphasis-color)#000;
--bs-table-active-bg: rgba(var(--bs-emphasis-color-rgb), 0.1);
--bs-table-hover-color: var(--bs-emphasis-color);
--bs-table-hover-bg: rgba(var(--bs-emphasis-color-rgb), 0.075);
width: 100%;
margin-bottom: 0;
vertical-align: top;
border-color: #373b3e;
height:15px;
font-size: 8px;
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
}

.label {
width: 50%;
height: 50%;
box-sizing: border-box;
padding: 0;
display: flex;
flex-direction: column;
justify-content: space-between;
margin: 0;
border: 0;
border-radius: 0;
background: transparent;
box-shadow: none;
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

.qr-container {
display: flex;
justify-content: space-between;
align-items: center;
}

.qr-container img {
width: 50px;
}

.expediteur {
font-size: 12px;
font-weight: bold;
}

.info-container {
font-size: 10px;
}

.note {
font-size: 10px;
background: #fffdf7;
}

.product-list {
margin-top: 5px;
}

.product-item {
display: flex;
justify-content: space-between;
border-bottom: 1px dashed #bbb;
padding: 2px 0;
}

.options {
text-align: center;
font-size: 10px;
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

<?php } elseif ($do == "10") { ?>

<style>
.table {
  width: 100%;
  font-size: 7px;
  border-collapse: collapse;
}

.table td, .table th {
  border: 1px solid #373b3e;
  padding: 2px 4px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  vertical-align: middle;
  height: 12px;
}

.table tr {
  height: 12px;
}


@page {
size: 10cm 10cm;
margin: 0;
}
@media print {
body { margin:0; padding:0; }
.print-button { display:none; }
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
width: 10cm;
height: 10cm;
display: flex;
flex-wrap: wrap;
box-sizing: border-box;
background: #fff;
}

.label {
width: 100%;
height: 100%;
box-sizing: border-box;
padding: 0mm;
border: 0;
border-radius: 0;
background: #fff;
box-shadow: 0 0 5px rgba(0,0,0,0.1);
display: flex;
flex-direction: column;
justify-content: space-between;
}

.qr-container,
.expediteur,
.info-container,
.note,
.options,
.footer {
border: 1px solid #ddd;
border-radius: 6px;
background: #fafafa;
padding: 6px 8px;
margin-bottom: 6px;
box-shadow: inset 0 0 5px rgba(0,0,0,0.05);
}

.qr-container {
display: flex;
justify-content: space-between;
align-items: center;
}

.qr-container img {
width: 40px;
}

.expediteur {
font-size: 11px;
font-weight: bold;
}

.info-container {
font-size: 9px;
}

.note {
font-size: 9px;
background: #fffdf7;
}

.product-list {
margin-top: 4px;
}

.product-item {
display: flex;
justify-content: space-between;
border-bottom: 1px dashed #bbb;
padding: 2px 0;
}

.options {
text-align: center;
font-size: 9px;
}

.option {
display: inline-block;
border: 1px solid #bbb;
border-radius: 20px;
padding: 2px 6px;
margin: 2px;
background: #f0f0f0;
}

.footer {
text-align: center;
font-size: 8px;
color: #666;
border: none;
box-shadow: none;
}
</style>

<?php } ?>

<button class="print-button" onclick="window.print()">🖨️ Imprimer</button>

<div class="page">
<?php foreach ($orders_list as $order): 

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

$qrData = $order['or_id'];
?>

<div class="label">



<div class="table-responsive">
<table class="table table-bordered" style="">
<!-- Row 1: 1 column -->
<tr>
<td colspan="2" class='text-center'>
<h3 style='text-transform: uppercase;'><b><?= html_entity_decode($city['city_name'] ?? '-') ?></b></h3>
</td>
</tr>

<!-- Row 2: 2 columns -->
<tr>
<td><svg id="barcode-<?= $order['or_id'] ?>"></svg></td>
<td><h1 class='text-center'><b>B</b></h1></td>
</tr>

<!-- Row 3: 1 column -->
<tr>
<td colspan="2">
<div>Expéditeur:</div>  
<div class='text-center'><?= html_entity_decode($trade['user_name']) ?></div>
</td>
</tr>

<!-- Row 4: 2 columns -->
<tr>

<td>
<div class="info">Client :<br> <strong><?= html_entity_decode($order['or_name']) ?></strong></div>
</td>

<td>
<div class="info">Téléphone Client :<br> <strong><?= html_entity_decode($order['or_phone']) ?></strong></div>
</td>

</tr>

<!-- Row 5: 1 column -->
<tr>
<td colspan="2">
<div class="info">Adresse :<br> <strong><?= html_entity_decode($order['or_address']) ?></strong></div>
</td>
</tr>



<!-- Row 6: 2 columns -->
<tr>
<td>
<div class="info">Montant :<br> <strong><h4><b><?= html_entity_decode($order['or_total']) ?></b></h4></strong></div>
</td>
<td>
<div class="info">Note :<br> <strong><?= html_entity_decode($order['or_note']) ?></strong></div>
</td>
</tr>


<!-- Row 8: 2 columns -->
<tr>
<td>
<div class="">
Produits:<br>
<div class="">
<?php if ($items && count($items) > 0): ?>
<?php foreach ($items as $item): ?>
<?php
$name = html_entity_decode($item['product_name']);
$shortName = mb_substr($name, 0, 30, 'UTF-8');
if (mb_strlen($name, 'UTF-8') > 30) {
$shortName .= '...';
}
?>
<div class="">
<span>x<?= htmlspecialchars($item['quantity']) ?> <?= htmlspecialchars($shortName) ?></span>
</div>
<?php endforeach; ?>
<?php else: ?>
<?php
$name = html_entity_decode($order['or_item']);
$shortName = mb_substr($name, 0, 30, 'UTF-8');
if (mb_strlen($name, 'UTF-8') > 30) {
$shortName .= '...';
}
?>
<div class="">
<span>x<?= htmlspecialchars($order['or_qty']) ?> <?= htmlspecialchars($shortName) ?></span>
</div>
<?php endif; ?>
</div>
</div>

</td>
<td colspan="2">
<div id="qr-<?= $order['or_id'] ?>" class="qr-code"></div>

</td>
</tr>


<tr>
<!-- العمود الأول -->
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

<!-- العمود الثاني -->
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

<div class="mb-2">
<strong>Fragile:</strong>
<span class="ms-1">
<i class="fa-regular <?= ($order['or_fragile'] == 5) ? 'fa-check-square' : 'fa-square' ?>"></i> Oui
</span>
<span class="ms-1">
<i class="fa-regular <?= ($order['or_fragile'] != 5) ? 'fa-check-square' : 'fa-square' ?>"></i> Non
</span>
</div>
</td>
</tr>




<!-- Row 11: 1 column -->
<tr>
<td colspan="2">
<?= $set_name; ?> | Livraison e-commerce <br>
<?= $set_name; ?> SARL n'est pas responsable de vos achats.
</td>
</tr>

</table>
</div>



</div>
<?php endforeach; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/qrcodejs/qrcode.min.js"></script>
<script>
<?php foreach ($orders_list as $order): ?>
new QRCode(document.getElementById("qr-<?= $order['or_id'] ?>"), {
text: "<?= $order['or_id'] ?>",
width: 50,
height: 50
});
<?php endforeach; ?>
<?php foreach ($orders_list as $order): ?>

JsBarcode("#barcode-<?= $order['or_id'] ?>", "<?= htmlspecialchars($order['or_id']) ?>", {
format: "CODE128",
lineColor: "#000",
width: 2,
height: 40,
displayValue: true
});
<?php endforeach; ?>
</script>


<?php
}
}
?>
