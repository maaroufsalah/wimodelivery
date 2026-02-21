<?php
global $con;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $news_id    = $_POST['news_id'] ?? 0; // تأكد أنك ترسل ID
    $color      = $_POST['color'] ?? '';
    $background = $_POST['background'] ?? '';
    $user       = $_POST['user'] ?? 0;
    $type       = $_POST['type'] ?? '';
    $details    = trim($_POST['details'] ?? '');
    $old_image  = $_POST['old_image'] ?? ''; // اسم الصورة القديمة
    $user_id    = 0;

    if (empty($news_id)) {
        echo '<div class="alert alert-danger">ID invalide.</div>';
        exit();
    }

    // التحقق العام
    if (empty($type)) {
        echo '<div class="alert alert-danger">⚠️ Veuillez choisir un type.</div>';
        exit();
    }

    // تحقق خاص لكل نوع
    if ($type == 'pop') {
        if (empty($_FILES['image']['name']) && empty($old_image)) {
            echo '<div class="alert alert-danger">⚠️ Pour le type POP, veuillez choisir une image.</div>';
            exit();
        }
        if (empty($color) || empty($background)) {
            echo '<div class="alert alert-danger">⚠️ Veuillez remplir la couleur et le background pour le type POP.</div>';
            exit();
        }
    }

    if ($type == 'alert') {
        if (empty($details) || empty($color) || empty($background)) {
            echo '<div class="alert alert-danger">⚠️ Pour le type Alert, veuillez remplir les détails, la couleur et le background.</div>';
            exit();
        }
    }

    // رفع الصورة الجديدة إذا وُجدت
    $imageName = $old_image; // افتراضيًا نحافظ على القديمة
    if (!empty($_FILES['image']['name'])) {
        $targetDir = "uploads/news/";
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        $imageName = time() . "_" . basename($_FILES["image"]["name"]);
        $targetFile = $targetDir . $imageName;
        move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile);

        // حذف الصورة القديمة (اختياري)
        if (!empty($old_image) && file_exists($targetDir . $old_image)) {
            unlink($targetDir . $old_image);
        }
    }

    // تنفيذ التحديث
    $stmt = $con->prepare("
        UPDATE news SET
            n_color = :color,
            n_bg = :background,
            n_note = :details,
            n_user = :user,
            n_type = :type,
            n_image = :image
        WHERE n_id = :id
    ");
    $stmt->execute([
        ':color'      => $color,
        ':background' => $background,
        ':details'    => htmlspecialchars($details, ENT_QUOTES, 'UTF-8'),
        ':user'       => $user,
        ':type'       => $type,
        ':image'      => $imageName,
        ':id'         => $news_id
    ]);

    echo '
    <div class="alert alert-success alert-dismissible fade show" role="alert">
    ✅ News mis à jour avec succès !
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    ';

    echo '<script>document.getElementById("formId").reset();</script>';

    $con = null;
}
?>
