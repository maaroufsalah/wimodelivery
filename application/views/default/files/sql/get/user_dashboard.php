<?php
include get_file("files/sql/get/session");

// التاريخ
$from = $_GET['from'] ?? null;
$to   = $_GET['to'] ?? null;

// دوال مساعدة
function fetchSum($con, $sql, $params = []) {
    $stmt = $con->prepare($sql);
    foreach($params as $k=>$v) $stmt->bindValue($k,$v);
    $stmt->execute();
    return $stmt->fetchColumn() ?? 0;
}

function fetchCount($con, $sql, $params = []) {
    $stmt = $con->prepare($sql);
    foreach($params as $k=>$v) $stmt->bindValue($k,$v);
    $stmt->execute();
    return $stmt->rowCount();
}

function echoCard($title, $value, $iconClass, $bgClass='bg-white text-dark') {
    echo "
    <div class='col-sm-4 mb-3'>
      <div class='card radius-15 $bgClass shadow'>
        <div class='card-body d-flex align-items-center'>
          <div class='me-auto'>
            <p class='mb-0 fw-bold'>$title</p>
            <h4 class='my-1'>$value</h4>
          </div>
          <div class='widgets-icons'>
            <i class='$iconClass fa-2x'></i>
          </div>
        </div>
      </div>
    </div>";
}

// إعداد المتغيرات الرئيسية
$paramsOrders = [':trade'=>$loginId];
$whereOrders = "WHERE or_trade=:trade AND or_unlink=0";

$totalOrders    = fetchSum($con,"SELECT COUNT(*) FROM orders $whereOrders",$paramsOrders);
$deliveredCount = fetchSum($con,"SELECT COUNT(*) FROM orders $whereOrders AND or_state_delivery=1 AND or_delivered IS NOT NULL",$paramsOrders);
$postponedCount = fetchSum($con,"SELECT COUNT(*) FROM orders $whereOrders AND or_state_delivery=5 AND or_postponed IS NOT NULL",$paramsOrders);
$returnedCount  = fetchSum($con,"SELECT COUNT(*) FROM orders $whereOrders AND or_state_delivery!=1 AND or_invoice!=0",$paramsOrders);

$tauxDelivered = $totalOrders>0?round(($deliveredCount/$totalOrders)*100):0;
$tauxPostponed = $totalOrders>0?round(($postponedCount/$totalOrders)*100):0;
$tauxReturned  = $totalOrders>0?round(($returnedCount/$totalOrders)*100):0;

// الفواتير
$benefice = fetchSum($con,"SELECT SUM(in_net) FROM invoice WHERE in_user=:loginId AND in_unlink=0", [':loginId'=>$loginId]);
$unpaid   = fetchSum($con,"SELECT SUM(in_total) FROM invoice WHERE in_state=0 AND in_user=:loginId AND in_unlink=0", [':loginId'=>$loginId]);
$paid     = fetchSum($con,"SELECT SUM(in_total) FROM invoice WHERE in_state=1 AND in_user=:loginId AND in_unlink=0", [':loginId'=>$loginId]);

$productCount = fetchCount($con,"SELECT * FROM products WHERE p_user=:loginId AND p_unlink=0", [':loginId'=>$loginId]);
$invoiceCount = fetchCount($con,"SELECT * FROM invoice WHERE in_user=:loginId AND in_unlink=0", [':loginId'=>$loginId]);
$claimCount   = fetchCount($con,"SELECT * FROM claim WHERE claim_user=:loginId AND claim_unlink=0", [':loginId'=>$loginId]);

// جلب حالات الطرود
$stateResult = $con->query("SELECT * FROM state WHERE state_unlink=0 ORDER BY state_name ASC")->fetchAll();
$ordersByState = [];
$totalStates = 0;
foreach($stateResult as $row){
    $count = fetchSum($con,"SELECT COUNT(or_id) FROM orders WHERE or_state_delivery=:state AND or_trade=:trade AND or_unlink=0", [':state'=>$row['state_id'], ':trade'=>$loginId]);
    $ordersByState[$row['state_id']] = $count;
    $totalStates += $count;
}

?>

<!-- ==== فلترة التاريخ ==== -->
<form method="get" class="row g-2 mb-4">
  <div class="col-md-4"><label>De :</label><input type="date" name="from" class="form-control" value="<?= $from ?>"></div>
  <div class="col-md-4"><label>A :</label><input type="date" name="to" class="form-control" value="<?= $to ?>"></div>
  <div class="col-md-4"><button type="submit" class="btn btn-primary w-100 mt-4">Filtrer</button></div>
</form>

