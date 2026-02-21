<?php 

$bootstrap = new Bootstrap();
$bootstrap->runBootstrap();

include get_file("files/sql/get/os_settings");
include get_file("files/sql/get/session");
include get_file("files/sql/get/functions");

if (SRM("POST")) {

$orderIdsRaw = $_POST['order_id']; // "1,2,3"
$do = isset($_GET['do']) ? $_GET['do'] : 'Manage';

if ($do == 'Manage') {

} elseif ($do == 'state') {

$state_id  = intval($_POST['state_id'] ?? 0);
$new_date  = trim($_POST['postponed_date'] ?? '');
$note      = trim($_POST['note'] ?? '');
$user_id   = $loginId; // موظف قام بالتأجيل

if (!empty($orderIdsRaw) && $state_id > 0) {
try {
// تحويل السلسلة إلى مصفوفة أرقام صحيحة
$orderIdsArray = array_filter(array_map('intval', explode(',', $orderIdsRaw)));
if (empty($orderIdsArray)) {
throw new Exception("Liste de commandes invalide.");
}


// or_delivered

if ($state_id == 1){
$delivered_date = date("Y-m-d H:i:s");
}else{
$delivered_date = null;
}

// تحويل المصفوفة إلى سلسلة مفصولة بفواصل
$orderIdsList = implode(',', $orderIdsArray);

$con->beginTransaction();

// تحديث حالة الطلبات
$updateOrder = $con->prepare("
UPDATE 
pickup SET 
pi_state = :state_id
WHERE pi_id IN ($orderIdsList)");
$updateOrder->execute([
':state_id' => $state_id
]);





$con->commit();
echo '<div class="success">Commande(s) mise(s) à jour avec succès.</div>';

if (function_exists('load_url')) {
load_url("", 2);
}

} catch (Exception $e) {
$con->rollBack();
echo '<div class="error">Erreur: ' . $e->getMessage() . '</div>';
}
} else {
echo '<div class="error">Veuillez fournir un ID de commande et une état valide.</div>';
}

$con = null;
















}elseif($do == "delivery"){

$delivery_id = POST ("delivery_id");

$updateOrderDelivery = $con->prepare("UPDATE pickup SET pi_delivery_user = :user_id WHERE pi_id IN ($orderIdsRaw)");
$updateOrderDelivery->execute([
':user_id' => $delivery_id
]);


if (function_exists('load_url')) {
load_url("", 2);
}
$con = null;






}elseif($do == "print"){

$print_id = POST ("print_id");


print "<script>window.open('print_sticker?do=$print_id&pickup_ids=$orderIdsRaw', '_blank');</script>";


$con = null;







}else{


}






















}

?>

