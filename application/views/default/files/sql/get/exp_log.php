<?php

$bootstrap = new Bootstrap();
$bootstrap->runBootstrap();

include get_file("files/sql/get/os_settings");
include get_file("files/sql/get/session");
include get_file("files/sql/get/functions");
include get_file("Admin/admin_header");


$ids = isset($_GET['ids']) ? $_GET['ids'] : ''; // القائمة لو موجودة
$exp = isset($_GET['exp']) ? $_GET['exp'] : ''; // القائمة لو موجودة


define("page_title", "Bon Expédition");
$type = "";
$type_name = "Bon Expédition";



$stmt = $con->prepare("SELECT * FROM expeditions WHERE expedition_id = '".$exp."' LIMIT 1");
$stmt->execute();
$exp_data = $stmt->fetch();



$stmt = $con->prepare("SELECT * FROM users WHERE user_id = '".$exp_data['delivery_user_id']."' LIMIT 1");
$stmt->execute();
$user = $stmt->fetch();




$stmt = $con->prepare("SELECT * FROM orders WHERE or_id IN (".$ids.") ORDER BY or_id DESC");
$stmt->execute();
$orders = $stmt->fetchAll();

$f = new NumberFormatter("fr", NumberFormatter::SPELLOUT);

?>

<style>
@media print {
.noPrint { display: none !important; }
body {
background: white;
font-size: 12pt;
margin: 0;
}

.page {
width: 210mm;
min-height: 297mm;
padding: 20mm;
margin: 0;
background: white;
box-shadow: none;
}

table {
width: 100%;
border-collapse: collapse;
font-size: 11px;
}

th, td {
padding: 4px;
border: 1px solid #000;
}

.footer {
position: fixed;
bottom: 0;
font-size: 10pt;
}
}

.page {
background: #fff;
padding: 20mm;
margin: 0;
box-shadow: 0 0 5px rgba(0,0,0,0.1);
font-family: 'Segoe UI', sans-serif;
}

h2, h4 {
margin: 0;
padding: 0;
}

.invoice-header, .invoice-details, .invoice-table, .totals, .signature, .footer {
margin-bottom: 20px;
}

.totals td {
font-weight: bold;
}

.signature {
text-align: right;
padding-top: 40px;
font-size: 14px;
}

</style>

<script>
function printPage() {
window.print();
}
</script>

<div class="page">
<div class="text-end mb-4">
<button onclick="printPage()" class="btn btn-dark noPrint">Imprimer</button>
</div>

<div class="invoice-header d-flex justify-content-between">
<div>
<img src="uploads/<?= $set_logo ?>" width="200" alt="Logo">
</div>
<div class="text-end">


<div style="text-align: left;">
<h4>Livreur :</h4>
<div><?= $user['user_name'] ?></div>
<div><?= $user['user_email'] ?></div>
</div>




</div>
</div>

<div class="invoice-details d-flex justify-content-between">

<div class="">
<h4><?= $type_name; ?> N° :  <?= $exp_data['expedition_id'] ?></h4>
<div>Date : <?= $exp_data['expedition_date'] ?></div>
</div>
</div>

<table class="invoice-table table text-center">
<thead>
<tr>
<th>N° de Colis</th>
<th>Destinataire</th>
<th>Téléphone</th>
<th>Ville</th>
<th>Montant (C.O.D)</th>
</tr>
</thead>
<tbody>
<?php if ($orders): ?>
<?php foreach ($orders as $row):
$state = $con->query("SELECT state_name FROM state WHERE state_id = " . $row['or_state_delivery'])->fetchColumn();
$city = $con->query("SELECT city_name FROM city WHERE city_id = " . $row['or_city'])->fetchColumn();
$order = $con->query("SELECT * FROM orders WHERE or_id = " . $row['or_id'])->fetch();
?>
<tr>
<td><?= $row['or_id'] ?></td>
<td><?= $order['or_name'] ?></td>
<td><?= $order['or_phone'] ?></td>
<td><?= $city ?></td>
<td><?= number_format($order['or_total'], 2) ?> Dhs</td>
</tr>
<?php endforeach; ?>
<?php else: ?>
<tr><td colspan="4">Aucune donnée disponible</td></tr>
<?php endif; ?>
</tbody>
<tfoot class="totals">
<tr>
<td colspan="4" class="text-end">Total colis</td>
<td><?= count($orders)?></td>
</tr>


</tfoot>
</table>

<div class="signature">Cachet et signature</div>

<div class="mt-3">
</div>

<div class="footer text-center">
<?= html_entity_decode($set_bottom_paper) ?>
</div>
</div>

