<?php 
global $con;


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'], $_POST['sec_id'])) {
    $product_id = $_POST['product_id'];
    $sec_id = $_POST['sec_id'];

    // تنفيذ الحذف من القسم فقط
    $stmt = $con->prepare("DELETE FROM section_products WHERE product_id = ? AND sec_id = ?");
    if ($stmt->execute([$product_id, $sec_id])) {
        echo json_encode(["status" => "success", "message" => "تم حذف المنتج من القسم بنجاح"]);
    } else {
        echo json_encode(["status" => "error", "message" => "حدث خطأ أثناء الحذف"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "طلب غير صالح"]);
}
?>

