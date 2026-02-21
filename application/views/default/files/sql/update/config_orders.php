<?php 

$bootstrap = new Bootstrap();
$bootstrap->runBootstrap();

include get_file("files/sql/get/os_settings");
include get_file("files/sql/get/session");
include get_file("files/sql/get/functions");

if (SRM("POST")) {

$orderIdsRaw = $_POST['order_id']; // "1,2,3"
$do = isset($_GET['do']) ? $_GET['do'] : 'Manage';


if (empty($orderIdsRaw)){
print "<div class='alert alert-danger'>Choisir des colis</div>";
exit();
}


if ($do == 'Manage') {

} elseif ($do == 'state') {

$state_id  = intval($_POST['state_id'] ?? 0);
$new_date  = trim($_POST['postponed_date'] ?? '');
$note      = trim($_POST['note'] ?? '');
$user_id   = $loginId; // موظف قام بالتأجيل

if (!empty($orderIdsRaw)) {
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
echo '<div class="badge badge-danger">Veuillez fournir la note .</div>';
exit();
}
}

// تحويل المصفوفة إلى سلسلة مفصولة بفواصل
$orderIdsList = implode(',', $orderIdsArray);

$con->beginTransaction();

// تحديث حالة الطلبات
$updateOrder = $con->prepare("
UPDATE 
orders SET 
or_state_delivery = :state_id, 
or_delivered = :or_delivered
WHERE or_id IN ($orderIdsList)");
$updateOrder->execute([
':state_id' => $state_id,
':or_delivered' => $delivered_date
]);

// إذا كانت الحالة "Reporté"
if ($state_id == 5 && !empty($new_date)) {
$updateOrderDate = $con->prepare("UPDATE orders SET or_postponed = :postponed_date WHERE or_id IN ($orderIdsList)");
$updateOrderDate->execute([
':postponed_date' => $new_date
]);
}


if ($state_id == 54 && !empty($programmed_date)) {
$updateOrderDate = $con->prepare("UPDATE orders SET or_programmed_date = :or_programmed_date WHERE or_id IN ($orderIdsList)");
$updateOrderDate->execute([
':or_programmed_date' => $programmed_date
]);
}





// إدراج سجلات state_activity لكل طلب
$insertLog = $con->prepare("INSERT INTO state_activity (sa_date, sa_state, sa_order, sa_user, sa_note) 
VALUES (NOW(), :state_id, :order_id, :user_id, :note)");
foreach ($orderIdsArray as $order_id) {
$insertLog->execute([
':state_id' => $state_id,
':order_id' => $order_id,
':user_id'  => $user_id,
':note'     => $note
]);
}

$con->commit();
echo '<div class="success">Commande(s) mise(s) à jour avec succès.</div>';



print "<script>$('.update_data').click();</script>";










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

$updateOrderDelivery = $con->prepare("UPDATE orders SET or_delivery_user = :user_id WHERE or_id IN ($orderIdsRaw)");
$updateOrderDelivery->execute([
':user_id' => $delivery_id
]);


if (function_exists('load_url')) {
load_url("", 2);
}
$con = null;






}elseif($do == "print"){

$print_id = POST ("print_id");

if ($loginRank == "admin"){
$fee = POST ("print_fee");

$updatePrint = $con->prepare("UPDATE orders SET or_print = :fee WHERE or_id IN ($orderIdsRaw)");
$updatePrint->execute([
':fee' => $fee
]);
}

print "<script>window.open('print_sticker?do=$print_id&orders_ids=$orderIdsRaw', '_blank');</script>";


$con = null;



}elseif($do == "unlink"){


if ($loginRank == "admin"){
$updateUnlink = $con->prepare("UPDATE orders SET or_unlink = '1' WHERE or_id IN ($orderIdsRaw)");
$updateUnlink->execute();
}

if (function_exists('load_url')) {
load_url("", 2);
}


$con = null;





}elseif($do == "export"){


print "<script>window.open('get_export?ids=$orderIdsRaw', '_blank');</script>";




}elseif($do == "send_to_api"){

$api_id = POST ("api_id");


include get_file("Admin/api_list");






}else{


}






















}

?>

