<?php
header("Content-Type: application/json");

global $con;


$response = ['success' => false, 'message' => '', 'data' => []];

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Méthode non autorisée');
    }

    global $con;

    // بيانات المستخدم (لتوثيق)
    $email = POST('email');
    $password = POST('password');

    if (empty($email) || empty($password)) {
        throw new Exception("Email et mot de passe requis.");
    }

    // تحقق من المستخدم
    $stmt_user = $con->prepare("SELECT user_id, user_pass FROM users WHERE user_email = :email LIMIT 1");
    $stmt_user->execute([':email' => $email]);
    $user_data = $stmt_user->fetch(PDO::FETCH_ASSOC);

    if (!$user_data) {
        throw new Exception("Utilisateur non trouvé.");
    }

    if (!password_verify($password, $user_data['user_pass'])) {
        throw new Exception("Mot de passe incorrect.");
    }

    $user = $user_data['user_id']; // معرف المستخدم

    // جلب بيانات الطرد من POST
    $code = POST("code");
    $warehouse = POST("warehouse", 1, 'int');
    $fragile = POST("fragile", 0, 'int');
    $try = POST("try", 0, 'int');
    $open = POST("open", 0, 'int');
    $change = POST("change", 0, 'int');
    $price = POST("price", 0.0, 'float');
    $city_name = POST("city_name", "");
    $name = POST("name");
    $phone = POST("phone");
    $item = POST("item");
    $location = POST("location");
    $note = POST("note");
    $qty = POST("qty" ,0, 'int');
    $change_code = POST("change_code");
    $pickup = POST("pickup");
    $box = POST("box", 0, 'int');

    $order_created = date('Y-m-d H:i:s');

    // تحقق الحقول الإلزامية
    if (empty($code) || !$warehouse || empty($city_name) || empty($name) || empty($phone) || empty($location)) {
        throw new Exception('Veuillez remplir tous les champs obligatoires (*)');
    }

    if (!preg_match("/^[0-9]{10}$/", $phone)) {
        throw new Exception('Numéro de téléphone invalide.');
    }

    // بحث city_id من اسم المدينة
    $stmt_city = $con->prepare("SELECT city_id FROM city WHERE city_name = :name AND city_unlink = 0 LIMIT 1");
    $stmt_city->execute([':name' => $city_name]);
    $city_data = $stmt_city->fetch(PDO::FETCH_ASSOC);

    if (!$city_data) {
        throw new Exception('Ville introuvable ou désactivée.');
    }
    $city_id = $city_data['city_id'];

    // التحقق من الصندوق
    $box_price = 0;
    if ($box > 0) {
        $stmt_box = $con->prepare("SELECT box_price FROM box WHERE box_id = :id");
        $stmt_box->execute([':id' => $box]);
        $box_data = $stmt_box->fetch(PDO::FETCH_ASSOC);
        if (!$box_data) {
            throw new Exception('Box introuvable');
        }
        $box_price = $box_data['box_price'];
    }

    // التحقق من كود التغيير
    if ($change_code > 0) {
        $colis = $con->prepare("SELECT * FROM orders WHERE or_id = :id");
        $colis->execute([':id' => $change_code]);
        if (!$colis->fetch(PDO::FETCH_ASSOC)) {
            throw new Exception('Colis introuvable');
        }
    }

    // الآن نستخدم معرف المستخدم $user
    $stmt = $con->prepare("
        INSERT INTO orders (
            or_code, or_warehouse, or_trade, or_fragile, or_try, or_open_package, or_change,
            or_total, or_city, or_name, or_phone, or_address, or_note, or_item, or_qty, or_change_code,
            or_box, or_box_price, or_pickup_date, or_unlink, or_created
        ) VALUES (
            :or_code, :or_warehouse, :or_trade, :or_fragile, :or_try, :or_open_package, :or_change,
            :or_total, :or_city, :or_name, :or_phone, :or_shipped, :or_note, :or_item, :or_qty, :or_change_code,
            :or_box, :or_box_price, :or_pickup_date, 0, :or_created
        )
    ");

    $stmt->bindParam(':or_code', $code, PDO::PARAM_STR);
    $stmt->bindParam(':or_warehouse', $warehouse, PDO::PARAM_INT);
    $stmt->bindParam(':or_trade', $user, PDO::PARAM_INT);
    $stmt->bindParam(':or_fragile', $fragile, PDO::PARAM_INT);
    $stmt->bindParam(':or_try', $try, PDO::PARAM_INT);
    $stmt->bindParam(':or_open_package', $open, PDO::PARAM_INT);
    $stmt->bindParam(':or_change', $change, PDO::PARAM_INT);
    $stmt->bindParam(':or_total', $price, PDO::PARAM_STR);
    $stmt->bindParam(':or_city', $city_id, PDO::PARAM_INT);
    $stmt->bindParam(':or_name', $name, PDO::PARAM_STR);
    $stmt->bindParam(':or_phone', $phone, PDO::PARAM_STR);
    $stmt->bindParam(':or_shipped', $location, PDO::PARAM_STR);
    $stmt->bindParam(':or_note', $note, PDO::PARAM_STR);
    $stmt->bindParam(':or_item', $item, PDO::PARAM_STR);
    $stmt->bindParam(':or_qty', $qty, PDO::PARAM_STR);
    $stmt->bindParam(':or_change_code', $change_code, PDO::PARAM_STR);
    $stmt->bindParam(':or_box', $box, PDO::PARAM_INT);
    $stmt->bindParam(':or_box_price', $box_price, PDO::PARAM_STR);
    $stmt->bindParam(':or_pickup_date', $pickup, PDO::PARAM_STR);
    $stmt->bindParam(':or_created', $order_created, PDO::PARAM_STR);

    if ($stmt->execute()) {
        $new_id = $con->lastInsertId();
        $or_code = 'WMD-' . $new_id;
        $stmt_code = $con->prepare("UPDATE orders SET or_code = ? WHERE or_id = ?");
        $stmt_code->execute([$or_code, $new_id]);

        $response['success'] = true;
        $response['message'] = 'Colis ajouté avec succès.';
        $response['data'] = ['order_id' => $new_id, 'or_code' => $or_code];
    } else {
        throw new Exception('Erreur lors de l\'insertion.');
    }

} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
exit;
