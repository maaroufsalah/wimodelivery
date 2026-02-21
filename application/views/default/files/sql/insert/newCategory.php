<?php 
global $con;

if(SRM("POST")){

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
INSERT INTO classes 
(c_name, c_image, c_type, c_unlink) 
VALUES
(:c_name, :c_image, :c_type, 0)");

// ربط القيم
$stmt->bindParam(':c_name', $c_name, PDO::PARAM_STR);
$stmt->bindParam(':c_type', $c_type, PDO::PARAM_INT);
$stmt->bindParam(':c_image', $imageSql, PDO::PARAM_STR);



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