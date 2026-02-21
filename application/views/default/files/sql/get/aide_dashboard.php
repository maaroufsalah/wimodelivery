<?php

include get_file("files/sql/get/session");


$loginAide = $loginUser['user_aide'];

$from = $_GET['from'] ?? null;
$to = $_GET['to'] ?? null;

function fetchSum($con, $sql, $from, $to, $dateColumn = null) {
if (!empty($from) && !empty($to) && $dateColumn !== null) {
if (stripos($sql, 'WHERE') !== false) {
$sqlWithDate = str_ireplace(
'WHERE',
"WHERE DATE($dateColumn) BETWEEN :from AND :to AND",
$sql
);
} else {
$sqlWithDate = $sql . " WHERE DATE($dateColumn) BETWEEN :from AND :to";
}
$stmt = $con->prepare($sqlWithDate);
$stmt->bindValue(':from', $from);
$stmt->bindValue(':to', $to);
} else {
$stmt = $con->prepare($sql);
}
$stmt->execute();
return $stmt->fetchColumn() ?? 0;
}

function fetchCount($con, $sql, $from, $to, $dateColumn = null) {
if (!empty($from) && !empty($to) && $dateColumn !== null) {
if (stripos($sql, 'WHERE') !== false) {
$sqlWithDate = str_ireplace(
'WHERE',
"WHERE DATE($dateColumn) BETWEEN :from AND :to AND",
$sql
);
} else {
$sqlWithDate = $sql . " WHERE DATE($dateColumn) BETWEEN :from AND :to";
}
$stmt = $con->prepare($sqlWithDate);
$stmt->bindValue(':from', $from);
$stmt->bindValue(':to', $to);
} else {
$stmt = $con->prepare($sql);
}
$stmt->execute();
return $stmt->rowCount();
}

function echoCard($title, $value, $iconClass) {
echo "
<div class='col-sm-4 mb-3'>
<div class='card radius-10 bg-white'>
<div class='card-body text-dark'>
<div class='d-flex align-items-center'>
<div class='me-auto'>
<p class='mb-0 fw-bold'>$title</p>
<p class='my-1 fs-5'>$value</p>
</div>
<div class='widgets-icons bg-light-success text-success ms-auto'>
<i class='$iconClass fa-2x'></i>
</div>
</div>
</div>
</div>
</div>";
}

function echoCardLink($title, $count, $link, $iconClass, $bgClass) {
echo "
<div class='col-sm-4 mb-3'>
<a href='$link' class='text-decoration-none'>
<div class='card radius-10 $bgClass'>
<div class='card-body'>
<div class='d-flex align-items-center'>
<div class='me-auto'>
<p class='mb-0 text-white fw-bold'>$title</p>
<h4 class='my-1 text-white'>$count</h4>
</div>
<i class='$iconClass fa-3x text-white'></i>
</div>
</div>
</div>
</a>
</div>";
}


function convertDate($str) {
// نفترض التنسيق YYYY-MM-DD أو يمكن تعديل حسب الحاجة
return date('Y-m-d', strtotime($str));
}












// --- جلب البيانات مع أسماء أعمدة التاريخ المناسبة ---
$feeCompanyCount = fetchSum($con, "SELECT SUM(in_fee) FROM invoice WHERE in_user = '$loginAide' ", $from, $to, 'in_date');
$benefice = $feeCompanyCount;
$unpaid = fetchSum($con, "SELECT SUM(in_total) FROM invoice WHERE in_state = '0' AND in_unlink = '0' AND in_user = '$loginAide'", $from, $to, 'in_date');
$paid = fetchSum($con, "SELECT SUM(in_total) FROM invoice WHERE in_state = '1' AND in_unlink = '0' AND in_user = '$loginAide'", $from, $to, 'in_date');
$productCount = fetchCount($con, "SELECT * FROM products WHERE p_unlink = '0' AND p_user = '$loginAide'", $from, $to, 'p_date');
$invoiceCount = fetchCount($con, "SELECT * FROM invoice WHERE in_unlink = '0' AND in_user = '$loginAide'", $from, $to, 'in_date');

