<?php 

include get_file("files/sql/get/os_settings");
include get_file("files/sql/get/session");

global $con;

$do = isset ($_GET['do']) ? $_GET ['do'] : 'Manage' ;
if ($do == 'pickup'){

if (SRM("POST")) {

$userId = POST("user", 0, 'int');
$orders = POST("order_id"); // can be array or comma separated string
$type   = "pickup";
$via    = $loginId;
$rank   = "user";
$date   = date('Y-m-d H:i:s');

// ✅ Insert log
$col_array = "lp_gid,lp_via,lp_type,lp_date,lp_user,lp_rank";
$col_value = "?, ?, ?, ?, ?, ?";

$stmt = $con->prepare("INSERT INTO log_print ($col_array) VALUES ($col_value)");
$stmt->execute([$orders, $via, $type, $date, $userId, $rank]);

// ✅ Update orders
if (!empty($orders)) {
// if orders is array
if (is_array($orders)) {
$placeholders = implode(',', array_fill(0, count($orders), '?'));
$stmt = $con->prepare("UPDATE orders SET or_pickup = 1 WHERE or_id IN ($placeholders)");
$stmt->execute($orders);
} else {
// if it's comma separated string
$ids = array_filter(array_map('intval', explode(',', $orders)));
if ($ids) {
$placeholders = implode(',', array_fill(0, count($ids), '?'));
$stmt = $con->prepare("UPDATE orders SET or_pickup = 1 WHERE or_id IN ($placeholders)");
$stmt->execute($ids);
}
}
}

$con = null;

if (function_exists('load_url')) {
load_url("pickup", 1); 
}
}



}elseif ($do == 'outlog_user'){















if (SRM("POST")) {

$userId = POST("user", 0, 'int');
$orders = POST("order_id");
$type   = "outlog_user";
$via    = $loginId;
$rank   = "user";
$date   = date('Y-m-d H:i:s');

$col_array = "lp_gid,lp_via,lp_type,lp_date,lp_user,lp_rank";
$col_value = "?, ?, ?, ?, ?, ?";

$stmt = $con->prepare("INSERT INTO log_print ($col_array) VALUES ($col_value)");
$stmt->execute([$orders, $via, $type, $date, $userId, $rank]);

$lastId = $con->lastInsertId();

if (!empty($orders)) {
$update = $con->prepare("UPDATE orders SET or_r_user = ? WHERE or_id IN ($orders)");
$update->execute(array_merge([$userId]));
}

$con = null;

if (function_exists('load_url')) {
load_url("outLogUser", 1); 
}
}











}elseif ($do == 'outlog_delivery'){










if (SRM("POST")) {

$userId = POST("user", 0, 'int');
$orders = POST("order_id");
$type = "outlog_delivery";
$via    = $loginId;
$rank   = "delivery";
$date   = date('Y-m-d H:i:s');

$col_array = "lp_gid,lp_via,lp_type,lp_date,lp_user,lp_rank";
$col_value = "?, ?, ?, ?, ?, ?";

$stmt = $con->prepare("INSERT INTO log_print ($col_array) VALUES ($col_value)");
$stmt->execute([$orders, $via, $type, $date, $userId, $rank]);

$lastId = $con->lastInsertId();

if (!empty($orders)) {
$update = $con->prepare("UPDATE orders SET or_r_delivery = ? WHERE or_id IN ($orders)");
$update->execute(array_merge([$userId]));
}

$con = null;

if (function_exists('load_url')) {
load_url("outLogDelivery", 1); 
}
}















}else{

}


?>