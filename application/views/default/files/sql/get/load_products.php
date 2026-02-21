<?php

global $con;
$category = isset($_POST['category']) ? $_POST['category'] : '';
$sub_category = isset($_POST['sub_category']) ? $_POST['sub_category'] : '';
$search = isset($_POST['search']) ? $_POST['search'] : '';
$display = isset($_POST['display']) ? $_POST['display'] : 10;
$page = isset($_POST['page']) ? $_POST['page'] : 1;

$start = ($page - 1) * $display;

// Base query
$query = "SELECT * FROM products WHERE p_unlink = '0'";

// Apply filters
if ($category) {
    $query .= " AND p_category = :category";
}
if ($sub_category) {
    $query .= " AND p_sub_category = :sub_category";
}
if ($search) {
    $query .= " AND p_name LIKE :search";
}

// Limit the number of records
$query .= " LIMIT $start, $display";

// Execute the query
$stmt = $con->prepare($query);
if ($category) {
    $stmt->bindParam(':category', $category, PDO::PARAM_INT);
}
if ($sub_category) {
    $stmt->bindParam(':sub_category', $sub_category, PDO::PARAM_INT);
}
if ($search) {
    $stmt->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
}
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get total product count for pagination
$total_query = "SELECT COUNT(*) FROM products WHERE p_unlink = '0'";
if ($category) {
    $total_query .= " AND p_category = :category";
}
if ($sub_category) {
    $total_query .= " AND p_sub_category = :sub_category";
}
if ($search) {
    $total_query .= " AND p_name LIKE :search";
}

$total_stmt = $con->prepare($total_query);
if ($category) {
    $total_stmt->bindParam(':category', $category, PDO::PARAM_INT);
}
if ($sub_category) {
    $total_stmt->bindParam(':sub_category', $sub_category, PDO::PARAM_INT);
}
if ($search) {
    $total_stmt->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
}
$total_stmt->execute();
$total_data = $total_stmt->fetchColumn();
$total_pages = ceil($total_data / $display);

echo json_encode([
    'products' => $products,
    'total_pages' => $total_pages
]);
?>
