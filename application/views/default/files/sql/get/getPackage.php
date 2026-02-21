<style>
body{
font-family: sans-serif;
}

.btn-sm, .btn-group-sm > .btn {
font-size: 10px;
}
</style>

<?php 

$bootstrap = new Bootstrap();
$bootstrap->runBootstrap();
global $con;

include get_file("files/sql/get/os_settings");
include get_file("files/sql/get/session");
include get_file("files/sql/get/functions");

$table = "orders";
$primaryKey = "or_id";

// إعدادات العرض والصفحات
$display = isset($_POST["display"]) ? (int) $_POST["display"] : 10;
$limit = in_array($display, [10, 50, 100, 200, 600]) ? $display : 10;

$page = isset($_POST['page']) && (int) $_POST['page'] > 1 ? (int) $_POST['page'] : 1;
$start = ($page - 1) * $limit;

// تحويل قيمة واحدة أو مصفوفة إلى مصفوفة أعداد صحيحة
function toIntArray($val) {
    if (is_array($val)) {
        return array_filter(array_map('intval', $val), fn($v) => $v > 0);
    } elseif (!empty($val)) {
        return [intval($val)];
    }
    return [];
}

// استقبال الفلاتر
$search         = isset($_POST['search']) ? trim($_POST['search']) : '';
$filter_city    = isset($_POST['city']) ? toIntArray($_POST['city']) : [];
$filter_state   = isset($_POST['state']) ? (array)$_POST['state'] : [];
$filter_trade   = isset($_POST['user']) ? toIntArray($_POST['user']) : [];
$filter_delivery= isset($_POST['duser']) ? toIntArray($_POST['duser']) : [];
$export         = $_POST['exports'] ?? '';
$change         = $_POST['change'] ?? "";


print $change ; 

// تصدير البيانات
if ($export === "true") {
    echo '
    <script>
    window.open(
        "package_export?city=' . implode(',', $filter_city) . 
        '&user=' . implode(',', $filter_trade) . 
        '&state=' . implode(',', $filter_state) . '",
        "_blank"
    );
    </script>';
}

// دالة لبناء شرط IN
function buildInCondition($field, $values, &$params) {
    if (count($values) === 0) return '';
    $placeholders = implode(',', array_fill(0, count($values), '?'));
    $params = array_merge($params, $values);
    return " AND $field IN ($placeholders) ";
}

// دالة لبناء شرط or_change
function buildChangeFlagCondition($change, &$params) {
    if (!empty($change)) {
        return " AND or_change = '1' ";
    }
    return '';
}

// دالة لبناء شرط الحالة
function buildStateCondition($filter_state, &$params) {
    if (empty($filter_state)) {
        return " AND or_state_delivery != 0 ";
    }

    $conditions = [];
    $values = [];

    foreach ($filter_state as $state_val) {
        if ($state_val === 'int') {
            $conditions[] = "or_state_delivery = 0";
        } elseif ($state_val === 'unlink') {
            continue; // سيتم التعامل مع unlink مركزي
        } else {
            $values[] = (int)$state_val;
        }
    }

    if ($values) {
        $placeholders = implode(',', array_fill(0, count($values), '?'));
        $conditions[] = "or_state_delivery IN ($placeholders)";
        $params = array_merge($params, $values);
    }

    return empty($conditions) ? " AND or_state_delivery != 0 " : " AND (" . implode(" OR ", $conditions) . ")";
}

// ===== شرط or_unlink مركزي =====
$has_unlink_filter = in_array('unlink', $filter_state, true);
$unlink_cond = $has_unlink_filter ? " or_unlink = '1' " : " or_unlink = '0' ";

// تصفية حسب صلاحية المستخدم
if ($loginRank == "admin") {
    $xoo = $unlink_cond;
} elseif ($loginRank == "user") {
    $xoo = $unlink_cond . " AND or_trade = '" . intval($loginId) . "' ";
} elseif ($loginRank == "aide") {
    $xoo = $unlink_cond . " AND or_trade = '" . intval($loginUser['user_aide']) . "' ";
} elseif ($loginRank == "delivery") {
    $xoo = $unlink_cond . " AND or_delivery_user = '" . intval($loginId) . "' ";
} else {
    $xoo = " or_unlink = '10' ";
}

