<?php 

$bootstrap = new Bootstrap();
$bootstrap->runBootstrap();

include get_file("files/sql/get/os_settings");
include get_file("files/sql/get/session");
include get_file("files/sql/get/functions");
include get_file("Admin/admin_header");


define ("page_title","Produits");


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
if ((hasUserPermission($con, $loginId, 27 ,'admin')) || hasUserPermissionAide($con, $loginId, 51 ,'user') || ($loginRank == "user")){


$stmt = $con->prepare("SELECT * FROM users WHERE user_unlink = '0' AND user_rank = 'user' ORDER BY user_name");
$stmt->execute();
$f_user = $stmt->fetchAll();


// استعلام الفئات الرئيسية
$query = "SELECT * FROM classes WHERE c_unlink = '0' ORDER BY c_name ASC";
$stmt = $con->prepare($query);
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($categories)>0){

// استعلام الفئات الفرعية لجميع الفئات في استعلام واحد
$query = "SELECT * FROM s_classes WHERE sub_category IN (" . implode(',', array_map('intval', array_column($categories, 'c_id'))) . ")";
$stmt = $con->prepare($query);
$stmt->execute();
$subcategories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ترتيب الفئات الفرعية حسب الفئة الرئيسية
$subcategoriesByCategory = [];
foreach ($subcategories as $subcategory) {
$subcategoriesByCategory[$subcategory['sub_category']][] = $subcategory;
}
}

?>

<!-- HTML لتصميم الواجهة -->
<div style="text-align: right;">
<a href='?do=new' class="btn btn-primary my-3 btn-sm">Ajouter Produits</a>
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


<?php if ($loginRank == "admin") :?>
<div class="col-sm-12">
<div class="my-3">
<div class="input">Vendeur</div>
<select name='user' class='js-select user w-100'>
<option value='int' disabled selected>Choisir Vendeur</option>
<?php foreach ($f_user as $row): ?>
<option value='<?= $row['user_id'] ?>'><?= $row['user_name'] ?></option>
<?php endforeach; ?>
</select>
</div>
</div>
<?php endif;?>


<div class='col-sm-12' style="">
<div class='container' style="">
<p class="gap-1">
<a class="btn btn-white w-100" data-bs-toggle="collapse" href="#category" role="button" aria-expanded="false" aria-controls="collapseExample">
Catégorie
</a>
</p>

<div class="collapse" id="category">
<div class="row gx-4 gx-lg-5 row-cols-2 row-cols-md-3 row-cols-xl-4 justify-content-center">
<?php
foreach ($categories as $category) {

$query = "SELECT * FROM type WHERE type_id = '".$category['c_type']."' LIMIT 1";
$stmt = $con->prepare($query);
$stmt->execute();
$type = $stmt->fetch();

echo '
<div class="col form-check">
<input name="category[]" class="form-check-input" type="checkbox" value="' . $category["c_id"] . '" id="flexCheckDefault' . $category["c_id"] . '">
<label class="form-check-label" for="flexCheckDefault' . $category["c_id"] . '">
<b>' . $type["type_name"] . '-->(' . $category["c_name"] . ')</b>
</label>
<hr>
';

// عرض الفئات الفرعية المتعلقة بهذه الفئة
if (isset($subcategoriesByCategory[$category['c_id']])) {
foreach ($subcategoriesByCategory[$category['c_id']] as $subcategory) {
echo '
<div class="ml-5">
<input name="sub_category[]" class="form-check-input" type="checkbox" value="' . $subcategory["sub_id"] . '" id="flexCheckDefault2' . $subcategory["sub_id"] . '">
<label class="form-check-label" for="flexCheckDefault2' . $subcategory["sub_id"] . '">
' . $subcategory["sub_name"] . '
</label>
</div>';
}
}

echo '</div>';
}
?>
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

