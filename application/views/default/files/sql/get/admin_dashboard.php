<?php
// ---------------------------------------------------------------
// Advanced Dashboard (نسخة محسّنة) — احفظ فوق البلوك القديم أو ضعه في ملف جديد
// يفترض أن $con معرف من قبل (PDO) كما في كودك الأصلي.
// ---------------------------------------------------------------

$from = $_GET['from'] ?? null;
$to = $_GET['to'] ?? null;

/* ---------- الدوال (بقيت كما هي لتضمن عدم تغيير المنطق) ---------- */
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

function echoCard($title, $value, $iconClass, $bgClass = 'bg-white', $textClass = 'text-dark') {
    echo "
    <div class='col-sm-6 col-md-4 col-lg-3 mb-3'>
      <div class='card radius-10 $bgClass h-100'>
        <div class='card-body $textClass d-flex align-items-center'>
          <div class='me-3 icon-round-sm'>
            <i class='$iconClass fa-2x'></i>
          </div>
          <div class='flex-grow-1'>
            <p class='mb-0 fw-bold small text-uppercase'>$title</p>
            <p class='my-1 fs-5 fw-bold'>$value</p>
          </div>
        </div>
      </div>
    </div>";
}

function echoCardLink($title, $count, $link, $iconClass, $bgClass) {
    echo "
    <div class='col-sm-6 col-md-4 col-lg-3 mb-3'>
      <a href='$link' class='text-decoration-none'>
        <div class='card radius-10 $bgClass h-100'>
          <div class='card-body text-white d-flex align-items-center'>
            <div class='me-3 icon-round-sm text-white-50'>
              <i class='$iconClass fa-2x'></i>
            </div>
            <div class='flex-grow-1'>
              <p class='mb-0 fw-bold small text-uppercase'>$title</p>
              <h4 class='my-1 fw-bold'>$count</h4>
            </div>
          </div>
        </div>
      </a>
    </div>";
}

function countUsersByRank($con, $rank, $startDate = null, $endDate = null) {
    $query = "SELECT COUNT(*) FROM users WHERE user_rank = :rank AND user_unlink = 0";
    $params = [':rank' => $rank];

    if (!empty($startDate) && !empty($endDate)) {
        $query .= " AND DATE(user_created) BETWEEN :start AND :end";
        $params[':start'] = $startDate;
        $params[':end'] = $endDate;
    }

    $stmt = $con->prepare($query);
    $stmt->execute($params);
    return $stmt->fetchColumn();
}

/* ---------- استعلاماتك الأصلية (بقيت كما هي) ---------- */
$feeDeliveryCount = fetchSum($con, "SELECT SUM(d_in_total) FROM delivery_invoice WHERE d_in_unlink = '0' AND d_in_state = '1' ", $from, $to, 'd_in_date');
$feeCompanyCount = fetchSum($con, "SELECT SUM(is_net) FROM invoice_script ", $from, $to, 'is_date');

$feeCompanyCount_paied = fetchSum($con, "SELECT SUM(is_net) FROM invoice_script WHERE is_pay = '1' ", $from, $to, 'is_date');
$feeCompanyCount_unpaied = fetchSum($con, "SELECT SUM(is_net) FROM invoice_script WHERE is_pay = '0' ", $from, $to, 'is_date');

$benefice = $feeCompanyCount_paied - $feeDeliveryCount;
$unpaid = fetchSum($con, "SELECT SUM(in_net) FROM invoice WHERE in_state = '0' AND in_unlink = '0'", $from, $to, 'in_date');
$paid = fetchSum($con, "SELECT SUM(in_net) FROM invoice WHERE in_state = '1' AND in_unlink = '0'", $from, $to, 'in_date');
$productCount = fetchCount($con, "SELECT * FROM products WHERE p_unlink = '0'", $from, $to, 'p_date');
$invoiceCount = fetchCount($con, "SELECT * FROM invoice WHERE in_unlink = '0'", $from, $to, 'in_date');
$deliveryInvoiceCount = fetchCount($con, "SELECT * FROM delivery_invoice WHERE d_in_unlink = '0'", $from, $to, 'd_in_date');
$claimCount = fetchCount($con, "SELECT * FROM claim WHERE claim_unlink = '0'", $from, $to, 'claim_date');
$lpCount = fetchCount($con, "SELECT * FROM log_print WHERE lp_unlink = '0' AND lp_type = 'logPickup'", $from, $to, 'lp_date');