// ===== دالة بناء الاستعلام النهائي =====
function buildQuery($baseQuery, $search, $filter_city, $filter_state, $filter_trade, $filter_delivery, $change, &$params) {
    if ($search !== '') {
        $srs = '%' . str_replace(' ', '%', $search) . '%';
        $baseQuery .= " AND (
            or_id LIKE ? OR
            UNIX_TIMESTAMP(or_created) LIKE ? OR
            or_name LIKE ? OR
            or_phone LIKE ?
        ) ";
        array_push($params, $srs, $srs, $srs, $srs);
    }

    $baseQuery .= buildInCondition('or_city', $filter_city, $params);
    $baseQuery .= buildStateCondition($filter_state, $params);
    $baseQuery .= buildInCondition('or_trade', $filter_trade, $params);
    $baseQuery .= buildInCondition('or_delivery_user', $filter_delivery, $params);
    $baseQuery .= buildChangeFlagCondition($change, $params);

    return $baseQuery;
}

// ===== استعلام البيانات =====
$params = [];
$query = buildQuery("SELECT * FROM $table WHERE $xoo", $search, $filter_city, $filter_state, $filter_trade, $filter_delivery, $change, $params);
$query .= " ORDER BY or_id DESC LIMIT $start, $limit";

$statement = $con->prepare($query);
$statement->execute($params);
$data = $statement->fetchAll(PDO::FETCH_ASSOC);

// ===== استعلام العدد =====
$countParams = [];
$countQuery = buildQuery("SELECT COUNT(*) FROM $table WHERE $xoo", $search, $filter_city, $filter_state, $filter_trade, $filter_delivery, $change, $countParams);

$statement = $con->prepare($countQuery);
$statement->execute($countParams);
$total_data = $statement->fetchColumn();





// بعد هذا يمكنك متابعة طباعة النتائج كما في كودك الأصلي

