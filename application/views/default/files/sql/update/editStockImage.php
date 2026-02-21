<?php 
global $con;

if (SRM("POST")) {

$id = POST("id", 0, 'int');  // معرف المنتج

// التحقق من الحقول الإلزامية
if (!$id) {
echo "<div class='alert alert-danger'>Veuillez remplir tous les champs obligatoires (*)</div>";
exit();
}

// التحقق مما إذا كان المنتج موجودًا في قاعدة البيانات
$stmt = $con->prepare("SELECT COUNT(*) FROM products WHERE p_id = :id");
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$productExists = $stmt->fetchColumn();

if ($productExists == 0) {
echo "<div class='alert alert-danger'>Le produit spécifié n'existe pas.</div>";
exit();
}

// مسار تخزين الصور
$uploadDir = "uploads/products/";

// إنشاء المجلد إذا لم يكن موجودًا
if (!is_dir($uploadDir)) {
mkdir($uploadDir, 0777, true);
}

$uploadedImages = [];

// جلب الصور المخزنة حاليًا لهذا المنتج لمنع التكرار
$stmt = $con->prepare("SELECT image_url FROM product_images WHERE product_id = ?");
$stmt->execute([$id]);
$existingImages = $stmt->fetchAll(PDO::FETCH_COLUMN);

// التحقق من تحميل الصور
if (!empty($_FILES['images']['name'][0])) {
foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
$fileName = basename($_FILES['images']['name'][$key]);
$uniqueName = time() . "_" . $fileName;
$filePath = $uploadDir . $uniqueName;

// التحقق من نوع الصورة
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
if (!in_array($_FILES['images']['type'][$key], $allowedTypes)) {
echo "<div class='alert alert-danger'>Type de fichier non autorisé.</div>";
continue;
}

// التحقق مما إذا كانت الصورة مكررة
if (in_array($uniqueName, $existingImages)) {
continue; // تخطي الصورة إذا كانت موجودة مسبقًا
}

// نقل الصورة إلى المجلد
if (move_uploaded_file($tmp_name, $filePath)) {
$uploadedImages[] = $uniqueName;
}
}
}

// إذا تم رفع صور جديدة، نقوم بحفظها في قاعدة البيانات
if (!empty($uploadedImages)) {
$stmt = $con->prepare("INSERT INTO product_images (product_id, image_url) VALUES (?, ?)");
foreach ($uploadedImages as $image) {
$stmt->execute([$id, $image]);
}
}

echo "<div class='alert alert-success my-3'>Mise à jour réussie!</div>";
load_url("",1);			

}
?>
