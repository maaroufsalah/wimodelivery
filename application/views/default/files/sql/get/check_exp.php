<?php 

include get_file("files/sql/get/os_settings");
include get_file("files/sql/get/session");
include get_file("files/sql/get/functions");

global $con;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['code'])) {
    $code = trim($_POST['code']);
    $exp  = trim($_POST['exp']);
    $date = date("Y-m-d H:i:s");

    if (empty($code)) {
        echo "❌ Code vide.";
        exit;
    }

    if (empty($exp)) {
        echo "❌ EXP vide.";
        exit;
    }

    // Recherche de la commande
    $stmt = $con->prepare("SELECT * FROM expeditions WHERE expedition_id = ? LIMIT 1");
    $stmt->execute([$exp]);
    $data = $stmt->fetch();

    if ($data) {
        // Vérifier si le colis existe dans l’expédition
        $checkColis = $con->prepare("SELECT COUNT(*) FROM expedition_colis WHERE colis_id = ? AND expedition_id = ?");
        $checkColis->execute([$code, $data['expedition_id']]);
        if ($checkColis->fetchColumn() == 0) {
            echo "<div class='alert alert-danger'>❌ Colis non trouvé dans cette expédition.</div>";
            exit;
        }

        // Mise à jour du scan
        $sql = "UPDATE expedition_colis SET scan = ? WHERE colis_id = ? AND expedition_id = ?";
        $params = [1, $code, $data['expedition_id']];
        $update = $con->prepare($sql);
        $update->execute($params);

        if ($update->rowCount()) {
            // Récupérer les statistiques après la mise à jour
            $stmt = $con->prepare("SELECT COUNT(*) FROM expedition_colis WHERE expedition_id = ?");
            $stmt->execute([$data['expedition_id']]);
            $count_exp_total = $stmt->fetchColumn();

            $stmt = $con->prepare("SELECT COUNT(*) FROM expedition_colis WHERE scan = 1 AND expedition_id = ?");
            $stmt->execute([$data['expedition_id']]);
            $count_exp = $stmt->fetchColumn();

            echo "(" . $count_exp . "/" . $count_exp_total . ")";
            echo "<div class='alert alert-success'>✅ Scanné avec succès (colis : <b>$code</b>)</div>";
        } else {
            echo "<div class='alert alert-warning'>⚠️ Ce colis est peut-être déjà scanné ou introuvable.</div>";
        }

    } else {
        echo "<div class='alert alert-danger'>❌ Expédition introuvable dans la base.</div>";
    }
}

$con = null;
?>
