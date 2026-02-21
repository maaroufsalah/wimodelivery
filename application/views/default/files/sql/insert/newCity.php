<?php 
global $con;

if(SRM("POST")){

$city_name = POST("city");




if(empty($city_name)){

print "
<div class='alert alert-danger'>
Veuillez remplir tous les champs obligatoires (*)
</div>
";

exit();	

}






$stmt = $con->prepare("INSERT INTO city (city_name, city_unlink) VALUES (:city_name, 0)");
$stmt->bindParam(':city_name', $city_name, PDO::PARAM_STR);

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