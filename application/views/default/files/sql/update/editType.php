<?php 
global $con;

if(SRM("POST")){

$type_id = POST("id");
$type_name = POST("type");

$uploadDir = "uploads/type/";
$allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];




if(empty($type_name)){

print "
<div class='alert alert-danger'>
Veuillez remplir tous les champs obligatoires (*)
</div>
";

exit();	

}






$imagePath = '';
if (!empty($_FILES['image']['name'])) {
$imageExt = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
if (in_array($imageExt, $allowedExtensions)) {
$imagePath = $uploadDir . "image_" . time() . "." . $imageExt;
$imageSql = "image_" . time() . "." . $imageExt;
move_uploaded_file($_FILES['image']['tmp_name'], $imagePath);
}
}




$sql = "
UPDATE type SET 
type_name = :type_name
";

if (!empty($imagePath)) {
$sql .= ", type_image = :type_image";
}


$sql .= " WHERE md5(type_id) = :type_id";


$stmt = $con->prepare($sql);


// ربط القيم
$stmt->bindParam(':type_name', $type_name, PDO::PARAM_STR);

if (!empty($imagePath)) {
$stmt->bindParam(':type_image', $imageSql, PDO::PARAM_STR);
}

$stmt->bindParam(':type_id', $type_id, PDO::PARAM_STR);


if ($stmt->execute()) {
echo "
<div class='alert alert-success'>
Terminé avec succès
</div>
";
if (function_exists('load_url')) {
load_url("app_settings?do=type", 2); // إعادة توجيه المستخدم
}
} else {
echo "
<div class='alert alert-danger'>
Insert Error
</div>
";
}

// إغلاق الاتصال
$stmt = null;
$con = null;





}


?>