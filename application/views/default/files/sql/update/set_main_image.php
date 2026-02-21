<?php

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])) {
    global $con;

    $imageId = intval($_POST['id']);

    // جلب معرف المنتج من الصورة المحددة
    $stmt = $con->prepare("SELECT product_id FROM product_images WHERE id = ?");
    $stmt->execute([$imageId]);
    $productId = $stmt->fetch(PDO::FETCH_COLUMN);

    if ($productId) {
        // إزالة تعيين الصورة الأساسية الحالية
        $stmt = $con->prepare("UPDATE product_images SET is_main = 0 WHERE product_id = ?");
        $stmt->execute([$productId]);

        // تعيين الصورة الجديدة كصورة رئيسية
        $stmt = $con->prepare("UPDATE product_images SET is_main = 1 WHERE id = ?");
        $stmt->execute([$imageId]);

        echo json_encode(["status" => "success", "message" => "L'image principale a été mise à jour!"]);
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