$(document).ready(function() {
let debounceTimer;
let user = ''; // تعريف متغير user

function load_data(page = 1, search = '', display = '', category = [], sub_category = [], userParam = '') {
console.log("📤 إرسال البيانات:", { page, search, display, category, sub_category, userParam });

$.ajax({
url: 'getStock',
method: 'POST',
data: {
page: page,
search: encodeURIComponent(search),
display: display,
category: category,
sub_category: sub_category,
user: userParam
},
dataType: 'html',
cache: false,
beforeSend: function () {
$('.loader').html(`
<div class="progress" role="progressbar" aria-label="Animated striped example" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100">
<div class="progress-bar progress-bar-striped progress-bar-animated" style="width: 100%"></div>
</div>
`);
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

function getSelectedCategories() {
let categories = [];
$("input[name='category[]']:checked").each(function () {
categories.push($(this).val());
});
return categories;
}

function getSelectedSubCategories() {
let subCategories = [];
$("input[name='sub_category[]']:checked").each(function () {
subCategories.push($(this).val());
});
return subCategories;
}

$(document).on('change', '.user', function () {
user = $(this).val();
clearTimeout(debounceTimer);
debounceTimer = setTimeout(function () {
let search = $('.searchbox').val();
let display = $('.display').val();
let category = getSelectedCategories();
let sub_category = getSelectedSubCategories();
load_data(1, search, display, category, sub_category, user);
}, 300);
});

$('.searchbox').keyup(function() {
let search = $(this).val();
let display = $('.display').val();
let category = getSelectedCategories();
let sub_category = getSelectedSubCategories();
load_data(1, search, display, category, sub_category, user);
});

$('.display').change(function() {
let display = $(this).val();
let search = $('.searchbox').val();
let category = getSelectedCategories();
let sub_category = getSelectedSubCategories();
load_data(1, search, display, category, sub_category, user);
});

$(document).on('click', "input[name='category[]']", function() {
let category = getSelectedCategories();
let search = $('.searchbox').val();
let display = $('.display').val();
let sub_category = getSelectedSubCategories();
load_data(1, search, display, category, sub_category, user);
});

$(document).on('click', "input[name='sub_category[]']", function() {
let sub_category = getSelectedSubCategories();
let search = $('.searchbox').val();
let display = $('.display').val();
let category = getSelectedCategories();
load_data(1, search, display, category, sub_category, user);
});

$(document).on('click', '.page-link', function(event) {
event.preventDefault();
let page = $(this).attr('data-page');
let search = $('.searchbox').val();
let display = $('.display').val();
let category = getSelectedCategories();
let sub_category = getSelectedSubCategories();
load_data(page, search, display, category, sub_category, user);
});

load_data(1, '', '', [], [], user);
});
</script>

<?php 
}
}elseif($do == "new"){

if ((hasUserPermission($con, $loginId, 27 ,'admin')) || ($loginRank == "user") || hasUserPermissionAide($con, $loginId, 51 ,'user')){

$id = "formId";
$result = "data_result";
$action = "newStock";
$method = "post";
formAwdStart ($id,$result,$action,$method); 


// التحقق من القيم المستلمة لتجنب الأخطاء
$category = isset($_GET['category']) ? intval($_GET['category']) : 0;
$sub_category = isset($_GET['sub_category']) ? intval($_GET['sub_category']) : 0;

// استعلام warehouse
$stmt = $con->prepare("SELECT * FROM warehouse WHERE wh_unlink = '0' ORDER BY wh_name");
$stmt->execute();
$warehouse = $stmt->fetchAll();

// استعلام المستخدمين
$stmt = $con->prepare("SELECT * FROM users WHERE user_unlink = '0' AND user_rank = 'user' ORDER BY user_name");
$stmt->execute();
$user = $stmt->fetchAll();

// استعلام الفئات الرئيسية
$stmt = $con->prepare("SELECT * FROM classes WHERE c_unlink = '0' ORDER BY c_name");
$stmt->execute();
$classe = $stmt->fetchAll();

$stmt = $con->prepare("SELECT * FROM brand WHERE brand_unlink = '0' ORDER BY brand_name");
$stmt->execute();
$brand = $stmt->fetchAll();



// استعلام الفئات الفرعية (مستخدم bindParam لتجنب SQL Injection)
$stmt = $con->prepare("SELECT * FROM s_classes WHERE sub_unlink = '0' AND sub_category = :category ORDER BY sub_name");
$stmt->bindParam(':category', $category, PDO::PARAM_INT);
$stmt->execute();
$sub = $stmt->fetchAll();
?>





<div class="card">
<div class="card-header">
<h5><b>+</b> Ajouter Produits</h5>
</div>

<div class="card-body">
<div class="row">

<!-- اختيار الفئة -->
<div class="col-sm-12">
<div class="my-3">
<div class="input">Catégorie</div>
<select name="category" class="js-select category w-100">
<option value="0" disabled selected>Choisir Catégorie</option>
<?php foreach ($classe as $row): ?>
<?php
$query = "SELECT * FROM type WHERE type_id = '".$row['c_type']."' LIMIT 1";
$stmt = $con->prepare($query);
$stmt->execute();
$type = $stmt->fetch();

?>
<option value="<?= $row['c_id']; ?>" <?= ($category == $row['c_id']) ? 'selected' : ''; ?>>
<?= $type['type_name']; ?>-->(<?= $row['c_name']; ?>)
</option>
<?php endforeach; ?>
</select>
</div>
</div>

<!-- اختيار الفئة الفرعية -->
<div class="col-sm-6" style='display:none'>
<div class="my-3">
<div class="input">Sous-Catégorie</div>
<select name="sub_category" class="js-select sub_category w-100">
<option value="0" disabled selected>Choisir Sous-Catégorie</option>
<?php foreach ($sub as $row): ?>


<option value="<?= $row['sub_id']; ?>" <?= ($sub_category == $row['sub_id']) ? 'selected' : ''; ?>>
<?= $row['sub_name']; ?>
</option>
<?php endforeach; ?>
</select>
</div>
</div>

<?php if ($loginRank == "admin"):?>

<!-- اختيار البائع -->
<div class="col-sm-12">
<div class="my-3">
<div class="input">Vendeur</div>
<select name="user" class="js-select w-100">
<option value="0" disabled selected>Choisir Vendeur</option>
<?php foreach ($user as $row): ?>
<option value="<?= $row['user_id']; ?>"><?= ($row['user_name']); ?></option>
<?php endforeach; ?>
</select>
</div>
</div>
<?php elseif ($loginRank == "user"): ?>
<input type='hidden' name='user' value='<?=$loginId;?>'/>


<?php elseif ($loginRank == "aide"): ?>
<input type='hidden' name='user' value='<?=$loginUser['user_aide'];?>'/>

<?php endif; ?>

<!-- اختيار المستودع -->
<div class="col-sm-12">
<div class="my-3">
<div class="input">Entrepôt - Agence</div>
<select name="warehouse" class="js-select w-100">
<option value="0" disabled selected>Choisir Entrepôt</option>
<?php foreach ($warehouse as $row): ?>
<option value="<?= $row['wh_id']; ?>"><?= ($row['wh_name']); ?></option>
<?php endforeach; ?>
</select>
</div>
</div>

<div class="col-sm-6" style='display:none'>
<div class="my-3">
<div class="input">Marque</div>
<select name="brand" class="js-select w-100">
<option value="0" disabled selected>Choisir Marque</option>
<option value="0">Sans Marque</option>

<?php foreach ($brand as $row): ?>
<option value="<?= $row['brand_id']; ?>"><?= $row['brand_name']; ?></option>
<?php endforeach; ?>
</select>
</div>
</div>




<div class="col-sm-6" style='display:none'>
<div class="my-3">
<div class="input">Code Bar</div>
<input name="code" type="text" class="form-control" placeholder=""/>
</div>
</div>



<!-- حقل السعر والكمية -->
<div class="col-sm-3" style='display:none'>  
<div class="my-3">
<div class="input">Prix d'Achat</div>
<input name="buy" type="number" class="form-control" placeholder=""/>
</div>
</div>

<div class="col-sm-3" style='display:none'>
<div class="my-3">
<div class="input">Prix de Vente</div>
<input name="sell" type="number" class="form-control" placeholder=""/>
</div>
</div>


<div class="col-sm-3" style='display:none'>
<div class="my-3">
<div class="input">Montant Avant Remise</div>
<input name="discount" type="number" class="form-control" placeholder=""/>
</div>

</div>
<div class="col-sm-6" >
<div class="my-3">
<div class="input">Qté</div>
<input name="qty" type="number" class="form-control" placeholder=""/>
</div>
</div>

<!-- حقل اسم المنتج -->
<div class="col-sm-6">
<div class="my-3">
<div class="input">Le Nom de Produit</div>
<input name="name" type="text" class="form-control" placeholder=""/>
</div>
</div>



<div class="col-sm-12">
<div class="my-3">
<div class="form-group">
<label for="images">Photo de Produit :</label>
<input type="file" name="images[]" id="images" multiple class="form-control">
</div>
</div>
</div>


<!-- حقل الملاحظات -->
<div class="col-sm-12">
<div class="my-3">
<div class="input">Note</div>
<input name="note" type="text" class="form-control" placeholder=""/>
</div>
</div>

<!-- حقل تفاصيل المنتج -->
<div class="col-sm-12">
<div class="input">Détails de Produit</div>
<textarea class="editor" name="details"></textarea>
</div>

<!-- زر التأكيد -->
<div class="col-sm-12 text-center">
<div id ='<?php print $result ;?>'></div>
</div>

<!-- زر التأكيد -->
<div class="col-sm-12 text-center">
<button class="btn my-3 btn-primary">Valider</button>
</div>

</div>
</div>
</div>

<script>
$(document).ready(function(){
// عند تغيير الفئة، اجلب الفئات الفرعية باستخدام AJAX
$('.category').change(function(){
let category_id = $(this).val();
window.location.href = `stocks?do=new&category=${category_id}`;
});

$('.sub_category').change(function(){
let sub_category_id = $(this).val();
let category_id = $('.category').val();
window.location.href = `stocks?do=new&category=${category_id}&sub_category=${sub_category_id}`;
});
});
</script>
<?php
formAwdEnd ();
}
}elseif($do == "edit"){
if ($loginRank == "admin" || ($loginRank == "user" && $stock['p_state'] == 0)) {


?>
<div class="row">





<div class="col-sm-8">
<?php
$id = "formId";  // معرّف النموذج
$result = "data_result"; 
$action = "editStock"; 
$method = "post"; 
formAwdStart($id, $result, $action, $method); 

// استلام معرف المنتج
$stockId = $_GET['id'] ?? '';

// التحقق من وجود معرف المنتج
if (!empty($stockId)) {
$stmt = $con->prepare("SELECT * FROM products WHERE md5(p_id) = :stock_id");
$stmt->bindParam(':stock_id', $stockId, PDO::PARAM_STR);
$stmt->execute();
$stock = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$stock) {
echo "<div class='alert alert-danger'>Le produit spécifié n'existe pas.</div>";
exit();
}

// تحميل البيانات من قاعدة البيانات

if (!empty($_GET['category'])){
$category = $_GET['category'];
}else{
$category = $stock['p_category'] ?? 0;
}

$sub_category = $stock['p_sub_category'] ?? 0;
$user_id = $stock['p_user'] ?? 0;
$warehouse_id = $stock['p_warehouse'] ?? 0;
$brand_id = $stock['p_brand'] ?? 0;
$buy_price = $stock['p_buy'] ?? '';
$sell_price = $stock['p_sell'] ?? '';
$qty = $stock['p_qty'] ?? '';
$discount = $stock['p_discount'] ?? '';
$name = $stock['p_name'] ?? '';
$code = $stock['p_code'] ?? '';
$note = $stock['p_note'] ?? '';
$details = $stock['p_details'] ?? '';

// استعلام الفئات الرئيسية
$stmt = $con->prepare("SELECT * FROM classes WHERE c_unlink = '0' ORDER BY c_name");
$stmt->execute();
$classe = $stmt->fetchAll();

// استعلام الفئات الفرعية
$stmt = $con->prepare("SELECT * FROM s_classes WHERE sub_unlink = '0' AND sub_category = :category ORDER BY sub_name");
$stmt->bindParam(':category', $category, PDO::PARAM_INT);
$stmt->execute();
$sub = $stmt->fetchAll();

// استعلام المستودعات
$stmt = $con->prepare("SELECT * FROM warehouse WHERE wh_unlink = '0' ORDER BY wh_name");
$stmt->execute();
$warehouse = $stmt->fetchAll();

$stmt = $con->prepare("SELECT * FROM brand WHERE brand_unlink = '0' ORDER BY brand_name");
$stmt->execute();
$brand = $stmt->fetchAll();



// استعلام المستخدمين (البائعين)
$stmt = $con->prepare("SELECT * FROM users WHERE user_unlink = '0' AND user_rank = 'user' ORDER BY user_name");
$stmt->execute();
$user = $stmt->fetchAll();
?>

<input type='hidden' name='id' value='<?= ($stockId); ?>'/>

<div class="card">
<div class="card-header">
<h5><b>Modifier Produit</b></h5>
</div>
<div class="card-body">
<div class="row">
<!-- اختيار الفئة -->
<div class="col-sm-6">
<div class="my-3">
<div class="input">Catégorie</div>
<select name="category" class="category form-control">
<option value="0" disabled>Choisir Catégorie</option>
<?php foreach ($classe as $row): ?>
<option value="<?= $row['c_id']; ?>" <?= ($category == $row['c_id']) ? 'selected' : ''; ?>>
<?= $row['c_name']; ?>
</option>
<?php endforeach; ?>
</select>
</div>
</div>

<!-- اختيار الفئة الفرعية -->
<div class="col-sm-6">
<div class="my-3">
<div class="input">Sous-Catégorie</div>
<select name="sub_category" class="sub_category form-control">
<option value="0" disabled>Choisir Sous-Catégorie</option>
<?php foreach ($sub as $row): ?>
<option value="<?= $row['sub_id']; ?>" <?= ($sub_category == $row['sub_id']) ? 'selected' : ''; ?>>
<?= $row['sub_name']; ?>
</option>
<?php endforeach; ?>
</select>
</div>
</div>

<?php if ($loginRank == "admin"):?>

<!-- اختيار البائع -->
<div class="col-sm-6">
<div class="my-3">
<div class="input">Vendeur</div>
<select name="user" class="form-control">
<option value="0" disabled>Choisir Vendeur</option>
<?php foreach ($user as $row): ?>
<option value="<?= $row['user_id']; ?>" <?= ($user_id == $row['user_id']) ? 'selected' : ''; ?>>
<?= ($row['user_name']); ?>
</option>
<?php endforeach; ?>
</select>
</div>
</div>
<?php elseif ($loginRank == "user"): ?>
<input type='hidden' name='user' value='<?=$loginId;?>'/>

<?php elseif ($loginRank == "aide"): ?>
<input type='hidden' name='user' value='<?=$loginUser['user_aide'];?>'/>

<?php endif; ?>

<!-- اختيار المستودع -->
<div class="col-sm-6">
<div class="my-3">
<div class="input">Entrepôt</div>
<select name="warehouse" class="form-control">
<option value="0" disabled>Choisir Entrepôt</option>
<?php foreach ($warehouse as $row): ?>
<option value="<?= $row['wh_id']; ?>" <?= ($warehouse_id == $row['wh_id']) ? 'selected' : ''; ?>>
<?= ($row['wh_name']); ?>
</option>
<?php endforeach; ?>
</select>
</div>
</div>

<div class="col-sm-6"  style="display:none;">
<div class="my-3">
<div class="input">Marque</div>
<select name="brand" class="form-control">
<option value="0" disabled>Choisir Marque</option>
<?php foreach ($brand as $row): ?>
<option value="<?= $row['brand_id']; ?>" <?= ($brand_id == $row['brand_id']) ? 'selected' : ''; ?>>
<?= ($row['brand_name']); ?>
</option>
<?php endforeach; ?>
</select>
</div>
</div>



<div class="col-sm-6" style="display:none;">
<div class="my-3">
<div class="input">Code Bar</div>
<input name="code" type="text" class="form-control" value="<?= ($code); ?>"/>
</div>
</div>

<!-- حقول الأسعار والكمية -->
<div class="col-sm-3"  style="display:none;">
<div class="my-3">
<div class="input">Prix d'Achat</div>
<input name="buy" type="number" class="form-control" value="<?= ($buy_price); ?>"/>
</div>
</div>

<div class="col-sm-3"  style="display:none;">
<div class="my-3">
<div class="input">Prix de Vente</div>
<input name="sell" type="number" class="form-control" value="<?= ($sell_price); ?>"/>
</div>
</div>

<div class="col-sm-6">
<div class="my-3">
<div class="input">Qté</div>
<input name="qty" type="number" class="form-control" value="<?= ($qty); ?>"/>
</div>
</div>



<div class="col-sm-3"  style="display:none;">
<div class="my-3">
<div class="input">Montant Avant Remise</div>
<input name="discount" type="number" class="form-control" value="<?= ($discount); ?>"/>
</div>
</div>



<!-- حقل اسم المنتج -->
<div class="col-sm-6">
<div class="my-3">
<div class="input">Le Nom de Produit</div>
<input name="name" type="text" class="form-control" value="<?= ($name); ?>"/>
</div>
</div>

<!-- حقل الملاحظات -->
<div class="col-sm-12">
<div class="my-3">
<div class="input">Note</div>
<input name="note" type="text" class="form-control" value="<?= ($note); ?>"/>
</div>
</div>

<!-- حقل التفاصيل -->
<div class="col-sm-12">
<div class="input">Détails de Produit</div>
<textarea class="form-control editor" name="details"><?=$details; ?></textarea>
</div>



<?php if ($loginRank == "admin"):?>
<div class="col-sm-12">
  <div class="my-3">
    <label>État de stock</label>
    <select name="state" class="form-select">
      <option value="0" <?= $stock['p_state'] == 0 ? 'selected' : '' ?>>Inactif</option>
      <option value="1" <?= $stock['p_state'] == 1 ? 'selected' : '' ?>>Actif</option>
    </select>
  </div>
</div>
<?php else : ?>
<input type='hidden' name='state' value='<?= $stock['p_state']; ?>'/>
<?php endif ; ?>





<div class="card my-5">
<div class="card-header">
<h5><b>Options du produit</b></h5>
</div>

<div class="card-body">

<div id="option-units-container"></div>

<div class="text-center mt-4">
<button type="button" onclick="addOptionUnit()" class="btn btn-sm btn-primary">
Ajouter une unité d'option
</button>
</div>

</div>
</div>

<script>
let unitIndex = 0;
function addOptionUnit() {
const container = document.getElementById("option-units-container");

const unitDiv = document.createElement("div");
unitDiv.className = "option-unit border rounded p-3 mb-3 bg-light";
unitDiv.setAttribute("data-unit-id", unitIndex); // نضيف معرف واضح

unitDiv.innerHTML = `
<hr>
<div class="mb-2">
<label class="form-label"><b>Nom de l'unité d'option:</b></label>
<input type="text" class="form-control" name="options[${unitIndex}][unit_name]" required placeholder="ex: Taille, Couleur">
</div>

<div class="option-values" id="option-values-${unitIndex}">
<!-- Valeurs ajoutées ici -->
</div>

<div class="mt-2">
<button type="button" onclick="addOptionValue(${unitIndex})" class="btn btn-sm btn-outline-secondary">Ajouter une valeur</button>
<button type="button" class="btn btn-sm btn-outline-danger ms-2" onclick="this.closest('.option-unit').remove()">Supprimer cette unité</button>
</div>
`;

container.appendChild(unitDiv);

// ✅ فقط أضف قيمة ابتدائية إذا لم تكن موجودة قيم بالفعل (مثلاً من إعادة تحميل)
setTimeout(() => {
const valuesContainer = document.getElementById(`option-values-${unitIndex}`);
if (valuesContainer && valuesContainer.children.length === 0) {
addOptionValue(unitIndex);
}
}, 0);

unitIndex++; // مهم! زيادته بعد الإضافة
}


function addOptionValue(unitId) {
const valuesContainer = document.getElementById(`option-values-${unitId}`);
if (!valuesContainer) return;

const valueDiv = document.createElement("div");
valueDiv.className = "row g-2 align-items-center mb-2 option-value";
valueDiv.innerHTML = `
<div class="col-md-5">
<input type="text" class="form-control" name="options[${unitId}][values][]" placeholder="Valeur (ex: Rouge, M)" required>
</div>
<div class="col-md-4">
<input type="number" step="0.01" class="form-control" name="options[${unitId}][prices][]" placeholder="Prix supplémentaire (€)" value="0">
</div>
<div class="col-md-3">
<button class="btn btn-sm btn-danger" type="button" onclick="this.closest('.option-value').remove()">Supprimer</button>
</div>
`;

valuesContainer.appendChild(valueDiv);
}

</script>


















<!-- زر التأكيد -->
<div class="col-sm-12 text-center">
<div id ='<?php print $result ;?>'></div>
</div>


<!-- زر التأكيد -->
<div class="col-sm-12 text-center">
<button class="btn my-3 btn-primary">Valider</button>
</div>
</div>
</div>
</div>





<script>
$(document).ready(function(){
let stockId = "<?= ($stockId); ?>";

$('.category').change(function(){
let category_id = $(this).val();
window.location.href = `stocks?do=edit&id=${stockId}&category=${category_id}`;
});

$('.sub_category').change(function(){
let sub_category_id = $(this).val();
let category_id = $('.category').val();
window.location.href = `stocks?do=edit&id=${stockId}&category=${category_id}&sub_category=${sub_category_id}`;
});
});
</script>




<?php
} else {
echo "<div class='alert alert-danger'>Aucun identifiant spécifié.</div>";
}
formAwdEnd();
?>







<div class="card my-5">
<div class="card-body">


<?php
// استعلام جميع السجلات
$stmt = $con->prepare("SELECT * FROM stock_log WHERE md5(p_id) = '".$stockId."' AND (rank != 'user' OR rank IS null)   ORDER BY change_date DESC");
$stmt->execute();
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);




?>


<h6>Historique du Stock (Modification Qté)</h6>

<table class="table table-bordered table-striped">
<thead class="table-dark">
<tr>
<th>#</th>
<th>Produit ID</th>
<th>Utilisateur ID</th>
<th>Changement</th>
<th>Ancienne QTE</th>
<th>Nouvelle QTE</th>
<th>Type d'opération</th>
<th>Date</th>
</tr>
</thead>
<tbody>
<?php foreach ($logs as $log): ?>
<?php
$stmt = $con->prepare("SELECT * FROM users WHERE user_id = '".$log['user_id']."' LIMIT 1");
$stmt->execute();
$via_user = $stmt->fetch();
?>
<tr>
<td><?= ($log['log_id']) ?></td>
<td><?= ($log['p_id']) ?></td>
<td><?= ($via_user['user_id']) ?>-<?= ($via_user['user_name']) ?></td>
<td><?= ($log['change_qty']) ?></td>
<td><?= ($log['old_qty']) ?></td>
<td><?= ($log['new_qty']) ?></td>
<td><?= ($log['operation_type']) ?></td>
<td><?= ($log['change_date']) ?></td>


</tr>
<?php endforeach; ?>
</tbody>
</table>

</div>
</div>










<div class="card my-5">
<div class="card-body">


<?php
// استعلام جميع السجلات
$stmt = $con->prepare("SELECT * FROM stock_log WHERE md5(p_id) = '".$stockId."' AND rank = 'user' ORDER BY change_date DESC");
$stmt->execute();
$logs_c = $stmt->fetchAll(PDO::FETCH_ASSOC);




?>


<h6>Historique du Stock (Colis Ajouté par Stock)</h6>

<table class="table table-bordered table-striped">
<thead class="table-dark">
<tr>
<th>#</th>
<th>Produit ID</th>
<th>Utilisateur ID</th>
<th>Changement</th>
<th>Ancienne QTE</th>
<th>Nouvelle QTE</th>
<th>Type d'opération</th>
<th>Date</th>
</tr>
</thead>
<tbody>
<?php foreach ($logs_c as $log): ?>
<?php
$stmt = $con->prepare("SELECT * FROM users WHERE user_id = '".$log['user_id']."' LIMIT 1");
$stmt->execute();
$via_user = $stmt->fetch();
?>
<tr>
<td><?= ($log['log_id']) ?></td>
<td><?= ($log['p_id']) ?></td>
<td><?= ($via_user['user_id']) ?>-<?= ($via_user['user_name']) ?></td>
<td><?= ($log['change_qty']) ?></td>
<td><?= ($log['old_qty']) ?></td>
<td><?= ($log['new_qty']) ?></td>
<td><?= ($log['operation_type']) ?></td>
<td><?= ($log['change_date']) ?></td>


</tr>
<?php endforeach; ?>
</tbody>
</table>

</div>
</div>


























</div>






<div class="col-sm-4">



<?php
$id = "formId_photo";  // معرّف النموذج
$result = "data_result_photo"; 
$action = "editStockImage"; 
$method = "post"; 
formAwdStart($id, $result, $action, $method); 

// استلام معرف المنتج
$stockId = $_GET['id'] ?? '';

// التحقق من وجود معرف المنتج
if (!empty($stockId)) {
$stmt = $con->prepare("SELECT * FROM products WHERE md5(p_id) = :stock_id");
$stmt->bindParam(':stock_id', $stockId, PDO::PARAM_STR);
$stmt->execute();
$stock = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$stock) {
echo "<div class='alert alert-danger'>Le produit spécifié n'existe pas.</div>";
exit();
}

?>

<input type='hidden' name='id' value='<?=$stock['p_id']; ?>'/>

<div class="card">
<div class="card-header">
<h5><b>Modifier Photos de Produit</b></h5>
</div>
<div class="card-body">
<div class="row">




<!-- حقل تحميل الصور -->
<div class="form-group">
<label for="images">Télécharger des images :</label>
<input type="file" name="images[]" id="images" multiple class="form-control">
</div>





<!-- زر التأكيد -->
<div class="col-sm-12 text-center">
<div id ='<?php print $result ;?>'></div>
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







<?php
// جلب الصور المخزنة في قاعدة البيانات
$stmt = $con->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY is_main DESC");
$stmt->execute([$stock['p_id']]);
$images = $stmt->fetchAll();
?>

<div class="row">
<?php foreach ($images as $image): ?>
<div class="col-md-3 text-center image-container" id="image-<?= $image['id']; ?>">
<img src="uploads/products/<?= ($image['image_url']); ?>" class="img-thumbnail" width="100">
<br>
<?php if ($image['is_main'] == 1): ?>
<span class="badge bg-success">Image Principale</span>
<?php else: ?>
<button class="btn btn-primary btn-sm mt-2 set-main-image" data-id="<?= $image['id']; ?>">Principale</button>
<?php endif; ?>
<button class="btn btn-danger btn-sm mt-2 delete-image" data-id="<?= $image['id']; ?>">Supprimer</button>
</div>
<?php endforeach; ?>
</div>

<script>
$(document).ready(function() {
$(".set-main-image").click(function() {
let imageId = $(this).data("id");

$.ajax({
url: "set_main_image", // ملف تعيين الصورة الأساسية
type: "POST",
data: { id: imageId },
success: function(response) {
location.reload(); // تحديث الصفحة لإظهار التغيير
}
});
});

$(".delete-image").click(function() {
let imageId = $(this).data("id");

if (confirm("Êtes-vous sûr de vouloir supprimer cette image ?")) {
$.ajax({
url: "delete_image",
type: "POST",
data: { id: imageId },
success: function(response) {
$("#image-" + imageId).fadeOut("slow", function() {
$(this).remove();
});
}
});
}
});
});
</script>



<?php
// استرجاع المجموعات الخاصة بالمنتج
$productId = $stock['p_id'];
$stmt = $con->prepare("SELECT * FROM product_option_groups WHERE product_id = ?");
$stmt->execute([$productId]);
$groups = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($groups as $group):
$groupId = $group['id'];
?>
<div class="card my-5" data-group-id="<?= $groupId ?>">
<div class="card-header d-flex justify-content-between align-items-center">
<input type="text" class="form-control form-control-sm w-50 group-name-input"
value="<?= ($group['group_name']) ?>">
<button class="btn btn-sm btn-danger delete-group-btn" data-group-id="<?= $groupId ?>">Supprimer le groupe</button>
</div>
<div class="card-body">
<table class="table table-bordered table-sm mb-0">
<thead>
<tr>
<th>Nom</th>
<th>Prix supplémentaire</th>
<th>Action</th>
</tr>
</thead>
<tbody>
<?php
$stmt2 = $con->prepare("SELECT * FROM product_option_values WHERE group_id = ?");
$stmt2->execute([$groupId]);
$values = $stmt2->fetchAll(PDO::FETCH_ASSOC);

foreach ($values as $value):
?>
<tr data-value-id="<?= $value['id'] ?>">
<td><input type="text" class="form-control form-control-sm value-name-input"
value="<?= ($value['value_name']) ?>"></td>
<td><input type="number" step="0.01" class="form-control form-control-sm value-price-input"
value="<?= $value['value_price'] ?>"></td>
<td><button class="btn btn-sm btn-danger delete-value-btn" data-value-id="<?= $value['id'] ?>">Supprimer</button></td>
</tr>
<?php endforeach ?>
</tbody>
</table>
</div>
</div>
<?php endforeach ?>
</div>

<!-- Bootstrap Modal لعرض الرسائل -->
<div class="modal fade" id="resultModal" tabindex="-1" aria-labelledby="resultModalLabel" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered">
<div class="modal-content">
<div class="modal-header bg-primary text-white">
<h5 class="modal-title" id="resultModalLabel">Résultat</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
</div>
<div class="modal-body" id="resultModalBody">
<!-- الرسالة تظهر هنا -->
</div>
<div class="modal-footer">
<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
</div>
</div>
</div>
</div>

<script>
$(document).ready(function () {

function showModalMessage(message, isError = false) {
$('#resultModalLabel').text(isError ? 'Erreur' : 'Succès');
$('#resultModalBody').text(message);
$('.modal-header').toggleClass('bg-danger', isError).toggleClass('bg-primary', !isError);
const modal = new bootstrap.Modal(document.getElementById('resultModal'));
modal.show();
}

// تعديل اسم المجموعة
$(".group-name-input").on("change", function () {
let groupId = $(this).closest(".card").data("group-id");
let newName = $(this).val();
$.post("update_group_name", { group_id: groupId, group_name: newName })
.done(response => showModalMessage(response))
.fail(err => showModalMessage("Une erreur s'est produite lors de la mise à jour.", true));
});

// تعديل اسم أو سعر القيمة
$(".value-name-input, .value-price-input").on("change", function () {
let row = $(this).closest("tr");
let valueId = row.data("value-id");
let field = $(this).hasClass("value-name-input") ? 'name' : 'price';
let newValue = $(this).val();
$.post("update_value", { value_id: valueId, field: field, new_value: newValue })
.done(response => showModalMessage(response))
.fail(err => showModalMessage("Une erreur s'est produite lors de la mise à jour.", true));
});

// حذف المجموعة
$(".delete-group-btn").on("click", function () {
let groupId = $(this).data("group-id");
if (!confirm("Voulez-vous vraiment supprimer ce groupe ?")) return;
$.post("delete_option_group", { group_id: groupId })
.done(response => {
$(`[data-group-id=${groupId}]`).fadeOut();
showModalMessage(response);
})
.fail(err => showModalMessage("Une erreur s'est produite lors de la suppression.", true));
});

// حذف القيمة
$(".delete-value-btn").on("click", function () {
let valueId = $(this).data("value-id");
if (!confirm("Voulez-vous vraiment supprimer cette valeur ?")) return;
$.post("delete_value", { value_id: valueId })
.done(response => {
$(`[data-value-id=${valueId}]`).fadeOut();
showModalMessage(response);
})
.fail(err => showModalMessage("Une erreur s'est produite lors de la suppression.", true));
});

});
</script>



</div>
</div>
<?php
}
}else{






}
?>






</div>
</div>

























</main>

<?php include get_file("Admin/admin_footer");?>