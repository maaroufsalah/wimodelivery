<?php 

global $con;

if (SRM("GET")) {

    $id = $_GET["id"] ?? '';
    $state = $_GET["state"] ?? '';

    if (empty($id)) {
        echo "<div class='alert alert-danger'>Veuillez choisir un état</div>";
        exit();
    }

    // تحقق من وجود المطالبة
    $stmt = $con->prepare("SELECT * FROM claim WHERE md5(claim_id) = :id LIMIT 1");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $count = $stmt->rowCount();

    if ($count == 0) {
        echo "<div class='alert alert-warning'>Aucune réclamation trouvée</div>";
        exit();
    }

    // تحديث الحالة
    $update = $con->prepare("UPDATE claim SET claim_state = :state WHERE md5(claim_id) = :id");
    $update->bindParam(':state', $state);
    $update->bindParam(':id', $id);
    $update->execute();

    // إعادة التوجيه
    load_url("claim", 2);
}
?>
