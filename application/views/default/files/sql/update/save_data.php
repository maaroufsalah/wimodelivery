<?php
global $con;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
$rowIndex = isset($_POST['rowIndex']) ? intval($_POST['rowIndex']) : null;
$warehouse = isset($_POST['warehouse']) ? intval($_POST['warehouse']) : 0;
$user = isset($_POST['user']) ? intval($_POST['user']) : 0;

if ($rowIndex !== null && isset($_POST['data'][$rowIndex])) {
$data = $_POST['data'][$rowIndex];

// استخراج القيم من الصف
$col1 = isset($data[0]) ? trim($data[0]) : '';
$col2 = isset($data[1]) ? trim($data[1]) : '';
$col3 = isset($data[2]) ? trim($data[2]) : '';
$col4 = isset($data[3]) ? trim($data[3]) : '';
$col5 = isset($data[4]) ? trim($data[4]) : '';
$col6 = isset($data[5]) ? trim($data[5]) : '';
$col7 = isset($data[6]) ? intval($data[6]) : 0;
$col8 = isset($data[7]) ? intval($data[7]) : 1;
$col9 = isset($data[8]) ? trim($data[8]) : '';
$col10 = isset($data[9]) ? trim($data[9]) : '';
$col11 = isset($data[10]) ? trim($data[10]) : '';


// هنا يجب تعديل رقم العمود حسب مكان code_colis في الإكسل، مثلاً 11 إذا هو العمود 12:
$change_code = isset($_POST['code_colis'][$rowIndex]) ? trim($_POST['code_colis'][$rowIndex]) : '';



// التحقق من وجود الطرد إذا كان change_code > 0 أو غير فارغ (حسب نوع البيانات)
if (!empty($change_code)) {
$colis = $con->prepare("SELECT * FROM orders WHERE or_id = :id AND or_trade = :user");
$colis->execute([':id' => $change_code, ':user' => $user]);
$colis_data = $colis->fetch(PDO::FETCH_ASSOC);

if (!$colis_data) {
echo "<div class='alert alert-danger'>Colis introuvable</div>";
exit();
}
}

// بيانات الطلب
$name = $col1;
$city = $col2;
$price = $col3;
$phone = $col4;
$location = $col5;
$note = $col6;
$product_id = $col7;
$product_qty = $col8;

// خيارات إضافية
$change = (strtolower($col9) === 'oui') ? 1 : 0;
$try = (strtolower($col10) === 'oui') ? 1 : 0;
$open = (strtolower($col11) === 'oui') ? 1 : 0;

$order_created = date('Y-m-d H:i:s');

// تحقق من الحقول الأساسية
if (!$warehouse || !$user || empty($city) || empty($name) || empty($phone) || empty($location)) {
echo "<div class='alert alert-danger'>Veuillez remplir tous les champs obligatoires (*)</div>";
exit();
}

// تحقق من رقم الهاتف
if (!preg_match('/^[0-9]{9,15}$/', $phone)) {
echo "<div class='alert alert-danger'>Numéro de téléphone invalide</div>";
exit();
}

try {
// إدخال الطلب
$stmt = $con->prepare("INSERT INTO orders (
or_warehouse, or_trade, or_change, or_try, or_open_package,
or_total, or_city, or_name, or_phone, or_address, or_note, or_created, or_change_code
) VALUES (
:or_warehouse, :or_trade, :or_change, :or_try, :or_open_package,
:or_total, :or_city, :or_name, :or_phone, :or_address, :or_note, :or_created, :or_change_code
)");

$stmt->bindParam(':or_warehouse', $warehouse, PDO::PARAM_INT);
$stmt->bindParam(':or_trade', $user, PDO::PARAM_INT);
$stmt->bindParam(':or_change', $change, PDO::PARAM_INT);
$stmt->bindParam(':or_try', $try, PDO::PARAM_INT);
$stmt->bindParam(':or_open_package', $open, PDO::PARAM_INT);
$stmt->bindParam(':or_total', $price, PDO::PARAM_STR);
$stmt->bindParam(':or_city', $city);
$stmt->bindParam(':or_name', $name);
$stmt->bindParam(':or_phone', $phone);
$stmt->bindParam(':or_address', $location);
$stmt->bindParam(':or_note', $note);
$stmt->bindParam(':or_created', $order_created);
$stmt->bindParam(':or_change_code', $change_code);

if ($stmt->execute()) {
$order_id = $con->lastInsertId();

// إدخال تفاصيل المنتج
if (!empty($product_id)) {
$stmt = $con->prepare("SELECT * FROM products WHERE p_id = :pid LIMIT 1");
$stmt->bindParam(':pid', $product_id, PDO::PARAM_INT);
$stmt->execute();
$item = $stmt->fetch(PDO::FETCH_ASSOC);

if ($item) {
$product_name = $item['p_name'];
$unit_price = $item['p_sell'];
$quantity = $product_qty;
$total_price = $unit_price * $quantity;

$stmt_item = $con->prepare("INSERT INTO order_items (order_id, product_id, product_name, quantity, unit_price, total_price)
VALUES (?, ?, ?, ?, ?, ?)");
$stmt_item->execute([$order_id, $product_id, $product_name, $quantity, $unit_price, $total_price]);
}
}


echo "<div class='alert alert-success'>Colis enregistré avec succès !</div>";

print "
<script>
$('.f_{$rowIndex}').remove();
</script>
";
} else {
echo "<div class='alert alert-danger'>Échec de l'enregistrement de la commande</div>";
}

} catch (PDOException $e) {
echo "<div class='alert alert-danger'>Erreur: " . $e->getMessage() . "</div>";
}
} else {
echo "<div class='alert alert-warning'>Aucune donnée trouvée pour cette ligne</div>";
}
} else {
echo "<div class='alert alert-danger'>Méthode non autorisée</div>";
}
?>
