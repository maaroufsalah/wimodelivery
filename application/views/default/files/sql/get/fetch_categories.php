<?php
// اتصال بقاعدة البيانات
global $con;

// استعلام لجلب جميع الفئات الرئيسية
$query = "SELECT c_id, c_name FROM classes WHERE c_unlink = 0";
$stmt = $con->prepare($query);
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($categories as $category) {
    echo '<li class="cate-item">
            <input type="radio" name="category" value="' . $category['c_id'] . '" id="category_' . $category['c_id'] . '">
            <label for="category_' . $category['c_id'] . '">
                <span>' . $category['c_name'] . '</span>
            </label>
          </li>';
}
?>