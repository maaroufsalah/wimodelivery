<?php
global $con;
include get_file("files/sql/get/session");


global $html;
// استقبال البيانات من AJAX
$page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
$search = isset($_POST['search']) ? $_POST['search'] : '';
$display = isset($_POST['display']) ? (int)$_POST['display'] : 10;
$froms = isset($_POST['froms']) ? (int)$_POST['froms'] : 0;
$to = isset($_POST['to']) ? (int)$_POST['to'] : 0;
$user = isset($_POST['user']) ? (int)$_POST['user'] : 0;

// التأكد من أن المتغيرات قابلة للقسمة (عدم القسمة على صفر)
$display = ($display > 0) ? $display : 10;  // إذا كانت قيمة العرض صفر أو غير صالحة، نقوم بإعطائها قيمة افتراضية
$page = ($page > 0) ? $page : 1;  // التأكد من أن الصفحة أكبر من صفر

// حساب قيمة الإزاحة (Offset) للصفحات
$offset = ($page - 1) * $display;

// بناء استعلام قاعدة البيانات
$query = "SELECT e.expedition_status,e.expedition_id, e.expedition_code, e.expedition_date, w_from.wh_name AS sender_name, w_to.wh_name AS receiver_name, u.user_name AS delivery_name
FROM expeditions e
JOIN warehouse w_from ON e.sender_warehouse_id = w_from.wh_id
JOIN warehouse w_to ON e.receiver_warehouse_id = w_to.wh_id
JOIN users u ON e.delivery_user_id = u.user_id
WHERE 1=1";

// إضافة الفلاتر
if (!empty($search)) {
$query .= " AND (e.expedition_code LIKE :search OR w_from.wh_name LIKE :search OR w_to.wh_name LIKE :search OR u.user_name LIKE :search)";
}
if ($froms > 0) {
$query .= " AND e.sender_warehouse_id = :froms";
}
if ($to > 0) {
$query .= " AND e.receiver_warehouse_id = :to";
}
if ($user > 0) {
$query .= " AND e.delivery_user_id = :user";
}

// إضافة ترتيب وتقسيم الصفحات
$query .= " ORDER BY e.expedition_date DESC LIMIT :offset, :display";

// تحضير الاستعلام
$stmt = $con->prepare($query);

// ربط المعاملات
if (!empty($search)) {
$stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
}
if ($froms > 0) {
$stmt->bindValue(':froms', $froms, PDO::PARAM_INT);
}
if ($to > 0) {
$stmt->bindValue(':to', $to, PDO::PARAM_INT);
}
if ($user > 0) {
$stmt->bindValue(':user', $user, PDO::PARAM_INT);
}
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':display', $display, PDO::PARAM_INT);

// تنفيذ الاستعلام
$stmt->execute();

// جلب النتائج
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// حساب العدد الكلي للصفوف (للتنقل بين الصفحات)
$totalQuery = "SELECT COUNT(*) FROM expeditions e WHERE 1=1";
if (!empty($search)) {
$totalQuery .= " AND (e.expedition_code LIKE :search OR w_from.wh_name LIKE :search OR w_to.wh_name LIKE :search OR u.user_name LIKE :search)";
}
$stmtTotal = $con->prepare($totalQuery);
if (!empty($search)) {
$stmtTotal->bindValue(':search', "%$search%", PDO::PARAM_STR);
}
if ($froms > 0) {
$stmtTotal->bindValue(':froms', $froms, PDO::PARAM_INT);
}
if ($to > 0) {
$stmtTotal->bindValue(':to', $to, PDO::PARAM_INT);
}
if ($user > 0) {
$stmtTotal->bindValue(':user', $user, PDO::PARAM_INT);
}
$stmtTotal->execute();
$totalRows = $stmtTotal->fetchColumn();

// إذا كانت النتائج صفر، نعيّن عدد الصفحات إلى صفر لتجنب القسمة على صفر
$totalPages = ($totalRows > 0) ? ceil($totalRows / $display) : 0;


