<?php 
global $con;

if(SRM("POST")){

$state_name = POST("state");
$state_color = POST("color");
$state_background = POST("background");
$state_rank = POST("rank");




if(empty($state_name)){

print "
<div class='alert alert-danger'>
Veuillez remplir tous les champs obligatoires (*)
</div>
";

exit();	

}


if(empty($state_background)){

print "
<div class='alert alert-danger'>
Veuillez remplir tous les champs obligatoires (*)
</div>
";

exit();	

}


if(empty($state_color)){

print "
<div class='alert alert-danger'>
Veuillez remplir tous les champs obligatoires (*)
</div>
";

exit();	

}


$stmt = $con->prepare("
INSERT INTO 
state 
(state_rank,state_name, state_background, state_color, state_unlink) 
VALUES 
(:state_rank, :state_name, :state_background, :state_color, 0)");

// ربط القيم
$stmt->bindParam(':state_name', $state_name, PDO::PARAM_STR);
$stmt->bindParam(':state_background', $state_background, PDO::PARAM_STR);
$stmt->bindParam(':state_color', $state_color, PDO::PARAM_STR);
$stmt->bindParam(':state_rank', $state_rank, PDO::PARAM_STR);




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