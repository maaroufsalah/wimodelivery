<?php 
global $con;

if (SRM("POST")) {

  $name = POST("name");
  $cities = $_POST['city'] ?? []; // استقبلها كمصفوفة

  if (empty($name) || empty($cities)) {
    echo "
    <div class='alert alert-danger'>
    Veuillez remplir tous les champs obligatoires (*)
    </div>
    ";
    exit();
  }

  // حول المصفوفة إلى نص IDs مفصولة بفواصل
  $cities_string = implode(',', $cities);

  // الإدخال في الجدول الحالي
  $stmt = $con->prepare("INSERT INTO warehouse (wh_name, wh_city, wh_unlink) VALUES (:wh_name, :wh_city, 0)");
  $stmt->bindParam(':wh_name', $name, PDO::PARAM_STR);
  $stmt->bindParam(':wh_city', $cities_string, PDO::PARAM_STR);

  if ($stmt->execute()) {
    echo "
    <div class='alert alert-success'>
    Terminé avec succès
    </div>
    ";
    if (function_exists('load_url')) {
      load_url("agency", 2);
    }
  } else {
    echo "
    <div class='alert alert-danger'>
    Insert Error
    </div>
    ";
  }

  $stmt = null;
  $con = null;

}
?>
