<?php 

include get_file("files/sql/get/os_settings");
include get_file("files/sql/get/session");

global $con;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['code'])) {
$code  = trim($_POST['code']);
$state = trim($_POST['state']);
$date  = date("Y-m-d H:i:s");

if ($code === "") {
echo "❌ Code vide.";
exit;
}

if ($state === "") {
echo "❌ Statut vide.";
exit;
}

// Recherche de la commande
$stmt = $con->prepare("SELECT * FROM orders WHERE or_id = ? AND or_unlink = '0'");
$stmt->execute([$code]);
$data = $stmt->fetch();

if ($data) {
// Construction dynamique de la requête UPDATE
if ($state == "1") {
$sql = "UPDATE orders SET or_state_delivery = ?, or_delivered = 1, or_d_date = ? WHERE or_id = ?";
$params = [$state, $date, $code];
} elseif ($state == "4") {
$sql = "UPDATE orders SET or_state_delivery = ?, or_returned = 1, or_r_date = ? WHERE or_id = ?";
$params = [$state, $date, $code];
} else {
$sql = "UPDATE orders SET or_state_delivery = ? WHERE or_id = ?";
$params = [$state, $code];
}





// Exécution du UPDATE
$update = $con->prepare($sql);
$update->execute($params);



if ($state == "66") {

$stmt = $con->prepare("SELECT * FROM orders WHERE or_unlink = '0' AND or_id = '$code' LIMIT 1");
$stmt->execute();
$order = $stmt->fetch();

$orderIdsRaw  = $order['or_id'].",0";

include get_file("files/sql/update/r_stock");
}


// Insertion dans state_activity
$insert = $con->prepare("INSERT INTO state_activity (sa_date, sa_state, sa_order, sa_user, sa_note, sa_rank) VALUES (?, ?, ?, ?, ?, ?)");
$insert->execute([$date, $state, $code, $loginId, 'Par Scan', 'delivery']);

// Son + message succès
echo "<audio id='audio2' preload='auto' src='uploads/scan.mp3' oncanplaythrough='this.play();'></audio>";
echo "<div class='alert alert-success'>Scanné et statut modifié avec succès (colis : <b>$code</b>)</div>";
} else {
echo "<div class='alert alert-danger'>❌ Colis introuvable dans la base.</div>";
}
}
$con = null;
?>
