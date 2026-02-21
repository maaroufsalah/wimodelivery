<?php 
include get_file("files/sql/get/session");
global $con;

if (SRM("POST")) {

    $id = POST("id");  // معرف المنتج (md5)
    $warehouse = POST("warehouse", 0, 'int');
    $brand = POST("brand", 0, 'int');
    $user = POST("user", 0, 'int');  // معرف المستخدم الذي يعدل المنتج
    $category = POST("category", 0, 'int');
    $sub_category = POST("sub_category", 0, 'int');

    $buy = POST("buy", 0.0, 'float');
    $sell = POST("sell", 0.0, 'float');
    $discount = POST("discount", 0.0, 'float');
    $qty = POST("qty", 0, 'int');

    $name = POST("name");
    $code = POST("code");
    $state = POST("state", 0, 'int');
    $note = POST("note");
    $details = POST("details");

    // التحقق من الحقول الإلزامية
    if (!$id || !$warehouse || !$user || empty($category) || empty($qty) || empty($name) ) {
        echo "<div class='alert alert-danger'>Veuillez remplir tous les champs obligatoires (*)</div>";
        exit();
    }

    // استخراج p_id الأصلي من md5(p_id)
    $stmtProductId = $con->prepare("SELECT p_id, p_qty FROM products WHERE md5(p_id) = :id");
    $stmtProductId->bindParam(':id', $id, PDO::PARAM_STR);
    $stmtProductId->execute();
    $productData = $stmtProductId->fetch(PDO::FETCH_ASSOC);

    if (!$productData) {
        echo "<div class='alert alert-danger'>Le produit spécifié n'existe pas.</div>";
        exit();
    }

    $productId = $productData['p_id'];
    $old_qty = (int)$productData['p_qty'];

    // حساب الفرق في الكمية
    $diff = $qty - $old_qty;
    if ($diff > 0) {
        $operation_type = 'increase';
    } elseif ($diff < 0) {
        $operation_type = 'decrease';
    } else {
        $operation_type = 'edit';
    }

    // استعلام تحديث المنتج
    $stmt = $con->prepare("
        UPDATE products SET
            p_name = :p_name,
            p_code = :p_code,
            p_brand = :p_brand,
            p_user = :p_user,
            p_note = :p_note,
            p_details = :p_details,
            p_warehouse = :p_warehouse,
            p_category = :p_category,
            p_sub_category = :p_sub_category,
            p_buy = :p_buy,
            p_sell = :p_sell,
            p_discount = :p_discount,
            p_state = :p_state,
            p_qty = :p_qty
        WHERE p_id = :p_id
    ");

    // ربط القيم
    $stmt->bindParam(':p_id', $productId, PDO::PARAM_INT);
    $stmt->bindParam(':p_warehouse', $warehouse, PDO::PARAM_INT);
    $stmt->bindParam(':p_brand', $brand, PDO::PARAM_INT);
    $stmt->bindParam(':p_code', $code, PDO::PARAM_STR);
    $stmt->bindParam(':p_user', $user, PDO::PARAM_INT);
    $stmt->bindParam(':p_sub_category', $sub_category, PDO::PARAM_INT);
    $stmt->bindParam(':p_category', $category, PDO::PARAM_INT);
    $stmt->bindParam(':p_buy', $buy, PDO::PARAM_STR);
    $stmt->bindParam(':p_sell', $sell, PDO::PARAM_STR);
    $stmt->bindParam(':p_discount', $discount, PDO::PARAM_STR);
    $stmt->bindParam(':p_qty', $qty, PDO::PARAM_INT);
    $stmt->bindParam(':p_state', $state, PDO::PARAM_INT);
    $stmt->bindParam(':p_name', $name, PDO::PARAM_STR);
    $stmt->bindParam(':p_note', $note, PDO::PARAM_STR);
    $stmt->bindParam(':p_details', $details, PDO::PARAM_STR);

    // تنفيذ التحديث
    if ($stmt->execute()) {

        // تسجيل التعديل في سجل المخزون (stock_log)
        if ($diff !== 0) {
            $stmtLog = $con->prepare("
                INSERT INTO stock_log 
                (p_id, user_id, change_qty, old_qty, new_qty, operation_type, change_date) 
                VALUES 
                (:p_id, :user_id, :change_qty, :old_qty, :new_qty, :operation_type, NOW())
            ");
            $stmtLog->execute([
                ':p_id' => $productId,
                ':user_id' => $loginId,  // تأكد أن $user هو معرف المستخدم الذي قام بالتعديل
                ':change_qty' => $diff,
                ':old_qty' => $old_qty,
                ':new_qty' => $qty,
                ':operation_type' => $operation_type,
            ]);
        }

        // معالجة خيارات المنتج كما عندك في كودك (يمكنك وضعها هنا كما هي)
        if (!empty($_POST['options'])) {
            foreach ($_POST['options'] as $groupKey => $optGroup) {
                $groupName = $optGroup['unit_name'] ?? '';
                $values = $optGroup['values'] ?? [];
                $prices = $optGroup['prices'] ?? [];

                if ($groupName && is_array($values)) {
                    // حذف المجموعات السابقة (يفضل حذف قديم لتجنب التكرار)
                    $con->prepare("DELETE FROM product_option_groups WHERE product_id = ?")->execute([$productId]);

                    // إنشاء المجموعة الجديدة
                    $stmtGroup = $con->prepare("INSERT INTO product_option_groups (product_id, group_name) VALUES (?, ?)");
                    $stmtGroup->execute([$productId, $groupName]);
                    $groupId = $con->lastInsertId();

                    // إدخال القيم الجديدة
                    foreach ($values as $i => $val) {
                        if (trim($val) === '') continue;
                        $price = isset($prices[$i]) ? floatval($prices[$i]) : 0;

                        $stmtValue = $con->prepare("INSERT INTO product_option_values (group_id, value_name, value_price) VALUES (?, ?, ?)");
                        $stmtValue->execute([$groupId, $val, $price]);
                    }
                }
            }
        }

        echo "<div class='alert alert-success'>Mise à jour réussie!</div>";
        load_url("", 2); // إعادة توجيه

    } else {
        echo "<div class='alert alert-danger'>Erreur lors de la mise à jour.</div>";
    }

    // إغلاق الاتصالات
    $stmt = null;
    $con = null;
}
?>
