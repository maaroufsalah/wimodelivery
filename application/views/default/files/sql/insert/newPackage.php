<?php 
include get_file("files/sql/get/os_settings");
include get_file("files/sql/get/session");
include get_file("App/tee");

global $con;

if (SRM("POST")) {


$warehouse = POST("warehouse", 0, 'int');
$user = POST("user", 0, 'int');
$fragile = POST("fragile", 0, 'int');
$try = POST("try", 0, 'int');
$open = POST("open", 0, 'int');
$change = POST("change", 0, 'int');
$price = POST("price", 0.0, 'float');
$city = POST("city", "");
$name = POST("name");
$phone = POST("phone");
$item = POST("item");
$location = POST("location");
$note = POST("note");
$qty = POST("qty");
$change_code = POST("change_code");
$order_created = date('Y-m-d H:i:s');


$box = POST("box", 0, 'int'); // box_id

// التحقق من box_id
if ($box > 0) {
$stmt_box = $con->prepare("SELECT box_price FROM box WHERE box_id = :id");
$stmt_box->execute([':id' => $box]);
$box_data = $stmt_box->fetch(PDO::FETCH_ASSOC);

if ($box_data) {
$box_price = $box_data['box_price'];
} else {
echo "<div class='alert alert-danger'>Box introuvable</div>";
exit();
}
} else{
$box_price = 0;
}



if ($change_code > 0) {
$colis = $con->prepare("SELECT * FROM orders WHERE or_id = :id AND or_trade = '".$user."'");
$colis->execute([':id' => $change_code]);
$colis_data = $colis->fetch(PDO::FETCH_ASSOC);

if ($colis_data) {
} else {
echo "<div class='alert alert-danger'>Colis introuvable</div>";
exit();
}
} else{
}




$pickup = POST("pickup");


// التحقق من الحقول الإلزامية
if (!$warehouse || !$user || empty($city) || empty($name) || empty($phone) || empty($location)) {
echo "<div class='alert alert-danger'>Veuillez remplir tous les champs obligatoires (*)</div>";
exit();
}


if (!preg_match("/^[0-9]{10}$/", $phone)) {
echo "<div class='alert alert-danger'>Numéro de téléphone invalide.</div>";
exit;
}
// تحضير الاستعلام
$stmt = $con->prepare("
INSERT INTO orders
(
or_warehouse, or_trade, or_fragile, or_try, or_open_package, or_change, 
or_total, or_city, or_name, or_phone, or_address, or_note, or_item, or_qty, or_change_code,
or_box, or_box_price, or_pickup_date, or_unlink, or_created
) 
VALUES 
(
:or_warehouse, :or_trade, :or_fragile, :or_try, :or_open_package, :or_change, 
:or_total, :or_city, :or_name, :or_phone, :or_shipped, :or_note,:or_item,:or_qty,:or_change_code, 
:or_box, :or_box_price, :or_pickup_date, 0,:or_created
)
");



// ربط القيم
$stmt->bindParam(':or_warehouse', $warehouse, PDO::PARAM_INT);
$stmt->bindParam(':or_trade', $user, PDO::PARAM_INT);
$stmt->bindParam(':or_fragile', $fragile, PDO::PARAM_INT);
$stmt->bindParam(':or_try', $try, PDO::PARAM_INT);
$stmt->bindParam(':or_open_package', $open, PDO::PARAM_INT);
$stmt->bindParam(':or_change', $change, PDO::PARAM_INT);
$stmt->bindParam(':or_total', $price, PDO::PARAM_STR);
$stmt->bindParam(':or_city', $city, PDO::PARAM_STR);
$stmt->bindParam(':or_name', $name, PDO::PARAM_STR);
$stmt->bindParam(':or_phone', $phone, PDO::PARAM_STR);
$stmt->bindParam(':or_item', $item, PDO::PARAM_STR);
$stmt->bindParam(':or_qty', $qty, PDO::PARAM_STR);
$stmt->bindParam(':or_change_code', $change_code, PDO::PARAM_STR);
$stmt->bindParam(':or_shipped', $location, PDO::PARAM_STR);
$stmt->bindParam(':or_note', $note, PDO::PARAM_STR);
$stmt->bindParam(':or_box', $box, PDO::PARAM_INT);
$stmt->bindParam(':or_box_price', $box_price, PDO::PARAM_STR);

$stmt->bindParam(':or_pickup_date', $pickup, PDO::PARAM_STR);
$stmt->bindParam(':or_created', $order_created, PDO::PARAM_STR);

// تنفيذ الاستعلام
if ($stmt->execute()) {
$new_id = $con->lastInsertId();
$or_code = 'WMD-' . $new_id;
$stmt_code = $con->prepare("UPDATE orders SET or_code = ? WHERE or_id = ?");
$stmt_code->execute([$or_code, $new_id]);

echo "<div class='alert alert-success'>Terminé avec succès</div>";

$stmt = $con->prepare("SELECT * FROM users WHERE user_rank = 'admin' AND user_unlink = '0'");
$stmt->execute();
$tUser = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach($tUser as $tu){

$firebase_token = $tu['user_firebase_id'];
$response = sendFCMModern($firebase_token, "$set_name", "Colis Ajouté Par Vendeur N° : $user");
echo $response;


}


load_url("packages?state=int",2);		



exit();
} else {
echo "<div class='alert alert-danger'>Insert Error</div>";
}

// إغلاق الاتصال
$stmt = null;
$con = null;
}
?>





