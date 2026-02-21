<?php
global $con;
session_start();

require_once("Classes/PHPMailer-master/src/PHPMailer.php");
require_once("Classes/PHPMailer-master/src/SMTP.php");
require_once("Classes/PHPMailer-master/src/Exception.php");

include get_file("files/sql/get/os_settings");
include get_file("files/sql/get/functions");

// 🛒 تحميل السلة
$cart = isset($_COOKIE['cart']) ? json_decode($_COOKIE['cart'], true) : [];

// ✅ التحقق من أن السلة غير فارغة
if (empty($cart)) {
    echo "<div class='alert alert-danger'>Votre panier est vide</div>";
    exit();
}

// استقبال البيانات من الفورم
$warehouse = intval($_POST["warehouse"] ?? 0);
$user      = intval($_POST["user"] ?? 0);
$fragile   = intval($_POST["fragile"] ?? 0);
$try       = intval($_POST["try"] ?? 0);
$open      = intval($_POST["open"] ?? 0);
$change    = intval($_POST["change"] ?? 0);
$price     = POST("total", 0.0, 'float');
$city      = trim($_POST["city"] ?? "");
$name      = trim($_POST["name"] ?? "");
$phone     = trim($_POST["phone"] ?? "");
$location  = trim($_POST["location"] ?? "");
$note      = trim($_POST["note"] ?? "");
$box       = intval($_POST["box"] ?? 0);
$pickup    = trim($_POST["pickup"] ?? "");
$order_created = date('Y-m-d H:i:s');

// ✅ تحقق الحقول الأساسية
if (!$warehouse || !$user || empty($city) || empty($name) || empty($phone) || empty($location)) {
    echo "<div class='alert alert-danger'>Veuillez remplir tous les champs obligatoires (*)</div>";
    exit();
}

if (!preg_match('/^[0-9]{9,15}$/', $phone)) {
    echo "<div class='alert alert-danger'>Numéro de téléphone invalide</div>";
    exit();
}

// ✅ التحقق من وجود الـ Box
if ($box > 0) {
    $stmt_box = $con->prepare("SELECT box_price FROM box WHERE box_id = :id");
    $stmt_box->execute([':id' => $box]);
    $box_data = $stmt_box->fetch(PDO::FETCH_ASSOC);
    if ($box_data) {
        $box_price = floatval($box_data['box_price']);
    } else {
        echo "<div class='alert alert-danger'>Box introuvable</div>";
        exit();
    }
} else {
    $box_price = 0;
}

// ✅ التحقق من توفر الكمية لكل منتج قبل أي إدخال
foreach ($cart as $item) {
    $product_id = intval($item['product_id'] ?? 0);
    $quantity   = intval($item['quantity'] ?? 0);

    if ($product_id <= 0 || $quantity <= 0) continue;

    $stmt_check = $con->prepare("SELECT p_name, p_qty FROM products WHERE p_id = ?");
    $stmt_check->execute([$product_id]);
    $prod = $stmt_check->fetch(PDO::FETCH_ASSOC);

    if (!$prod) {
        echo "<div class='alert alert-danger'>Produit introuvable (ID: $product_id)</div>";
        exit();
    }

    if ($prod['p_qty'] < $quantity) {
        echo "<div class='alert alert-danger'>Stock insuffisant pour le produit <strong>" . htmlspecialchars($prod['p_name']) . "</strong> (Disponible: " . $prod['p_qty'] . ")</div>";
        exit();
    }
}

