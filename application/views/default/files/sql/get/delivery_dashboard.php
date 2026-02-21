<?php
include get_file("files/sql/get/session");

$from = $_GET['from'] ?? null;
$to   = $_GET['to'] ?? null;

// --- دوال مساعدة ---
function fetchSum($con, $sql, $params = []) {
    $stmt = $con->prepare($sql);
    foreach($params as $k=>$v) $stmt->bindValue($k,$v);
    $stmt->execute();
    return $stmt->fetchColumn() ?? 0;
}

$params = [':loginId'=>$loginId];
$whereOrders = " WHERE or_unlink=0 AND or_delivery_user=:loginId ";
$whereInvoices = " WHERE d_in_unlink=0 AND d_in_user=:loginId ";

if(!empty($from) && !empty($to)){
    $whereOrders .= " AND DATE(or_created) BETWEEN :from AND :to ";
    $whereInvoices .= " AND DATE(d_in_date) BETWEEN :from AND :to ";
    $params[':from']=$from;
    $params[':to']=$to;
}

// --- الطرود ---
$sqlOrders = "
SELECT
COUNT(*) AS total_orders,
SUM(CASE WHEN or_state_delivery=1 AND or_delivered IS NOT NULL THEN 1 ELSE 0 END) AS delivered_count,
SUM(CASE WHEN or_state_delivery=5 AND or_postponed IS NOT NULL THEN 1 ELSE 0 END) AS postponed_count,
SUM(CASE WHEN or_state_delivery!=1 AND or_invoice!=0 THEN 1 ELSE 0 END) AS returned_count
FROM orders $whereOrders
";
$stmt=$con->prepare($sqlOrders);
foreach($params as $k=>$v) $stmt->bindValue($k,$v);
$stmt->execute();
$row=$stmt->fetch(PDO::FETCH_ASSOC);

$totalOrders    = (int)$row['total_orders'];
$deliveredCount = (int)$row['delivered_count'];
$postponedCount = (int)$row['postponed_count'];
$returnedCount  = (int)$row['returned_count'];

$tauxDelivered  = $totalOrders>0?round(($deliveredCount/$totalOrders)*100):0;
$tauxPostponed  = $totalOrders>0?round(($postponedCount/$totalOrders)*100):0;
$tauxReturned   = $totalOrders>0?round(($returnedCount/$totalOrders)*100):0;

// --- الفواتير ---
$benefice = fetchSum($con,"SELECT SUM(d_in_total) FROM delivery_invoice $whereInvoices",$params);
$unpaid   = fetchSum($con,"SELECT SUM(d_in_total) FROM delivery_invoice WHERE d_in_state=0 AND d_in_unlink=0 AND d_in_user=:loginId".(!empty($from)? " AND DATE(d_in_date) BETWEEN :from AND :to":""),$params);
$paid     = fetchSum($con,"SELECT SUM(d_in_total) FROM delivery_invoice WHERE d_in_state=1 AND d_in_unlink=0 AND d_in_user=:loginId".(!empty($from)? " AND DATE(d_in_date) BETWEEN :from AND :to":""),$params);
$delivery_invoiceCount = fetchSum($con,"SELECT COUNT(*) FROM delivery_invoice $whereInvoices",$params);
?>

<div class="container my-4">

<!-- ===== فلترة التاريخ ===== -->
<form method="get" class="row g-2 mb-4">
    <div class="col-md-4"><label>De :</label><input type="date" name="from" class="form-control" value="<?= htmlspecialchars($from) ?>"/></div>
    <div class="col-md-4"><label>A :</label><input type="date" name="to" class="form-control" value="<?= htmlspecialchars($to) ?>"/></div>
    <div class="col-md-4"><button type="submit" class="btn btn-primary w-100 mt-4">Filtrer</button></div>
</form>

