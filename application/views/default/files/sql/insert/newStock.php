<?php 
global $con;

if (SRM("POST")) {

// قراءة القيم
$warehouse     = POST("warehouse", 0, 'int');
$brand         = POST("brand", 0, 'int');
$user          = POST("user", 0, 'int');
$category      = POST("category", 0, 'int');
$sub_category  = POST("sub_category", 0, 'int');
$buy           = POST("buy", 0.0, 'float');
$sell          = POST("sell", 0.0, 'float');
$discount      = POST("discount", 0.0, 'float');
$qty           = POST("qty", 0, 'int');
$name          = POST("name");
$code          = POST("code");
$state         = POST("state", 0, 'int');
$note          = POST("note");
$details       = POST("details");

// التحقق من الحقول الإلزامية
if (!$warehouse || !$user || !$category || !$qty || empty($name) ) {
echo "<div class='alert alert-danger'>Veuillez remplir tous les champs obligatoires (*)</div>";
exit();
}

// إدخال المنتج
$stmt = $con->prepare("
INSERT INTO products
(
p_name, p_code, p_brand, p_user, p_note, p_details, p_date, p_warehouse, 
p_category, p_sub_category, p_buy, p_sell, p_discount, p_state, p_qty, p_unlink
) VALUES (
:p_name, :p_code, :p_brand, :p_user, :p_note, :p_details, NOW(), :p_warehouse, 
:p_category, :p_sub_category, :p_buy, :p_sell, :p_discount, :p_state, :p_qty, 0
)
");

$stmt->bindParam(':p_warehouse', $warehouse, PDO::PARAM_INT);
$stmt->bindParam(':p_brand', $brand, PDO::PARAM_INT);
$stmt->bindParam(':p_code', $code, PDO::PARAM_STR);
$stmt->bindParam(':p_user', $user, PDO::PARAM_INT);
$stmt->bindParam(':p_sub_category', $sub_category, PDO::PARAM_INT);
$stmt->bindParam(':p_category', $category, PDO::PARAM_INT);
$stmt->bindParam(':p_buy', $buy, PDO::PARAM_STR);
$stmt->bindParam(':p_sell', $sell, PDO::PARAM_STR);
$stmt->bindParam(':p_discount', $discount, PDO::PARAM_STR);
$stmt->bindParam(':p_qty', $qty, PDO::PARAM_INT);
$stmt->bindParam(':p_state', $state, PDO::PARAM_INT);
$stmt->bindParam(':p_name', $name, PDO::PARAM_STR);
$stmt->bindParam(':p_note', $note, PDO::PARAM_STR);
$stmt->bindParam(':p_details', $details, PDO::PARAM_STR);

if ($stmt->execute()) {
// الحصول على ID المنتج الجديد
$productId = $con->lastInsertId();

// مسار رفع الصور
$uploadDir = "uploads/products/";
if (!is_dir($uploadDir)) {
mkdir($uploadDir, 0777, true);
}

$uploadedImages = [];

// معالجة الصور إذا وجدت
if (!empty($_FILES['images']) && isset($_FILES['images']['tmp_name']) && is_array($_FILES['images']['tmp_name'])) {
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
if (!is_uploaded_file($tmp_name)) {
continue;
}

$fileName = basename($_FILES['images']['name'][$key]);
$uniqueName = time() . "_" . preg_replace("/\s+/", "_", $fileName);
$filePath = $uploadDir . $uniqueName;

if (!in_array($_FILES['images']['type'][$key], $allowedTypes)) {
echo "<div class='alert alert-danger'>Type de fichier non autorisé : $fileName</div>";
continue;
}

if (move_uploaded_file($tmp_name, $filePath)) {
$uploadedImages[] = $uniqueName;
}
}
}

// إدخال الصور في قاعدة البيانات
if (!empty($uploadedImages)) {
$stmtImg = $con->prepare("INSERT INTO product_images (product_id, image_url, is_main) VALUES (?, ?, ?)");
foreach ($uploadedImages as $img) {
$stmtImg->execute([$productId, $img ,1]);
}
$stmtImg = null;
}

echo "<div class='alert alert-success'>Terminé avec succès</div>";
load_url("stocks", 2);

} else {
echo "<div class='alert alert-danger'>Insert Error</div>";
}

// إغلاق الاتصال
$stmt = null;
$con = null;
}
?>
