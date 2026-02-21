<?php 
$bootstrap = new Bootstrap();
$bootstrap->runBootstrap();

include get_file("files/sql/get/os_settings");
include get_file("files/sql/get/session");
include get_file("files/sql/get/functions");

global $con;

$display = isset($_POST["display"]) ? $_POST["display"] : 10;
$limit = in_array($display, [10, 50, 100, 200]) ? $display : 10;

$page = isset($_POST['page']) && $_POST['page'] > 1 ? $_POST['page'] : 1;
$start = ($page - 1) * $limit;

$table  = "expeditions";
$search = isset($_POST['search']) ? $_POST['search'] : '';

$froms = isset($_POST['froms']) ? (int)$_POST['froms'] : 0;
$to = isset($_POST['to']) ? (int)$_POST['to'] : 0;
$user = isset($_POST['user']) ? (int)$_POST['user'] : 0;

$xoo = $loginRank == 'admin' ? " 1=1 " : " delivery_user_id = '$loginId' ";

$query = "SELECT * FROM $table WHERE $xoo ";

if ($search != '') {
$srs = str_replace(' ', '%', $search);
$query .= "AND (expedition_id LIKE '%$srs%' OR expedition_code LIKE '%$srs%') ";
}

if ($froms > 0) {
$query .= " AND sender_warehouse_id = '$froms'";
}
if ($to > 0) {
$query .= " AND receiver_warehouse_id = '$to'";
}
if ($user > 0) {
$query .= " AND delivery_user_id = '$user'";
}

$query .= " ORDER BY expedition_id DESC LIMIT $start, $limit";

$countQuery = "SELECT COUNT(*) FROM $table WHERE $xoo ";
if ($search != '') {
$countQuery .= "AND (expedition_id LIKE '%$srs%' OR expedition_code LIKE '%$srs%')";
}
$countStatement = $con->prepare($countQuery);
$countStatement->execute();
$total_data = $countStatement->fetchColumn();

$statement = $con->prepare($query);
$statement->execute();
$result = $statement->fetchAll();

$modals = []; // لتجميع المودالات

