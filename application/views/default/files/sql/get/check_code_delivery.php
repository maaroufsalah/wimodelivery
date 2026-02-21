<?php 

include get_file("files/sql/get/os_settings");
include get_file("files/sql/get/session");

global $con;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['code'])) {
    $code  = trim($_POST['code']);
    $delivery = trim($_POST['state']);
    $date  = date("Y-m-d H:i:s");

    if ($code === "" || !ctype_digit($code)) {
        echo "❌ Code invalide.";
        exit;
    }

    if ($delivery === "") {
        echo "❌ Statut vide.";
        exit;
    }

    // Recherche de la commande
    $stmt = $con->prepare("SELECT * FROM orders WHERE or_id = ? AND or_unlink = '0'");
    $stmt->execute([$code]);
    $data = $stmt->fetch();

    if ($data) {
        $oWarehouse = $data['or_warehouse'];
        $oCity = $data['or_city'];

        // Vérifier pricing
        $sql = "SELECT COUNT(*) 
                FROM pricing 
                WHERE pr_user_delivery = ? 
                  AND pr_warehouse = ? 
                  AND pr_city = ? 
                  AND pr_unlink = 0";
        $stmt = $con->prepare($sql);
        $stmt->execute([$delivery, $oWarehouse, $oCity]);
        $allowed = $stmt->fetchColumn();

        if ($allowed > 0) {
            // Mise à jour du livreur
            $sql = "UPDATE orders SET or_delivery_user = ? WHERE or_id = ?";
            $params = [$delivery, $code];
            $update = $con->prepare($sql);
            $update->execute($params);

            // Son + message succès
            echo "<audio id='audio2' preload='auto' src='uploads/scan.mp3' oncanplaythrough='this.play();'></audio>";
            echo "<div class='alert alert-success'>✅ Scanné et livreur modifié avec succès (colis : <b>$code</b>)</div>";
        } else {
            echo "<div class='alert alert-danger'>❌ Hors zone du livreur.</div>";
        }
    } else {
        echo "<div class='alert alert-danger'>❌ Colis introuvable dans la base.</div>";
    }
}

$con = null;
?>
