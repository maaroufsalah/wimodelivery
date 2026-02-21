<?php 
global $con;

if (SRM("POST")) {

    $id = POST("id");
    $name = POST("name");
    $note = POST("note");

    $uploadDir = "uploads/sections/";
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];

    if (empty($name) || empty($note)) {
        echo "<div class='alert alert-danger'>Veuillez remplir tous les champs obligatoires (*)</div>";
        exit();
    }

    // معالجة رفع الصورة
    $imagePath = '';
    $imageSql = '';
    if (!empty($_FILES['image']['name'])) {
        $imageExt = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (in_array($imageExt, $allowedExtensions)) {
            $imageFileName = "image_" . time() . "." . $imageExt;
            $imagePath = $uploadDir . $imageFileName;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $imagePath)) {
                $imageSql = $imageFileName;
            } else {
                echo "<div class='alert alert-danger'>Erreur lors de l'upload de l'image</div>";
                exit();
            }
        } else {
            echo "<div class='alert alert-danger'>Format d'image non autorisé</div>";
            exit();
        }
    }

    // إعداد استعلام SQL
    $sql = "
        UPDATE sections SET 
        sec_name = :name,
        sec_note = :note
    ";

    if (!empty($imageSql)) {
        $sql .= ", sec_image = :image";
    }

    $sql .= " WHERE md5(sec_id) = :id";

    $stmt = $con->prepare($sql);

    // ربط القيم
    $stmt->bindParam(':name', $name, PDO::PARAM_STR);
    $stmt->bindParam(':note', $note, PDO::PARAM_STR);
    $stmt->bindParam(':id', $id, PDO::PARAM_STR);

    if (!empty($imageSql)) {
        $stmt->bindParam(':image', $imageSql, PDO::PARAM_STR);
    }

    // تنفيذ الاستعلام
    if ($stmt->execute()) {
        echo "<div class='alert alert-success'>Terminé avec succès</div>";
        if (function_exists('load_url')) {
            load_url("sections", 2); // إعادة توجيه المستخدم
        }
    } else {
        echo "<div class='alert alert-danger'>Insert Error</div>";
    }

    // إغلاق الاتصال
    $stmt = null;
    $con = null;
}
?>
