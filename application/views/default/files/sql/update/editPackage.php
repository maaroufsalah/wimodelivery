<?php 
global $con;

// التحقق من أن الطلب هو POST باستخدام دالة SRM
if (SRM("POST")) {

// جلب البيانات من الفورم باستخدام دالة POST
$order_id = POST("order_id", 0, 'int'); // إضافة معرّف الطلب
$warehouse = POST("warehouse", 0, 'int');
$user = POST("user", 0, 'int');
$fragile = POST("fragile", 0, 'int');
$try = POST("try", 0, 'int');
$open = POST("open", 0, 'int');
$change = POST("change", 0, 'int');
$price = POST("price", 0.0, 'float');
$city = POST("city", 0,'int');
$name = POST("name");
$phone = POST("phone");
$location = POST("location");
$note = POST("note");
$box = POST("box");
$pickup = POST("pickup");
$item = POST("item");
$qty = POST("qty");



if ($box > 0) {
$stmt_box = $con->prepare("SELECT box_price FROM box WHERE box_id = :id");
$stmt_box->execute([':id' => $box]);
$box_data = $stmt_box->fetch(PDO::FETCH_ASSOC);

if ($box_data) {
$box_price = $box_data['box_price'];
} else {
echo "<div class='alert alert-danger'>Box introuvable</div>";
exit();
}
} else{
$box_price = 0;
}


// التحقق من الحقول الإلزامية
if (!$order_id || !$warehouse || !$user || empty($city) || empty($name) || empty($phone) || empty($location)) {
echo "<div class='alert alert-danger'>Veuillez remplir tous les champs obligatoires (*)</div>";
exit();
}
// تحضير الاستعلام لتحديث البيانات
$stmt = $con->prepare("
UPDATE orders
SET
or_warehouse = :or_warehouse,
or_trade = :or_trade,
or_fragile = :or_fragile,
or_try = :or_try,
or_open_package = :or_open_package,
or_change = :or_change,
or_total = :or_total,
or_city = :or_city,
or_name = :or_name,
or_phone = :or_phone,
or_address = :or_shipped,
or_note = :or_note,
or_box = :or_box,
or_box_price = :or_box_price,
or_pickup_date = :or_pickup_date,
or_item = :or_item,
or_qty = :or_qty
WHERE 

or_id = :order_id
");

// ربط القيم
$stmt->bindParam(':or_warehouse', $warehouse, PDO::PARAM_INT);
$stmt->bindParam(':or_trade', $user, PDO::PARAM_INT);
$stmt->bindParam(':or_fragile', $fragile, PDO::PARAM_INT);
$stmt->bindParam(':or_try', $try, PDO::PARAM_INT);
$stmt->bindParam(':or_open_package', $open, PDO::PARAM_INT);
$stmt->bindParam(':or_change', $change, PDO::PARAM_INT);
$stmt->bindParam(':or_total', $price, PDO::PARAM_STR); // تأكد أن هذه القيمة هي السعر الكلي
$stmt->bindParam(':or_city', $city, PDO::PARAM_INT);
$stmt->bindParam(':or_name', $name, PDO::PARAM_STR);
$stmt->bindParam(':or_phone', $phone, PDO::PARAM_STR);
$stmt->bindParam(':or_shipped', $location, PDO::PARAM_STR); // تأكد من أن "location" هي المكان الصحيح للشحن
$stmt->bindParam(':or_note', $note, PDO::PARAM_STR);
$stmt->bindParam(':or_box', $box, PDO::PARAM_STR);
$stmt->bindParam(':or_box_price', $box_price, PDO::PARAM_STR); // إذا كان السعر الخاص بالعلبة يختلف عن total، يجب استخدام قيمة منفصلة
$stmt->bindParam(':or_pickup_date', $pickup, PDO::PARAM_STR);
$stmt->bindParam(':or_item', $item, PDO::PARAM_STR);
$stmt->bindParam(':or_qty', $qty, PDO::PARAM_INT);
$stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT); // ربط معرّف الطلب

// تنفيذ الاستعلام
if ($stmt->execute()) {
echo "<div class='alert alert-success'>Mise à jour réussie</div>";
load_url("packages", 2); // إعادة توجيه المستخدم
exit();
} else {
echo "<div class='alert alert-danger'>Erreur de mise à jour</div>";
}

// إغلاق الاتصال
$stmt = null;
$con = null;
}
?>