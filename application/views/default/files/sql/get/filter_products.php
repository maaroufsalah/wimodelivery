<?php
global $con;

// Récupérer les données du formulaire
$category = $_GET['category'] ?? '';
$subcategory = $_GET['subcategory'] ?? '';
$brand = $_GET['brand'] ?? '';
$price = $_GET['price'] ?? 1000;

// Construire la requête SQL en fonction des filtres
$query = "SELECT * FROM products WHERE price <= :price";
$params = [':price' => $price];

if ($category) {
    $query .= " AND category_id = :category";
    $params[':category'] = $category;
}

if ($subcategory) {
    $query .= " AND subcategory_id = :subcategory";
    $params[':subcategory'] = $subcategory;
}

if ($brand) {
    $query .= " AND brand_id = :brand";
    $params[':brand'] = $brand;
}

$stmt = $con->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Afficher les résultats
foreach ($products as $product) {
    echo "
    <div class='product-card'>
        <img src='{$product['image']}' alt='{$product['name']}'>
        <h5>{$product['name']}</h5>
        <p>{$product['description']}</p>
        <p>Prix: {$product['price']}€</p>
    </div>
    ";
}
?>
