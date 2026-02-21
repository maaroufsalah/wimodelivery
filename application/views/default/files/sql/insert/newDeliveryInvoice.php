<?php 
global $con;

if (SRM("POST")) {

$user = isset($_POST['user']) ? intval($_POST['user']) : 0;
$orderIds = isset($_POST['order_id']) ? $_POST['order_id'] : '';

if ($user == 0 || empty($orderIds)) {
echo "<div class='alert alert-danger my-2'>Veuillez sélectionner au moins une commande.</div>";
exit;
}

// تحويل السلسلة إلى مصفوفة أرقام
$orderIdArray = array_filter(array_map('intval', explode(',', $orderIds)));

if (count($orderIdArray) == 0) {
echo "<div class='alert alert-danger my-2'>Aucune commande valide sélectionnée.</div>";
exit;
}

// جلب المستخدم
$stmt = $con->prepare("SELECT * FROM users WHERE user_id = ? LIMIT 1");
$stmt->execute([$user]);
if ($stmt->rowCount() == 0) {
echo "<div class='alert alert-danger my-2'>Utilisateur introuvable.</div>";
exit;
}
$displayUser = $stmt->fetch();

// جلب تفاصيل الطلبات دفعة واحدة
$inQuery = implode(',', array_fill(0, count($orderIdArray), '?'));
$stmt = $con->prepare("SELECT * FROM orders WHERE or_id IN ($inQuery)");
$stmt->execute($orderIdArray);
$orders = $stmt->fetchAll();

if (count($orders) == 0) {
echo "<div class='alert alert-danger my-2'>Aucune commande trouvée.</div>";
exit;
}

// عرض التفاصيل وإنشاء الفاتورة
foreach ($orders as $order) {

$fee = 0; // تعريف افتراضي

// الحالة
$stmt = $con->prepare("SELECT * FROM state WHERE state_id = ?");
$stmt->execute([$order['or_state_delivery']]);
$state = $stmt->fetch();

// المخزن
$stmt = $con->prepare("SELECT * FROM warehouse WHERE wh_id = ?");
$stmt->execute([$order['or_warehouse']]);
$warehouse = $stmt->fetch();

// المدينة
$stmt = $con->prepare("SELECT * FROM city WHERE city_id = ?");
$stmt->execute([$order['or_city']]);
$city = $stmt->fetch();

// التحقق من رسوم المستخدم
$stmt = $con->prepare("SELECT * FROM pricing WHERE pr_unlink = 0 AND pr_warehouse = ? AND pr_city = ? AND pr_user_delivery = ?");
$stmt->execute([$order['or_warehouse'], $order['or_city'], $user]);
if ($stmt->rowCount() > 0) {
$pricing = $stmt->fetch();
switch ($order['or_state_delivery']) {
case 1: $fee = $pricing['pr_delivery']; break;
case 60 : $fee = $pricing['pr_delivery']; break;
case 4: $fee = $pricing['pr_return']; break;
case 3: $fee = $pricing['pr_cancel']; break;
}
}else{
print "
<div class='alert alert-info'>
Il n'y a pas de frais de livraison entre <b>{$warehouse['wh_name']}</b> et <b>{$city['city_name']}</b>.
<a class='btn btn-white' href='pricing?do=delivery' target='_blank'>Ajouter Tarif</a>
</div>
";
exit();
}

echo "
<div class='alert alert-info my-2'>
Colis N° : " . $order['or_id'] . " <br>
Entrepôt : " . ($warehouse['wh_name'] ?? '-') . " <i class='fa-solid fa-arrow-right'></i> Ville : " . ($city['city_name'] ?? '-') . "<br> 
Frais de livraison (" . ($state['state_name'] ?? '-') . ") : " . $fee . "
</div>";
}

// إنشاء الفاتورة
$stmt = $con->prepare("INSERT INTO delivery_invoice (d_in_gid, d_in_user, d_in_date) VALUES (?, ?, ?)");
$stmt->execute([implode(',', $orderIdArray), $user, date('Y-m-d H:i:s')]);
$invoiceId = $con->lastInsertId();

// إنشاء تفاصيل الفاتورة وربط الطلبات
foreach ($orders as $order) {

$fee = 0;

// الحالة
$stmt = $con->prepare("SELECT * FROM state WHERE state_id = ?");
$stmt->execute([$order['or_state_delivery']]);
$state = $stmt->fetch();

// الرسوم
$stmt = $con->prepare("SELECT * FROM pricing WHERE pr_unlink = 0 AND pr_warehouse = ? AND pr_city = ? AND pr_user_delivery = ?");
$stmt->execute([$order['or_warehouse'], $order['or_city'], $user]);
if ($stmt->rowCount() > 0) {
$pricing = $stmt->fetch();
switch ($order['or_state_delivery']) {
case 1: $fee = $pricing['pr_delivery']; break;
case 60: $fee = $pricing['pr_delivery']; break;
case 4: $fee = $pricing['pr_return']; break;
case 3: $fee = $pricing['pr_cancel']; break;
}
}

// حفظ تفاصيل الفاتورة
$stmt = $con->prepare("INSERT INTO delivery_invoice_script (
dis_order, dis_warehouse, dis_city, dis_state, dis_fee, dis_invoice
) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->execute([
$order['or_id'],
$order['or_warehouse'],
$order['or_city'],
$order['or_state_delivery'],
$fee,
$invoiceId
]);

// تحديث الطلب وربطه بالفاتورة
$stmt = $con->prepare("UPDATE orders SET or_delivery_invoice = ? WHERE or_id = ?");
$stmt->execute([$invoiceId, $order['or_id']]);
}





// حساب in_fee من جدول invoice_script
$stmt = $con->prepare("
SELECT 
SUM(dis_fee) 
FROM delivery_invoice_script 
WHERE dis_invoice= ?
");
$stmt->execute([$invoiceId]);
$totals = $stmt->fetchColumn();

$inFee = $totals;


// تحديث جدول الفاتورة
$update = $con->prepare("UPDATE delivery_invoice SET d_in_total = ? WHERE d_in_id = ?");
$update->execute([$inFee, $invoiceId]);




echo "<div class='alert alert-success my-2'>La facture a été créée avec succès!</div>";

$con = null;

if (function_exists('load_url')) {
load_url("deliveryInvoice", 2);
}



}
?>
