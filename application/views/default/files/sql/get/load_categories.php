<?php
global $con;


// Query to get categories
$query = "SELECT * FROM classes";
$stmt = $con->prepare($query);
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

$options = '<option value="">Select Category</option>';
foreach ($categories as $category) {
    $options .= '<option value="' . $category['c_id'] . '">' . $category['c_name'] . '</option>';
}

echo $options;
?>
