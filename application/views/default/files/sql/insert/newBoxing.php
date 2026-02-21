<?php 
global $con;

if(SRM("POST")) {

  $boxing_name  = POST("boxing");
  $boxing_price = $_POST["price"];
  $boxing_type  = $_POST["type"] ?? '';

  // تحقق من القيم
  if(empty($boxing_name) || empty($boxing_price) || empty($boxing_type)) {
    echo "
    <div class='alert alert-danger'>
    Veuillez remplir tous les champs obligatoires (*)
    </div>
    ";
    exit();
  }

  // معالجة الصورة إذا موجودة
  $new_name = NULL; // القيمة الافتراضية

  if(isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
    $uploads_dir = 'uploads/box';
    if(!is_dir($uploads_dir)) {
      mkdir($uploads_dir, 0777, true);
    }

    $tmp_name = $_FILES['photo']['tmp_name'];
    $name     = basename($_FILES['photo']['name']);
    $ext      = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    $allowed  = ['jpg', 'jpeg', 'png', 'gif'];

    if(in_array($ext, $allowed)) {
      $new_name = uniqid() . "." . $ext;
      move_uploaded_file($tmp_name, "$uploads_dir/$new_name");
    } else {
      echo "<div class='alert alert-danger'>Format d'image non autorisé</div>";
      exit();
    }
  }

  // إعداد الاستعلام (أضف الأعمدة حسب الجدول)
  $stmt = $con->prepare("INSERT INTO box (box_name, box_price, box_type, box_photo, box_unlink) 
                         VALUES (:box_name, :box_price, :box_type, :box_photo, 0)");

  $stmt->bindParam(':box_name',  $boxing_name, PDO::PARAM_STR);
  $stmt->bindParam(':box_price', $boxing_price, PDO::PARAM_STR);
  $stmt->bindParam(':box_type',  $boxing_type, PDO::PARAM_STR);
  $stmt->bindParam(':box_photo', $new_name, PDO::PARAM_STR);

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

  $stmt = null;
  $con = null;

}
?>
