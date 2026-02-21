<?php 

include get_file("files/sql/get/os_settings");
include get_file("files/sql/get/session");

global $con;

if (SRM("POST")) {

$user = isset($_POST['user']) ? intval($_POST['user']) : 0;
$from = isset($_POST['from']) ? intval($_POST['from']) : 0;
$to = isset($_POST['to']) ? intval($_POST['to']) : 0;
$note = isset($_POST['note']) ? trim($_POST['note']) : '';
$orderIds = isset($_POST['order_id']) ? $_POST['order_id'] : '';

// تحويل orderIds إلى Array إذا كان نص مفصول بفواصل
if (!is_array($orderIds)) {
$orderIds = explode(',', $orderIds);
}

// التحقق من الحقول الأساسية
if ($user == 0 || $from == 0 || $to == 0 || empty($orderIds)) {
echo "<div class='alert alert-danger my-2'>Veuillez remplir tous les champs obligatoires.</div>";
exit;
}

try {
$con->beginTransaction();

// إنشاء الإرسالية
$stmt = $con->prepare("INSERT INTO expeditions (expedition_code, expedition_date, sender_warehouse_id, receiver_warehouse_id, delivery_user_id, expedition_note) 
VALUES (?, NOW(), ?, ?, ?, ?)");

$expedition_code = 'EXP-' . time(); // يمكنك تحسينه لاحقًا
$stmt->execute([$expedition_code, $from, $to, $user, $note]);
$expedition_id = $con->lastInsertId();

// ربط الطرود المختارة
$stmt2 = $con->prepare("INSERT INTO expedition_colis (expedition_id, colis_id) VALUES (?, ?)");
foreach ($orderIds as $colis_id) {
$stmt2->execute([$expedition_id, intval($colis_id)]);
}

// تحديث كل طلب بإضافة رقم الإرسالية داخله
$stmt3 = $con->prepare("UPDATE orders SET or_shipping = ? WHERE or_id = ?");
foreach ($orderIds as $colis_id) {
$stmt3->execute([$expedition_id, intval($colis_id)]);
}



$stmt4 = $con->prepare("UPDATE orders SET or_delivery_user = ? WHERE or_id = ?");
foreach ($orderIds as $colis_id) {
$stmt4->execute([$user, intval($colis_id)]);
}


$stmt4 = $con->prepare("UPDATE orders SET or_state_delivery = ? WHERE or_id = ?");
foreach ($orderIds as $colis_id) {
$stmt4->execute([6, intval($colis_id)]);

$insertLog = $con->prepare("INSERT INTO state_activity (sa_date, sa_state, sa_order, sa_user, sa_note) VALUES (NOW(), :state_id, :order_id, :user_id, :note)");
$insertLog->execute([
':state_id' => 6,
':order_id' => $colis_id,
':user_id' => $loginId,
':note' => "Via Expédition"
]);

}

$con->commit();

echo "<div class='alert alert-success'>Expédition créée avec succès !</div>";

load_url("shipping",2);			



} catch (Exception $e) {
$con->rollBack();
echo "<div class='alert alert-danger'>Erreur lors de la création de l'expédition: " . htmlspecialchars($e->getMessage()) . "</div>";
}

// إغلاق الاتصال
$stmt = null;
$stmt2 = null;
$stmt3 = null;
$con = null;

}
?>
