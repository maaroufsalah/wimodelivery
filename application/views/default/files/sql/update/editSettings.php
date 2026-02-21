<?php 
global $con;

// التحقق من أن الطلب هو POST
if (function_exists('SRM') && SRM("POST")) {

$uploadDir = "uploads/";
$allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'ico'];



// جلب البيانات من الفورم
$se_id = 1; // معرّف الطلب
$name = POST("name");
$note = POST("note");
$phone = POST("phone",'');
$whatsapp = POST("whatsapp",'');
$email = POST("email");
$id_number = POST("id_number",'');
$location = POST("location");
$bottom = POST("bottom");

// التحقق من الحقول الإلزامية
if (empty($name) || empty($note) || !$phone || !$whatsapp || empty($email)) {
echo "<div class='alert alert-danger'>Veuillez remplir tous les champs obligatoires (*)</div>";
exit();
}



$logoPath = '';
if (!empty($_FILES['logo']['name'])) {
$logoExt = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
if (in_array($logoExt, $allowedExtensions)) {
$logoPath = $uploadDir . "logo_" . time() . "." . $logoExt;
$logoSql = "logo_" . time() . "." . $logoExt;
move_uploaded_file($_FILES['logo']['tmp_name'], $logoPath);
}
}

$faviconPath = '';
if (!empty($_FILES['favicon']['name'])) {
$faviconExt = strtolower(pathinfo($_FILES['favicon']['name'], PATHINFO_EXTENSION));
if (in_array($faviconExt, $allowedExtensions)) {
$faviconPath = $uploadDir . "favicon_" . time() . "." . $faviconExt;
$faviconSql = "favicon_" . time() . "." . $faviconExt;
move_uploaded_file($_FILES['favicon']['tmp_name'], $faviconPath);
}
}






$sql = "UPDATE settings SET 
set_name = :set_name, 
set_note = :set_note, 
set_phone = :set_phone, 
set_whatsapp = :set_whatsapp, 
set_email = :set_email, 
set_id_number = :set_id_number, 
set_location = :set_location, 
set_bottom_paper = :set_bottom";
if (!empty($logoPath)) {
$sql .= ", set_logo = :set_logo";
}
if (!empty($faviconPath)) {
$sql .= ", set_favicon = :set_favicon";
}
$sql .= " WHERE set_id = :set_id";

$stmt = $con->prepare($sql);
$stmt->bindParam(':set_name', $name, PDO::PARAM_STR);
$stmt->bindParam(':set_note', $note, PDO::PARAM_STR);
$stmt->bindParam(':set_phone', $phone, PDO::PARAM_INT);
$stmt->bindParam(':set_whatsapp', $whatsapp, PDO::PARAM_INT);
$stmt->bindParam(':set_email', $email, PDO::PARAM_STR);
$stmt->bindParam(':set_id_number', $id_number, PDO::PARAM_INT);
$stmt->bindParam(':set_location', $location, PDO::PARAM_STR);
$stmt->bindParam(':set_bottom', $bottom, PDO::PARAM_STR);
$stmt->bindParam(':set_id', $se_id, PDO::PARAM_INT);
if (!empty($logoPath)) {
$stmt->bindParam(':set_logo', $logoSql, PDO::PARAM_STR);
}
if (!empty($faviconPath)) {
$stmt->bindParam(':set_favicon', $faviconSql, PDO::PARAM_STR);
}










// تنفيذ الاستعلام
if ($stmt->execute()) {
echo "<div class='alert alert-success'>Mise à jour réussie</div>";
if (function_exists('load_url')) {
load_url("app_settings", 2); // إعادة توجيه المستخدم
}
exit();
} else {
echo "<div class='alert alert-danger'>Erreur de mise à jour</div>";
}

// إغلاق الاتصال
$stmt = null;
$con = null;
}
?>
