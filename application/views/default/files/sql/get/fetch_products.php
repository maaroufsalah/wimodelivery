<?php



include get_file("files/sql/get/os_settings");
include get_file("files/sql/get/session");
include get_file("files/sql/get/functions");

global $con;

// جلب القيم من الرابط
$category = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$brand = isset($_GET['brand']) ? (int)$_GET['brand'] : 0;
$subcategory = isset($_GET['subcategory']) ? (int)$_GET['subcategory'] : 0;
$search = isset($_GET['search']) ? $_GET['search'] : '';
$minPrice = isset($_GET['minPrice']) ? (float)$_GET['minPrice'] : 0;
$maxPrice = isset($_GET['maxPrice']) ? (float)$_GET['maxPrice'] : 0;

// بناء استعلام البحث مع الفلاتر
$query = "SELECT p_id, p_name, p_sell, p_discount, p_category, p_sub_category, p_brand, p_note, p_details 
FROM products WHERE p_unlink = 0";
$conditions = [];

if ($category > 0) {
$conditions[] = "p_category = :category";
}
if ($brand > 0) {
$conditions[] = "p_brand = :brand";
}
if ($subcategory > 0) {
$conditions[] = "p_sub_category = :subcategory";
}
if ($search) {
$conditions[] = " p_name LIKE :search OR p_note LIKE :search ";
}
if ($minPrice > 0 && $maxPrice > 0) {
$conditions[] = "p_sell BETWEEN :minPrice AND :maxPrice";
}

if (count($conditions) > 0) {
$query .= " AND " . implode(" AND ", $conditions);
}

// إعداد الاستعلام
$stmt = $con->prepare($query);

// ربط القيم بالاستعلام باستخدام bindValue
if ($category > 0) {
$stmt->bindValue(':category', $category, PDO::PARAM_INT);
}
if ($brand > 0) {
$stmt->bindValue(':brand', $brand, PDO::PARAM_INT);
}
if ($subcategory > 0) {
$stmt->bindValue(':subcategory', $subcategory, PDO::PARAM_INT);
}
if ($search) {
$stmt->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
}
if ($minPrice > 0 && $maxPrice > 0) {
$stmt->bindValue(':minPrice', $minPrice, PDO::PARAM_STR);
$stmt->bindValue(':maxPrice', $maxPrice, PDO::PARAM_STR);
}

$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// عرض المنتجات
print '
<div class="grid-layout loadmore-item wow fadeInUp" data-wow-delay="0s" data-grid="grid-4" style="visibility: visible; animation-delay: 0s;">
';

// جلب الصورة الرئيسية للمنتج
foreach ($products as $product) {
$stmtImage = $con->prepare("SELECT image_url FROM product_images WHERE product_id = ? AND is_main = 1 LIMIT 1");
$stmtImage->execute([$product['p_id']]);
$mainImage = $stmtImage->fetchColumn();
$imageUrl = $mainImage ? "uploads/products/" . htmlspecialchars($mainImage) : "uploads/app/default.jpg";

// عرض البيانات
echo "
<div class='card-product  fl-item' style='display:block'>
<div class='card p-card mb-3' style='border:0;'>
<img src='{$imageUrl}' class='card-img-top'>
<div class='card-body text-center'>
<div class='card-product-info title link' style='align-items: center;height: 130px;'>
{$product['p_name']}
</div>
<span class='text-muted text-decoration-line-through'>
{$product['p_discount']} Dhs
</span>
{$product['p_sell']} Dhs
<a href='product_details?product_id={$product['p_id']}' class='btn btn-dark btn-sm'>Découvrir</a>
</div>
</div>
</div>
";
}

print '</div>';
?>
