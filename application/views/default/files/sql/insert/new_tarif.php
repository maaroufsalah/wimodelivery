<?php

global $con;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $warehouse = trim($_POST['warehouse'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $delivery = floatval($_POST['delivery'] ?? 0);
    $cancel = floatval($_POST['cancel'] ?? 0);
    $return = floatval($_POST['return'] ?? 0);

    if ($warehouse === '' || $city === '') {
        http_response_code(400);
        echo "<div class='alert alert-danger'>Veuillez remplir tous les champs requis (Ville et Dépôt).</div>";
        exit;
    }

    try {
        $con->beginTransaction();

        // التحقق من وجود سجل بنفس المدينة والمخزن
        $check = $con->prepare("SELECT sc_id FROM shipping_charges 
            WHERE sc_city = ? AND sc_warehouse = ? FOR UPDATE");
        $check->execute([$city, $warehouse]);

        if ($check->rowCount() > 0) {
            $con->rollBack();
            http_response_code(409);
            echo "<div class='alert alert-warning'>Cette combinaison (Ville + Dépôt) existe déjà.</div>";
            exit;
        }

        // إدخال البيانات
        $stmt = $con->prepare("INSERT INTO shipping_charges 
            (sc_warehouse, sc_city, sc_city_name, sc_delivery, sc_cancel, sc_return) 
            VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$warehouse, $city, $city, $delivery, $cancel, $return]);

        $con->commit();

        echo "<div class='alert alert-success'>Ajout effectué avec succès.</div>";

    } catch (PDOException $e) {
        $con->rollBack();
        http_response_code(500);
        echo "<div class='alert alert-danger'>Erreur: " . htmlspecialchars($e->getMessage()) . "</div>";
    }

} else {
    http_response_code(405);
    echo "<div class='alert alert-danger'>Méthode non autorisée.</div>";
}
