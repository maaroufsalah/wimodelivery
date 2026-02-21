<?php 
global $con;


$id = $_POST['id'] ?? 0;
$value = $_POST['value'] ?? '';

if ($id && $value) {
    $stmt = $con->prepare("UPDATE product_option_values SET value_name = ? WHERE id = ?");
    $stmt->execute([$value, $id]);
    echo "ok";
}


?>