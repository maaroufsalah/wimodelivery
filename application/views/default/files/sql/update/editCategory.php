<?php 
global $con;

if(SRM("POST")){

$c_id = POST("id");
$c_name = POST("category");
$c_type = POST("type" ,0 ,'int');

$uploadDir = "uploads/category/";
$allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];




if(empty($c_name)){

print "
<div class='alert alert-danger'>
Veuillez remplir tous les champs obligatoires (*)
</div>
";

exit();	

}


if(empty($c_type)){

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
UPDATE classes SET 
c_name = :c_name,
c_type = :c_type
";

if (!empty($imagePath)) {
$sql .= ", c_image = :c_image";
}


$sql .= " WHERE md5(c_id) = :c_id";


$stmt = $con->prepare($sql);


// ربط القيم
$stmt->bindParam(':c_name', $c_name, PDO::PARAM_STR);
$stmt->bindParam(':c_type', $c_type, PDO::PARAM_INT);

if (!empty($imagePath)) {
$stmt->bindParam(':c_image', $imageSql, PDO::PARAM_STR);
}

$stmt->bindParam(':c_id', $c_id, PDO::PARAM_STR);


if ($stmt->execute()) {
echo "
<div class='alert alert-success'>
Terminé avec succès
</div>
";
if (function_exists('load_url')) {
load_url("app_settings?do=category", 2); // إعادة توجيه المستخدم
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