<?php




global $con;

if ($con) {
$stmt = $con->prepare("SELECT * FROM settings WHERE set_id = '1' LIMIT 1");
$stmt->execute();
$settingsCount = $stmt->rowCount();
$set = $stmt->fetch();

if ($settingsCount > 0) {



$set_name = $set ['set_name'];
$set_note = $set ['set_note'];
$set_phone = $set ['set_phone'];
$set_email = $set ['set_email'];
$set_whatsapp = $set ['set_whatsapp'];
$set_logo = $set ['set_logo'];
$set_print_logo = $set ['set_print_logo'];
$set_favicon = $set ['set_favicon'];
$set_location = $set ['set_location'];
$set_id_number = $set ['set_id_number'];
$set_bottom_paper = $set ['set_bottom_paper'];




}
} else {
die("Database Error");
}
?>
