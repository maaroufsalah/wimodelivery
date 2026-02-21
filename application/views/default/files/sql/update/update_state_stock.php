<?php


global $con; 


if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id    = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $state = isset($_POST['state']) ? (int)$_POST['state'] : 0;

    if ($id > 0) {
        $stmt = $con->prepare("UPDATE products SET p_state = :state WHERE p_id = :id");
        $stmt->bindValue(":state", $state, PDO::PARAM_INT);
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        echo $stmt->execute() ? "success" : "error";

    }
}

?>




