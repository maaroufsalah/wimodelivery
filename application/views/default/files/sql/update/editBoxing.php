<?php 
global $con;

// التحقق من أن الطلب هو POST
if (function_exists('SRM') && SRM("POST")) {

  // جلب البيانات من الفورم
  $id    = POST("id");
  $name  = POST("boxing");
  $price = POST("price");
  $type  = POST("type");

  // التحقق من الحقول الإلزامية
  if (empty($name) || empty($price) || empty($type) || !$id) {
    echo "<div class='alert alert-danger'>Veuillez remplir tous les champs obligatoires (*)</div>";
    exit();
  }

  // جلب البيانات القديمة لجلب اسم الصورة القديم
  $stmtOld = $con->prepare("SELECT box_photo FROM box WHERE md5(box_id) = :box_id LIMIT 1");
  $stmtOld->bindParam(':box_id', $id, PDO::PARAM_STR);
  $stmtOld->execute();
  $oldRow = $stmtOld->fetch(PDO::FETCH_ASSOC);
  $oldPhoto = $oldRow ? $oldRow['box_photo'] : null;

  // معالجة رفع صورة جديدة (اختياري)
  $newPhotoName = $oldPhoto; // الافتراضي: نحتفظ بالصورة القديمة

  if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
    $uploads_dir = 'uploads/box';
    if (!is_dir($uploads_dir)) {
      mkdir($uploads_dir, 0777, true);
    }

    $tmp_name = $_FILES['photo']['tmp_name'];
    $nameFile = basename($_FILES['photo']['name']);
    $ext = strtolower(pathinfo($nameFile, PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];

    if (in_array($ext, $allowed)) {
      $newPhotoName = uniqid() . "." . $ext;
      if (move_uploaded_file($tmp_name, "$uploads_dir/$newPhotoName")) {
        // حذف الصورة القديمة إذا كانت موجودة
        if (!empty($oldPhoto) && file_exists("$uploads_dir/$oldPhoto")) {
          unlink("$uploads_dir/$oldPhoto");
        }
      } else {
        echo "<div class='alert alert-danger'>Erreur lors du téléchargement de l'image</div>";
        exit();
      }
    } else {
      echo "<div class='alert alert-danger'>Format d'image non autorisé</div>";
      exit();
    }
  }

  // تحديث البيانات
  $sql = "
    UPDATE box 
    SET 
      box_name = :box_name,
      box_price = :box_price,
      box_type = :box_type,
      box_photo = :box_photo
    WHERE 
      md5(box_id) = :box_id
  ";

  $stmt = $con->prepare($sql);
  $stmt->bindParam(':box_name', $name, PDO::PARAM_STR);
  $stmt->bindParam(':box_price', $price, PDO::PARAM_STR);
  $stmt->bindParam(':box_type', $type, PDO::PARAM_STR);
  $stmt->bindParam(':box_photo', $newPhotoName, PDO::PARAM_STR);
  $stmt->bindParam(':box_id', $id, PDO::PARAM_STR);

  if ($stmt->execute()) {
    echo "<div class='alert alert-success'>Mise à jour réussie</div>";
    if (function_exists('load_url')) {
      load_url("app_settings?do=boxing", 2); // إعادة توجيه
    }
    exit();
  } else {
    echo "<div class='alert alert-danger'>Erreur de mise à jour</div>";
  }

  $stmt = null;
  $con = null;

}
?>
