<?php
// اتصال بقاعدة البيانات
global $con;


// الحصول على فئة رئيسية
$category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;

// استعلام لجلب الفئات الفرعية بناءً على الفئة الرئيسية
$query = "SELECT sub_id, sub_name FROM s_classes WHERE sub_category = :category_id AND sub_unlink = 0";
$stmt = $con->prepare($query);
$stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
$stmt->execute();
$subcategories = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($subcategories as $subcategory) {
    echo '<li class="cate-item">
            <input type="radio" name="subcategory" value="' . $subcategory['sub_id'] . '" id="subcategory_' . $subcategory['sub_id'] . '">
            <label for="subcategory_' . $subcategory['sub_id'] . '">
                <span>' . $subcategory['sub_name'] . '</span>
            </label>
          </li>';
}
?>