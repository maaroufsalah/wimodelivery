<?php 


include get_file("files/sql/get/os_settings");
include get_file("files/sql/get/functions");

$rowsRaw = $_GET['orders_ids'] ?? '';
if (!empty($rowsRaw)) {

$rows = $rowsRaw;

$do = $_GET['do'] ?? 'Manage';



if ($do == 'a4'){
if (function_exists('load_url')) {
load_url("sticker_papper?do=a4&order_ids=".$rows, 1); // إعادة توجيه المستخدم
}
}elseif($do == '10'){
if (function_exists('load_url')) {
load_url("sticker_small?do=10&order_ids=".$rows, 1); // إعادة توجيه المستخدم
}
}


}




?>

