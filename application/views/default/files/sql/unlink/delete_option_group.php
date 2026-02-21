<?php 
global $con;

// الحصول على البيانات من الـ AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $groupId = $_POST['group_id'];

    // الاتصال بقاعدة البيانات
    try {

        $stmt = $con->prepare("DELETE FROM product_option_groups WHERE id = ?");
        $stmt->execute([$groupId]);

        $stmt = $con->prepare("DELETE FROM product_option_values WHERE group_id = ?");
        $stmt->execute([$groupId]);



        echo "Groupe supprimé avec succès";


    } catch (Exception $e) {
        echo "Erreur: " . $e->getMessage();
    }
}


?>