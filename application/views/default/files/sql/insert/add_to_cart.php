<?php
session_start();
global $con;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // استخراج البيانات من الفورم
    $product_id = intval($_POST['product_id']);
    $quantity = intval($_POST['quantity']);
    $base_price = floatval($_POST['price']);
    $options = [];

    // قراءة الخيارات المرسلة من الفورم
    if (isset($_POST['group_ids'])) {
        foreach ($_POST['group_ids'] as $group_id) {
            $input_name = "option_" . $group_id;
            if (isset($_POST[$input_name])) {
                $value_name = $_POST[$input_name];

                // جلب سعر الخيار من قاعدة البيانات
                $stmt = $con->prepare("SELECT * FROM product_option_values WHERE group_id = ? AND value_name = ?");
                $stmt->execute([$group_id, $value_name]);
                $option_value = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($option_value) {
                    $options[] = [
                        'group_id' => $group_id,
                        'name' => get_group_name($group_id, $con),
                        'value_name' => $value_name,
                        'value_price' => $option_value['value_price']
                    ];
                }
            }
        }
    }

    // حساب السعر النهائي
    $total_option_price = 0;
    foreach ($options as $opt) {
        $total_option_price += floatval($opt['value_price']);
    }
    $b_price = ($base_price + $total_option_price);
    $final_price = $b_price * $quantity;

    // جلب بيانات المنتج
    $stmt = $con->prepare("SELECT p_name FROM products WHERE p_id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        echo "<div class='alert alert-danger'>Produit introuvable.</div>";
        exit;
    }

    // إنشاء مدخل للسلة
    $new_item = [
        'product_id' => $product_id,
        'product_name' => $product['p_name'],
        'quantity' => $quantity,
        'base_price' => $b_price,
        'final_price' => $final_price,
        'options' => $options
    ];

    // استرجاع السلة من الكوكيز
    $cart = isset($_COOKIE['cart']) ? json_decode($_COOKIE['cart'], true) : [];

    // البحث عن تكرار في السلة (نفس المنتج ونفس الخيارات)
    $found = false;
    foreach ($cart as $index => $item) {
        if ($item['product_id'] == $product_id && compare_options($item['options'], $options)) {
            // إذا تم العثور على نفس العنصر بنفس الخيارات
            $cart[$index]['quantity'] += $quantity;
            $found = true;
            break;
        }
    }

    // إذا لم يتم العثور على نفس المنتج بنفس الخيارات
    if (!$found) {
        $cart[] = $new_item;
    }

    // تحديث الكوكيز
    setcookie('cart', json_encode($cart), time() + (86400 * 30), "/");

    echo "<div class='modal fade show' style='display:block; background:rgba(0,0,0,0.6);'>
        <div class='modal-dialog'>
            <div class='modal-content'>
                <div class='modal-header'><h5 class='modal-title'>Succès</h5></div>
                <div class='modal-body'>Produit ajouté au panier avec succès.</div>
                <div class='modal-footer'>
                    <button type='button' class='btn btn-primary' onclick='location.reload()'>OK</button>
                </div>
            </div>
        </div>
    </div>";
}

function compare_options($opts1, $opts2) {
    if (count($opts1) !== count($opts2)) return false;

    foreach ($opts1 as $index => $opt) {
        if (
            $opt['group_id'] != $opts2[$index]['group_id'] ||
            $opt['value_name'] != $opts2[$index]['value_name']
        ) {
            return false;
        }
    }
    return true;
}

function get_group_name($group_id, $con) {
    try {
        $stmt = $con->prepare("SELECT group_name FROM product_option_groups WHERE id = ?");
        $stmt->execute([$group_id]);
        $group = $stmt->fetch(PDO::FETCH_ASSOC);
        return $group ? $group['group_name'] : "Option";
    } catch (PDOException $e) {
        return "Unknown Group";
    }
}
?>
