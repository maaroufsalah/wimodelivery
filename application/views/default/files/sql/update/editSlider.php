<?php 
global $con;

if(SRM("POST")){

$id = POST("id");
$name = POST("name");
$note = POST("note");

$uploadDir = "uploads/sliders/";
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
UPDATE slider SET 
sli_name = :name,
sli_note = :note
";

if (!empty($imagePath)) {
$sql .= ", sli_image = :image";
}


$sql .= " WHERE md5(sli_id) = :id";


$stmt = $con->prepare($sql);


// ربط القيم
$stmt->bindParam(':name', $name, PDO::PARAM_STR);
$stmt->bindParam(':note', $note, PDO::PARAM_STR);

if (!empty($imagePath)) {
$stmt->bindParam(':image', $imageSql, PDO::PARAM_STR);
}

$stmt->bindParam(':id', $id, PDO::PARAM_STR);


if ($stmt->execute()) {
echo "
<div class='alert alert-success'>
Terminé avec succès
</div>
";
if (function_exists('load_url')) {
load_url("promotions", 2); // إعادة توجيه المستخدم
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