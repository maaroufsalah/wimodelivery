<?php 
global $con;

if(SRM("POST")){

  $id = POST("id");

  $name = POST("name");
  $cities = $_POST['city'] ?? []; // استقبل كمصفوفة

  if(empty($name)){
    print "
    <div class='alert alert-danger'>
    Veuillez remplir tous les champs obligatoires (*)
    </div>
    ";
    exit();
  }

  if(empty($cities)){
    print "
    <div class='alert alert-danger'>
    Veuillez remplir tous les champs obligatoires (*)
    </div>
    ";
    exit();
  }

  // حول المصفوفة إلى نص IDs مفصولة بفواصل
  $cities_string = implode(',', $cities);

  $sql = "
    UPDATE warehouse 
    SET 
      wh_name = :wh_name,
      wh_city = :wh_city
    WHERE md5(wh_id) = :wh_id
  ";

  $stmt = $con->prepare($sql);

  $stmt->bindParam(':wh_name', $name, PDO::PARAM_STR);
  $stmt->bindParam(':wh_city', $cities_string, PDO::PARAM_STR);
  $stmt->bindParam(':wh_id', $id, PDO::PARAM_STR); // md5 يجب أن يكون نص

  if ($stmt->execute()) {
    echo "
    <div class='alert alert-success'>
    Terminé avec succès
    </div>
    ";
    if (function_exists('load_url')) {
      load_url("agency", 2); // إعادة توجيه المستخدم
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
