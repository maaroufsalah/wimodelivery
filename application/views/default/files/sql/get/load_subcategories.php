<?php
global $con;


$category_id = isset($_GET['category_id']) ? $_GET['category_id'] : 0;

// Query to get subcategories based on selected category
$query = "SELECT * FROM s_classes WHERE c_id = :category_id";
$stmt = $con->prepare($query);
$stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
$stmt->execute();
$subcategories = $stmt->fetchAll(PDO::FETCH_ASSOC);

$options = '<option value="">Select Sub Category</option>';
foreach ($subcategories as $subcategory) {
    $options .= '<option value="' . $subcategory['sub_id'] . '">' . $subcategory['sub_name'] . '</option>';
}

echo $options;
?>