if (count($result) > 0) {

echo '<div class="table-responsive">
<table class="table table-bordered table-striped align-middle">
<thead class="table-dark">
<tr>
<th>ID</th>
<th>Expédition Code</th>
<th>Livreur</th>
<th>Date</th>
<th>De → À</th>
<th>Total Colis</th>
<th>État</th>
<th>Validation</th>
<th>Action</th>
</tr>
</thead>
<tbody>';

foreach ($result as $row) {
// جلب الكوليس الخاص بالشحنة
$stmt = $con->prepare("SELECT * FROM expedition_colis WHERE expedition_id = ? ORDER BY colis_id DESC");
$stmt->execute([$row['expedition_id']]);
$exp = $stmt->fetchAll();
$expRowCount = count($exp);


$thisExpeditionColisIds = [];

foreach ($exp as $data) {



$stmt = $con->prepare("SELECT * FROM orders WHERE or_id = ? LIMIT 1");
$stmt->execute([$data['colis_id']]);
$order = $stmt->fetch();

if ($order) {
$thisExpeditionColisIds[] = $order['or_id'];

}

}

// جلب التفاصيل
$stmt = $con->prepare("SELECT * FROM expedition_colis WHERE expedition_id = ? ORDER BY colis_id DESC");
$stmt->execute([$row['expedition_id']]);
$exp = $stmt->fetchAll();
$expRowCount = count($exp);

$stmt = $con->prepare("SELECT COUNT(*) FROM expedition_colis WHERE expedition_id = ? AND scan = '1'");
$stmt->execute([$row['expedition_id']]);
$exp_scaned = $stmt->fetchColumn();

$stmt = $con->prepare("SELECT * FROM warehouse WHERE wh_id = ? LIMIT 1");
$stmt->execute([$row['sender_warehouse_id']]);
$from = $stmt->fetch();

$stmt = $con->prepare("SELECT * FROM warehouse WHERE wh_id = ? LIMIT 1");
$stmt->execute([$row['receiver_warehouse_id']]);
$to = $stmt->fetch();

$stmt = $con->prepare("SELECT * FROM users WHERE user_id = ? LIMIT 1");
$stmt->execute([$row['delivery_user_id']]);
$user = $stmt->fetch();

$expCompleted = ($expRowCount == $exp_scaned);
$isPending = $row['expedition_status'];
$expeditionIdHashed = md5($row['expedition_id']);

echo '<tr>
<td>' . $row['expedition_id'] . '</td>
<td>' . htmlspecialchars($row['expedition_code']) . '</td>
<td>' . htmlspecialchars($user['user_name'] ?? '') . '</td>
<td>' . htmlspecialchars($row['expedition_date']) . '</td>
<td>' . htmlspecialchars($from['wh_name'] ?? '') . ' → ' . htmlspecialchars($to['wh_name'] ?? '') . '</td>
<td>' . $expRowCount . ' / ' . $exp_scaned . '</td>
<td>' . ($isPending ? '<span class="badge bg-success">Expédié</span>' : '<span class="badge bg-warning">En cours</span>') . '</td>
<td>';

if ($loginRank == "delivery") {
if ($expCompleted) {
if (empty($isPending)) {
echo "<a href='dataUpdate?do=shipping&id=$expeditionIdHashed' class='btn btn-info btn-sm'>Valider</a>";
} else {
echo "<span class='text-success'>Validé</span>";
}
} else {
echo "<a href='?do=scan&id=$expeditionIdHashed' class='btn btn-dark btn-sm'>Scanner</a>";
}
}

if ($loginRank == "admin") {
if ($isPending == 0) {
echo "<a href='dataUpdate?do=shipping&id=$expeditionIdHashed' class='btn btn-info btn-sm'>Valider</a>";
} else {
echo "<span class='text-success'>Validé</span>";
}

if (!$expCompleted) {
echo "<a href='?do=scan&id=$expeditionIdHashed' class='btn btn-dark btn-sm'>Scanner</a>";
}
}

echo '</td>
<td>
<button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalColis' . $row['expedition_id'] . '">Détails</button>';

if ($loginRank == "admin") {
echo '
<a data-bs-toggle="modal" data-bs-target="#modalDelete' . $row['expedition_id'] . '" class="btn btn-danger btn-sm">
<i class="fa-solid fa-trash"></i>
</a>';
}



if (count($thisExpeditionColisIds) > 0) {
$idsString = implode(',', $thisExpeditionColisIds);
echo '<a href="get_export?ids=' . $idsString . '" class="btn btn-success btn-sm mx-2 my-4" target="_blank">
<i class="fa fa-file-excel"></i>
</a>';
}

if (count($thisExpeditionColisIds) > 0) {
$idsString = implode(',', $thisExpeditionColisIds);
echo '<a href="exp_log?ids=' . $idsString . '&exp='.$row['expedition_id'].'" class="btn-sm mx-2  btn btn-info my-4" target="_blank">
<i class="fa fa-file-pdf"></i>
</a>';
}

echo '</td></tr>';

// بناء المودال
$modal = '
<div class="modal fade" id="modalColis' . $row['expedition_id'] . '" tabindex="-1">
<div class="modal-dialog modal-lg modal-dialog-centered">
<div class="modal-content">
<div class="modal-header">
<h5 class="modal-title">Colis Expédition #' . $row['expedition_code'] . '</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body">';
// مصفوفة خاصة بـ IDs الطرود لهذه الشحنة فقط

foreach ($exp as $data) {




$stmt = $con->prepare("SELECT * FROM orders WHERE or_id = ? LIMIT 1");
$stmt->execute([$data['colis_id']]);
$order = $stmt->fetch();

$modal .= '<div class="border p-2 mb-2">
ID Colis: <b>' . $order['or_id'] . '</b> | Destinataire: <b>' . htmlspecialchars($order['or_name']) . '</b> | Téléphone: <b>' . htmlspecialchars($order['or_phone']) . '</b>
</div>';
}

$modal .= '</div>
</div>
</div>
</div>';

$modals[] = $modal;

// مودال الحذف إن وجد
if ($loginRank == "admin") {
$modals[] = '
<div class="modal fade" id="modalDelete' . $row['expedition_id'] . '" tabindex="-1">
<div class="modal-dialog modal-dialog-centered">
<div class="modal-content">
<div class="modal-header"><h5 class="modal-title">Confirmer suppression</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<div class="modal-body text-center">
<p>Êtes-vous sûr ?</p>
<a href="dataUnlink?do=shipping&dataUnlinkId=' . md5($row['expedition_id']) . '" class="btn btn-danger">Oui</a>
</div>
</div></div></div>';
}
}

echo '</tbody></table></div>';

// اطبع جميع المودالات
foreach ($modals as $modal) {
echo $modal;
}

echo renderPagination($total_data, $page, $limit);

} else {
echo '<div class="alert alert-warning">Aucun résultat trouvé</div>';
}
?>
