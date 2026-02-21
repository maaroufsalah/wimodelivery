<?php 
global $con;

if(SRM("POST")){

$name = POST("name");
$note = POST("note");

$uploadDir = "uploads/sections/";
$allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];




if(empty($name)){

print "
<div class='alert alert-danger'>
Veuillez remplir tous les champs obligatoires (*)
</div>
";

exit();	

}

if(empty($note)){

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
INSERT INTO sections 
(sec_name, sec_image, sec_note, sec_unlink) 
VALUES
(:name, :image, :note, 0)");

// ربط القيم
$stmt->bindParam(':name', $name, PDO::PARAM_STR);
$stmt->bindParam(':note', $note, PDO::PARAM_STR);
$stmt->bindParam(':image', $imageSql, PDO::PARAM_STR);



if ($stmt->execute()) {
echo "
<div class='alert alert-success'>
Terminé avec succès
</div>
";

if (function_exists('load_url')) {
load_url("sections", 2); // إعادة توجيه المستخدم
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