// ✅ بدء المعاملة Transaction
try {
    $con->beginTransaction();

    // ✅ إدخال الطلب الرئيسي
    $stmt = $con->prepare("INSERT INTO orders (
        or_warehouse, or_trade, or_fragile, or_try, or_open_package, or_change,
        or_total, or_city, or_name, or_phone, or_address, or_note,
        or_box, or_box_price, or_created
    ) VALUES (
        :or_warehouse, :or_trade, :or_fragile, :or_try, :or_open_package, :or_change,
        :or_total, :or_city, :or_name, :or_phone, :or_address, :or_note,
        :or_box, :or_box_price, :or_created
    )");

    $stmt->bindParam(':or_warehouse', $warehouse, PDO::PARAM_INT);
    $stmt->bindParam(':or_trade', $user, PDO::PARAM_INT);
    $stmt->bindParam(':or_fragile', $fragile, PDO::PARAM_INT);
    $stmt->bindParam(':or_try', $try, PDO::PARAM_INT);
    $stmt->bindParam(':or_open_package', $open, PDO::PARAM_INT);
    $stmt->bindParam(':or_change', $change, PDO::PARAM_INT);
    $stmt->bindParam(':or_total', $price, PDO::PARAM_STR);
    $stmt->bindParam(':or_city', $city);
    $stmt->bindParam(':or_name', $name);
    $stmt->bindParam(':or_phone', $phone);
    $stmt->bindParam(':or_address', $location);
    $stmt->bindParam(':or_note', $note);
    $stmt->bindParam(':or_box', $box, PDO::PARAM_INT);
    $stmt->bindParam(':or_box_price', $box_price);
    $stmt->bindParam(':or_created', $order_created);
    $stmt->execute();

    $order_id = $con->lastInsertId();

    // ✅ إضافة عناصر السلة + تحديث المخزون + log
    foreach ($cart as $item) {
        $product_id   = $item['product_id'];
        $product_name = $item['product_name'];
        $quantity     = $item['quantity'];
        $unit_price   = $item['base_price'];
        $total_price  = $item['final_price'];

        // إدخال العنصر
        $stmt_item = $con->prepare("INSERT INTO order_items 
            (order_id, product_id, product_name, quantity, unit_price, total_price)
            VALUES (?, ?, ?, ?, ?, ?)");
        $stmt_item->execute([$order_id, $product_id, $product_name, $quantity, $unit_price, $total_price]);
        $item_id = $con->lastInsertId();

        // خيارات المنتج
        if (!empty($item['options'])) {
            foreach ($item['options'] as $opt) {
                $option_name  = $opt['name'];
                $option_value = $opt['value_name'];
                $option_price = $opt['value_price'];

                $stmt_opt = $con->prepare("INSERT INTO order_item_options 
                    (item_id, option_name, option_value, option_price)
                    VALUES (?, ?, ?, ?)");
                $stmt_opt->execute([$item_id, $option_name, $option_value, $option_price]);
            }
        }

        // ✅ تحديث الكمية في المخزون
        $stmt_qty = $con->prepare("SELECT p_qty FROM products WHERE p_id = ?");
        $stmt_qty->execute([$product_id]);
        $old_qty = (int)$stmt_qty->fetchColumn();
        $new_qty = max(0, $old_qty - $quantity);

        $stmt_update = $con->prepare("UPDATE products SET p_qty = ? WHERE p_id = ?");
        $stmt_update->execute([$new_qty, $product_id]);

        // ✅ تسجيل log المخزون
        $stmt_log = $con->prepare("INSERT INTO stock_log 
            (p_id, user_id, change_qty, old_qty, new_qty, operation_type, rank, change_date)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt_log->execute([
            $product_id,
            $user,
            -$quantity,
            $old_qty,
            $new_qty,
            'decrease',
            'user'
        ]);
    }

    // ✅ إنهاء المعاملة
    $con->commit();

    // ✅ تنظيف السلة
    setcookie('cart', '', time() - 3600, "/");
    unset($_COOKIE['cart']);

    echo "<div class='alert alert-success'>Colis enregistrée avec succès !</div>";
    if (function_exists('load_url')) {
        load_url("packages?state=int", 2);
    }

} catch (Exception $e) {
    $con->rollBack();
    echo "<div class='alert alert-danger'>Erreur: " . htmlspecialchars($e->getMessage()) . "</div>";
}
?>
