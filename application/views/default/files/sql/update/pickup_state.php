<?php 

global $con;


$id = $_GET['order_id'] ?? "";
$state = $_GET['state'] ?? "";


if (empty($id)&&empty($state)){

echo "<div class='alert alert-success my-2'>Data Invalide.</div>";


}else{



$stmt = $con->prepare("UPDATE orders SET or_state_delivery = ? WHERE md5(or_id) = ?");
$stmt->execute([$state,$id]);


echo "<div class='alert alert-success my-2'>validé avec succès.</div>";



if (function_exists('load_url')) {
load_url("pickup", 2);
}




}







?>