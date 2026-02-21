<?php 
global $con;

if (SRM("POST")) {


$user = POST("user");
$type = POST("type");
$phone = POST("phone");
$note = POST("note");
$location = POST("location");
$date = date("Y-m-d H:i:s");

if(empty($user)){
print "<div class='alert alert-danger'>User Id</div>";
exit ();
}


if(empty($type)){
print "<div class='alert alert-danger'>Veuillez choisir type de ramassage</div>";
exit ();
}


if(empty($phone)){
print "<div class='alert alert-danger'>Veuillez saisir téléphone</div>";
exit ();
}


if(empty($note)){
print "<div class='alert alert-danger'>Veuillez saisir remarque</div>";
exit ();
}


if(empty($location)){
print "<div class='alert alert-danger'>Veuillez saisir adresse</div>";
exit ();
}



// تحضير إدخال الشكوى
$stmt = $con->prepare("
INSERT INTO pickup (
pi_user,pi_phone,pi_note,pi_location,pi_date,pi_type
) VALUES (
?, ?, ?, ?, ?, ?
)
");

$now = date("Y-m-d H:i:s");
$orderId = $orders['or_id'];
$order_user = $orders['or_trade'];

if ($stmt->execute([$user,$phone,$note,$location,$date,$type])) {
echo "<div class='alert alert-success'>Ajouté avec succès</div>";
if (function_exists('load_url')) {
load_url("pickup_client", 2);
}
} else {
echo "<div class='alert alert-danger'>Erreur lors de l'insertion</div>";
}

// إغلاق
$stmt = null;
$con  = null;
}
?>
