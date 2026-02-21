<?php 

$bootstrap = new Bootstrap();
$bootstrap->runBootstrap();

include get_file("files/sql/get/os_settings");
include get_file("files/sql/get/session");
include get_file("files/sql/get/functions");


if ($loginRank == "admin"){

include get_file("Admin/admin_header");


define ("page_title","Promotions");


?>






<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
<!--begin::App Wrapper-->
<div class="app-wrapper">
<!--begin::Header-->





<?php include get_file("Admin/admin_nav_top");?>
<?php include get_file("Admin/admin_nav_left");?>







<main class="app-main">










<div class="app-content-header">
<div class="container-fluid">

<div class="row">

<div class="col-sm-6">
<h3 class="mb-0"><?php print page_title ;?></h3>
</div>

<div class="col-sm-6">
<ol class="breadcrumb float-sm-end">
<li class="breadcrumb-item"><a>Home</a></li>
<li class="breadcrumb-item active" aria-current="page"><?php print page_title ;?></li>
</ol>
</div>


</div>

</div>
</div>













<div class="app-content">
<div class="container-fluid">




<?php

$do = isset ($_GET['do']) ? $_GET ['do'] : 'Manage' ;
if ($do == 'Manage'){



?>

<!-- HTML لتصميم الواجهة -->
<div style="text-align: right;">
<a href='?do=new' class="btn btn-primary my-3 btn-sm">Ajouter Section</a>
</div>

<div class="card" style="border-radius:0rem">
<div class="card-body">

<div class="row">
<div class='col-6' style="text-align: left;">
<h6>Recherche</h6>
<input type="text" class="searchbox form-control mb-3" style="width:100%"/>
</div>

<div class='col-6' style="">
<h6>Afficher</h6>
<select class="display form-select">
<option value="10">10</option>
<option value="50">50</option>
<option value="100">100</option>
<option value="200">200</option>
</select>
</div>

<div class='col-sm-12' style="">
<div class='container' style="">



<div class="collapse" id="category">
<div class="row gx-4 gx-lg-5 row-cols-2 row-cols-md-3 row-cols-xl-4 justify-content-center">


</div>
</div>
</div>
</div>

</div>
<hr>
<div class="loader"></div>
<div id="dynamic_content"></div>
</div>
</div>





<script>
$(document).ready(function(){
load_data(1); // تحميل البيانات افتراضيًا

function load_data(page, search = '', display = '') {
console.log("📤 إرسال البيانات:", {
page: page,
search: search,
display: display
});


$.ajax({
url: 'getSlider',
method: 'POST',
data: { page: page, search: search, display: display },
dataType: 'html', 
cache: false,
beforeSend: function () {
$('.loader').html('<div class="progress" role="progressbar" aria-label="Animated striped example" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"><div class="progress-bar progress-bar-striped progress-bar-animated" style="width: 100%"></div></div>');
},
success: function (data) {
$('#dynamic_content').html(data);
$('.loader').html('');
},
error: function (xhr, status, error) {
console.error('⚠️ خطأ في AJAX:', error);
}
});
}

// عند تغيير قيمة البحث
$('.searchbox').keyup(function() {
var search = $(this).val();
var display = $('.display').val();
load_data(page, search, display); // ✅ إغلاق القوس بشكل صحيح
});

// عند تغيير قيمة عرض المنتجات
$('.display').change(function() {
var display = $(this).val();
var search = $('.searchbox').val();
load_data(page, search, display); // ✅ إغلاق القوس بشكل صحيح
});





$(document).on('click', '.page-link', function(event) {
event.preventDefault(); // منع إعادة تحميل الصفحة
let page = $(this).attr('data-page'); // التقاط رقم الصفحة
let search = $('.searchbox').val();
let display = $('.display').val();
load_data(page, search, display); // تحميل الصفحة المناسبة
});

});
</script>


<?php
}elseif($do == "new"){



$id = "formId";  // معرّف النموذج
$result = "data_result"; 
$action = "newSlider"; 
$method = "post"; 
formAwdStart($id, $result, $action, $method); 




?>



<div class="card">
<div class="card-header">
<h5><b>Ajouter Promotion</b></h5>
</div>
<div class="card-body">
<div class="row">




<!-- حقل اسم المنتج -->
<div class="col-sm-12">
<div class="my-3">
<div class="input">Le Nom de Promotion</div>
<input name="name" type="text" class="form-control" value=""/>
</div>
</div>

<!-- حقل الملاحظات -->
<div class="col-sm-12">
<div class="my-3">
<div class="input">Note</div>
<input name="note" type="text" class="form-control" value=""/>
</div>
</div>



<div class="col-sm-12">
<div class="my-3">
<div class="form-group">
<label for="image">Télécharger image :</label>
<input type="file" name="image" id="image" multiple class="form-control">
</div>
</div>
</div>



<!-- زر التأكيد -->
<div class="col-sm-12 text-center">
<div class="my-3">
<div id ='<?php print $result ;?>'></div>
</div>
</div>


<!-- زر التأكيد -->
<div class="col-sm-12 text-center">
<button class="btn my-3 btn-primary">Valider</button>
</div>
</div>
</div>
</div>








<?php
formAwdEnd();




}elseif($do == "edit"){
?>
<div class="row">





<div class="col-sm-8">
<?php
$id = "formId";  // معرّف النموذج
$result = "data_result"; 
$action = "editSlider"; 
$method = "post"; 
formAwdStart($id, $result, $action, $method); 

// استلام معرف القسم
$id = $_GET['id'] ?? '';

// التحقق من وجود معرف القسم
if (!empty($id) && isset($con)) {
$stmt = $con->prepare("SELECT * FROM slider WHERE md5(sli_id) = :sli_id");
$stmt->bindParam(':sli_id', $id, PDO::PARAM_STR);
$stmt->execute();
$item = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$item) {
echo "<div class='alert alert-danger'>Le produit spécifié n'existe pas.</div>";
exit();
}

// تحميل البيانات من قاعدة البيانات
$section_id = $item['sli_id'];  // الاحتفاظ بالمعرف الحقيقي
$name = $item['sli_name'] ?? '';
$note = $item['sli_note'] ?? '';
?>

<input type='hidden' name='id' value='<?= htmlspecialchars(md5($section_id)); ?>'/>

<div class="card">
<div class="card-header">
<h5><b>Modifier Section</b></h5>
</div>
<div class="card-body">
<div class="row">

<!-- حقل اسم القسم -->
<div class="col-sm-12">
<div class="my-3">
<div class="input">Le Nom de section</div>
<input name="name" type="text" class="form-control" value="<?= htmlspecialchars($name); ?>"/>
</div>
</div>

<!-- حقل الملاحظات -->
<div class="col-sm-12">
<div class="my-3">
<div class="input">Note</div>
<input name="note" type="text" class="form-control" value="<?= htmlspecialchars($note); ?>"/>
</div>
</div>

<!-- تحميل الصورة -->
<div class="col-sm-12">
<div class="form-group">
<label for="image">Télécharger image :</label>
<input type="file" name="image" id="image" class="form-control">
</div>
</div>

<!-- زر التأكيد -->
<div class="col-sm-12 text-center">
<div id ='<?= htmlspecialchars($result); ?>'></div>
</div>

<!-- زر التأكيد -->
<div class="col-sm-12 text-center">
<button class="btn my-3 btn-primary">Valider</button>
</div>
</div>
</div>
</div>

<?php
} else {
echo "<div class='alert alert-danger'>Aucun identifiant spécifié.</div>";
}
formAwdEnd();
?>

</div>


<div class="col-sm-4">

<?php
$id = $_GET['id'] ?? '';

if (!empty($id)) {
    // جلب بيانات السلايدر
    $stmt = $con->prepare("SELECT * FROM slider WHERE md5(sli_id) = :sli_id");
    $stmt->bindParam(':sli_id', $id, PDO::PARAM_STR);
    $stmt->execute();
    $slider = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$slider) {
        echo "<div class='alert alert-danger'>السلايدر المحدد غير موجود.</div>";
        exit();
    }

    // جلب المنتجات المرتبطة بالسلايدر
    $stmtProducts = $con->prepare("
        SELECT p.* FROM products p
        JOIN slider_products sp ON p.p_id = sp.product_id
        WHERE sp.slider_id = :slider_id
    ");
    $stmtProducts->bindParam(':slider_id', $slider['sli_id'], PDO::PARAM_INT);
    $stmtProducts->execute();
    $products = $stmtProducts->fetchAll(PDO::FETCH_ASSOC);
?>

<input type='hidden' name='id' value='<?= $slider['sli_id']; ?>' />

<div class="card">
    <div class="card-header">
        <h5><b>Produits En Promotion</b></h5>
    </div>
    <div class="card-body">
        <div class="row text-center">

            <?php if (!empty($products)): ?>
                <?php foreach ($products as $product): ?>

                    <?php
                    // جلب الصورة الرئيسية للمنتج
                    $stmt = $con->prepare("SELECT image_url FROM product_images WHERE product_id = ? AND is_main = 1 LIMIT 1");
                    $stmt->execute([$product['p_id']]);
                    $mainImage = $stmt->fetchColumn();
                    $imageUrl = $mainImage ? "uploads/products/" . htmlspecialchars($mainImage) : "uploads/app/default.jpg";
                    ?>

                    <div class="col-md-6 product-item" id="product-<?= $product['p_id']; ?>">
                        <div class="card mb-3">
                            <img src="<?= $imageUrl; ?>" class="card-img-top">
                            <div class="card-body">
                                <h5 class="card-title" style='font-size: 10px;'><?= htmlspecialchars($product['p_name']); ?></h5>
                                <button class="btn btn-danger my-2 btn-sm delete-product" data-id="<?= $product['p_id']; ?>" data-slider="<?= $slider['sli_id']; ?>">❌ supprimer</button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-warning">Il n'y a aucun produit dans ce slider.</div>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<?php
} else {
    echo "<div class='alert alert-danger'>السلايدر غير محدد.</div>";
}
?>

<script>
$(document).ready(function () {
$(".delete-product").click(function () {
var product_id = $(this).data("id");
var sec_id = $(this).data("slider");

if (confirm("Voulez-vous supprimer ce produit de la promotion ?")) {
$.ajax({
url: "delete_product_slider",
type: "POST",
data: { product_id: product_id, sec_id: sec_id },
dataType: "json",
success: function (response) {
if (response.status === "success") {
$("#product-" + product_id).fadeOut(500, function () {
$(this).remove();
});
} else {
alert(response.message);
}
},
error: function () {
alert("Une erreur s'est produite lors de la suppression !");
}
});
}
});
});
</script>



</div>


</div>
<?php
}else{






}
?>






</div>
</div>

























</main>

<?php include get_file("Admin/admin_footer");?>

<?php
}
?>