if (count($results)>0){

// بناء المحتوى الديناميكي (HTML)
$html = '';
?>
<?php foreach ($results as $row): ?>

<?php

$stmt = $con->prepare("SELECT * FROM expedition_colis WHERE expedition_id = '{$row['expedition_id']}' ORDER BY colis_id DESC");
$stmt->execute();
$expRowCount = $stmt->rowCount();
$exp = $stmt->fetchAll();


$stmt = $con->prepare("SELECT * FROM expedition_colis WHERE expedition_id = '{$row['expedition_id']}' AND scan = '1' ORDER BY colis_id DESC");
$stmt->execute();
$exp_scaned = $stmt->rowCount();





?>


<div class='card mt-2 shadow-sm' style='border-left: 3px solid #007bff;'>
<div class='card-body py-2'>
<div class='row align-items-center text-center'>

<div class='col-sm-1'>
<a  class='text-dark' title='Afficher les colis'>
<i class='fa-solid fa-2x fa-box-open'></i>
</a>
</div>

<div class='col-sm-10 text-start'>
<strong>Expédition #<?= $row['expedition_code']; ?></strong><br>
Livreur : <?= $row['delivery_name']; ?> |
Date : <?= $row['expedition_date']; ?><br>
De : <?= $row['sender_name']; ?> → À : <?= $row['receiver_name']; ?><br>
Total colis : <?= $expRowCount; ?> / <?=$exp_scaned;?> déjá scanné
</div>


<div class='col-sm-1'>
<a onclick='openCard<?= $row['expedition_id']; ?>();' class='text-dark' title='Afficher les colis'>
<i class='fa-solid fa-2x fa-bars'></i>
</a>
</div>





<div class='col-sm-12'>

<?php
$expCompleted = (count($exp) == $exp_scaned);
$isPending = $row['expedition_status'];
$expeditionIdHashed = md5($row['expedition_id']);

// Pour le rôle "delivery"
if ($loginRank == "delivery") {
if ($expCompleted) {
if (empty($isPending)) {
echo "
<div class='text-danger'>
En cours d'expédition
<a href='dataUpdate?do=shipping&id=$expeditionIdHashed' class='btn btn-info'>
valider
</a>
</div>";
} else {
echo "<div class='text-success'>Expédié</div>";
}
} else {
echo "
<div class='text-danger'>
<a href='?do=scan&id=$expeditionIdHashed' class='btn btn-dark my-2'>
Scanner de validation
</a>
</div>";
}
}

// Pour le rôle "admin"
if ($loginRank == "admin") {
if ($isPending = 0) {
echo "
<div class='text-danger'>
En cours d'expédition
<a href='dataUpdate?do=shipping&id=$expeditionIdHashed' class='btn btn-info'>
valider
</a>
</div>";
} else {
echo "<div class='text-success'>Expédié</div>";
}


if (!$expCompleted) {

echo "
<div class='text-danger'>
<a href='?do=scan&id=$expeditionIdHashed' class='btn btn-dark my-2'>
Scanner de validation
</a>
</div>";
}
}



if ($loginRank == "admin") {

echo "
<a data-bs-toggle='modal' data-bs-target='#modalDelete" . $row['expedition_id'] . "' class='btn btn-danger my-2 btn-sm' style=''>
<i class='fa-solid fa-trash'></i> Supprimer
</a>
";

}

?>
</div>



</div>
</div>
</div>







<!-- كارت تفاصيل الكوليس -->
<div id='card<?= $row['expedition_id']; ?>' class='card mb-3' style='display:none;background: #f8fbff;'>
<div class='card-body' id='colis-content-<?= $row['expedition_id']; ?>'>
<?php foreach ($exp as $data): ?>
<?php
// display exp packages
$stmt = $con->prepare("SELECT * FROM orders WHERE or_id = '{$data['colis_id']}' LIMIT 1");
$stmt->execute();
$order = $stmt->fetch();



// جلب بيانات المستخدم والمدينة والحالة
$trade = [];

if (!empty($order['or_trade'])) {
$tradeStmt = $con->prepare("SELECT * FROM users WHERE user_id = ? AND user_rank = 'user' LIMIT 1");
$tradeStmt->execute([$order['or_trade']]);
$trade = $tradeStmt->fetch(PDO::FETCH_ASSOC) ?: [];
}

$city = [];
if (!empty($order['or_city'])) {
$cityStmt = $con->prepare("SELECT * FROM city WHERE city_id = ? LIMIT 1");
$cityStmt->execute([$order['or_city']]);
$city = $cityStmt->fetch(PDO::FETCH_ASSOC) ?: [];
}


$state = [];
if (!empty($order['or_state_delivery'])) {
$stateStmt = $con->prepare("SELECT * FROM state WHERE state_id = ? LIMIT 1");
$stateStmt->execute([$order['or_state_delivery']]);
$state = $stateStmt->fetch() ?: [];
}


if (!empty($order['or_delivery_user'])) {
$dmuStmt = $con->prepare("SELECT * FROM users WHERE user_id = '{$order['or_delivery_user']}' AND user_rank = 'delivery' LIMIT 1");
$dmuStmt->execute();
$deliveryUser = $dmuStmt->fetch();
} else {
$deliveryUser = null; // في حال كانت القيمة فارغة
}
?>
<!-- كارت الطلب المختصر -->
<div class='card mt-2' style='border-left: 2px solid <?= $state['state_background'] ?? "#ccc" ?>;'>
<div class='card-body my-1'>
<div class='row align-items-center text-center my-2'>
<div class='col-sm-2'>

<?php if (($order['or_invoice'] ?? '0') == "0" || ($order['or_delivery_invoice'] ?? '0') == "0"): ?>
<label for = 'cb_<?= $order['or_id']; ?>'><?= $order['or_id']; ?></label>
<input type="checkbox" class="bulk-check order-checkbox" id= 'cb_<?= $order['or_id']; ?>' value="<?= $order['or_id']; ?>">
<?php else: ?>
<h6>ID : <b><?= $order['or_id']; ?></b></h6>
<?php endif; ?>


<?php if (!empty($trade['user_name'])): ?>
<h6>Vendeur : <b><?= $trade['user_name']; ?></b></h6>
<?php endif; ?>


<?php if (!empty($deliveryUser) && isset($deliveryUser['user_name'])): ?>
<h6>Livreur : <b><?= $deliveryUser['user_name']; ?></b></h6>
<?php endif; ?>


</div>
<div class='col-sm-3'>
<h6>Destinataire : <b><?= $order['or_name']; ?><br>(<?= $order['or_phone']; ?>)</b></h6>
</div>
<div class='col-sm-2'>
<h6>Prix Colis : <b><?= $order['or_total']; ?> Dhs</b></h6>
</div>
<div class='col-sm-2'>
<h6>Ville : <b><?= $city['city_name'] ?? '—'; ?></b></h6>
</div>



<div class='col-sm-2'>
<?php if ($order['or_state_delivery'] == 0): ?>
<a class='btn btn-sm' style='background:red;color:black'><b>En Attente</b></a>
<?php else: ?>
<a  class='btn btn-sm' style='background:<?= $state['state_background']; ?>;color:<?= $state['state_color']; ?>'><b><?= $state['state_name']; ?></b></a>
<?php endif; ?>
<?php
if (!empty($order['or_postponed'])){
print "<div class='text-info'>Reporter : <b>{$order['or_postponed']}</b></div>";
}
?>
</div>




<div class='col-sm-1'>
<a target='_blank' href='packages?do=edit&order_id=<?php print md5($order['or_id']) ;?>' class='text-dark'>
<i class="fa-solid fa-arrow-up-right-from-square"></i>
</a>
</div>
</div>
</div>
</div>





















<?php endforeach; ?>
</div>
</div>








<script>
function openCard<?= $row['expedition_id']; ?>(){
var card = document.getElementById('card<?= $row['expedition_id']; ?>');
var content = document.getElementById('colis-content-<?= $row['expedition_id']; ?>');

if (card.style.display === 'none') {
card.style.display = 'block';



} else {
card.style.display = 'none';
}
}
</script>











<?php


if ($loginRank == "admin") {
echo "<div class='modal fade' id='modalDelete" . $row['expedition_id'] . "' tabindex='-1' aria-hidden='true'>";
echo "<div class='modal-dialog modal-dialog-centered'>";
echo "<div class='modal-content'>";
echo "<div class='modal-header'>";
echo "<h5 class='modal-title'>Supprimer un élément</h5>";
echo "<button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>";
echo "</div>";
echo "<div class='modal-body'>";
echo "<div class='col-sm-12 text-center my-2'>";
echo "<h6>Êtes-vous sûr de bien vouloir supprimer cet élément ?</h6>";												
echo "<a class='btn btn-success' href='dataUnlink?do=shipping&dataUnlinkId=" . md5($row['expedition_id']) . "'>Oui, je veux</a>";
echo "</div>";
echo "</div>";
echo "</div>";
echo "</div>";
echo "</div>";
}

?>















<?php endforeach; ?>


<?php
}else{


print "<div class='text-center my-5'>";
print '<i class="fa-solid fa-file-half-dashed fa-3x my-2"></i>';
print "<h6>Aucun résultat trouvé</h6>";
print "</div>";


}
// روابط الصفحات (للتنقل بين الصفحات)
$html .= "<hr>";
$html .= "<div>Total : <b>$totalRows</b></div>";
$html .= "<hr>";
$html .= "<div class='pagination-wrapper text-center'>
<ul class='pagination mt-3' style='display: inline-flex;'>";

// الروابط السابقة
if ($page > 1) {
$html .= "<li class='page-item'><a class='page-link' href='#' data-page='" . ($page - 1) . "'>«</a></li>";
}

// الروابط الخاصة بكل صفحة
for ($i = 1; $i <= $totalPages; $i++) {
$active = ($i == $page) ? " active" : "";
$html .= "<li class='page-item$active'><a class='page-link' href='#' data-page='$i'>$i</a></li>";
}

// الروابط التالية
if ($page < $totalPages) {
$html .= "<li class='page-item'><a class='page-link' href='#' data-page='" . ($page + 1) . "'>»</a></li>";
}

$html .= "</ul>
</div>";

// إخراج المحتوى
echo $html;
?>