$totalFactures = fetchSum($con, "SELECT SUM(in_total) FROM invoice WHERE in_unlink = '0'", $from, $to, 'in_date');
$nombreFactures = fetchCount($con, "SELECT * FROM invoice WHERE in_unlink = '0'", $from, $to, 'in_date');

$totalCommandes = fetchSum($con, "SELECT SUM(or_total) FROM orders WHERE or_unlink = '0' AND or_state_delivery = '1' ", $from, $to, 'or_created');
$nombreCommandes = fetchCount($con, "SELECT * FROM orders WHERE or_unlink = '0' AND or_state_delivery = '1'", $from, $to, 'or_created');

$adminCount = countUsersByRank($con, 'admin', $from, $to);
$vendeurCount = countUsersByRank($con, 'user', $from, $to);
$livreurCount = countUsersByRank($con, 'delivery', $from, $to);

/* ---------- إحصاءات التوصيل (بقيت كما هي) ---------- */
$whereOrders = " WHERE or_unlink = '0' ";
$paramsOrders = [];
if (!empty($from) && !empty($to)) {
    $whereOrders .= " AND DATE(or_created) BETWEEN :from AND :to ";
    $paramsOrders[':from'] = $from;
    $paramsOrders[':to'] = $to;
}

$sqlTotalOrders = "SELECT COUNT(*) FROM orders $whereOrders";
$stmtTotalOrders = $con->prepare($sqlTotalOrders);
foreach ($paramsOrders as $k => $v) { $stmtTotalOrders->bindValue($k, $v); }
$stmtTotalOrders->execute();
$totalOrders = $stmtTotalOrders->fetchColumn();

$sqlDelivered = "SELECT COUNT(*) FROM orders $whereOrders AND or_state_delivery = 1 AND or_delivered IS NOT NULL";
$stmtDelivered = $con->prepare($sqlDelivered);
foreach ($paramsOrders as $k => $v) { $stmtDelivered->bindValue($k, $v); }
$stmtDelivered->execute();
$deliveredCount = $stmtDelivered->fetchColumn();

$sqlDeliveredSum = "SELECT SUM(or_total) FROM orders $whereOrders AND or_state_delivery = 1 AND or_delivered IS NOT NULL";
$stmtDeliveredSum = $con->prepare($sqlDeliveredSum);
foreach ($paramsOrders as $k => $v) { 
    $stmtDeliveredSum->bindValue($k, $v); 
}
$stmtDeliveredSum->execute();
$deliveredTotal = $stmtDeliveredSum->fetchColumn() ?? 0;

$sqlPostponed = "SELECT COUNT(*) FROM orders $whereOrders AND or_state_delivery = 5 AND or_postponed IS NOT NULL";
$stmtPostponed = $con->prepare($sqlPostponed);
foreach ($paramsOrders as $k => $v) { $stmtPostponed->bindValue($k, $v); }
$stmtPostponed->execute();
$postponedCount = $stmtPostponed->fetchColumn();

$sqlReturned = "SELECT COUNT(*) FROM orders $whereOrders AND or_state_delivery != 1 AND or_invoice != 0";
$stmtReturned = $con->prepare($sqlReturned);
foreach ($paramsOrders as $k => $v) { $stmtReturned->bindValue($k, $v); }
$stmtReturned->execute();
$returnedCount = $stmtReturned->fetchColumn();

$tauxDelivered = $totalOrders > 0 ? round(($deliveredCount / $totalOrders) * 100, 2) : 0;
$tauxPostponed = $totalOrders > 0 ? round(($postponedCount / $totalOrders) * 100, 2) : 0;
$tauxReturned = $totalOrders > 0 ? round(($returnedCount / $totalOrders) * 100, 2) : 0;
$otherCount = $totalOrders - ($deliveredCount + $postponedCount + $returnedCount);
$tauxOther = $totalOrders > 0 ? round(($otherCount / $totalOrders) * 100, 2) : 0;

?>


<style>
/* ---------- Visual theme (modern cards + gradients) ---------- */
:root{
  --radius: 14px;
  --card-shadow: 0 10px 25px rgba(13,27,62,0.08);
}

