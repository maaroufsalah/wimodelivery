<?php 
global $con;

include get_file("files/sql/get/os_settings");
include get_file("files/sql/get/session");
include get_file("files/sql/get/functions");

// --- Log fichier ---
$log_file = __DIR__ . '/../../../../logs/api.log';
if (!is_dir(dirname($log_file))) {
    mkdir(dirname($log_file), 0755, true);
}
function api_log($message, $log_file) {
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($log_file, "[$timestamp] $message\n", FILE_APPEND | LOCK_EX);
}

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
                        "code"        => $row['or_code'] ?? "WMD-" . $row['or_id'],
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

                    // --- Log avant envoi ---
                    api_log("=== ENVOI COLIS ===", $log_file);
                    api_log("or_id: " . $row['or_id'], $log_file);
                    api_log("code envoyé: WMD-" . $row['or_id'], $log_file);
                    api_log("destinataire: " . $row['or_name'] . " - " . $row['or_phone'], $log_file);
                    api_log("ville: " . $row['or_city'], $log_file);
                    api_log("prix: " . $row['or_total'], $log_file);
                    api_log("URL complète: " . $url, $log_file);

                    // تنفيذ الطلب عبر cURL (GET)
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                    $apiResponse = curl_exec($ch);

                    if (curl_errno($ch)) {
                        $curlError = curl_error($ch);
                        api_log("ERREUR cURL: " . $curlError, $log_file);
                        api_log("=== FIN ENVOI ===\n", $log_file);
                        $responses[] = [
                            "order_id" => $row['or_id'],
                            "error"    => $curlError
                        ];
                    } else {
                        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                        api_log("HTTP code: " . $httpCode, $log_file);
                        api_log("Réponse Oscario: " . $apiResponse, $log_file);
                        api_log("=== FIN ENVOI ===\n", $log_file);
                        $responses[] = [
                            "order_id"  => $row['or_id'],
                            "http_code" => $httpCode,
                            "sent_data" => $params,
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

            } elseif ($do == 3) {
                // API Chamel Express
                $token = "bfB7zbR6DBX23rZrFD2okNEnlF4MdLKQyt4Bw7rnxl8cmZhKrnJy92YLfsWeaMjlgV54wiPWX7GJkV14pPTAKM66ogEXoKQuOBBaHbwpyeyz9rMjSj8lk2UZ6nCEZeDe5kvlI6VrntgT4JWb20Cd34";
                $chamel_log_file = dirname($log_file) . '/api_chamel.log';

                // Récupérer les commandes avec le nom de ville (JOIN sur city)
                $stmt = $con->prepare("
                    SELECT o.*, c.city_name
                    FROM orders o
                    LEFT JOIN city c ON c.city_id = o.or_city
                    WHERE o.or_id IN ($placeholders)
                ");
                $stmt->execute($orderIdsArray);
                $orders_list = $stmt->fetchAll();

                // Construire les données pour chaque commande
                $ordersData = [];
                foreach ($orders_list as $row) {
                    $stmt_items = $con->prepare("SELECT * FROM order_items WHERE order_id = ?");
                    $stmt_items->execute([$row['or_id']]);
                    $items = $stmt_items->fetchAll();

                    $itemsText = [];
                    $totalQty = 0;
                    foreach ($items as $it) {
                        $itemsText[] = $it['oi_qty'] . "-" . $it['oi_name'];
                        $totalQty += intval($it['oi_qty']);
                    }

                    $ordersData[] = [
                        "code_suivi"  => $row['or_code'] ?? "WMD-" . $row['or_id'],
                        "destinataire" => $row['or_name'],
                        "telephone"   => $row['or_phone'],
                        "adresse"     => $row['or_address'],
                        "prix"        => floatval($row['or_total']),
                        "ville"       => strtoupper($row['city_name'] ?? $row['or_city']),
                        "marchandise" => implode(" + ", $itemsText) ?: "Produit",
                        "qte"         => $totalQty > 0 ? $totalQty : 1,
                        "peut_ouvrir" => true,
                        "change"      => false,
                        "commentaire" => $row['or_note'] ?? ""
                    ];
                }

                // 1 colis → endpoint simple, 2+ → bulk
                if (count($ordersData) === 1) {
                    $url  = "https://app.chamelexpress.com/api/client/post/store-commande";
                    $body = json_encode($ordersData[0]);
                } else {
                    $url  = "https://app.chamelexpress.com/api/client/store-commandes-bulk";
                    $body = json_encode(["orders" => $ordersData]);
                }

                api_log("=== ENVOI CHAMEL EXPRESS ===", $chamel_log_file);
                api_log("nb colis: " . count($ordersData), $chamel_log_file);
                api_log("url: " . $url, $chamel_log_file);
                api_log("body: " . $body, $chamel_log_file);

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    "Content-Type: application/json",
                    "Special-Token: " . $token
                ]);
                $apiResponse = curl_exec($ch);

                if (curl_errno($ch)) {
                    $curlError = curl_error($ch);
                    api_log("ERREUR cURL: " . $curlError, $chamel_log_file);
                    api_log("=== FIN CHAMEL ===\n", $chamel_log_file);
                    echo json_encode(["status" => "error", "message" => $curlError]);
                    exit;
                }

                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                api_log("HTTP code: " . $httpCode, $chamel_log_file);
                api_log("Réponse: " . json_encode(json_decode($apiResponse), JSON_UNESCAPED_UNICODE), $chamel_log_file);
                api_log("=== FIN CHAMEL ===\n", $chamel_log_file);

                header('Content-Type: application/json');
                echo json_encode([
                    "status"    => "success",
                    "http_code" => $httpCode,
                    "nb_colis"  => count($ordersData),
                    "response"  => $apiResponse
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
