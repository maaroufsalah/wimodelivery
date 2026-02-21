<?php
// اتصال بقاعدة البيانات
global $con;



// استعلام لجلب الماركات حيث `brand_unlink` = 0 (أي الماركات المتاحة)
$query = "SELECT brand_id, brand_name, brand_image FROM brand WHERE brand_unlink = 0";
$stmt = $con->prepare($query);
$stmt->execute();
$brands = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($brands as $brand) {
    echo '<li class="list-item d-flex gap-12 align-items-center">
            <input type="radio" name="brand" value="' . $brand['brand_id'] . '" id="brand_' . $brand['brand_id'] . '">
            <label for="brand_' . $brand['brand_id'] . '" class="label">
                <span>' . $brand['brand_name'] . '</span>
            </label>
          </li>';
}
?>
