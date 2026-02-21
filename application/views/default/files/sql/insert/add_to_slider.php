<?php
include get_file("files/sql/get/os_settings");
include get_file("files/sql/get/session");
include get_file("files/sql/get/functions");

global $con;

if (SRM("POST")) {



$product_id = POST('product_id');
$slider_id = POST('slider_id' ,0 ,'int');


// التحقق من وجود المنتج والسلايدر
$stmt_product = $con->prepare("SELECT * FROM products WHERE md5(p_id) = ?");
$stmt_product->execute([$product_id]);
$product = $stmt_product->fetch(PDO::FETCH_ASSOC);

$stmt_slider = $con->prepare("SELECT * FROM slider WHERE sli_id = ?");
$stmt_slider->execute([$slider_id]);
$slider = $stmt_slider->fetch(PDO::FETCH_ASSOC);

// التحقق من وجود السلايدر والمنتج
if (!$product || !$slider) {
echo "<div class='alert alert-danger'>Produit ou Slider introuvable.</div>";
exit();
}

// التحقق من أن المنتج لم يكن مضافًا بالفعل إلى السلايدر
$stmt_check = $con->prepare("SELECT COUNT(*) FROM slider_products WHERE slider_id = ? AND product_id = ?");
$stmt_check->execute([$slider_id, $product['p_id']]);
$count = $stmt_check->fetchColumn();

if ($count > 0) {
echo "<div class='alert alert-warning'>Ce produit est déjà ajouté au slider.</div>";
exit();
}

// إدخال المنتج في السلايدر
$stmt_insert = $con->prepare("INSERT INTO slider_products (slider_id, product_id, ordering) VALUES (?, ?, ?)");
$stmt_insert->execute([$slider_id, $product['p_id'], 0]); // قيمة الترتيب مبدئيًا 0

if ($stmt_insert->rowCount() > 0) {
echo "<div class='alert alert-success'>Produit ajouté au slider avec succès.</div>";
if (function_exists('load_url')) {
load_url("stocks", 2); // إعادة التوجيه بعد إضافة المنتج
}
} else {
echo "<div class='alert alert-danger'>Erreur lors de l'ajout du produit au slider.</div>";
}
}
?>