/* body tweaks */
body { background:#f6f8fb; color:#222; font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial; }

/* card style */
.card { border: none; border-radius: var(--radius); transition: transform .18s ease, box-shadow .18s ease; }
.card:hover { transform: translateY(-6px); box-shadow: var(--card-shadow); }

/* small rounded icon */
.icon-round-sm{
  width:56px;height:56px;border-radius:12px;display:flex;align-items:center;justify-content:center;
  background: rgba(255,255,255,0.15);
  box-shadow: inset 0 -2px 6px rgba(0,0,0,0.03);
}

/* gradients mapping (so your classes like bg-gradient-cosmic keep working) */
.bg-gradient-cosmic { background: linear-gradient(135deg,#4e73df,#375ac3); color:#fff !important; }
.bg-gradient-ibiza { background: linear-gradient(135deg,#7b2ff7,#f107a3); color:#fff !important; }
.bg-gradient-ohhappiness { background: linear-gradient(135deg,#17c0eb,#0ea5a4); color:#fff !important; }
.bg-success { background: linear-gradient(135deg,#12b886,#0ca678); color:#fff !important; }
.bg-info { background: linear-gradient(135deg,#0d6efd,#3b82f6); color:#fff !important; }

/* light variants used by echoCard */
.bg-light-primary { background: linear-gradient(135deg,#e8f0ff,#dbeafe); }
.bg-light-success { background: linear-gradient(135deg,#e6f6ee,#d1fae8); }
.bg-light-danger { background: linear-gradient(135deg,#ffecec,#ffd7d7); }
.bg-light-info { background: linear-gradient(135deg,#e8f9ff,#cff4ff); }

/* progress bar rounded */
.progress { height: 26px; border-radius: 12px; overflow: hidden; background: #e9eef8; }

/* header card */
.dashboard-header { display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:16px; }
.dashboard-title { font-size:20px; font-weight:700; }

/* small helpers */
.small-muted { font-size:12px; color:#6b7280; }

/* responsive tweaks */
@media (max-width:576px){
  .icon-round-sm{ width:46px;height:46px; }
  .dashboard-title{ font-size:18px; }
}

/* Dark mode */
body.dark-mode {
  background:#0b1220; color:#e6eef8;
}
body.dark-mode .card { background:#0f1724; color:#e6eef8; box-shadow: none; }
body.dark-mode .small-muted { color:#9aa6bf; }
body.dark-mode .bg-gradient-cosmic, body.dark-mode .bg-gradient-ibiza, body.dark-mode .bg-gradient-ohhappiness { filter: brightness(0.95); }
</style>

<div class="container-fluid py-3">

  <!-- Header: title + period filter + controls -->
  <div class="dashboard-header mb-3">



    <div class="d-flex gap-2 align-items-center">
      <form method="get" class="d-flex gap-2 align-items-center">
        <label class="small-muted mb-0">من</label>
        <input type="date" name="from" class="form-control form-control-sm" value="<?= htmlspecialchars($from) ?>" />
        <label class="small-muted mb-0">إلى</label>
        <input type="date" name="to" class="form-control form-control-sm" value="<?= htmlspecialchars($to) ?>" />
        <button class="btn btn-sm btn-primary">فلتر</button>
      </form>

      <button id="toggleTheme" class="btn btn-sm btn-outline-secondary" title="تبديل الوضع">
        <i class="fa-solid fa-moon"></i>
      </button>
    </div>
  </div>

  <!-- summary cards -->
  <div class="row g-3">
    <?php
    // استخدم دوال الكروت الأصلية (تم تحسين الستايل CSS أعلاه)
    echoCardLink('Administrateurs', $adminCount, 'users?rank=admin', 'fas fa-user-shield', 'bg-gradient-cosmic');
    echoCardLink('Vendeurs', $vendeurCount, 'users?rank=user', 'fas fa-store', 'bg-gradient-ibiza');
    echoCardLink('Livreurs', $livreurCount, 'users?rank=delivery', 'fas fa-motorcycle', 'bg-gradient-ohhappiness');
    echoCardLink('Produits en stock', $productCount, 'stocks', 'fas fa-boxes', 'bg-gradient-cosmic');
    echoCardLink('Factures clients', $invoiceCount, 'invoice', 'fas fa-receipt', 'bg-gradient-ibiza');
    echoCardLink('Factures livreurs', $deliveryInvoiceCount, 'deliveryInvoice', 'fas fa-money-bill-wave', 'bg-gradient-ohhappiness');
    echoCardLink('Réclamations', $claimCount, 'claim', 'fas fa-headset', 'bg-success');
    echoCardLink('Demandes de ramassage', $lpCount, 'log?do=pickup', 'fas fa-shipping-fast', 'bg-dark');
    ?>
  </div>

  <!-- Financial stats -->
  <div class="row g-3 mt-3">
    <div class="col-12">
      <div class="card p-3">
        <div class="row align-items-center">
          <div class="col-md-3 col-sm-6 mb-2">
            <div class="card radius-10 bg-gradient-cosmic h-100">
              <div class="card-body text-white d-flex align-items-center">
                <div class="me-3 icon-round-sm">
                  <i class="fas fa-box-open fa-2x"></i>
                </div>
                <div>
                  <p class="mb-0 fw-bold small text-uppercase">Total Colis Livrés</p>
                  <h4 class="my-1 fw-bold"><?= number_format($deliveredTotal, 2) . " DH"; ?></h4>
                </div>
              </div>
            </div>
          </div>

          <div class="col-md-3 col-sm-6 mb-2">
            <div class="card radius-10 bg-gradient-ibiza h-100">
              <div class="card-body text-white d-flex align-items-center">
                <div class="me-3 icon-round-sm">
                  <i class="fas fa-money-bill-transfer fa-2x"></i>
                </div>
                <div>
                  <p class="mb-0 fw-bold small text-uppercase">Frais de livraison</p>
                  <h4 class="my-1 fw-bold"><?= number_format($feeCompanyCount, 2) . " DH"; ?></h4>
                </div>
              </div>
            </div>
          </div>

          <div class="col-md-3 col-sm-6 mb-2">
            <div class="card radius-10 bg-gradient-ohhappiness h-100">
              <div class="card-body text-white d-flex align-items-center">
                <div class="me-3 icon-round-sm">
                  <i class="fas fa-hand-holding-dollar fa-2x"></i>
                </div>
                <div>
                  <p class="mb-0 fw-bold small text-uppercase">Frais payées</p>
                  <h4 class="my-1 fw-bold"><?= number_format($feeCompanyCount_paied, 2) . " DH"; ?></h4>
                </div>
              </div>
            </div>
          </div>

          <div class="col-md-3 col-sm-6 mb-2">
            <div class="card radius-10 bg-gradient-ibiza h-100">
              <div class="card-body text-white d-flex align-items-center">
                <div class="me-3 icon-round-sm">
                  <i class="fas fa-hand-holding-droplet fa-2x"></i>
                </div>
                <div>
                  <p class="mb-0 fw-bold small text-uppercase">Frais impayées</p>
                  <h4 class="my-1 fw-bold"><?= number_format($feeCompanyCount_unpaied, 2) . " DH"; ?></h4>
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>

  <!-- other small cards using echoCard (kept original function) -->
  <div class="row g-3 mt-3">
    <?php
    echoCard('Paiements des livreurs', number_format($feeDeliveryCount, 2) . " DH", 'fas fa-truck', 'bg-light-primary', 'text-primary');
    echoCard('Bénéfice livraison', number_format($benefice, 2) . " DH", 'fas fa-coins', 'bg-light-success', 'text-success');
    echoCard('Factures impayées', number_format($unpaid, 2) . " DH", 'fas fa-file-invoice-dollar', 'bg-light-danger', 'text-danger');
    echoCard('Factures payées', number_format($paid, 2) . " DH", 'fas fa-file-invoice', 'bg-light-info', 'text-info');
    ?>
  </div>

  <!-- Parcels statistics -->
  <div class="row mt-4">
    <div class="col-12">
      <div class="card">
        <div class="card-header bg-white">
          <h5 class="mb-0">Statistiques des colis</h5>
        </div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-3">
              <div class="card bg-success text-white radius-10 h-100">
                <div class="card-body d-flex align-items-center">
                  <div class="me-3 icon-round-sm">
                    <i class="fas fa-check-circle fa-2x"></i>
                  </div>
                  <div>
                    <h6 class="mb-1">Colis livrés</h6>
                    <h4 class="my-1"><?= $deliveredCount ?></h4>
                    <p class="mb-0"><?= $tauxDelivered ?>% du total</p>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-md-3">
              <div class="card bg-warning text-white radius-10 h-100">
                <div class="card-body d-flex align-items-center">
                  <div class="me-3 icon-round-sm">
                    <i class="fas fa-clock fa-2x"></i>
                  </div>
                  <div>
                    <h6 class="mb-1">Colis reportés</h6>
                    <h4 class="my-1"><?= $postponedCount ?></h4>
                    <p class="mb-0"><?= $tauxPostponed ?>% du total</p>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-md-3">
              <div class="card bg-danger text-white radius-10 h-100">
                <div class="card-body d-flex align-items-center">
                  <div class="me-3 icon-round-sm">
                    <i class="fas fa-undo-alt fa-2x"></i>
                  </div>
                  <div>
                    <h6 class="mb-1">Colis retournés</h6>
                    <h4 class="my-1"><?= $returnedCount ?></h4>
                    <p class="mb-0"><?= $tauxReturned ?>% du total</p>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-md-3">
              <div class="card bg-secondary text-white radius-10 h-100">
                <div class="card-body d-flex align-items-center">
                  <div class="me-3 icon-round-sm">
                    <i class="fas fa-box fa-2x"></i>
                  </div>
                  <div>
                    <h6 class="mb-1">Autres colis</h6>
                    <h4 class="my-1"><?= $otherCount ?></h4>
                    <p class="mb-0"><?= $tauxOther ?>% du total</p>
                  </div>
                </div>
              </div>
            </div>

          </div>

          <!-- progress bar -->
          <div class="mt-4">
            <div class="progress">
              <div class="progress-bar bg-success" role="progressbar" style="width: <?= $tauxDelivered ?>%;" aria-valuenow="<?= $tauxDelivered ?>" aria-valuemin="0" aria-valuemax="100">
                <?= $tauxDelivered ?>% Livrés
              </div>
              <div class="progress-bar bg-warning" role="progressbar" style="width: <?= $tauxPostponed ?>%;" aria-valuenow="<?= $tauxPostponed ?>" aria-valuemin="0" aria-valuemax="100">
                <?= $tauxPostponed ?>% Reportés
              </div>
              <div class="progress-bar bg-danger" role="progressbar" style="width: <?= $tauxReturned ?>%;" aria-valuenow="<?= $tauxReturned ?>" aria-valuemin="0" aria-valuemax="100">
                <?= $tauxReturned ?>% Retournés
              </div>
              <div class="progress-bar bg-secondary" role="progressbar" style="width: <?= $tauxOther ?>%;" aria-valuenow="<?= $tauxOther ?>" aria-valuemin="0" aria-valuemax="100">
                <?= $tauxOther ?>% Autres
              </div>
            </div>

            <div class="mt-3 text-center">
              <p class="mb-0">Total des colis : <strong><?= $totalOrders ?></strong></p>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>

  <!-- Charts -->
  <div class="row mt-4 g-3">
    <div class="col-md-6">
      <div class="card h-100">
        <div class="card-header bg-white">
          <h5 class="mb-0">Répartition des colis</h5>
        </div>
        <div class="card-body">
          <canvas id="pieChart" height="250"></canvas>
        </div>
      </div>
    </div>

    <div class="col-md-6">
      <div class="card h-100">
        <div class="card-header bg-white">
          <h5 class="mb-0">Comparaison financière</h5>
        </div>
        <div class="card-body">
          <canvas id="dashboardChart" height="250"></canvas>
        </div>
      </div>
    </div>
  </div>

</div> <!-- container -->

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// Dark mode toggle
document.getElementById('toggleTheme').addEventListener('click', function(){
  document.body.classList.toggle('dark-mode');
  // change icon
  this.querySelector('i').classList.toggle('fa-sun');
  this.querySelector('i').classList.toggle('fa-moon');
});

// Pie chart
const pieData = {
  labels: ['Livrés', 'Reportés', 'Retournés', 'Autres'],
  datasets: [{
    data: [<?= (int)$deliveredCount ?>, <?= (int)$postponedCount ?>, <?= (int)$returnedCount ?>, <?= (int)$otherCount ?>],
    backgroundColor: ['#28a745', '#ffc107', '#dc3545', '#6c757d'],
    hoverOffset: 6
  }]
};

const pieChart = new Chart(document.getElementById('pieChart'), {
  type: 'pie',
  data: pieData,
  options: {
    responsive: true,
    plugins: {
      legend: { position: 'bottom', labels: { color: getComputedStyle(document.body).color } },
      tooltip: { callbacks: { label: function(ctx){ return ctx.label + ': ' + ctx.raw; } } }
    }
  }
});

// Dashboard bar chart
const dashboardData = {
  labels: ['Paiements livreurs', 'Bénéfice livraison', 'Factures impayées', 'Factures payées'],
  datasets: [{
    label: 'Montants DH',
    data: [<?= floatval($feeDeliveryCount) ?>, <?= floatval($benefice) ?>, <?= floatval($unpaid) ?>, <?= floatval($paid) ?>],
    backgroundColor: ['#0d6efd', '#198754', '#dc3545', '#0dcaf0']
  }]
};

const dashboardChart = new Chart(document.getElementById('dashboardChart'), {
  type: 'bar',
  data: dashboardData,
  options: {
    responsive: true,
    plugins: { legend: { display: false } },
    scales: { y: { beginAtZero: true } }
  }
});
</script>

