<?php 
global $con;

// الحصول على البيانات من الـ AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $valueId = $_POST['value_id'];

    // الاتصال بقاعدة البيانات
    try {
        $stmt = $con->prepare("DELETE FROM product_option_values WHERE id = ?");
        $stmt->execute([$valueId]);
        echo "Valeur supprimée avec succès";
    } catch (Exception $e) {
        echo "Erreur: " . $e->getMessage();
    }
}
?>
