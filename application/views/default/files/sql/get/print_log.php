<?php

$bootstrap = new Bootstrap();
$bootstrap->runBootstrap();

include get_file("files/sql/get/os_settings");
include get_file("files/sql/get/session");
include get_file("files/sql/get/functions");
include get_file("Admin/admin_header");

$id = $_GET['id'];

$stmt = $con->prepare("SELECT * FROM log_print WHERE md5(lp_id) = ?");
$stmt->execute([$id]);
$log = $stmt->fetch();

$do = isset ($_GET['do']) ? $_GET ['do'] : 'Manage' ;

if ($log['lp_type'] == 'pickup'){

define("page_title", "Bon De Ramassage");
$type = "pickup";
$type_name = "Bon De Ramassage";

}elseif($log['lp_type'] == "outlog_user"){

define("page_title", "Bon De Retour Client");
$type = "outlog_user";
$type_name = "Bon De Retour Client";


}elseif($log['lp_type'] == "outlog_delivery"){

define("page_title", "Bon De Retour Livreur");
$type = "outlog_delivery";
$type_name = "Bon De Retour Livreur";

}




if ($log) {

$stmt = $con->prepare("SELECT * FROM users WHERE user_id = ? ");
$stmt->execute([$log['lp_user']]);
$user = $stmt->fetch();

$stmt = $con->prepare("SELECT * FROM orders WHERE or_id IN (".$log['lp_gid'].") ORDER BY or_id DESC");
$stmt->execute();
$orders = $stmt->fetchAll();

$f = new NumberFormatter("fr", NumberFormatter::SPELLOUT);
/*
$total = $con->query("SELECT SUM(or_total) FROM orders WHERE md5(or_delivery_invoice) = '$id' AND or_state_delivery = '1'")->fetchColumn();
$fee = $con->query("SELECT SUM(dis_fee) FROM delivery_invoice_script WHERE md5(dis_invoice) = '$id'")->fetchColumn();
*/
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
<img src="uploads/<?= $set_logo ?>" width="200px" alt="Logo">
</div>
<div class="text-end">

<div style="text-align: left;">
<div><?= $user['user_name'] ?></div>
</div>




</div>
</div>

<div class="invoice-details d-flex justify-content-between">

<div class="">
<h4><?= $type_name; ?> N° :  <?= $log['lp_id'] ?></h4>
<div>Date : <?= $log['lp_date'] ?></div>
</div>
</div>

<div class="table table-responsive">
<table class="invoice-table table text-center">
<thead>
<tr>
<th>N° de Colis</th>
<th>État</th>
<th>Date État</th>
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

// جلب اسم المدينة
$city = $con->query("SELECT city_name FROM city WHERE city_id = " . $row['or_city'])->fetchColumn();

// جلب تفاصيل الطلب
$order = $con->query("SELECT * FROM orders WHERE or_id = " . $row['or_id'])->fetch();

// جلب آخر تاريخ للحالة من جدول state_activity
$lastDate = $con->prepare("SELECT sa_date 
            FROM state_activity 
            WHERE sa_order = ? AND sa_state = ? 
            ORDER BY sa_id DESC LIMIT 1");
$lastDate->execute([$row['or_id'], $row['or_state_delivery']]);
$lastDate = $lastDate->fetchColumn();



?>
<tr>
<td><?= $row['or_id'] ?></td>
<td><?= $state ?></td>
<td><?= fd($lastDate) ?: '-' ?></td>
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
</div>
<div class="signature">Cachet et signature</div>

<div class="mt-3">
</div>

<div class="footer text-center">
<?= html_entity_decode($set_bottom_paper) ?>
</div>
</div>

<?php } ?>
