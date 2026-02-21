<?php

global $con;

// الحصول على البيانات من الـ AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $valueId = $_POST['value_id'];
    $field = $_POST['field'];  // إما 'name' أو 'price'
    $newValue = $_POST['new_value'];

    // الاتصال بقاعدة البيانات
    try {
        if ($field === 'name') {
            $stmt = $con->prepare("UPDATE product_option_values SET value_name = ? WHERE id = ?");
        } else {
            $stmt = $con->prepare("UPDATE product_option_values SET value_price = ? WHERE id = ?");
        }
        $stmt->execute([$newValue, $valueId]);
        echo "Valeur mise à jour avec succès";
    } catch (Exception $e) {
        echo "Erreur: " . $e->getMessage();
    }
}
?>