<!-- ===== بطاقات إحصائية ===== -->
<div class="row mb-4">
<?php
$cards = [
    ['title'=>'Bénéfices','value'=>number_format($benefice,2,',',' ') . ' MAD','icon'=>'fas fa-coins','bg'=>'bg-gradient-success'],
    ['title'=>'Factures','value'=>$delivery_invoiceCount,'icon'=>'fas fa-receipt','bg'=>'bg-gradient-primary'],
    ['title'=>'Total Commandes','value'=>$totalOrders,'icon'=>'fas fa-box','bg'=>'bg-gradient-warning'],
    ['title'=>'Autres Statuts','value'=>$postponedCount+$returnedCount,'icon'=>'fas fa-undo','bg'=>'bg-gradient-info']
];
foreach($cards as $c){
    echo "<div class='col-md-3 mb-3'>
    <div class='card text-white shadow {$c['bg']} radius-15 p-3'>
        <div class='d-flex align-items-center'>
            <div class='me-auto'>
                <h6>{$c['title']}</h6>
                <h3>{$c['value']}</h3>
            </div>
            <div><i class='{$c['icon']} fa-3x'></i></div>
        </div>
    </div>
    </div>";
}
?>
</div>

<!-- ===== شريط تقدم ديناميكي ===== -->
<div class="mb-5">
<h5>Distribution des colis (%)</h5>
<div class="progress" style="height:50px;">
    <div class="progress-bar bg-success progress-bar-animated" role="progressbar" style="width: <?= $tauxDelivered ?>%">Livré <?= $tauxDelivered ?>%</div>
    <div class="progress-bar bg-warning text-dark progress-bar-animated" role="progressbar" style="width: <?= $tauxPostponed ?>%">Reporté <?= $tauxPostponed ?>%</div>
    <div class="progress-bar bg-danger progress-bar-animated" role="progressbar" style="width: <?= $tauxReturned ?>%">Retourné <?= $tauxReturned ?>%</div>
</div>
</div>

<!-- ===== الرسوم البيانية ===== -->
<div class="row mb-5">
    <div class="col-md-6"><canvas id="chartInvoices"></canvas></div>
    <div class="col-md-6"><canvas id="chartColis"></canvas></div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// --- الرسم البياني للفواتير ---
new Chart(document.getElementById('chartInvoices').getContext('2d'),{
    type:'bar',
    data:{
        labels:['Non Payé','Payé','Bénéfice'],
        datasets:[{
            label:'Montant en Dhs',
            data:[<?= (float)$unpaid ?>, <?= (float)$paid ?>, <?= (float)$benefice ?>],
            backgroundColor:['rgba(220,53,69,0.7)','rgba(40,167,69,0.7)','rgba(255,193,7,0.7)'],
            borderColor:['rgba(220,53,69,1)','rgba(40,167,69,1)','rgba(255,193,7,1)'],
            borderWidth:1
        }]
    },
    options:{
        responsive:true,
        plugins:{legend:{display:false}, tooltip:{enabled:true}},
        scales:{y:{beginAtZero:true}}
    }
});

// --- Doughnut Chart لنسب الطرود ---
new Chart(document.getElementById('chartColis').getContext('2d'),{
    type:'doughnut',
    data:{
        labels:['Livré','Reporté','Retourné'],
        datasets:[{
            data:[<?= $deliveredCount ?>,<?= $postponedCount ?>,<?= $returnedCount ?>],
            backgroundColor:['#28a745','#ffc107','#dc3545'],
            hoverOffset:10
        }]
    },
    options:{
        responsive:true,
        plugins:{legend:{position:'bottom', labels:{boxWidth:20, padding:15}}, tooltip:{enabled:true}}
    }
});
</script>

<style>
.bg-gradient-success{background:linear-gradient(45deg,#28a745,#71dd8a);}
.bg-gradient-primary{background:linear-gradient(45deg,#007bff,#66b2ff);}
.bg-gradient-warning{background:linear-gradient(45deg,#ffc107,#ffdd6b);}
.bg-gradient-info{background:linear-gradient(45deg,#17a2b8,#6cc0d9);}
.radius-15{border-radius:15px;}
.progress-bar-animated{transition:width 1.5s ease;}
</style>
