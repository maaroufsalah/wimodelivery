<?php
global $con;
session_start();
if (isset($_POST['index'])) {
    $index = $_POST['index'];
    if (isset($_SESSION['cart'][$index])) {
        unset($_SESSION['cart'][$index]);
        $_SESSION['cart'] = array_values($_SESSION['cart']); // إعادة ترتيب الفهارس

        $total_general = 0;
        foreach ($_SESSION['cart'] as $item) {
            $total_general += $item['final_price'] * $item['quantity'];
        }

        echo json_encode([
            'status' => 'success',
            'grand_total' => number_format($total_general, 2)
        ]);
    }
}
?>
