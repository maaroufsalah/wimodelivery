<?php 

$bootstrap = new Bootstrap();
$bootstrap->runBootstrap();



global $con;

$error ="";
include get_file("files/sql/get/os_settings");
include get_file("files/sql/get/session");
include get_file("files/sql/get/functions");

$do = isset ($_GET['do']) ? $_GET ['do'] : 'Manage' ;

function GET($name){
return $_GET[$name];
}




if ($do == 'Manage'){




}elseif ($do == 'city'){

if($loginRank == "admin"){

// start
$id = GET("dataUnlinkId");

$sql = "
UPDATE city SET 
city_unlink =  '1'
WHERE md5(city_id) = :id
";

$stmt = $con->prepare($sql);
$stmt->bindParam(':id', $id, PDO::PARAM_STR); // فقط هذا المطلوب

if ($stmt->execute()) {
echo "<div class='alert alert-success'>Mise à jour réussie</div>";
if (function_exists('load_url')) {
load_url("app_settings?do=city", 2);
}
}

//end

}









}elseif ($do == 'claim'){

if($loginRank == "admin"){

// start
$id = GET("dataUnlinkId");

$sql = "
UPDATE claim SET 
claim_unlink =  '1'
WHERE md5(claim_id) = :id
";

$stmt = $con->prepare($sql);
$stmt->bindParam(':id', $id, PDO::PARAM_STR); // فقط هذا المطلوب

if ($stmt->execute()) {
echo "<div class='alert alert-success'>Mise à jour réussie</div>";
if (function_exists('load_url')) {
load_url("claim", 2);
}
}

//end

}












}elseif ($do == 'logs'){

if ($loginRank == "admin") {

    $id = GET("id");

    // 1. تحقق من وجود الفاتورة وجلب بياناتها
    $stmt = $con->prepare("SELECT * FROM log_print WHERE md5(lp_id) = ?");
    $stmt->execute([$id]);

    if ($stmt->rowCount() == 0) {
        echo "<div class='alert alert-danger my-2'>Bon introuvable.</div>";
        exit;
    }

    // 2. جلب الفاتورة لتحديد نوعها قبل الحذف
    $lp = $stmt->fetch();

    // 3. تحديد المسار حسب نوع الفاتورة
    if ($lp['lp_type'] == "pickup") {
        $loadDir = "pickup";
    } elseif ($lp['lp_type'] == "outlog_delivery") {
        $loadDir = "outLogDelivery";
    } elseif ($lp['lp_type'] == "outlog_user") {
        $loadDir = "outLogUser";
    } else {
        $loadDir = "";
    }

    // 4. حذف الفاتورة
    $stmt = $con->prepare("DELETE FROM log_print WHERE md5(lp_id) = ?");
    $stmt->execute([$id]);

    // 5. عرض رسالة النجاح وإعادة التوجيه
    echo "<div class='alert alert-success my-2'>Bon supprimée avec succès.</div>";

    if (function_exists('load_url') && !empty($loadDir)) {
        load_url($loadDir, 2);
    }

}







}elseif ($do == 'user'){

if($loginRank == "admin"){

// start
$id = GET("dataUnlinkId");

$sql = "
UPDATE users SET 
user_unlink =  '1'
WHERE md5(user_id) = :id
";

$stmt = $con->prepare($sql);
$stmt->bindParam(':id', $id, PDO::PARAM_STR); // فقط هذا المطلوب

if ($stmt->execute()) {
echo "<div class='alert alert-success'>Mise à jour réussie</div>";
if (function_exists('load_url')) {
load_url("users", 2);
}
}

//end

}



}elseif ($do == 'state'){

if($loginRank == "admin"){

// start
$id = GET("dataUnlinkId");

$sql = "
UPDATE state SET 
state_unlink =  '1'
WHERE md5(state_id) = :id
";

$stmt = $con->prepare($sql);
$stmt->bindParam(':id', $id, PDO::PARAM_STR); // فقط هذا المطلوب

if ($stmt->execute()) {
echo "<div class='alert alert-success'>Mise à jour réussie</div>";
if (function_exists('load_url')) {
load_url("app_settings?do=state", 2);
}
}

//end

}


}elseif ($do == 'boxing'){

if($loginRank == "admin"){

// start
$id = GET("dataUnlinkId");

$sql = "
UPDATE box SET 
box_unlink =  '1'
WHERE md5(box_id) = :id
";

$stmt = $con->prepare($sql);
$stmt->bindParam(':id', $id, PDO::PARAM_STR); // فقط هذا المطلوب

if ($stmt->execute()) {
echo "<div class='alert alert-success'>Mise à jour réussie</div>";
if (function_exists('load_url')) {
load_url("app_settings?do=boxing", 2);
}
}

//end

}

}elseif ($do == 'type'){

if($loginRank == "admin"){

// start
$id = GET("dataUnlinkId");

$sql = "
UPDATE type SET 
type_unlink =  '1'
WHERE md5(type_id) = :id
";

$stmt = $con->prepare($sql);
$stmt->bindParam(':id', $id, PDO::PARAM_STR); // فقط هذا المطلوب

if ($stmt->execute()) {
echo "<div class='alert alert-success'>Mise à jour réussie</div>";
if (function_exists('load_url')) {
load_url("app_settings?do=type", 2);
}
}

//end

}


}elseif ($do == 'category'){

if($loginRank == "admin"){

// start
$id = GET("dataUnlinkId");

$sql = "
UPDATE classes SET 
c_unlink =  '1'
WHERE md5(c_id) = :id
";

$stmt = $con->prepare($sql);
$stmt->bindParam(':id', $id, PDO::PARAM_STR); // فقط هذا المطلوب

if ($stmt->execute()) {
echo "<div class='alert alert-success'>Mise à jour réussie</div>";
if (function_exists('load_url')) {
load_url("app_settings?do=category", 2);
}
}

//end

}


}elseif ($do == 'brand'){

if($loginRank == "admin"){

// start
$id = GET("dataUnlinkId");

$sql = "
UPDATE brand SET 
brand_unlink =  '1'
WHERE md5(brand_id) = :id
";

$stmt = $con->prepare($sql);
$stmt->bindParam(':id', $id, PDO::PARAM_STR); // فقط هذا المطلوب

if ($stmt->execute()) {
echo "<div class='alert alert-success'>Mise à jour réussie</div>";
if (function_exists('load_url')) {
load_url("app_settings?do=brand", 2);
}
}

//end

}


}elseif ($do == 'stock'){

if($loginRank == "admin"){

// start
$id = GET("dataUnlinkId");

$sql = "
UPDATE products SET 
p_unlink =  '1'
WHERE md5(p_id) = :id
";

$stmt = $con->prepare($sql);
$stmt->bindParam(':id', $id, PDO::PARAM_STR); // فقط هذا المطلوب

if ($stmt->execute()) {
echo "<div class='alert alert-success'>Mise à jour réussie</div>";
if (function_exists('load_url')) {
load_url("stocks", 2);
}
}

//end

}


}elseif ($do == 'section'){

if($loginRank == "admin"){

// start
$id = GET("dataUnlinkId");

$sql = "
UPDATE products SET 
p_unlink =  '1'
WHERE md5(p_id) = :id
";

$stmt = $con->prepare($sql);
$stmt->bindParam(':id', $id, PDO::PARAM_STR); // فقط هذا المطلوب

if ($stmt->execute()) {
echo "<div class='alert alert-success'>Mise à jour réussie</div>";
if (function_exists('load_url')) {
load_url("stocks", 2);
}
}

//end

}


}elseif ($do == 'warehouse'){

if($loginRank == "admin"){

// start
$id = GET("dataUnlinkId");

$sql = "
UPDATE warehouse SET 
wh_unlink =  '1'
WHERE md5(wh_id) = :id
";

$stmt = $con->prepare($sql);
$stmt->bindParam(':id', $id, PDO::PARAM_STR); // فقط هذا المطلوب

if ($stmt->execute()) {
echo "<div class='alert alert-success'>Mise à jour réussie</div>";
if (function_exists('load_url')) {
load_url("agency", 2);
}
}

//end

}











































}elseif ($do == 'invoice'){


$id = GET("dataUnlinkId");




// تحقق من وجود الفاتورة
$stmt = $con->prepare("SELECT * FROM invoice WHERE md5(in_id) = ?");
$stmt->execute([$id]);
if ($stmt->rowCount() == 0) {
echo "<div class='alert alert-danger my-2'>Facture introuvable.</div>";
exit;
}

// 1. إزالة ربط الفاتورة من الطلبات
$stmt = $con->prepare("UPDATE orders SET or_invoice = NULL WHERE md5(or_invoice) = ?");
$stmt->execute([$id]);

// 2. حذف تفاصيل الفاتورة
$stmt = $con->prepare("DELETE FROM invoice_script WHERE md5(is_invoice_id) = ?");
$stmt->execute([$id]);

// 3. حذف الفاتورة نفسها
$stmt = $con->prepare("DELETE FROM invoice WHERE md5(in_id) = ?");
$stmt->execute([$id]);

echo "<div class='alert alert-success my-2'>Facture supprimée avec succès.</div>";



if (function_exists('load_url')) {
load_url("invoice", 2);
}










}elseif ($do == 'delivery_invoice'){


$id = GET("dataUnlinkId");




// تحقق من وجود الفاتورة
$stmt = $con->prepare("SELECT * FROM delivery_invoice WHERE md5(d_in_id) = ?");
$stmt->execute([$id]);
if ($stmt->rowCount() == 0) {
echo "<div class='alert alert-danger my-2'>Facture introuvable.</div>";
exit;
}

// 1. إزالة ربط الفاتورة من الطلبات
$stmt = $con->prepare("UPDATE orders SET or_delivery_invoice = NULL WHERE md5(or_delivery_invoice) = ?");
$stmt->execute([$id]);

// 2. حذف تفاصيل الفاتورة
$stmt = $con->prepare("DELETE FROM delivery_invoice_script WHERE md5(dis_invoice) = ?");
$stmt->execute([$id]);

// 3. حذف الفاتورة نفسها
$stmt = $con->prepare("DELETE FROM delivery_invoice WHERE md5(d_in_id) = ?");
$stmt->execute([$id]);

echo "<div class='alert alert-success my-2'>Facture supprimée avec succès.</div>";



if (function_exists('load_url')) {
load_url("deliveryInvoice", 2);
}






}elseif ($do == 'shipping'){


$id = GET("dataUnlinkId");




$stmt = $con->prepare("SELECT * FROM expeditions WHERE md5(expedition_id) = ?");
$stmt->execute([$id]);
if ($stmt->rowCount() == 0) {
echo "<div class='alert alert-danger my-2'>Facture introuvable.</div>";
exit;
}

$stmt = $con->prepare("UPDATE orders SET or_shipping = 0 WHERE md5(or_shipping) = ?");
$stmt->execute([$id]);

$stmt = $con->prepare("DELETE FROM expedition_colis WHERE md5(expedition_id) = ?");
$stmt->execute([$id]);

$stmt = $con->prepare("DELETE FROM expeditions WHERE md5(expedition_id) = ?");
$stmt->execute([$id]);

echo "<div class='alert alert-success my-2'>expedition supprimée avec succès.</div>";



if (function_exists('load_url')) {
load_url("shipping", 2);
}






}elseif ($do == 'orders'){


$id = GET("id");


$stmt = $con->prepare("SELECT * FROM orders WHERE or_unlink = '0' AND md5(or_id) = '$id' LIMIT 1");
$stmt->execute();
$order = $stmt->fetch();


$orderIdsRaw  = $order['or_id'].",0";


include get_file("files/sql/update/r_stock");



$stmt = $con->prepare("UPDATE orders SET or_unlink = '1' WHERE md5(or_id) = ?");
$stmt->execute([$id]);



if (function_exists('load_url')) {
load_url("packages", 2);
}





}elseif ($do == 'news'){


$id = GET("dataUnlinkId");


try {

$stmt = $con->prepare("DELETE FROM news WHERE md5(n_id) = ?");
$stmt->execute([$id]);



echo "supprimé avec succès";
if (function_exists('load_url')) {
load_url("app_settings?do=news", 2);
}


} catch (Exception $e) {
echo "Erreur: " . $e->getMessage();
}






}else{

print "
<div class='alert alert-danger'>Page non trouvée</div>
";

}
?>