<?php

global $con;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $groupId = $_POST['group_id'];
    $groupName = $_POST['group_name'];

    // الاتصال بقاعدة البيانات
    try {
        $stmt = $con->prepare("UPDATE product_option_groups SET group_name = ? WHERE id = ?");
        $stmt->execute([$groupName, $groupId]);
        echo "Nom du groupe mis à jour avec succès";
    } catch (Exception $e) {
        echo "Erreur: " . $e->getMessage();
    }
}
?>