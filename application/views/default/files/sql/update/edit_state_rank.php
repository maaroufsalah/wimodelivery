<?php 
global $con;

// التحقق من أن الطلب هو POST
if (function_exists('SRM') && SRM("POST")) {


// جلب البيانات من الفورم
$id = POST("id");



$rank = POST("rank");


// التحقق من الحقول الإلزامية
if (empty($rank) || !$id) {
echo "
<div class='alert alert-danger'>Veuillez remplir tous les champs obligatoires (*)</div>
";
exit();
}





$sql = "
UPDATE state 
SET 
state_rank = :state_rank

WHERE 

md5(state_id) = :state_id

";


$stmt = $con->prepare($sql);


$stmt->bindParam(':state_rank', $rank, PDO::PARAM_STR);



$stmt->bindParam(':state_id', $id, PDO::PARAM_STR); // MD5 يجب أن يكون نصًا وليس عددًا صحيحًا








// تنفيذ الاستعلام
if ($stmt->execute()) {
echo "<div class='alert alert-success'>Mise à jour réussie</div>";
if (function_exists('load_url')) {
load_url("app_settings?do=state", 2); // إعادة توجيه المستخدم
}
exit();
} else {
echo "<div class='alert alert-danger'>Erreur de mise à jour</div>";
}

// إغلاق الاتصال
$stmt = null;
$con = null;
}
?>
