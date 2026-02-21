<?php
global $con;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "<div class='alert alert-danger my-2'>Méthode invalide.</div>";
    exit;
}

try {
    // قراءة المدخلات
    $user = isset($_POST['user']) ? intval($_POST['user']) : 0;
    $orderIdsStr = isset($_POST['order_id']) ? trim($_POST['order_id']) : '';
    $orderIdArray = array_values(array_filter(array_map(function($v){
        $v = trim($v);
        return $v === '' ? null : intval($v);
    }, explode(',', $orderIdsStr)), function($v){ return $v > 0; }));

    if ($user <= 0 || empty($orderIdArray)) {
        echo "<div class='alert alert-danger my-2'>Veuillez sélectionner au moins une commande et un utilisateur valide.</div>";
        exit;
    }

    // تحقق وجود المستخدم
    $stmt = $con->prepare("SELECT * FROM users WHERE user_id = ? LIMIT 1");
    $stmt->execute([$user]);
    if ($stmt->rowCount() == 0) {
        echo "<div class='alert alert-danger my-2'>Utilisateur introuvable.</div>";
        exit;
    }
    $displayUser = $stmt->fetch(PDO::FETCH_ASSOC);

    // ابدأ المعاملة لحماية التزام البيانات
    $con->beginTransaction();

    // بناء placeholders واسترجاع الطلبات مع قفل الصفوف
    $placeholders = implode(',', array_fill(0, count($orderIdArray), '?'));
    $sql = "SELECT or_id, or_invoice, or_fee, or_box, or_box_price, or_print, or_total, or_warehouse, or_city, or_state_delivery, or_phone, or_fpc, or_fee_change 
            FROM orders 
            WHERE or_id IN ($placeholders)
            FOR UPDATE";
    $stmt = $con->prepare($sql);
    $stmt->execute($orderIdArray);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($orders) == 0) {
        $con->rollBack();
        echo "<div class='alert alert-danger my-2'>Aucune commande trouvée.</div>";
        exit;
    }

    // تحقق إن بعض الطلبات مفوترة سابقًا
    $alreadyLinked = [];
    foreach ($orders as $o) {
        if (!empty($o['or_invoice']) && intval($o['or_invoice']) > 0) {
            $alreadyLinked[] = intval($o['or_id']);
        }
    }
    if (!empty($alreadyLinked)) {
        $con->rollBack();
        $ids = implode(', ', $alreadyLinked);
        echo "<div class='alert alert-danger my-2'>Les commandes suivantes sont déjà liées à une autre facture : {$ids}</div>";
        exit;
    }

    // تحقق من توفر أسعار الشحن لكل طلب (user_pricing ثم shipping_charges)
    $missingCharges = [];
    $computedFees = []; // indexed by or_id
    foreach ($orders as $o) {
        $orderId = intval($o['or_id']);
        $warehouse = intval($o['or_warehouse']);
        $city = intval($o['or_city']);
        $state = intval($o['or_state_delivery']);

        $fee = null;
        // إذا or_fee موجود بالفعل فخذه
        if (isset($o['or_fee']) && floatval($o['or_fee']) > 0) {
            $fee = floatval($o['or_fee']);
        } else {
            // جرب user_pricing
            $stmt = $con->prepare("SELECT * FROM user_pricing WHERE up_unlink = 0 AND up_warehouse = ? AND up_city = ? AND up_user = ? LIMIT 1");
            $stmt->execute([$warehouse, $city, $user]);
            $up = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($up) {
                switch ($state) {
                    case 1:
                    case 60:
                        $fee = floatval($up['up_delivery']);
                        break;
                    case 3:
                        $fee = floatval($up['up_cancel']);
                        break;
                    case 4:
                        $fee = floatval($up['up_return']);
                        break;
                    default:
                        $fee = 0;
                }
            } else {
                // جرب shipping_charges
                $stmt = $con->prepare("SELECT * FROM shipping_charges WHERE sc_unlink = 0 AND sc_warehouse = ? AND sc_city = ? LIMIT 1");
                $stmt->execute([$warehouse, $city]);
                $sc = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($sc) {
                    switch ($state) {
                        case 1:
                        case 60:
                            $fee = floatval($sc['sc_delivery']);
                            break;
                        case 3:
                            $fee = floatval($sc['sc_cancel']);
                            break;
                        case 4:
                            $fee = floatval($sc['sc_return']);
                            break;
                        default:
                            $fee = 0;
                    }
                } else {
                    $missingCharges[] = $orderId;
                }
            }
        }
        $computedFees[$orderId] = $fee;
    }

    if (!empty($missingCharges)) {
        $con->rollBack();
        $ids = implode(', ', $missingCharges);
        echo "
        <div class='alert alert-danger text-center my-2'>
            Les frais de livraison manquent pour les commandes : <b>{$ids}</b>.<br>
            Merci d'ajouter un tarif pour ces trajets. <br>
            <a target='_blank' href='pricing?do=new' class='btn btn-white my-1'>Ajouter Tarif</a>
        </div>";
        exit;
    }

    // إنشاء الفاتورة
    $in_gid_str = implode(',', $orderIdArray);
    $stmt = $con->prepare("INSERT INTO invoice (in_gid, in_user, in_type, in_date) VALUES (?, ?, 'user', NOW())");
    $stmt->execute([$in_gid_str, $user]);
    $invoiceId = $con->lastInsertId();

    // إعداد الاستعلامات للإدخال والتحديث
    $insertScript = $con->prepare("
        INSERT INTO invoice_script (
            is_order, is_warehouse, is_city, is_state,
            is_box_id, is_box_price, is_fa, is_print, is_fees, is_net,
            is_note, is_date, is_invoice_id
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)
    ");
    $updateOrderInvoice = $con->prepare("UPDATE orders SET or_invoice = ? WHERE or_id = ?");

    // أدخل كل سطر بالفاتورة وحدث الطلب
    foreach ($orders as $o) {
        $orderId = intval($o['or_id']);
        $warehouse = intval($o['or_warehouse']);
        $city = intval($o['or_city']);
        $state = intval($o['or_state_delivery']);

        // منطق اختيار الرسوم مع مراعاة or_fee_change و or_fee
        $fee = 0;
        $fa  = 0;
        $note = null;

        if (!empty($o['or_fee_change']) && floatval($o['or_fee_change']) > 0 && !empty($o['or_fee']) && floatval($o['or_fee']) > 0) {
            $fee = floatval($o['or_fee']);
            $fa = 0;
            $note = "FC";
        } elseif (!empty($o['or_fee']) && floatval($o['or_fee']) > 0) {
            $fee = floatval($o['or_fee']);
            $fa = 0;
            $note = "GC";
        } elseif (!empty($o['or_fee_change']) && floatval($o['or_fee_change']) > 0) {
            $fee = floatval($o['or_fee']); // حافظت على منطقك
            $fa = 0;
            $note = "FC";
        } else {
            $fee = floatval($computedFees[$orderId] ?? 0);
            $fa = floatval($o['or_fee'] ?? 0);
        }

        $printPrice = isset($o['or_print']) ? floatval($o['or_print']) : 0;
        $boxPrice = isset($o['or_box_price']) ? floatval($o['or_box_price']) : 0;
        $boxId = isset($o['or_box']) ? $o['or_box'] : null;

        $deductions = $fee + $boxPrice + $printPrice + $fa;

        $insertScript->execute([
            $orderId,
            $warehouse,
            $city,
            $state,
            $boxId,
            $boxPrice,
            $fa,
            $printPrice,
            $fee,
            $deductions,
            $note,
            $invoiceId
        ]);

        $updateOrderInvoice->execute([$invoiceId, $orderId]);
    }

    // حساب المجاميع وتحديث الفاتورة
    $stmt = $con->prepare("
        SELECT 
            SUM(is_fees) AS total_fees,
            SUM(is_box_price) AS total_boxes,
            SUM(is_print) AS total_prints,
            SUM(is_fa) AS total_fa
        FROM invoice_script 
        WHERE is_invoice_id = ?
    ");
    $stmt->execute([$invoiceId]);
    $totals = $stmt->fetch(PDO::FETCH_ASSOC);

    $totalFees = floatval($totals['total_fees'] ?? 0);
    $totalBoxes = floatval($totals['total_boxes'] ?? 0);
    $totalPrints = floatval($totals['total_prints'] ?? 0);
    $totalFa = floatval($totals['total_fa'] ?? 0);
    $inFee = $totalFees + $totalBoxes + $totalPrints + $totalFa;

    $stmt = $con->prepare("
        SELECT IFNULL(SUM(or_total),0) 
        FROM orders 
        WHERE or_invoice = ? AND or_state_delivery IN (1,60)
    ");
    $stmt->execute([$invoiceId]);
    $inTotal = floatval($stmt->fetchColumn());

    $inNet = $inTotal - $inFee;

    $update = $con->prepare("UPDATE invoice SET in_fee = ?, in_total = ?, in_net = ? WHERE in_id = ?");
    $update->execute([$inFee, $inTotal, $inNet, $invoiceId]);

    // التزام المعاملة
    $con->commit();

    echo "<div class='alert alert-success my-2'>La facture a été créée avec succès! (ID: {$invoiceId})</div>";

    if (function_exists('load_url')) {
        load_url("invoice", 3);
    }

} catch (Exception $e) {
    if ($con && $con->inTransaction()) $con->rollBack();
    $msg = htmlspecialchars($e->getMessage());
    echo "<div class='alert alert-danger my-2'>Erreur lors de la création de la facture: {$msg}</div>";
    // سجّل الخطأ في لوج إن أردت
}
?>