<!-- ==== البطاقات الرئيسية ==== -->
<div class="row mb-4">
<?php
echoCard('Revenue', number_format($benefice,2,',',' ') . ' MAD','fas fa-coins','bg-gradient-success text-white');
echoCard('Facture Non-Payé', number_format($unpaid,2,',',' ') . ' MAD','fas fa-file-invoice-dollar','bg-gradient-warning text-dark');
echoCard('Facture Payé', number_format($paid,2,',',' ') . ' MAD','fas fa-file-invoice','bg-gradient-primary text-white');

echoCard('Produits En stock', $productCount,'fas fa-boxes','bg-gradient-info text-white');
echoCard('Factures', $invoiceCount,'fas fa-receipt','bg-gradient-cosmic text-white');
echoCard('Réclamations', $claimCount,'fas fa-headset','bg-gradient-danger text-white');
?>
</div>

<!-- ==== شريط تقدم الطرود ==== -->
<div class="mb-5">
<h5>Distribution des colis (%)</h5>
<div class="progress" style="height:50px;">
  <div class="progress-bar bg-success progress-bar-animated" style="width: <?= $tauxDelivered ?>%">Livré <?= $tauxDelivered ?>%</div>
  <div class="progress-bar bg-warning text-dark progress-bar-animated" style="width: <?= $tauxPostponed ?>%">Reporté <?= $tauxPostponed ?>%</div>
  <div class="progress-bar bg-danger progress-bar-animated" style="width: <?= $tauxReturned ?>%">Retourné <?= $tauxReturned ?>%</div>
</div>
</div>

<!-- ==== جدول الحالات والنسب ==== -->
<div class="row mb-4">
<?php foreach($stateResult as $row):
    $count = $ordersByState[$row['state_id']] ?? 0;
    $percentage = $totalStates>0?round(($count/$totalStates)*100,1):0;
?>
<div class="col-md-4 mb-2">
  <a href="packages?state=<?= $row['state_id'] ?>" class="text-decoration-none">
    <div class="card radius-15 shadow p-3">
      <div class="d-flex align-items-center">
        <div class="me-auto">
          <h6 class="mb-1"><?= $row['state_name'] ?></h6>
          <small><?= $count ?> commandes (<?= $percentage ?>%)</small>
        </div>
        <div style="width:30px;height:30px;background:<?= $row['state_background'] ?>;border-radius:50%;"></div>
      </div>
    </div>
  </a>
</div>
<?php endforeach; ?>
</div>

<!-- ==== الرسوم البيانية ==== -->
<div class="row my-5">
  <div class="col-md-6"><canvas id="chartColis"></canvas></div>
  <div class="col-md-6"><canvas id="chartInvoices"></canvas></div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Doughnut الطرود
new Chart(document.getElementById('chartColis').getContext('2d'),{
    type:'doughnut',
    data:{
        labels:['Livré','Reporté','Retourné'],
        datasets:[{data:[<?= $deliveredCount ?>,<?= $postponedCount ?>,<?= $returnedCount ?>],
        backgroundColor:['#28a745','#ffc107','#dc3545'], hoverOffset:10}]
    },
    options:{responsive:true,plugins:{legend:{position:'bottom'}}}
});

// Bar الفواتير
new Chart(document.getElementById('chartInvoices').getContext('2d'),{
    type:'bar',
    data:{
        labels:['Non-Payé','Payé','Bénéfices'],
        datasets:[{
            label:'Montant en MAD',
            data:[<?= (float)$unpaid ?>,<?= (float)$paid ?>,<?= (float)$benefice ?>],
            backgroundColor:['rgba(255,99,132,0.6)','rgba(54,162,235,0.6)','rgba(255,206,86,0.6)'],
            borderColor:['rgba(255,99,132,1)','rgba(54,162,235,1)','rgba(255,206,86,1)'],
            borderWidth:1
        }]
    },
    options:{responsive:true,scales:{y:{beginAtZero:true}}}
});
</script>

<style>
.radius-15{border-radius:15px;}
.bg-gradient-success{background:linear-gradient(45deg,#28a745,#71dd8a);}
.bg-gradient-primary{background:linear-gradient(45deg,#007bff,#66b2ff);}
.bg-gradient-warning{background:linear-gradient(45deg,#ffc107,#ffdd6b);}
.bg-gradient-info{background:linear-gradient(45deg,#17a2b8,#6cc0d9);}
.bg-gradient-cosmic{background:linear-gradient(45deg,#9c27b0,#e040fb);}
.bg-gradient-danger{background:linear-gradient(45deg,#dc3545,#ff6b6b);}
.progress-bar-animated{transition:width 1.5s ease;}
.widgets-icons i{display:block;}
</style>
