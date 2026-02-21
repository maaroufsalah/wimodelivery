<?php 

$bootstrap = new Bootstrap();
$bootstrap->runBootstrap();

include get_file("files/sql/get/os_settings");
include get_file("files/sql/get/session");
include_once get_file("files/sql/get/functions");
include get_file("Admin/admin_header");


define ("page_title","Facture - Livreur");


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
if ((hasUserPermission($con, $loginId, 18 ,'admin')) || $loginRank == "delivery"){


// جلب المستخدمين
$stmt = $con->prepare("SELECT * FROM users WHERE user_unlink = '0' AND user_rank = 'delivery' ORDER BY user_name ASC");
$stmt->execute();
$users = $stmt->fetchAll();
?>

<!-- زر إنشاء فاتورة -->
<div style="text-align: right;">
<?php if ($loginRank == "admin"):?>
<a href='?do=new' class="btn btn-primary my-3 btn-sm">Créer une facture</a>
<? endif ;?>
</div>

<!-- بطاقة الفلاتر -->
<div class="card" style="border-radius:0rem">
<div class="card-body">
<div class="row">

<!-- البحث -->
<div class='col-6' style="text-align: left;">
<h6>Recherche</h6>
<input type="text" class="searchbox form-control mb-3" style="width:100%" placeholder="Recherche..."/>
</div>

<!-- عدد العناصر المعروضة -->
<div class='col-6'>
<h6>Afficher</h6>
<select class="display form-select">
<option value="10">10</option>
<option value="50">50</option>
<option value="100">100</option>
<option value="200">200</option>
</select>
</div>

<?php if ($loginRank == "admin"):?>

<!-- اختيار العميل -->
<div class="col-sm-6">
<div class="my-3">
<div class="input">livreur</div>
<select class="js-select w-100 filter-user">
<option value="0" disabled selected>Choisir livreur</option>
<?php foreach ($users as $row): ?>
<option value='<?= $row['user_id'] ?>'><?= htmlspecialchars($row['user_name']) ?></option>
<?php endforeach; ?>
</select>
</div>
</div>
<?php endif; ?>

<!-- اختيار الحالة -->
<div class="col-sm-6">
<div class="my-3">
<div class="input">État</div>
<select class="js-select w-100 filter-state">
<option value="-1"  selected>Choisir</option>
<option value='0'>Non-Payé</option>
<option value='1'>Payé</option>
</select>
</div>
</div>

</div>

<hr>

<!-- Loader -->
<div class="loader"></div>

<!-- محتوى AJAX -->
<div id="dynamic_content"></div>
</div>
</div>

<!-- سكربت الجافاسكريبت -->
<script>
$(document).ready(function(){

// تحميل البيانات عند تحميل الصفحة
load_data(1);

function load_data(page, search = '', display = '', state = '', user = '') {
console.log("📤 Envoi des données:", { page, search, display, state, user });

$.ajax({
url: 'getDeliveryInvoice', // اسم سكربت PHP الذي يُعالج الطلب
method: 'POST',
data: { page, search, display, state, user },
dataType: 'html',
cache: false,
beforeSend: function () {
$('.loader').html('<div class="progress" role="progressbar" aria-label="Chargement..." aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"><div class="progress-bar progress-bar-striped progress-bar-animated" style="width: 100%"></div></div>');
},
success: function (data) {
$('#dynamic_content').html(data);
$('.loader').html('');
},
error: function (xhr, status, error) {
console.error('⚠️ Erreur AJAX:', error);
$('.loader').html('<div class="text-danger">Erreur de chargement</div>');
}
});
}


let search = $(this).val();
let display = $('.display').val();
let state = $('.filter-state').val();
let user = $('.filter-user').val();
load_data(1, search, display, state, user);

// البحث
$('.searchbox').keyup(function() {
let search = $(this).val();
let display = $('.display').val();
let state = $('.filter-state').val();
let user = $('.filter-user').val();
load_data(1, search, display, state, user);
});

// عدد العناصر
$('.display').change(function() {
let display = $(this).val();
let search = $('.searchbox').val();
let state = $('.filter-state').val();
let user = $('.filter-user').val();
load_data(1, search, display, state, user);
});

// تغيير المستخدم
$('.filter-user').change(function() {
let user = $(this).val();
let display = $('.display').val();
let search = $('.searchbox').val();
let state = $('.filter-state').val();
load_data(1, search, display, state, user);
});

// تغيير الحالة
$('.filter-state').change(function() {
let state = $(this).val();
let display = $('.display').val();
let search = $('.searchbox').val();
let user = $('.filter-user').val();
load_data(1, search, display, state, user);
});

// التنقل بين الصفحات
$(document).on('click', '.page-link', function(e) {
e.preventDefault();
let page = $(this).data('page');
let search = $('.searchbox').val();
let display = $('.display').val();
let state = $('.filter-state').val();
let user = $('.filter-user').val();
load_data(page, search, display, state, user);
});
});
</script>

<?php
}
}elseif($do == "new"){
if (hasUserPermission($con, $loginId, 19 ,'admin')){
$getUser = $_GET['user'] ?? 0;

$id = "formId";  // معرّف النموذج
$result = "data_result"; 
$action = "newDeliveryInvoice"; 
$method = "post"; 
formAwdStart($id, $result, $action, $method); 


$stmt = $con->prepare("
    SELECT u.user_id, u.user_name, COUNT(o.or_id) AS total_orders
    FROM users u
    INNER JOIN orders o ON o.or_delivery_user = u.user_id
    WHERE o.or_state_delivery IN(1,60) AND o.or_delivery_invoice = '0'
      AND u.user_unlink = 0
      AND u.user_rank = 'delivery'
    GROUP BY u.user_id, u.user_name
    ORDER BY u.user_name
");
$stmt->execute();
$users = $stmt->fetchAll();

// display orders


$dateFrom = $_GET['date_from'] ?? '';
$dateTo = $_GET['date_to'] ?? '';

if (!empty($dateFrom) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateFrom)) {
    $dateFrom = '';
}
if (!empty($dateTo) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateTo)) {
    $dateTo = '';
}

$orderQuery = "
SELECT * 
FROM orders 
WHERE 
or_unlink = '0' 
AND or_delivery_user = :user 
AND or_delivery_invoice = '0' 
AND or_state_delivery IN (1,60)
";

$params = [':user' => $getUser];

if (!empty($dateFrom) && !empty($dateTo)) {
    $orderQuery .= " AND DATE(or_delivered) BETWEEN :dateFrom AND :dateTo";
    $params[':dateFrom'] = $dateFrom;
    $params[':dateTo'] = $dateTo;
} elseif (!empty($dateFrom)) {
    $orderQuery .= " AND DATE(or_delivered) >= :dateFrom";
    $params[':dateFrom'] = $dateFrom;
} elseif (!empty($dateTo)) {
    $orderQuery .= " AND DATE(or_delivered) <= :dateTo";
    $params[':dateTo'] = $dateTo;
}

$orderQuery .= " ORDER BY or_id DESC";

$stmt = $con->prepare($orderQuery);
$stmt->execute($params);
$orders = $stmt->fetchAll();



?>

<input type="hidden" name="order_id" value="">


<div class="card">
<div class="card-header">
<h5><b>Ajouter une facture</b></h5>
</div>
<div class="card-body">
<div class="row">

<div class="col-sm-12">
<div class="my-3">
<div class="input">Compte facturation (Livreur)</div>
<select name='user' class='js-select d-user w-100'>
  <option value='0' disabled selected>Choisir Livreur</option>
  <?php foreach ($users as $row): ?>
    <option value='<?= htmlspecialchars($row['user_id']) ?>' <?= ($row['user_id'] == $getUser) ? 'selected' : '' ?>>
      <?= htmlspecialchars($row['user_name']) ?> (<?= $row['total_orders'] ?> livraisons)
    </option>
  <?php endforeach; ?>
</select>

</div>
</div>



<?php if (!empty($getUser)): ?>
<div class="col-sm-12">
    <div class="my-3">
        <label for="filter-date-from" class="form-label">De</label>
        <input type="date" id="filter-date-from" name="date_from" class="form-control" value="<?= htmlspecialchars($_GET['date_from'] ?? '') ?>">
    </div>
    <div class="my-3">
        <label for="filter-date-to" class="form-label">À</label>
        <input type="date" id="filter-date-to" name="date_to" class="form-control" value="<?= htmlspecialchars($_GET['date_to'] ?? '') ?>">
    </div>
</div>
<?php endif; ?>










<div class="col-sm-12">
<h5 class='my-3'><b>Colis pouvant être facturés</b></h5> 
</div>
<hr>
<div class="col-sm-12">

<?php include get_file("files/sql/get/fetch_orders");?>






<?php if (empty($getUser)): ?>
<div class="col-sm-12 text-center">
<div class="row my-3">
<div class="col-4">
<a><b>Livreur</b></a>
</div>

<div class="col-4">
<a><b>colis</b></a>
</div>

<div class="col-4">
<a><b>Gérer</b></a>
</div>

</div>
<?php foreach ($users as $row): ?>
<div class="row my-3">
<div class="col-4">
<a><?= htmlspecialchars($row['user_name']) ?></a>
</div>

<div class="col-4">
<a>(<?= $row['total_orders'] ?>)</a>
</div>

<div class="col-4">
<a href='deliveryInvoice?do=new&user=<?= htmlspecialchars($row['user_id']) ?>' class='btn btn-dark btn-sm'>Créer</a>
</div>

</div>
<hr>
<?php endforeach; ?>
</div>
<?php endif; ?>






</div>








<!-- بطاقة التحكم الثابتة -->
<div class="position-fixed bottom-0 start-0 end-0 bg-white shadow-lg border-top py-3 px-4 zindex-tooltip" style="z-index: 1055;">
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3">

<!-- زر التأكيد -->
<div class="col-sm-12 text-center">
<div class="my-3">
<div id ='<?php print $result ;?>'></div>
</div>
</div>


<!-- زر تحديد الكل -->
<a id="selectAllBtn" class="btn btn-outline-dark">
<i class="fa-regular fa-square-check"></i> Tout sélectionner
</a>


<!-- زر التأكيد -->
<button  onclick="return collectSelectedIds('formId')" class="btn my-3 btn-primary">Valider</button>



</div>
</div>





</div>
</div>
</div>








<?php
formAwdEnd();


?>




<script>

let selectAllBtn = document.getElementById('selectAllBtn');
let allSelected = false;

selectAllBtn.addEventListener('click', function() {
let checkboxes = document.querySelectorAll('.order-checkbox');
allSelected = !allSelected;

checkboxes.forEach(function(checkbox) {
checkbox.checked = allSelected;
});

selectAllBtn.textContent = allSelected ? 'Désélectionner tout' : 'Sélectionner tout';
});


function collectSelectedIds(formId) {
let selectedIds = [];
// تحديد جميع checkboxes المرتبطة بالفورم المحدد
document.querySelectorAll('.order-checkbox:checked').forEach(function(checkbox) {
selectedIds.push(checkbox.value); // إضافة قيمة order_id
});

// تعيين هذه القيم في hidden input داخل الفورم المحدد
document.querySelector(`#${formId} input[name="order_id"]`).value = selectedIds.join(',');

return true;
}





$('.d-user').change(function(){
    let id = $(this).val();
    window.location.href = `?do=new&user=${id}`;
});

document.getElementById('filter-date-from')?.addEventListener('change', updateDateFilters);
document.getElementById('filter-date-to')?.addEventListener('change', updateDateFilters);

function updateDateFilters() {
    let fromDate = document.getElementById('filter-date-from')?.value || '';
    let toDate = document.getElementById('filter-date-to')?.value || '';
    let url = new URL(window.location.href);
    url.searchParams.set('date_from', fromDate);
    url.searchParams.set('date_to', toDate);
    window.location.href = url.toString();
}


</script>





<?php
}
}elseif($do == "open"){
?>
<div class="row">



</div>
<?php
}else{






}
?>






</div>
</div>

























</main>

<?php include get_file("Admin/admin_footer");?>