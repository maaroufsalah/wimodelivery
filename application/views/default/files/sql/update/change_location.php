<?php 
global $con;

include get_file("files/sql/get/session");


if (SRM("POST")) {

$order_id = POST("order_id"); // معرّف الطلب (md5)
$user = POST("user", 0, 'int');
$name = POST("name");
$phone = POST("phone");
$location = POST("location");

if (!$order_id || !$user || empty($name) || empty($phone) || empty($location)) {
echo "<div class='alert alert-danger'>Veuillez remplir tous les champs obligatoires (*)</div>";
exit();
}




// display order


$stmt = $con->prepare("SELECT * FROM orders WHERE md5(or_id) = :or_id");
$stmt->bindParam(':or_id', $order_id, PDO::PARAM_STR);
$stmt->execute();
$or = $stmt->fetch(PDO::FETCH_ASSOC);


// ✅ تأكد أن أسماء الأعمدة صحيحة حسب هيكل جدولك
$stmt = $con->prepare("
UPDATE orders
SET
or_trade = :or_trade,
or_name_new = :or_name,
or_phone_new = :or_phone,
or_location_new = :or_location
WHERE md5(or_id) = :order_id
");

$stmt->bindParam(':or_trade', $user, PDO::PARAM_INT);
$stmt->bindParam(':or_name', $name, PDO::PARAM_STR);
$stmt->bindParam(':or_phone', $phone, PDO::PARAM_STR);
$stmt->bindParam(':or_location', $location, PDO::PARAM_STR);
$stmt->bindParam(':order_id', $order_id, PDO::PARAM_STR);

if ($stmt->execute()) {

// ✅ تحديث حالة الطلب
$updateOrder = $con->prepare("
UPDATE orders 
SET or_state_delivery = :state_id
WHERE md5(or_id) = :order_id
");
$stateId = 57;
$updateOrder->execute([
':state_id' => $stateId,
':order_id' => $order_id
]);


$insertLog = $con->prepare("
INSERT INTO state_activity (sa_date, sa_state, sa_order, sa_user, sa_note)
VALUES (NOW(), :state_id, :order_id, :user_id, :note)
");
$insertLog->execute([
':state_id' => $stateId,
':order_id' => $or['or_id'],
':user_id'  => $loginId,
':note'     => null // أو ملاحظة حسب حاجتك
]);

echo "<div class='alert alert-success'>Mise à jour réussie</div>";
load_url("packages", 2);
exit();
} else {
echo "<div class='alert alert-danger'>Erreur de mise à jour</div>";
}

$stmt = null;
$con = null;
}
?>
