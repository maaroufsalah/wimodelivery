<?php 

$bootstrap = new Bootstrap();
$bootstrap->runBootstrap();



global $con;

$error ="";
include get_file("files/sql/get/os_settings");
include get_file("files/sql/get/session");
include get_file("files/sql/get/functions");

$do = isset ($_GET['do']) ? $_GET ['do'] : 'Manage' ;

function GET($name){
return $_GET[$name];
}




if ($do == 'Manage'){








}elseif ($do == 'invoice'){


$id = GET("id");




$stmt = $con->prepare("SELECT * FROM invoice WHERE md5(in_id) = ?");
$stmt->execute([$id]);
if ($stmt->rowCount() == 0) {
echo "<div class='alert alert-danger my-2'>Facture introuvable.</div>";
exit;
}


$stmt = $con->prepare("UPDATE invoice SET in_state = '1' WHERE md5(in_id) = ?");
$stmt->execute([$id]);


$stmt = $con->prepare("UPDATE invoice_script SET is_pay = '1' WHERE md5(is_invoice_id) = ?");
$stmt->execute([$id]);


echo "<div class='alert alert-success my-2'>Data succès.</div>";



if (function_exists('load_url')) {
load_url("invoice", 2);
}










}elseif ($do == 'delivery_invoice'){


$id = GET("id");





$stmt = $con->prepare("SELECT * FROM delivery_invoice WHERE md5(d_in_id) = ?");
$stmt->execute([$id]);
if ($stmt->rowCount() == 0) {
echo "<div class='alert alert-danger my-2'>Facture introuvable.</div>";
exit;
}


$stmt = $con->prepare("UPDATE delivery_invoice SET d_in_state = '1' WHERE md5(d_in_id) = ?");
$stmt->execute([$id]);


$stmt = $con->prepare("UPDATE delivery_invoice_script SET dis_pay = '1' WHERE md5(dis_invoice) = ?");
$stmt->execute([$id]);


echo "<div class='alert alert-success my-2'>Validé avec succès.</div>";



if (function_exists('load_url')) {
load_url("deliveryInvoice", 2);
}






}elseif ($do == 'shipping'){




$id = GET("id");





$stmt = $con->prepare("SELECT * FROM expeditions WHERE md5(expedition_id) = ?");
$stmt->execute([$id]);
if ($stmt->rowCount() == 0) {
echo "<div class='alert alert-danger my-2'>expeditions introuvable.</div>";
exit;
}


$stmt = $con->prepare("UPDATE expeditions SET expedition_status = '1' WHERE md5(expedition_id) = ?");
$stmt->execute([$id]);



$stmt = $con->prepare("SELECT * FROM expedition_colis WHERE md5(expedition_id) = '{$id}' ORDER BY colis_id DESC");
$stmt->execute();
$expRowCount = $stmt->rowCount();
$exp = $stmt->fetchAll();

foreach ($exp as $data){



$stmt = $con->prepare("UPDATE orders SET or_state_delivery = '51' WHERE or_id = ?");
$stmt->execute([$data['colis_id']]);



$insertLog = $con->prepare("INSERT INTO state_activity (sa_date, sa_state, sa_order, sa_user, sa_note) VALUES (NOW(), :state_id, :order_id, :user_id, :note)");
$insertLog->execute([
':state_id' => 51,
':order_id' => $data['colis_id'],
':user_id' => $loginId,
':note' => "Validation - Expédition"
]);




$stmt = $con->prepare("UPDATE orders SET or_exp_date = NOW() WHERE or_id = ?");
$stmt->execute([$data['colis_id']]);


}





echo "<div class='alert alert-success my-2'>validé avec succès.</div>";



if (function_exists('load_url')) {
load_url("shipping", 2);
}







}elseif ($do == 'restore'){



$id = GET("id");



$stmt = $con->prepare("UPDATE orders SET or_unlink = '0' WHERE md5(or_id) = ?");
$stmt->execute([$id]);


echo "<div class='alert alert-success my-2'>validé avec succès.</div>";



if (function_exists('load_url')) {
load_url("packages", 2);
}






}elseif ($do == 'delivery_unlink'){



$id = GET("id");



$stmt = $con->prepare("UPDATE orders SET or_delivery_user = '0' WHERE md5(or_id) = ?");
$stmt->execute([$id]);


echo "<div class='alert alert-success my-2'>validé avec succès.</div>";



if (function_exists('load_url')) {
load_url("packages", 2);
}









}elseif ($do == 'close_state'){



$id = GET("id");
$state = GET("state");




$stmt = $con->prepare("UPDATE orders SET or_close = '$state' WHERE md5(or_id) = ?");
$stmt->execute([$id]);




echo "<div class='alert alert-success my-2'>validé avec succès.</div>";



if (function_exists('load_url')) {
load_url("packages", 2);
}


}else{

print "
<div class='alert alert-danger'>Page non trouvée</div>
";

}
?>