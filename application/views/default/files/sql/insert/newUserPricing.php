<?php
global $con;
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
// استخراج البيانات من النموذج
$userId = $_POST['userDelivery'];
$city = $_POST['city'];
$warehouse = $_POST['warehouse'];
$delivery = $_POST['delivery'];
$cancel = $_POST['cancel'];
$return = $_POST['return'];

// تحقق من وجود معرف id لتحديد ما إذا كانت العملية تعديل أو إضافة
if (isset($_POST['id'])) {
// تعديل التسعيرة الحالية
$id = $_POST['id']; // استخدام md5 كطريقة لتشفير id للحماية
$stmt = $con->prepare("
UPDATE user_pricing SET 
up_delivery = ?, up_cancel = ?, up_return = ? 
WHERE md5(up_id) = ? 
");
$stmt->execute([$delivery, $cancel, $return, $id]);

print "<div class='alert alert-success'>Mise à jour réussie</div>";


} else {


// إضافة تسعيرة جديدة
$stmt = $con->prepare("
INSERT INTO user_pricing (up_user, up_warehouse, up_city, up_delivery, up_cancel, up_return) 
VALUES (?, ?, ?, ?, ?, ?)
");
$stmt->execute([$userId, $warehouse, $city, $delivery, $cancel, $return]);


if (function_exists('load_url')) {
load_url("pricing?do=user&user=$userId", 2);
}



}




exit();
}
?>
