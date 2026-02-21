<?php 
global $con;

include get_file("files/sql/get/os_settings");
include get_file("files/sql/get/session");
include get_file("files/sql/get/functions");

if (SRM("POST")) {

    $orderIdsRaw = $_POST['order_id'] ?? ''; 
    $api_id = POST("api_id");
    $do = $_GET['do'] ?? $api_id;

    if (!empty($orderIdsRaw)) {
        try {
            $orderIdsArray = array_filter(array_map('intval', explode(',', $orderIdsRaw)));
            if (empty($orderIdsArray)) {
                throw new Exception("Liste de commandes invalide.");
            }

            $placeholders = implode(',', array_fill(0, count($orderIdsArray), '?'));

            if ($do == 1) {
                // API dummy
                $stmt = $con->prepare("SELECT * FROM orders WHERE or_id IN ($placeholders)");
                $stmt->execute($orderIdsArray);
                $orders_list = $stmt->fetchAll();

                $responses = [];
                foreach ($orders_list as $row) {
                    $responses[] = [
                        "order_id" => $row['or_id'],
                        "message"  => "API 1 pas encore implémentée."
                    ];
                }

                header('Content-Type: application/json');
                echo json_encode([
                    "status"    => "success",
                    "responses" => $responses
                ]);
                exit;

            } elseif ($do == 2) {
                // API Oscario.org
                $stmt = $con->prepare("SELECT * FROM orders WHERE or_id IN ($placeholders)");
                $stmt->execute($orderIdsArray);
                $orders_list = $stmt->fetchAll();

                $responses = [];

                foreach ($orders_list as $row) {
                    // جلب العناصر
                    $stmt_items = $con->prepare("SELECT * FROM order_items WHERE order_id = ?");
                    $stmt_items->execute([$row['or_id']]);
                    $items = $stmt_items->fetchAll();

                    $itemsText = [];
                    $totalQty = 0;
                    foreach ($items as $it) {
                        $itemsText[] = $it['oi_qty'] . "-" . $it['oi_name'];
                        $totalQty += intval($it['oi_qty']);
                    }
                    $productsText = implode(" + ", $itemsText);

                    // 🔑 Token et Secret Key
                    $tk = "29150fa19d13f04298bbe1a9672d0097";
                    $sk = "d8e62021f4426fadb2ba81b328b0d8fa";

                    // البيانات التي سترسل لـ API
                    $params = [
                        "tk"          => $tk,
                        "sk"          => $sk,
                        "code"        => $row['or_id'],
                        "fullname"    => $row['or_name'],
                        "phone"       => $row['or_phone'],
                        "city"        => $row['or_city'],   // API تحتاج ID المدينة
                        "address"     => $row['or_address'],
                        "price"       => $row['or_total'],
                        "product"     => $productsText ?: "Produit",
                        "qty"         => $totalQty > 0 ? $totalQty : 1,
                        "note"        => $row['or_note'] ?? "-",
                        "change"      => 0,
                        "openpackage" => 1
                    ];

                    // بناء الرابط (GET)
                    $url = "https://oscario.org/addcolis.php?" . http_build_query($params);

                    // تنفيذ الطلب عبر cURL (GET)
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                    $apiResponse = curl_exec($ch);

                    if (curl_errno($ch)) {
                        $responses[] = [
                            "order_id" => $row['or_id'],
                            "error"    => curl_error($ch)
                        ];
                    } else {
                        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                        $responses[] = [
                            "order_id"  => $row['or_id'],
                            "http_code" => $httpCode,
                            "sent_data" => $params,   // 🔍 مفيد للاختبار
                            "response"  => $apiResponse
                        ];
                    }
                    curl_close($ch);
                }

                header('Content-Type: application/json');
                echo json_encode([
                    "status"    => "success",
                    "responses" => $responses
                ]);
                exit;

            } else {
                echo json_encode([
                    "status"  => "error",
                    "message" => "Action inconnue."
                ]);
                exit;
            }

        } catch (Exception $e) {
            echo json_encode([
                "status"  => "error",
                "message" => $e->getMessage()
            ]);
            exit;
        }
    } else {
        echo json_encode([
            "status"  => "error",
            "message" => "Aucune commande sélectionnée."
        ]);
        exit;
    }
}
?>
