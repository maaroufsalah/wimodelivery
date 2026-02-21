<?php 
global $con;

// التحقق من أن الطلب هو POST
if (function_exists('SRM') && SRM("POST")) {


// جلب البيانات من الفورم
$id = POST("id");
$name = POST("city");


// التحقق من الحقول الإلزامية
if (empty($name) || !$id) {
echo "
<div class='alert alert-danger'>Veuillez remplir tous les champs obligatoires (*)</div>
";
exit();
}





$sql = "
UPDATE city SET 
city_name = :city_name 
";

$sql .= " WHERE md5(city_id) = :city_id";

$stmt = $con->prepare($sql);


$stmt->bindParam(':city_name', $name, PDO::PARAM_STR);
$stmt->bindParam(':city_id', $id, PDO::PARAM_STR);









// تنفيذ الاستعلام
if ($stmt->execute()) {
echo "<div class='alert alert-success'>Mise à jour réussie</div>";
if (function_exists('load_url')) {
load_url("app_settings?do=city", 2); // إعادة توجيه المستخدم
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
