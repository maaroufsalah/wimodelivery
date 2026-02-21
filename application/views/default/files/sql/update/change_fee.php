<?php 
global $con;

include get_file("files/sql/get/session");


if (SRM("POST")) {

$order_id = POST("order_id"); // معرّف الطلب (md5)


$fee = POST("fee");
$fpc = POST("fpc");
$print = POST("print");
$newfee = POST("newfee");

if (!$order_id || empty($fee)) {
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
or_fee = :or_fee,
or_fee_change = :or_change_fee,
or_print = :or_print,
or_fpc = :or_fpc
WHERE md5(or_id) = :order_id
");


$stmt->bindParam(':or_fee', $fee, PDO::PARAM_STR);
$stmt->bindParam(':or_change_fee', $newfee, PDO::PARAM_STR);
$stmt->bindParam(':or_print', $print, PDO::PARAM_STR);
$stmt->bindParam(':or_fpc', $fpc, PDO::PARAM_STR);
$stmt->bindParam(':order_id', $order_id, PDO::PARAM_STR);

if ($stmt->execute()) {



echo "<div class='alert alert-success'>Mise à jour réussie</div>";

} else {
echo "<div class='alert alert-danger'>Erreur de mise à jour</div>";
}

$stmt = null;
$con = null;
}
?>
