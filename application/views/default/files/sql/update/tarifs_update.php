<?php

global $con;

$do = isset ($_GET['do']) ? $_GET ['do'] : 'Manage' ;
if ($do == 'Manage'){











if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id = intval($_POST['id'] ?? 0);
    $delivery = floatval($_POST['delivery'] ?? 0);
    $cancel = floatval($_POST['cancel'] ?? 0);
    $return = floatval($_POST['return'] ?? 0);

    if ($id <= 0) {
        http_response_code(400);
        echo "<div class='alert alert-danger'>ID invalide.</div>";
        exit;
    }

    try {
        // Démarrer une transaction
        $con->beginTransaction();

        // Verrouiller la ligne avant mise à jour
        $check = $con->prepare("SELECT sc_id FROM shipping_charges WHERE sc_id = ? FOR UPDATE");
        $check->execute([$id]);

        if ($check->rowCount() === 0) {
            $con->rollBack();
            http_response_code(404);
            echo "<div class='alert alert-warning'>Enregistrement non trouvé.</div>";
            exit;
        }

        // Mise à jour
        $stmt = $con->prepare("UPDATE shipping_charges SET sc_delivery = ?, sc_cancel = ?, sc_return = ? WHERE sc_id = ?");
        $stmt->execute([$delivery, $cancel, $return, $id]);

        // Confirmer la transaction
        $con->commit();

        echo "<div class='alert alert-success'>Mise à jour réussie.</div>";

    } catch (PDOException $e) {
        $con->rollBack();
        http_response_code(500);
        echo "<div class='alert alert-danger'>Erreur: " . htmlspecialchars($e->getMessage()) . "</div>";
    }

} else {
    http_response_code(405);
    echo "<div class='alert alert-danger'>Méthode non autorisée.</div>";
}




























}elseif($do == "delivery"){
















if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id = intval($_POST['id'] ?? 0);
    $delivery = floatval($_POST['delivery'] ?? 0);
    $cancel = floatval($_POST['cancel'] ?? 0);
    $return = floatval($_POST['return'] ?? 0);

    if ($id <= 0) {
        http_response_code(400);
        echo "<div class='alert alert-danger'>ID invalide.</div>";
        exit;
    }

    try {
        // Démarrer une transaction
        $con->beginTransaction();

        // Verrouiller la ligne avant mise à jour
        $check = $con->prepare("SELECT pr_id FROM pricing WHERE pr_id = ? FOR UPDATE");
        $check->execute([$id]);

        if ($check->rowCount() === 0) {
            $con->rollBack();
            http_response_code(404);
            echo "<div class='alert alert-warning'>Enregistrement non trouvé.</div>";
            exit;
        }

        // Mise à jour
        $stmt = $con->prepare("UPDATE pricing SET pr_delivery = ?, pr_cancel = ?, pr_return = ? WHERE pr_id = ?");
        $stmt->execute([$delivery, $cancel, $return, $id]);

        // Confirmer la transaction
        $con->commit();

        echo "<div class='alert alert-success'>Mise à jour réussie.</div>";

    } catch (PDOException $e) {
        $con->rollBack();
        http_response_code(500);
        echo "<div class='alert alert-danger'>Erreur: " . htmlspecialchars($e->getMessage()) . "</div>";
    }

} else {
    http_response_code(405);
    echo "<div class='alert alert-danger'>Méthode non autorisée.</div>";
}




















}elseif($do == "user"){



}else{



}
