<?php 
global $con;

if(SRM("POST")){

$sub_name = POST("sub_category");
$category = POST("category" ,0 ,'int');



if(empty($category)){

print "
<div class='alert alert-danger'>
Veuillez remplir tous les champs obligatoires (*)
</div>
";

exit();	

}

if(empty($sub_name)){

print "
<div class='alert alert-danger'>
Veuillez remplir tous les champs obligatoires (*)
</div>
";

exit();	

}






$stmt = $con->prepare("
INSERT INTO s_classes 
(sub_name, sub_category, sub_unlink) 
VALUES
(:sub_name , :sub_category, 0)");

// ربط القيم
$stmt->bindParam(':sub_name', $sub_name, PDO::PARAM_STR);
$stmt->bindParam(':sub_category', $category, PDO::PARAM_INT);



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