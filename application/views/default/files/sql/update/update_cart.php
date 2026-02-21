<?php
// تحديث الكمية في السلة
if (isset($_POST['action']) && $_POST['action'] === 'update') {
    $index = isset($_POST['index']) ? intval($_POST['index']) : null;
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : null;

    if ($index === null || $quantity === null) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid data']);
        exit;
    }

    $cart = isset($_COOKIE['cart']) ? json_decode($_COOKIE['cart'], true) : [];

    if (isset($cart[$index])) {
        $cart[$index]['quantity'] = $quantity;

        // إعادة احتساب السعر النهائي
        $base_price = floatval($cart[$index]['base_price']);
        $cart[$index]['final_price'] = $base_price * $quantity;

        setcookie('cart', json_encode($cart), time() + (86400 * 30), "/");

        echo json_encode(['status' => 'updated', 'final_price' => $cart[$index]['final_price']]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Item not found']);
    }
}

// حذف العنصر من السلة
if (isset($_POST['action']) && $_POST['action'] === 'remove') {
    $index = isset($_POST['index']) ? intval($_POST['index']) : null;

    if ($index === null) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid data']);
        exit;
    }

    $cart = isset($_COOKIE['cart']) ? json_decode($_COOKIE['cart'], true) : [];

    if (isset($cart[$index])) {
        unset($cart[$index]);

        // إعادة ترتيب الفهارس لتفادي المشاكل عند الحذف المتكرر
        $cart = array_values($cart);

        // تحديث الكوكيز
        setcookie('cart', json_encode($cart), time() + (86400 * 30), "/");

        echo json_encode(['status' => 'removed']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Item not found']);
    }
}

?>
