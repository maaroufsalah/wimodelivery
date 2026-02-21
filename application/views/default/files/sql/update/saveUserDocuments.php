<?php
global $con;

$upload_dir = "uploads/docs/";
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

function alert($message, $type = 'success') {
    return "<div class='alert alert-$type alert-dismissible fade show' role='alert'>
    $message
    <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
    </div>";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $user_id = $_POST['id'];

    // 1. جلب البيانات القديمة للمستخدم
    $stmt_old = $con->prepare("SELECT user_ice_one, user_ice_tow, user_ice_bank FROM users WHERE md5(user_id) = ?");
    $stmt_old->execute([$user_id]);
    $old_data = $stmt_old->fetch(PDO::FETCH_ASSOC);

    // 2. تهيئة البيانات الجديدة
    $new_data = [
        'ice_one' => $old_data['user_ice_one'],
        'ice_tow' => $old_data['user_ice_tow'],
        'ice_bank' => $old_data['user_ice_bank'],
    ];

    // 3. رفع الملفات الجديدة واستبدال فقط ما تغيّر
    foreach (['ice_one', 'ice_tow', 'ice_bank'] as $file_key) {
        if (isset($_FILES[$file_key]) && $_FILES[$file_key]['error'] === 0) {
            $ext = strtolower(pathinfo($_FILES[$file_key]['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];

            if (!in_array($ext, $allowed)) {
                exit(alert("Type de fichier non autorisé pour $file_key", "danger"));
            }

            $doc_name = $file_key . "_" . uniqid() . "." . $ext;
            $upload_path = $upload_dir . $doc_name;

            if (!move_uploaded_file($_FILES[$file_key]['tmp_name'], $upload_path)) {
                exit(alert("Échec du téléchargement de $file_key", "danger"));
            }

            // حذف القديم إذا وجد
            if (!empty($old_data['user_' . $file_key]) && file_exists($upload_dir . $old_data['user_' . $file_key])) {
                unlink($upload_dir . $old_data['user_' . $file_key]);
            }

            $new_data[$file_key] = $doc_name;
            echo alert("$file_key téléchargé avec succès", "success");
        }
    }

    // 4. تحديث فقط ما تم رفعه أو الحفاظ على القديم
    $sql = "UPDATE users SET 
                user_ice_one = :ice_one, 
                user_ice_tow = :ice_tow, 
                user_ice_bank = :ice_bank 
            WHERE md5(user_id) = :user_id";

    $stmt = $con->prepare($sql);
    $stmt->bindParam(':ice_one', $new_data['ice_one']);
    $stmt->bindParam(':ice_tow', $new_data['ice_tow']);
    $stmt->bindParam(':ice_bank', $new_data['ice_bank']);
    $stmt->bindParam(':user_id', $user_id);

    if ($stmt->execute()) {
        echo alert("Les données ont été mises à jour avec succès!", "success");
            if (function_exists('load_url')) {
      load_url("", 2);
    }
    } else {
        echo alert("Erreur lors de la mise à jour des données.", "danger");
    }
}
?>