// طباعة النتائج
if (count($data) > 0) {


function getStateActivity($order_id, $con) {
$stmt = $con->prepare("SELECT sa.*, 
u.user_name, 
s.state_name 
FROM state_activity sa 
LEFT JOIN users u ON u.user_id = sa.sa_user 
LEFT JOIN state s ON s.state_id = sa.sa_state 
WHERE sa.sa_order = ? 
ORDER BY sa.sa_id ASC");

$stmt->execute([$order_id]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($rows) == 0) {
echo "<div class='alert alert-info'>--</div>";
}else{

echo '<ul class="timeline">';
foreach ($rows as $row) {
echo '
<li class="timeline-item mb-5">
<h6 class="fw-bold">' . ($row["state_name"]) . '</h6>
<p class="text-muted mb-2"><small><i class="bi bi-calendar3"></i> ' . fd($row["sa_date"]) . '</small></p>
<p class="mb-1">' . ($row["sa_note"]) . '</p>
<p class="text-muted"><small>Mis à jour par :  ' . ($row["user_name"]) . '</small></p>
</li>';
}
echo '</ul>';
}
}
?>
<div class="table-responsive">
<table class="table table-bordered table-hover align-middle text-center">
<thead class="table-dark">
<tr>
<th>ID</th>
<th>Vendeur</th>
<th>Destinataire</th>
<th>Destinataire <br> changé</th>
<th>Prix</th>
<th>Ville</th>
<th>Livreur</th>
<?php if(($loginRank == "admin")||($loginRank == "user")):?>
<th>Emballage</th>
<?php endif;?>
<th>État</th>
<?php if(($loginRank == "admin")||($loginRank == "delivery")):?>
<th>Facture Livreur</th>
<?php endif;?>
<?php if(($loginRank == "admin")||($loginRank == "user")):?>
<th>Facture Vendeur</th>
<?php endif;?>
<th>Actions</th>
</tr>
</thead>
<tbody>

<?php foreach ($data as $row): ?>
<?php




$con->query("
UPDATE orders SET or_seen = 1 WHERE or_seen = 0 AND or_unlink = '0' AND or_id = '".$row['or_id']."'
");





$created_date = new DateTime($row['or_created']);
$now = new DateTime(); // pas besoin de date("Y-m-d")

$interval = $created_date->diff($now);




// جلب بيانات المستخدم والمدينة والحالة
$tradeStmt = $con->prepare("SELECT * FROM users WHERE user_id = ? AND user_rank = 'user' LIMIT 1");
$tradeStmt->execute([$row['or_trade']]);
$trade = $tradeStmt->fetch() ?: [];

$cityStmt = $con->prepare("SELECT * FROM city WHERE city_id = ? LIMIT 1");
$cityStmt->execute([$row['or_city']]);
$city = $cityStmt->fetch() ?: [];



$stateStmt = $con->prepare("SELECT * FROM state WHERE state_id = ? LIMIT 1");
$stateStmt->execute([$row['or_state_delivery']]);
$state = $stateStmt->fetch() ?: [];

if (($row['or_box'])>0){

$boxStmt = $con->prepare("SELECT * FROM box WHERE box_id = ? LIMIT 1");
$boxStmt->execute([$row['or_box']]);
$box = $boxStmt->fetch() ?: [];

}

if (($row['or_invoice'])>0){
$iStmt = $con->prepare("SELECT * FROM invoice WHERE in_id = ? LIMIT 1");
$iStmt->execute([$row['or_invoice']]);
$invoice = $iStmt->fetch() ?: [];
}

if (($row['or_delivery_invoice'])>0){
$idStmt = $con->prepare("SELECT * FROM delivery_invoice WHERE d_in_id = ? LIMIT 1");
$idStmt->execute([$row['or_delivery_invoice']]);
$delivery_invoice = $idStmt->fetch() ?: [];
}

$app_whatsapp = urlencode("مرحبًا ، أتواصل معك بخصوص الطلب الذي اشتريته من البائع " . ($trade['user_name'] ?? ''));

// المنتجات داخل الطلب
$stmt = $con->prepare("SELECT * FROM order_items WHERE order_id = ?");
$stmt->execute([$row['or_id']]);
$items = $stmt->fetchAll();



if (!empty($row['or_delivery_user'])) {
$dmuStmt = $con->prepare("SELECT * FROM users WHERE user_id = '{$row['or_delivery_user']}' AND user_rank = 'delivery' LIMIT 1");
$dmuStmt->execute();
$deliveryUser = $dmuStmt->fetch();
} else {
$deliveryUser = null; // في حال كانت القيمة فارغة
}
?>

<?php
if ($row['or_unlink'] == 0){

?>
<tr  style="border-left: 2px solid <?= $state['state_background'] ?? '#ccc' ?>;">



<td>
<?php
$showCheckbox = (
// الشرط الخاص بـ delivery
($loginRank == "delivery" &&
$row['or_invoice'] == "0" &&
$row['or_delivery_invoice'] == "0" &&
$row['or_state_delivery'] != 1)
) || (
// الشرط الخاص بـ admin
$loginRank == "admin" || $loginRank == "user" &&
$row['or_invoice'] == "0" &&
$row['or_delivery_invoice'] == "0"
);
?>

<?php if ($showCheckbox): ?>
<label for='cb_<?= $row['or_id']; ?>'><b><?= $row['or_id']; ?></b></label>
<input onclick="updateHiddenField();" type="checkbox" class="bulk-check order-checkbox" id='cb_<?= $row['or_id']; ?>' value="<?= $row['or_id']; ?>">
<?php else: ?>
<b><?= $row['or_id']; ?></b>
<?php endif; ?>
<div style='font-size:10px;'><?= "Ajoutée : " . fd($row['or_created']); ?></div>
<?php if (($row['or_exp_date']) > 0): ?>
<div><i class="fa-solid fa-share-from-square"></i> <?= fd($row['or_exp_date']) ?></div>
<?php endif; ?>



<?php if (($row['or_change']) > 0): ?>
<div class="badge bg-warning">Change (colis N° : <?=$row['or_change_code']?>)</div>
<?php endif; ?>


<?php if (($row['or_fee_change'] > 0) && ($row['or_fee'] > 0)): ?>
    <div class="badge bg-success">FC</div>
<?php elseif ($row['or_fee'] > 0): ?>
    <div class="badge bg-info">GC</div>
<?php elseif ($row['or_fee_change'] > 0): ?>
    <div class="badge bg-success">FC</div>
<?php endif; ?>


<?php if (($row['or_fpc']) > 0): ?>
<div class="badge bg-danger">FPC</div>
<?php endif; ?>



</td>







<td>
<?= $trade['user_name'] ?? '—' ?><br>(<?= $trade['user_owner'] ?? '—' ?>)<br><?= $trade['user_phone'] ?? '—' ?>
<a class="text-success mx-1" target="_blank" href="https://wa.me/+212<?= $trade['user_phone']; ?>">
<i class="fa-brands fa-whatsapp"></i>
</a>
</td>



<td>
<b><?= $row['or_name']; ?><br>(<?= $row['or_phone']; ?>)</b><br>
<a class="text-success mx-1" target="_blank" href="https://wa.me/+212<?= $row['or_phone']; ?>">
<i class="fa-brands fa-whatsapp"></i>
</a>
</td>



<td>
<?php if (!empty($row['or_name_new'])) : ?>
<b><?= ($row['or_name_new']); ?><br>(<?= ($row['or_phone_new']); ?>)</b><br>
<?php endif; ?>

<?php if (!empty($row['or_phone_new'])) : ?>
<a class="text-success mx-1" target="_blank" href="https://wa.me/+212<?= ($row['or_phone_new']); ?>">
<i class="fa-brands fa-whatsapp"></i>
</a>
<?php endif; ?>

<?php if (!empty($row['or_location_new'])) : ?>
<div>(<?= ($row['or_location_new']); ?>)</div>
<?php endif; ?>
</td>




<td>
<b><?= $row['or_total']; ?> Dhs</b>
<?php if ($row['or_state_delivery'] == 60): ?>
<div style="color: blue; font-weight: bold;"><i class="fa-solid fa-repeat"></i> Échange</div>
<?php endif; ?>
</td>



<td>
<?= $city['city_name'] ?? '—'; ?>
<?php if ($loginRank == "user" || $loginRank == "admin"): ?>
<br><a class="text-success btn btn-sm my-2" href="claim?do=new&id=<?= md5($row['or_id']); ?>">
<i class="fa-solid fa-headset"></i> Réclamer
</a>
<?php endif; ?>
</td>


<td>
<?php if (!empty($deliveryUser['user_name'])): ?>
<div><b><?= $deliveryUser['user_name'] ?></b></div>
<div><b><?= $deliveryUser['user_phone'] ?></b></div>
<a class="text-success mx-1" target="_blank" href="https://wa.me/+212<?= $deliveryUser['user_phone']; ?>">
<i class="fa-brands fa-whatsapp"></i>
</a>
<?php endif; ?>
</td>


<?php if(($loginRank == "admin")||($loginRank == "user")):?>

<td>
<?php if (!empty($row['or_box'])): ?>
<div><b><?= $box['box_name'] ?> - <?= $row['or_box_price'] ?></b></div>
<?php else : ?>
-
<?php endif; ?>
</td>
<?php endif; ?>


<td>

<div id="n_td_state_<?= $row['or_id']; ?>" style='display:none;'>
<div id="result_state"></div>
</div>

<div id="n_td_state_<?= $row['or_id']; ?>">
<?php if ($row['or_state_delivery'] == 0): ?>
<a data-bs-toggle='modal' data-bs-target='#modal_state<?= $row['or_id']; ?>'  class='btn btn-sm' style='background:#169dd0;color:black'><b>En Attente</b></a>
<?php else: ?>
<a data-bs-toggle="modal" data-bs-target="#modal_state<?= $row['or_id']; ?>" class="btn btn-sm" style="background:<?= $state['state_background']; ?>;color:<?= $state['state_color']; ?>;">
<b><?= $state['state_name']; ?></b>
</a>
<?php endif; ?>

<?php if ($row['or_state_delivery'] == 5): ?>
<?php if (!empty($row['or_postponed'])): ?>
<div class="text-info" style=''><b><?= $row['or_postponed'] ?></b></div>
<?php endif; ?>
<?php endif; ?>


<?php if ($row['or_state_delivery'] == 54): ?>
<?php if (!empty($row['or_programmed_date'])): ?>
<div class="text-info"><b><?= $row['or_programmed_date'] ?></b></div>
<?php endif; ?>
<?php endif; ?>

</div>



</td>



<?php if(($loginRank == "admin")||($loginRank == "delivery")):?>
<td>
<?php if (($row['or_delivery_invoice']) > 0): ?>
<a target="_blank" class="text-info" href="print_delivery_invoice?id=<?= md5($row['or_delivery_invoice']); ?>">L / Facturé</a>
<?php if ($delivery_invoice['d_in_state'] == "1"): ?>
- <span class="text-success">Payé</span>
<?php endif; ?>
<?php endif; ?>
</td>
<?php endif; ?>


<?php if(($loginRank == "admin")||($loginRank == "user")):?>
<td>
<?php if (($row['or_invoice']) > 0): ?>
<a target="_blank" class="btn btn-sm btn-warning my-1" href="print_invoice?id=<?= md5($row['or_invoice']); ?>">V / Facturé</a>
<?php if ($invoice['in_state'] == "1"): ?>
<div class="text-success">Payé</div>
<?php endif; ?>
<?php endif; ?>
</td>
<?php endif; ?>


<td>
<div class='' style='display: inline-flex;gap: 3px;'>


<a data-bs-toggle="modal" data-bs-target="#orderDetailsModal<?= $row['or_id']; ?>" class="text-white btn btn-sm d-flex align-items-center bg-success get-detail">
<i class="fa-solid fa-eye"></i>
</a>

<?php if (hasUserPermission($con, $loginId, 10 ,'admin')): ?>
<a data-bs-toggle="modal" data-bs-target="#modalDelete<?= $row['or_id']; ?>" class="text-white btn btn-sm d-flex align-items-center bg-danger get-detail">
<i class="fa-solid fa-trash-alt"></i>
</a>
<?php endif; ?>

<?php if (($loginRank == "admin")||($loginRank == "agency")||($loginRank == "delivery")||($loginRank == "user")): ?>
<a data-bs-toggle="modal" data-bs-target="#tracking<?= $row['or_id']; ?>" class="text-white btn btn-sm d-flex align-items-center bg-dark get-detail">
<i class="fa-solid fa-history"></i>
</a>
<?php endif; ?>

<?php if ((hasUserPermission($con, $loginId, 9 ,'admin'))): ?>
<a class='btn btn-sm d-flex align-items-center bg-info get-detail text-white' href='?do=edit&order_id=<?= md5($row['or_id']); ?>'>
<i class="fa-solid fa-edit"></i>
</a>
<?php endif; ?>


<?php if (($row['or_state_delivery'] == 0) and ($loginRank == "user")): ?>
<a class='btn btn-sm d-flex align-items-center bg-success get-detail text-white' href='?do=edit&order_id=<?= md5($row['or_id']); ?>'>
<i class="fa-solid fa-edit"></i>
</a>
<a data-bs-toggle='modal' data-bs-target='#modalDelete<?= $row['or_id']; ?>' class='btn btn-sm d-flex align-items-center bg-danger get-detail text-white'>
<i class="fa-solid fa-trash"></i>
</a>
<?php endif; ?>




</div>
</td>


</tr>


<?php
}else{
?>

<tr>
<td colspan="12">
Colis N° <b><?php print $row['or_id'] ;?></b> Déja Supprimé 
<a href='dataUpdate?do=restore&id=<?= md5($row['or_id']); ?>' class='btn btn-sm btn-success'>Restaurer</a>
</td>
</tr>

<?php
}
?>



<!-- يمكن عرض التفاصيل كما هو في div منفصل أو Modal إذا أردت -->






<!-- مودال التفاصيل -->
<div class="modal fade" id="orderDetailsModal<?= $row['or_id']; ?>" tabindex="-1" aria-labelledby="orderDetailsModalLabel<?= $row['or_id']; ?>" aria-hidden="true">
<div class="modal-dialog modal-lg modal-dialog-scrollable">
<div class="modal-content">
<div class="modal-header">
<h5 class="modal-title" id="orderDetailsModalLabel<?= $row['or_id']; ?>">Détails de la commande #<?= $row['or_id']; ?></h5>
<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body" style="font-size: 14px;">

<div class='row'><div class='col-4'><b>Date :</b></div><div class='col-8'><?= date('d/m/Y H:i', strtotime($row['or_created'])); ?></div></div><hr>

<div class='row'>
<div class='col-4'><b>Statut :</b></div>
<div class='col-8'>
<?php if ($row['or_state_delivery'] == 0): ?>
<a data-bs-toggle='modal' data-bs-target='#modal_state<?= $row['or_id']; ?>'  class='btn btn-sm' style='background:#169dd0;color:black'><b>En Attente</b></a>
<?php else: ?>
<a data-bs-toggle='modal' data-bs-target='#modal_state<?= $row['or_id']; ?>'  class='btn btn-sm' style='background:<?= $state['state_background']; ?>;color:<?= $state['state_color']; ?>'><b><?= $state['state_name']; ?></b></a>
<?php endif; ?>
<a data-bs-toggle='modal' data-bs-target='#tracking<?= $row['or_id']; ?>'  class='text-info'>Suivi</a>
</div>
</div><hr>

<div class='row'><div class='col-4'><b>Code Colis :</b></div><div class='col-8'><?= $row['or_code']; ?></div></div><hr>

<div class='row'><div class='col-4'><b>Expéditeur :</b></div><div class='col-8'><?= $trade['user_name'] ?? '—'; ?></div></div><hr>

<div class='row'><div class='col-4'><b>Destinataire :</b></div><div class='col-8'><?= $row['or_name'] ?? '—'; ?></div></div><hr>

<?php if (($row['or_box'])>0): ?>
<div class='row'><div class='col-4'><b>Emballage :</b></div><div class='col-8'><?= $box['box_name'] ?? '—'; ?> - ( <?= $row['or_box_price'] ?? '—'; ?> Dhs )</div></div><hr>
<?php endif; ?>

<div class='row'>
<div class='col-4'><b>Téléphone :</b></div>
<div class='col-8'>
<a class='btn btn-info btn-sm' href='tel:<?= $row['or_phone']; ?>'><i class="fa-solid fa-square-phone"></i> <?= $row['or_phone']; ?></a>
<a class='text-success mx-3' href='https://wa.me/+212<?= $row['or_phone']; ?>?text=<?= $app_whatsapp; ?>'><i class="fa-brands fa-2x fa-whatsapp"></i></a>
</div>
</div><hr>

<div class='row'><div class='col-4'><b>Ville :</b></div><div class='col-8'><?= $city['city_name'] ?? '—'; ?></div></div><hr>

<div class='row'><div class='col-4'><b>Adresse :</b></div><div class='col-8'><?= $row['or_address']; ?></div></div><hr>

<div class='row'>
<div class='col-4'><b>Produit :</b></div>
<div class='col-8'>

<?php if ($items):?>
<?php foreach ($items as $item):
$stmt = $con->prepare("SELECT * FROM order_item_options WHERE item_id = ?");
$stmt->execute([$item['item_id']]);
$options = $stmt->fetchAll(); ?>
<div>x(<?= $item['quantity'] ?>) | <?= ($item['product_name']) ?></div>
<?php endforeach; ?>
<?php else: ?>
<div>x(<?= $row['or_qty'] ?>) | <?= ($row['or_item']) ?></div>
<?php endif; ?>
</div>
</div><hr>

<h5>Total : <span class="text-primary"><?= number_format($row['or_total'], 2) ?> Dhs</span></h5><hr>

<?php if (($row['or_state_delivery'] == 0) and ($loginRank == "user")): ?>
<div class='text-center mb-3'>
<a class='btn btn-info btn-sm mx-2' href='?do=edit&order_id=<?= md5($row['or_id']); ?>'>Modifier</a>
<a data-bs-toggle='modal' data-bs-target='#modalDelete<?= $row['or_id']; ?>' class='btn btn-sm btn-danger mx-2'>Supprimer</a>
</div>
<?php endif; ?>

<div class='text-center mb-3'>
<?php if ((hasUserPermission($con, $loginId, 11 ,'admin'))): ?>
<a class='btn btn-sm btn-dark' href='dataUpdate?do=delivery_unlink&id=<?= md5($row['or_id']); ?>'>Supprimer Au livreur</a>
<?php endif; ?>
<?php if ((hasUserPermission($con, $loginId, 9 ,'admin'))): ?>
<a class='btn btn-info btn-sm mx-2' href='?do=edit&order_id=<?= md5($row['or_id']); ?>'>Modifier</a>
<?php endif; ?>
<?php if ((hasUserPermission($con, $loginId, 10 ,'admin'))): ?>
<a data-bs-toggle='modal' data-bs-target='#modalDelete<?= $row['or_id']; ?>' class='btn btn-sm btn-danger mx-2'>Supprimer</a>
<?php endif; ?>

<?php if (((hasUserPermission($con, $loginId, 9 ,'admin')) ||$loginRank == "user" || hasUserPermissionAide($con, $loginId, 57 ,"aide"))): ?>
<?php if ($row['or_delivery_user'] !== "0"): ?>

<a class='btn btn-info btn-sm my-2 mx-2' href='?do=change&order_id=<?= md5($row['or_id']); ?>'>Changement d'adresse </a>


<?php endif; ?>
<?php endif; ?>


<?php if ($loginRank == "admin"): ?>
<a class='btn btn-danger btn-sm my-2 mx-2' href='?do=fee&order_id=<?= md5($row['or_id']); ?>'>Frais ajoutés</a>
<?php endif; ?>



</div>

<hr>


</div>
<div class="modal-footer">
<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
</div>
</div>
</div>
</div>




<?php if (hasUserPermission($con, $loginId, 10 ,'admin') || $loginRank == "user"): ?>
<!-- نافذة الحذف -->
<div class='modal fade' id='modalDelete<?= $row['or_id']; ?>' tabindex='-1'>
<div class='modal-dialog modal-dialog-centered'>
<div class='modal-content'>
<div class='modal-header'>
<h5 class='modal-title'>Supprimer un élément</h5>
<button type='button' class='btn-close' data-bs-dismiss='modal'></button>
</div>
<div class='modal-body text-center'>
<h6>Êtes-vous sûr de vouloir supprimer cet élément ?</h6>
<a class='btn btn-success' href='dataUnlink?do=orders&id=<?= md5($row['or_id']); ?>'>Oui, je veux</a>
</div>
</div>
</div>
</div>
<?php endif; ?>




<?php if (($loginRank == "admin")||($loginRank == "agency")||($loginRank == "delivery")||($loginRank == "user")): ?>
<!-- نافذة الحذف -->
<div class='modal fade' id='tracking<?= $row['or_id']; ?>' tabindex='-1'>
<div class='modal-dialog modal-dialog-centered'>
<div class='modal-content'>
<div class='modal-header'>
<h5 class='modal-title'>Suivi Votre colis <?= $row['or_id']; ?></h5>
<button type='button' class='btn-close' data-bs-dismiss='modal'></button>
</div>

<div class='modal-body text-center'>


<ul class="timeline">
<li class="timeline-item mb-5">
<h6 class="fw-bold">En Attente</h6>
<p class="text-muted mb-2"><small><i class="bi bi-calendar3"></i> <?= fd($row['or_created']); ?></small></p>
</li>
</ul>


<?php getStateActivity($row['or_id'], $con); ?>
</div>

</div>
</div>
</div>
<?php endif; ?>



















<?php endforeach; ?>
</tbody>
</table>
</div>








<?php foreach ($data as $row): ?>


<?php
$showCheckbox = (
// الشرط الخاص بـ delivery
($loginRank == "delivery" &&
$row['or_invoice'] == "0" &&
$row['or_delivery_invoice'] == "0" &&
$row['or_state_delivery'] != 1)
) || (
// الشرط الخاص بـ admin
$loginRank == "admin" &&
$row['or_invoice'] == "0" &&
$row['or_delivery_invoice'] == "0"
);
?>
<?php if ($showCheckbox): ?>


<!-- نافذة تغيير الحالة -->
<div class='modal fade' id='modal_state<?= $row['or_id']; ?>' tabindex='-1'>
<div class='modal-dialog modal-dialog-centered'>
<div class='modal-content'>
<div class='modal-header'>
<h5 class='modal-title'>Changer l'état de livraison : <?= $row['or_id']; ?></h5>
<button type='button' class='btn-close ud' data-bs-dismiss='modal'></button>
</div>

<?php
$id = "formIdstate".$row['or_id'];
$result = "data_result_state_".$row['or_id'];
$action = "update_order_state";
$method = "post";
formAwdStart($id, $result, $action, $method); 
?>

<div class='modal-body text-center'>
<input type="hidden" name="order_id_s" value="<?= $row['or_id']; ?>" />

<label>Choisir une nouvelle état:</label>
<select id="state_select<?= $row['or_id']; ?>" name="state_id" class="form-select" required>
<option value="">Sélectionner une état</option>
<option value="0">En Attente</option>
<?php

if ($loginRank == "delivery"){
$stmt = $con->prepare("SELECT * FROM state WHERE state_unlink = '0' AND state_rank = 'delivery' ORDER BY state_name ASC");
$stmt->execute();
$states = $stmt->fetchAll(PDO::FETCH_ASSOC);

}else{
$stmt = $con->prepare("SELECT * FROM state WHERE state_unlink = '0' ORDER BY state_name ASC");
$stmt->execute();
$states = $stmt->fetchAll(PDO::FETCH_ASSOC);

}


foreach ($states as $state) {
echo '<option value="' . $state['state_id'] . '" data-name="' . ($state['state_name']) . '">' . ($state['state_name']) . '</option>';
}
?>
</select>

<div id="report_date_div<?= $row['or_id']; ?>" style="display: none;">
<label for="postponed_date<?= $row['or_id']; ?>">Nouvelle date de report:</label>
<input type="date" id="postponed_date<?= $row['or_id']; ?>" name="postponed_date" class="form-control">
</div>

<label for="note<?= $row['or_id']; ?>">Note / Justification (toutes les autres situations):</label>
<input type="text" id="note<?= $row['or_id']; ?>" name="note" placeholder="Ex: Client a demandé un report" class="form-control">

<div id="programmed_date_div<?= $row['or_id']; ?>" style="display: none;">
    <label for="programmed_date<?= $row['or_id']; ?>">Date programmé :</label>
    <input type="date" id="programmed_date<?= $row['or_id']; ?>" name="programmed_date" class="form-control">
</div>



<div id="<?= ($result); ?>"></div>
<button class="btn w-100 btn-primary mt-3">Mettre à jour</button>
</div>
<?php formAwdEnd(); ?>
</div>
</div>
</div>
<?php endif; ?>


<script>
$(document).ready(function() {
    let selectId = '#state_select<?= $row['or_id']; ?>';
    let reportDiv = '#report_date_div<?= $row['or_id']; ?>';
    let programmedDiv = '#programmed_date_div<?= $row['or_id']; ?>';

    $(selectId).on('change', function() {
        let selectedId = $(selectId).val(); 
        let selectedName = $(selectId + ' option:selected').data('name');

        if (selectedName === 'Reporté') {
            $(reportDiv).show();
        } else {
            $(reportDiv).hide();
        }

        if (selectedId == 54) { // الحالة programé
            $(programmedDiv).show();
        } else {
            $(programmedDiv).hide();
        }
    });

    // تشغيل الفحص عند الفتح
    $(selectId).trigger('change');
});

</script>

<?php endforeach; ?>





<?php 


echo renderPagination($total_data, $page, $limit);


} else {

print "<div class='text-center my-5'>";
print '<i class="fa-solid fa-file-half-dashed fa-3x my-2"></i>';
print "<h6>Aucun résultat trouvé</h6>";
print "</div>";



}

?>