$claimCount = fetchCount($con, "SELECT * FROM claim WHERE claim_unlink = '0' AND claim_user = '$loginAide'", $from, $to, 'claim_date');



$totalFactures = fetchSum($con, "SELECT SUM(in_total) FROM invoice WHERE in_unlink = '0' AND in_user = '$loginAide'", $from, $to, 'in_date');
$nombreFactures = fetchCount($con, "SELECT * FROM invoice WHERE in_unlink = '0' AND in_user = '$loginAide'", $from, $to, 'in_date');

$totalCommandes = fetchSum($con, "SELECT SUM(or_total) FROM orders WHERE or_unlink = '0' AND or_state_delivery = '1' AND or_trade = '$loginAide' ", $from, $to, 'or_created');
$nombreCommandes = fetchCount($con, "SELECT * FROM orders WHERE or_unlink = '0' AND or_state_delivery = '1' AND or_trade = '$loginAide' ", $from, $to, 'or_created');





// chart cod+feedelivery

$conditions = ["in_unlink = 0 AND in_user = '$loginAide'"];
$params = [];

if (!empty($from) && !empty($to)) {
$conditions[] = "DATE(STR_TO_DATE(in_date, '%Y-%m-%d')) BETWEEN :from AND :to";
$params[':from'] = $from;
$params[':to'] = $to;
}

$whereClause = "";
if (count($conditions) > 0) {
$whereClause = " WHERE " . implode(" AND ", $conditions);
}

$sql = "
SELECT 
DATE(STR_TO_DATE(in_date, '%Y-%m-%d')) AS invoice_date,
SUM(in_total) AS total_sum,
SUM(in_fee) AS net_sum
FROM invoice
$whereClause
GROUP BY invoice_date
ORDER BY invoice_date ASC
";

$stmt = $con->prepare($sql);
foreach ($params as $k => $v) {
$stmt->bindValue($k, $v);
}
$stmt->execute();


$dates = [];
$totals = [];
$nets = [];

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
$dates[] = $row['invoice_date'];
$totals[] = (float)$row['total_sum'];
$nets[] = (float)$row['net_sum'];
}




?>


<form method="get" class="row g-2 mb-4">
<div class="col-md-4">
<input type="date" name="from" class="form-control" value="<?= htmlspecialchars($from) ?>" />
</div>
<div class="col-md-4">
<input type="date" name="to" class="form-control" value="<?= htmlspecialchars($to) ?>" />
</div>
<div class="col-md-4">
<button type="submit" class="btn btn-primary w-100">Filtrer</button>
</div>
</form>


<div class="row">
<?php




echoCard('charge de livraison', "$benefice Dhs", 'fas fa-coins');
echoCard('Facture Non-Payé', "$unpaid Dhs", 'fas fa-file-invoice-dollar');
echoCard('Facture Payé', "$paid Dhs", 'fas fa-file-invoice');

echoCardLink('Produits En stock', $productCount, 'stocks', 'fas fa-boxes', 'bg-gradient-cosmic');
echoCardLink('Facture', $invoiceCount, 'invoice', 'fas fa-receipt', 'bg-gradient-ibiza');

echoCardLink('Réclamations', $claimCount, 'claim', 'fas fa-headset', 'bg-success');

?>
</div>

<!-- رسم بياني باستخدام Chart.js -->
<div class="row my-5">
<div class="col-sm-6">
<div class="">
<canvas id="dashboardChart" height="130"></canvas>
</div>
</div>
<div class="col-sm-6">
<div class="">
<canvas id="invoiceChart" height="130"></canvas>
</div>
</div>
</div>


