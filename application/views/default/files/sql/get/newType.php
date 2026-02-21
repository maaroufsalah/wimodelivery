<?php 
global $con;

if(SRM("POST")){

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


if(empty($_FILES['image']['name'])){

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


$stmt = $con->prepare("
INSERT INTO type 
(type_name, type_image, type_unlink) 
VALUES
(:type_name, :type_image, 0)");

// ربط القيم
$stmt->bindParam(':type_name', $type_name, PDO::PARAM_STR);
$stmt->bindParam(':type_image', $imageSql, PDO::PARAM_STR);



if ($stmt->execute()) {
echo "
<div class='alert alert-success'>
Terminé avec succès
</div>
";
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