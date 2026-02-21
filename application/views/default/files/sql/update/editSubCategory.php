<?php 
global $con;

if(SRM("POST")){

$sub_id = POST("id");
$sub_name = POST("sub_category");
$sub_category = POST("category" ,0 ,'int');



if(empty($sub_name)){

print "
<div class='alert alert-danger'>
Veuillez remplir tous les champs obligatoires (*)
</div>
";

exit();	

}


if(empty($sub_category)){

print "
<div class='alert alert-danger'>
Veuillez remplir tous les champs obligatoires (*)
</div>
";

exit();	

}




$sql = "
UPDATE s_classes SET 
sub_name = :sub_name,
sub_category = :sub_category
";



$sql .= " WHERE md5(sub_id) = :sub_id";


$stmt = $con->prepare($sql);


// ربط القيم
$stmt->bindParam(':sub_name', $sub_name, PDO::PARAM_STR);
$stmt->bindParam(':sub_category', $sub_category, PDO::PARAM_INT);


$stmt->bindParam(':sub_id', $sub_id, PDO::PARAM_STR);


if ($stmt->execute()) {
echo "
<div class='alert alert-success'>
Terminé avec succès
</div>
";
if (function_exists('load_url')) {
load_url("app_settings?do=sub_category", 2); // إعادة توجيه المستخدم
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