<div class="row text-center mb-4">
<div class="col-md-6">
<div class="card bg-light p-3">
<h4>Total factures</h4>
<p class="fs-3 text-success"><?= number_format($totalFactures, 2, ',', ' ') ?> MAD</p>
<p>Nombre de factures : <?= $nombreFactures ?></p>
</div>


<div class="card bg-light p-3">
<h4>Total commandes</h4>
<p class="fs-3 text-primary"><?= number_format($totalCommandes, 2, ',', ' ') ?> MAD</p>
<p>Nombre de commandes : <?= $nombreCommandes ?></p>
</div>
</div>




<div class="col-md-6">




<?php
if (empty($from) || empty($to)) {
$stmt = $con->prepare("SELECT * FROM state  WHERE state_unlink = '0' ORDER BY state_name ASC");
$stmt->execute();
$stateResult = $stmt->fetchAll();

foreach ($stateResult as $row) {
$stmt = $con->prepare("
SELECT COUNT(or_id) 
FROM orders  
WHERE or_state_delivery = :state_id 
AND or_unlink = '0' AND or_trade = '$loginAide'
");
$stmt->bindValue(':state_id', $row['state_id']);
$stmt->execute();
$orderByStateCount = $stmt->fetchColumn();

echo "<a class='text-dark' href='packages?state=" . $row['state_id'] . "'>
<div class='card my-0 radius-0'>
<div class='card-body'>
<div class='row align-items-center'>
<div class='col-2'><h6 class='text-dark'>$orderByStateCount</h6></div>
<div class='col-7'><h6 class='text-dark'>" . $row['state_name'] . "</h6></div>
<div class='col-3'>
<div class='p-4' style='background:" . $row['state_background'] . ";border-radius: 40px;'></div>
</div>
</div>
</div>
</div>
</a>
<hr class='my-0'>";
}
}
?>



</div>
</div>





<script>
const ctx = document.getElementById('invoiceChart').getContext('2d');
const invoiceChart = new Chart(ctx, {
type: 'line',
data: {
labels: <?= json_encode($dates) ?>,
datasets: [
{
label: 'Total COD (Total)',
data: <?= json_encode($totals) ?>,
borderColor: 'rgba(54, 162, 235, 1)',
backgroundColor: 'rgba(54, 162, 235, 0.2)',
fill: true,
tension: 0.3
},
{
label: 'Frais de livraison (Frais)',
data: <?= json_encode($nets) ?>,
borderColor: 'rgba(255, 99, 132, 1)',
backgroundColor: 'rgba(255, 99, 132, 0.2)',
fill: true,
tension: 0.3
}
]
},
options: {
responsive: true,
interaction: {
mode: 'index',
intersect: false,
},
stacked: false,
plugins: {
title: {
display: true,
text: 'Total COD Par Date'
}
},
scales: {
x: {
display: true,
title: {
display: true,
text: 'Date'
}
},
y: {
display: true,
title: {
display: true,
text: 'valeur en MAD'
},
beginAtZero: true
}
}
}
});
</script>

<script>
const ctx2 = document.getElementById('dashboardChart').getContext('2d');
const dashboardChart = new Chart(ctx2, {
type: 'bar',
data: {
labels: ['Facture Non-Payé', 'Facture Payé', 'Bénéfices de livraison'],
datasets: [{
label: 'Montant en Dhs',
data: [<?php echo (float)$unpaid; ?>, <?php echo (float)$paid; ?>, <?php echo (float)$benefice; ?>],
backgroundColor: [
'rgba(255, 99, 132, 0.6)',
'rgba(54, 162, 235, 0.6)',
'rgba(255, 206, 86, 0.6)'
],
borderColor: [
'rgba(255, 99, 132, 1)',
'rgba(54, 162, 235, 1)',
'rgba(255, 206, 86, 1)'
],
borderWidth: 1
}]
},
options: {
responsive: true,
scales: {
y: {
beginAtZero: true
}
}
}
});
</script>
