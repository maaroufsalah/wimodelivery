<?php

$bootstrap = new Bootstrap();
$bootstrap->runBootstrap();

include get_file("files/sql/get/os_settings");
include get_file("files/sql/get/session");
include get_file("files/sql/get/functions");
include get_file("Admin/admin_header");

define("page_title", "Facture");

$id = $_GET['id'];

$stmt = $con->prepare("SELECT * FROM delivery_invoice WHERE md5(d_in_id) = ?");
$stmt->execute([$id]);
$invoice = $stmt->fetch();

if ($invoice) {

$stmt = $con->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$invoice['d_in_user']]);
$user = $stmt->fetch();

$stmt = $con->prepare("SELECT * FROM delivery_invoice_script WHERE dis_invoice = ? ORDER BY dis_id DESC");
$stmt->execute([$invoice['d_in_id']]);
$invoice_scripts = $stmt->fetchAll();

$f = new NumberFormatter("fr", NumberFormatter::SPELLOUT);

$total = $con->query("SELECT SUM(or_total) FROM orders WHERE or_delivery_invoice = '".$invoice['d_in_id']."'  ")->fetchColumn();
$fee = $con->query("SELECT SUM(dis_fee) FROM delivery_invoice_script WHERE md5(dis_invoice) = '$id'")->fetchColumn();
$net = ($total - $fee);

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
<h2><?= $set_name ?></h2>
<div><?= $set_location ?></div>
<div><?= $set_phone ?></div>
<div><?= $set_email ?></div>
<?php if (!empty($set_id_number)): ?>
<div>ICE : <?= $set_id_number ?></div>
<?php endif; ?>
</div>
</div>

<div class="invoice-details d-flex justify-content-between">
<div>
<h4>Livreur :</h4>
<div><?= $user['user_name'] ?></div>
</div>
<div class="text-end">
<h4>Facture N° : FC-<?= $invoice['d_in_id'] ?></h4>
<div>Date : <?= $invoice['d_in_date'] ?></div>
</div>
</div>

<div class="table table-responsive">

<table class="invoice-table table text-center">
<thead>
<tr>
<th>#</th> <!-- ✅ العمود الجديد للتسلسل -->
<th>N° de Colis</th>
<th>État</th>
<th>Téléphone</th>
<th>Ville</th>
<th>Montant (C.O.D)</th>
<th>Frais</th>
<th>Net</th>
</tr>
</thead>
<tbody>
<?php if ($invoice_scripts): ?>
<?php 
$counter = 1; // ✅ عدّاد الأرقام التسلسلية
foreach ($invoice_scripts as $row):
    $state = $con->query("SELECT state_name FROM state WHERE state_id = " . $row['dis_state'])->fetchColumn();
    $city = $con->query("SELECT city_name FROM city WHERE city_id = " . $row['dis_city'])->fetchColumn();
    $order = $con->query("SELECT * FROM orders WHERE or_id = " . $row['dis_order'])->fetch();
?>
<tr>
<td><?= $counter++ ?></td> <!-- ✅ الرقم التسلسلي -->
<td><?= $row['dis_order']; ?></td>
<td><?= $state ?></td>
<td><?= $order['or_phone'] ?></td>
<td><?= $city ?></td>
<td><?= number_format($order['or_total'], 2) ?> Dhs</td>
<td><?= number_format($row['dis_fee'], 2) ?> Dhs</td>
<td><?= number_format(($order['or_total']-$row['dis_fee']), 2) ?> Dhs</td>
</tr>
<?php endforeach; ?>
<?php else: ?>
<tr><td colspan="8">Aucune donnée disponible</td></tr>
<?php endif; ?>
</tbody>
<tfoot class="totals">

<tr>
<td colspan="7" class="text-end">Nombre total de colis</td>
<td><?= count($invoice_scripts) ?></td>
</tr>

<tr>
<td colspan="7" class="text-end">Total C.O.D</td>
<td><?= number_format($total, 2) ?> Dhs</td>
</tr>
<tr>
<td colspan="7" class="text-end">Frais de Livraison</td>
<td><?= number_format($fee, 2) ?> Dhs</td>
</tr>
<tr>
<td colspan="7" class="text-end" style="font-size:22px">Net</td>
<td style="font-size:22px"><?= number_format($net, 2) ?> Dhs</td>
</tr>
<!-- ✅ عدد الطرود -->

</tfoot>
</table>

</div>

<div class="signature">Cachet et signature</div>

<div class="mt-3">
<strong>Arrêtée la présente facture à la somme de :</strong><br>
<em><?= ucfirst($f->format($net)) ?> dirhams.</em>
</div>

<div class="footer text-center">
<?= html_entity_decode($set_bottom_paper) ?>
</div>
</div>

<?php } ?>
