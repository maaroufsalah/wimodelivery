<?php
include get_file("files/sql/get/os_settings");
include get_file("files/sql/get/session");
include get_file("files/sql/get/functions");

global $con;

if (SRM("POST")){

    $product_id = POST('product_id');
    $section_id = POST('section_id' ,0 ,'int');

    if (empty($product_id) && empty($section_id)){
        print "
        <div class='alert alert-danger my-2'>
        Veuillez remplir tous les champs obligatoires (*)
        </div>
        ";
        exit();
    }

    // التأكد من وجود المنتج والقسم
    $stmt = $con->prepare("SELECT * FROM products WHERE md5(p_id) = ?");
    $stmt->execute([ $product_id ]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt2 = $con->prepare("SELECT * FROM sections WHERE sec_id = ?");
    $stmt2->execute([ $section_id ]);
    $section = $stmt2->fetch(PDO::FETCH_ASSOC);

    // التأكد من وجود المنتج والقسم
    if (!$product || !$section) {
        echo "<div class='alert alert-danger'>Produit ou section non trouvé.</div>";
        exit();
    }

    // التحقق من أن المنتج غير موجود بالفعل في القسم
    $stmt_check = $con->prepare("SELECT COUNT(*) FROM section_products WHERE sec_id = ? AND product_id = ?");
    $stmt_check->execute([ $section['sec_id'], $product['p_id'] ]);
    $count = $stmt_check->fetchColumn();

    if ($count > 0) {
        echo "<div class='alert alert-warning'>Ce produit est déjà associé à cette section.</div>";
        exit();
    }

    // إدخال المنتج في القسم مع قيمة ordering
    $ordering = 0;  // يمكن تخصيص ترتيب مبدئي هنا
    $stmt3 = $con->prepare("INSERT INTO section_products (sec_id, product_id, ordering) VALUES (?, ?, ?)");
    $stmt3->execute([ $section['sec_id'], $product['p_id'], $ordering ]);

    // التأكد من نجاح الإدخال
    if ($stmt3->rowCount() > 0) {
        echo "
        <div class='alert alert-success'>
        Terminé avec succès
        </div>
        ";

        if (function_exists('load_url')) {
            load_url("stocks", 2); // إعادة توجيه المستخدم
        }
    } else {
        echo "
        <div class='alert alert-danger'>
        Erreur d'insertion
        </div>
        ";
    }
}
?>
