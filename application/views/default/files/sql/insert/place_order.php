<?php
session_start();
require_once("Classes/PHPMailer-master/src/PHPMailer.php");
require_once("Classes/PHPMailer-master/src/SMTP.php");
require_once("Classes/PHPMailer-master/src/Exception.php");
include get_file("files/sql/get/os_settings");
include get_file("files/sql/get/functions");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

global $con;

// 1. الحصول على البريد الإلكتروني
$user_email = $_POST['email'] ?? null;
if (!$user_email || !filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
    exit("L'adresse e-mail est invalide.");
}

// 2. التحقق أو إنشاء حساب المستخدم
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} else {
    $stmt = $con->prepare("SELECT user_id, user_name FROM users WHERE user_email = ?");
    $stmt->execute([$user_email]);
    $existing_user = $stmt->fetch();

    if ($existing_user) {
        $user_id = $existing_user['user_id'];
        $user_name = $existing_user['user_name'];
    } else {
        $user_name = $_POST['last-name'] . " " . $_POST['first-name'];
        $random_pass = bin2hex(random_bytes(4));
        $hashed_pass = password_hash($random_pass, PASSWORD_DEFAULT);

        $stmt = $con->prepare("INSERT INTO users (user_name, user_email, user_pass) VALUES (?, ?, ?)");
        $stmt->execute([$user_name, $user_email, $hashed_pass]);
        $user_id = $con->lastInsertId();

        send_welcome_email($user_email, $user_name, $random_pass);
    }
}

// 3. استرجاع السلة
$cart = isset($_COOKIE['cart']) ? json_decode($_COOKIE['cart'], true) : [];

// 4. إعدادات الطلب
$order_total = array_sum(array_column($cart, 'final_price'));
$order_phone = $_POST['phone'] ?? '';
$order_address = $_POST['address'] ?? '';
$order_note = $_POST['note'] ?? '';
$order_city = $_POST['city'] ?? '';
$order_country = $_POST['country'] ?? '';
$order_gateway = $_POST['payment'] ?? '';
$order_created = date('Y-m-d H:i:s');

// 5. حفظ الطلب
$stmt = $con->prepare("INSERT INTO orders (or_owner, or_total, or_phone, or_address, or_note, or_city, or_country, or_gateway, or_name, or_created) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->execute([$user_id, $order_total, $order_phone, $order_address, $order_note, $order_city, $order_country, $order_gateway, $user_name, $order_created]);
$order_id = $con->lastInsertId();

// 6. حفظ المنتجات والخيارات
$items_html = "";
foreach ($cart as $item) {
    $product_id = $item['product_id'];
    $product_name = $item['product_name'];
    $quantity = $item['quantity'];
    $unit_price = $item['base_price'];
    $total_price = $item['final_price'];

    $stmt = $con->prepare("INSERT INTO order_items (order_id, product_id, product_name, quantity, unit_price, total_price) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$order_id, $product_id, $product_name, $quantity, $unit_price, $total_price]);
    $item_id = $con->lastInsertId();

    // تنسيق السطر في الإيميل
    $options_list = "";
    if (!empty($item['options'])) {
        foreach ($item['options'] as $opt) {
            $option_name = $opt['name'];
            $option_value = $opt['value_name'];
            $option_price = $opt['value_price'];
            $options_list .= "<li>{$option_name}: {$option_value} (+{$option_price} Dhs)</li>";

            $stmt = $con->prepare("INSERT INTO order_item_options (item_id, option_name, option_value, option_price) VALUES (?, ?, ?, ?)");
            $stmt->execute([$item_id, $option_name, $option_value, $option_price]);
        }
    }

    $items_html .= "
        <tr>
            <td>{$product_name}</td>
            <td>{$quantity}</td>
            <td>{$unit_price} Dhs</td>
            <td>{$total_price} Dhs</td>
            <td><ul>{$options_list}</ul></td>
        </tr>";
}

// 7. إرسال بريد تأكيد
send_confirmation_email($user_email, [
    'order_id' => $order_id,
    'order_total' => $order_total,
    'order_address' => $order_address,
    'items_html' => $items_html
]);

// 8. مسح السلة
setcookie('cart', json_encode([]), time() - 3600, "/");

echo "Commande enregistrée avec succès !";
if (function_exists('load_url')) {
load_url("order_confirmation?order_id={$order_id}", 1); // إعادة توجيه المستخدم
}


// دالة إرسال بريد التأكيد
function send_confirmation_email($to, $details) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'mail.foxlivrason.ma';
        $mail->SMTPAuth = true;
        $mail->Username = 'support@foxlivrason.ma';
        $mail->Password = 'DjgWaA)NGHOl';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('support@foxlivrason.ma', 'Boutique foxlivraison');
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = 'Confirmation de votre commande';

        $mail->Body = "
            <h3>Merci pour votre commande</h3>
            <p>Commande #: {$details['order_id']}</p>
            <p>Adresse de livraison: {$details['order_address']}</p>
            <table border='1' cellpadding='5' cellspacing='0'>
                <thead>
                    <tr>
                        <th>Produit</th>
                        <th>Quantité</th>
                        <th>Prix unitaire</th>
                        <th>Prix total</th>
                        <th>Options</th>
                    </tr>
                </thead>
                <tbody>
                    {$details['items_html']}
                </tbody>
            </table>
            <h4>Total: {$details['order_total']} Dhs</h4>
        ";
        $mail->send();
    } catch (Exception $e) {
        // فشل الإرسال
    }
}

// دالة إرسال الترحيب
function send_welcome_email($to, $user_name, $random_pass) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'mail.foxlivrason.ma';
        $mail->SMTPAuth = true;
        $mail->Username = 'support@foxlivrason.ma';
        $mail->Password = 'DjgWaA)NGHOl';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('support@foxlivrason.ma', 'Votre Boutique');
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = 'Bienvenue dans notre Boutique!';
        $mail->Body = "
            <h3>Bienvenue, {$user_name}!</h3>
            <p>Voici vos identifiants:</p>
            <p>Email: {$to}</p>
            <p>Mot de passe: {$random_pass}</p>
        ";
        $mail->send();
    } catch (Exception $e) {
        // فشل الإرسال
    }
}
?>
