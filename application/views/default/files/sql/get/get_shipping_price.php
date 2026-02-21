<?php
global $con;

$warehouse = intval($_GET['warehouse'] ?? 0);
$city = intval($_GET['city'] ?? 0);
$user = intval($_GET['user'] ?? 0);

// تحقق أن كل القيم الأساسية موجودة
if ($warehouse > 0 && $city > 0 && $user > 0) {

// محاولة جلب تسعيرة خاصة بالمستخدم
$stmt = $con->prepare("SELECT up_delivery FROM user_pricing WHERE up_warehouse = ? AND up_city = ? AND up_user = ?");
$stmt->execute([$warehouse, $city, $user]);
$user_pricing = $stmt->fetch(PDO::FETCH_ASSOC);

// تحقق أن القيمة موجودة وليست فارغة أو صفر
if ($user_pricing && isset($user_pricing['up_delivery']) && floatval($user_pricing['up_delivery']) > 0) {
echo json_encode([
'delivery_type' => 'user',
'up_delivery' => floatval($user_pricing['up_delivery'])
]);
exit;
}
}

// إذا لم توجد قيمة خاصة، نحاول جلب التسعيرة العامة
if ($warehouse > 0 && $city > 0) {
$stmt = $con->prepare("SELECT sc_delivery FROM shipping_charges WHERE sc_warehouse = ? AND sc_city = ?");
$stmt->execute([$warehouse, $city]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

echo json_encode([
'delivery_type' => 'default',
'sc_delivery' => isset($row['sc_delivery']) ? floatval($row['sc_delivery']) : null
]);
} else {
echo json_encode(['sc_delivery' => null]);
}
