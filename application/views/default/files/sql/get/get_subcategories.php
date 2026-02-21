<?php
include 'db_connection.php'; // اتصال قاعدة البيانات

$categoryId = $_GET['category_id'] ?? ''; // استرجاع category_id من الاستعلام

if ($categoryId) {
    // استعلام SQL للحصول على التصنيفات الفرعية بناءً على sub_category
    $stmt = $con->prepare("SELECT * FROM s_classes WHERE sub_category = ?");
    $stmt->execute([$categoryId]);
    $subcategories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // إرجاع التصنيفات الفرعية على شكل خيارات select
    foreach ($subcategories as $subcategory) {
        echo "<option value='{$subcategory['sub_id']}'>{$subcategory['sub_name']}</option>";
    }
} else {
    // في حال عدم وجود تصنيفات فرعية
    echo "<option value='0' disabled>Choisir Sous-Catégorie</option>";
}
?>
