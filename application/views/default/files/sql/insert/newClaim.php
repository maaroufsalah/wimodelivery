<?php 
global $con;

if (SRM("POST")) {

    $order = POST("orderId"); // md5
    $name  = POST("name");
    $note  = POST("note");

    // تحقق من القيم
    if (empty($order) ||  empty($note)) {
        echo "<div class='alert alert-danger'>Veuillez remplir tous les champs obligatoires (*)</div>";
        exit();
    }

    // التحقق من الطلب عبر md5
    $stmt = $con->prepare("SELECT * FROM orders WHERE md5(or_id) = ? LIMIT 1");
    $stmt->execute([$order]);
    $orders = $stmt->fetch();

    if (!$orders) {
        echo "<div class='alert alert-danger'>Commande introuvable</div>";
        exit();
    }

    // تحضير إدخال الشكوى
    $stmt = $con->prepare("
        INSERT INTO claim (
            claim_date, claim_orders, claim_user, claim_state, claim_name, claim_note
        ) VALUES (
            ?, ?, ?, 0, ?, ?
        )
    ");

    $now = date("Y-m-d H:i:s");
    $orderId = $orders['or_id'];
    $order_user = $orders['or_trade'];

    if ($stmt->execute([$now, $orderId, $order_user, $name, $note])) {
        echo "<div class='alert alert-success'>Ajouté avec succès</div>";
        if (function_exists('load_url')) {
            load_url("claim", 2);
        }
    } else {
        echo "<div class='alert alert-danger'>Erreur lors de l'insertion</div>";
    }

    // إغلاق
    $stmt = null;
    $con  = null;
}
?>
