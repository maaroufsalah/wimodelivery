<?php
global $con;

include get_file("files/sql/get/os_settings");
include get_file("files/sql/get/session");


if (empty($orderIdsRaw)) {
echo "<div class='alert alert-danger'>Choisir des colis</div>";
exit();
}

if (preg_match('/^[0-9,]+$/', $orderIdsRaw)) {
$orderIds = array_filter(array_map('intval', explode(',', $orderIdsRaw)));

if (empty($orderIds)) {
echo "❌ Aucun ID valide trouvé.";
exit();
}

$userId   = $loginId;
$userRank = $loginRank;
$notes = "Mise à jour du stock à partir des commandes : " . implode(',', $orderIds);

try {
$con->beginTransaction();

// 1️⃣ Récupérer les produits depuis les commandes
$placeholders = implode(',', array_fill(0, count($orderIds), '?'));
$sql = "
SELECT order_items.product_id, order_items.quantity, orders.or_id
FROM order_items
INNER JOIN orders ON order_items.order_id = orders.or_id
WHERE orders.or_id IN ($placeholders) AND orders.or_stock = 0
";
$stmt = $con->prepare($sql);
$stmt->execute($orderIds);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (($items>0)) {



// 2️⃣ Mise à jour du stock
foreach ($items as $item) {
$productId = (int) $item['product_id'];
$qtyToAdd  = (int) $item['quantity'];

$oldQtyStmt = $con->prepare("SELECT p_qty FROM products WHERE p_id = :pid");
$oldQtyStmt->execute([':pid' => $productId]);
$oldQty = (int) $oldQtyStmt->fetchColumn();

$newQty = $oldQty + $qtyToAdd;

// Update stock
$updateSql = "UPDATE products SET p_qty = :new_qty WHERE p_id = :pid";
$updateStmt = $con->prepare($updateSql);
$updateStmt->execute([
':new_qty' => $newQty,
':pid'     => $productId
]);

// Insert log
$logSql = "
INSERT INTO stock_log 
(p_id, user_id, change_qty, old_qty, new_qty, operation_type, change_date, notes, rank)
VALUES 
(:p_id, :user_id, :change_qty, :old_qty, :new_qty, :operation_type, NOW(), :notes, :rank)
";
$logStmt = $con->prepare($logSql);
$logStmt->execute([
':p_id'           => $productId,
':user_id'        => $userId,
':change_qty'     => $qtyToAdd,
':old_qty'        => $oldQty,
':new_qty'        => $newQty,
':operation_type' => 'Retour stock',
':notes'          => $notes,
':rank'           => $userRank
]);
}

// 3️⃣ Marquer les commandes comme traitées
$updateOrderSql = "UPDATE orders SET or_stock = 1 WHERE or_id IN ($placeholders)";
$updateOrderStmt = $con->prepare($updateOrderSql);
$updateOrderStmt->execute($orderIds);
}

$con->commit();
echo "✅ Stock mis à jour et opérations enregistrées avec succès.";

} catch (Exception $e) {
$con->rollBack();
echo "❌ Erreur : " . $e->getMessage();
}

} else {
echo "❌ Liste des commandes invalide.";
}
