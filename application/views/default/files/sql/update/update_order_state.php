<?php
global $con;

include get_file("files/sql/get/os_settings");
include get_file("files/sql/get/session");
include get_file("files/sql/get/functions");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
$order_id = intval($_POST['order_id_s'] ?? 0);
$state_id = intval($_POST['state_id'] ?? 0);
$new_date = trim($_POST['postponed_date'] ?? '');
$note     = trim($_POST['note'] ?? '');
$login_user_id = $loginId;

if ($state_id == 1) {
$delivered_date = date("Y-m-d H:i:s");
} else {
$delivered_date = null;
}


if ($state_id == 54){
$programmed_date = trim($_POST['programmed_date'] ?? '');

if(empty($programmed_date)){
echo '<div class="error">Veuillez fournir une date - programmé.</div>';
exit();
}
}



if ($state_id == 5){
if(empty($new_date)){
echo '<div class="error">Veuillez fournir une date - reporté.</div>';
exit();
}
}




if (($state_id == 3) || ($state_id == 2)){
if(empty($note)){
print "<div class='alert alert-danger'>سبب رفض الطلبية</div>";
exit();
}
}


if ($order_id > 0) {
try {
$con->beginTransaction();

// تحديث حالة الطلب
$updateOrder = $con->prepare("UPDATE orders SET or_state_delivery = :state_id, or_delivered = :or_delivered WHERE or_id = :order_id");
$updateOrder->execute([
':state_id' => $state_id,
':or_delivered' => $delivered_date,
':order_id' => $order_id
]);

// تأجيل التاريخ لو الحالة Reporté
if ($state_id == 5 && !empty($new_date)) {
$updateOrderDate = $con->prepare("UPDATE orders SET or_postponed = :postponed_date WHERE or_id = :order_id");
$updateOrderDate->execute([
':postponed_date' => $new_date,
':order_id' => $order_id
]);
}




if ($state_id == 54 && !empty($programmed_date)) {
$updateOrderDate = $con->prepare("UPDATE orders SET or_programmed_date = :or_programmed_date WHERE or_id = :order_id");
$updateOrderDate->execute([
':or_programmed_date' => $programmed_date,
':order_id' => $order_id
]);
}




// تسجيل النشاط
$insertLog = $con->prepare("INSERT INTO state_activity (sa_date, sa_state, sa_order, sa_user, sa_note) VALUES (NOW(), :state_id, :order_id, :user_id, :note)");
$insertLog->execute([
':state_id' => $state_id,
':order_id' => $order_id,
':user_id' => $login_user_id,
':note' => $note
]);

$con->commit();
echo '<div class="alert alert-success my-2">Commande mise à jour avec succès.</div>';

print "
<script>
setTimeout(function() {
    var btn = document.querySelector('.update_data');
    if (btn) {
        btn.click();
    }
}, 300);
</script>
";




// ✅ تحديد $do ديناميكيًا
$trade_stmt = $con->prepare("SELECT or_trade, or_code FROM orders WHERE or_id = ?");
$trade_stmt->execute([$order_id]);
$trade_row = $trade_stmt->fetch();

if (!$trade_row) {
exit('<div class="error">Commande introuvable.</div>');
}

$or_trade = intval($trade_row['or_trade'] ?? 0);
$order_code = trim($trade_row['or_code'] ?? '');

// جلب المستخدم
$user_stmt = $con->prepare("SELECT user_id FROM users WHERE user_id = ? LIMIT 1");
$user_stmt->execute([$or_trade]);
$user_row = $user_stmt->fetch();

if (!$user_row) {
exit('<div class="error">Utilisateur introuvable.</div>');
}

$user_id = intval($user_row['user_id'] ?? 0);

// جلب API المناسبة
$api_stmt = $con->prepare("SELECT api_id FROM api WHERE api_user = ? LIMIT 1");
$api_stmt->execute([$user_id]);
$api_row = $api_stmt->fetch();

$do = isset($_GET['do']) ? intval($_GET['do']) : intval($api_row['api_id'] ?? 0);

echo "<div class='info'>API ID détectée: $do</div>";

// ✅ تنفيذ CURL حسب $do
if ($do == 1) {

}

if ($do == 2) {

}



} catch (Exception $e) {
$con->rollBack();
echo '<div class="error">Erreur: ' . $e->getMessage() . '</div>';
}
} else {
echo '<div class="error">Veuillez fournir un ID de commande et une état valide.</div>';
}
}

$con = null;
?>
