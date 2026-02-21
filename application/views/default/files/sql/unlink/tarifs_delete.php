<?php
global $con;
$id = $_POST['id'] ?? 0;
$type = $_POST['type'] ?? '';

$table = '';
$pk = '';

if ($type === 'globale') {
$table = 'shipping_charges';
$pk = 'sc_id';
} elseif ($type === 'user') {
$table = 'user_pricing';
$pk = 'up_id';
} elseif ($type === 'delivery') {
$table = 'pricing';
$pk = 'pr_id';
}

if ($table && $id) {
$stmt = $con->prepare("DELETE FROM $table WHERE $pk = ?");
$stmt->execute([$id]);
echo "Tarif supprimé avec succès.";
} else {
echo "Requête invalide.";
}
