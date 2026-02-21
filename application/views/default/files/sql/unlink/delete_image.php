<?php

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])) {
    global $con;

    $imageId = intval($_POST['id']);

    // جلب مسار الصورة من قاعدة البيانات
    $stmt = $con->prepare("SELECT image_url FROM product_images WHERE id = ?");
    $stmt->execute([$imageId]);
    $image = $stmt->fetch(PDO::FETCH_COLUMN);

    if ($image) {
        $filePath = "uploads/products/" . $image;

        // حذف الصورة من المجلد إذا كانت موجودة
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        // حذف الصورة من قاعدة البيانات
        $stmt = $con->prepare("DELETE FROM product_images WHERE id = ?");
        $stmt->execute([$imageId]);

        echo json_encode(["status" => "success", "message" => "L'image a été supprimée avec succès!"]);
        exit();
    } else {
        echo json_encode(["status" => "error", "message" => "Image non trouvée."]);
        exit();
    }
} else {
    echo json_encode(["status" => "error", "message" => "Requête invalide."]);
    exit();
}
?>
