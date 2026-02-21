<?php 

$bootstrap = new Bootstrap();
$bootstrap->runBootstrap();

include get_file("files/sql/get/os_settings");
include get_file("files/sql/get/session");
include_once get_file("files/sql/get/functions");
include get_file("Admin/admin_header");


define ("page_title","Facture Vendeur");


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
<?php if ((hasUserPermission($con, $loginId, 15 ,'admin')) || ($loginRank == "user") || hasUserPermissionAide($con, $loginId, 49 ,'user')):?>

<?php
// جلب المستخدمين
$stmt = $con->prepare("SELECT * FROM users WHERE user_unlink = '0' AND user_rank = 'user' ORDER BY user_name ASC");
$stmt->execute();
$users = $stmt->fetchAll();
?>

<!-- زر إنشاء فاتورة -->
<div style="text-align: right;">
<?php if ($loginRank == "admin"):?>
<a href='?do=new' class="btn btn-primary my-3 btn-sm">Créer une facture</a>
<?php endif;?>
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

<?php if (($loginRank == "admin")):?>

<!-- اختيار العميل -->
<div class="col-sm-6">
<div class="my-3">
<div class="input">Client</div>
<select class="js-select w-100 filter-user">
<option value="0" disabled selected>Choisir client</option>
<?php foreach ($users as $row): ?>
<option value='<?= $row['user_id'] ?>'><?= ($row['user_name']) ?></option>
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
url: 'getInvoice', // اسم سكربت PHP الذي يُعالج الطلب
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
<?php endif; ?>

<?php


}elseif($do == "new"){
    ?>
<?php if ((hasUserPermission($con, $loginId, 16 ,'admin'))): ?>

<?php
// قراءة معرّف المستخدم وفلتر التواريخ من GET
$getUser  = isset($_GET['user']) ? (int)$_GET['user'] : 0;
$dateFrom = $_GET['date_from'] ?? '';
$dateTo   = $_GET['date_to'] ?? '';

// بدء نموذج الفاتورة
$id     = "formId";  
$result = "data_result"; 
$action = "newInvoice"; 
$method = "post"; 
formAwdStart($id, $result, $action, $method); 

// جلب المستخدمين مع عدد الطلبات غير المفوترة
$stmt = $con->prepare("
    SELECT 
        u.user_id, 
        u.user_name, 
        u.user_owner,
        COUNT(o.or_id) AS total_orders
    FROM users u
    INNER JOIN orders o ON o.or_trade = u.user_id
    WHERE o.or_state_delivery IN (1,3,60) 
      AND o.or_invoice = '0'
      AND o.or_unlink = '0'
      AND u.user_unlink = 0
      AND u.user_rank = 'user'
    GROUP BY u.user_id, u.user_name, u.user_owner
    ORDER BY u.user_name
");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// حساب المجاميع
$totalUsers  = count($users);
$totalOrders = array_sum(array_column($users, 'total_orders'));

// بناء استعلام الطلبات مع فلتر التاريخ
$orderQuery = "
    SELECT * 
    FROM orders 
    WHERE 
        or_unlink = '0' 
        AND or_trade = :user 
        AND or_invoice = '0' 
        AND or_state_delivery IN (1,3,60)
";
$params = [':user' => $getUser];

// تحقق من صحة التواريخ بصيغة YYYY-MM-DD
if (!empty($dateFrom) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateFrom)) $dateFrom = '';
if (!empty($dateTo) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateTo))   $dateTo   = '';

// إضافة شروط التاريخ حسب الحقول المملوءة
if ($dateFrom && $dateTo) {
    $orderQuery .= " AND DATE(or_delivered) BETWEEN :dateFrom AND :dateTo";
    $params[':dateFrom'] = $dateFrom;
    $params[':dateTo']   = $dateTo;
} elseif ($dateFrom) {
    $orderQuery .= " AND DATE(or_delivered) >= :dateFrom";
    $params[':dateFrom'] = $dateFrom;
} elseif ($dateTo) {
    $orderQuery .= " AND DATE(or_delivered) <= :dateTo";
    $params[':dateTo'] = $dateTo;
}

$orderQuery .= " ORDER BY or_id DESC";

// جلب الطلبات إذا تم اختيار بائع
$orders = [];
if ($getUser > 0) {
    $stmt = $con->prepare($orderQuery);
    $stmt->execute($params);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<input type="hidden" name="order_id" value="">

<div class="card">
<div class="card-header">
    <h5><b>Ajouter une facture</b></h5>
</div>
<div class="card-body">
<div class="row">

<!-- اختيار المستخدم -->
<div class="col-sm-12">
    <div class="my-3">
        <div class="input">Compte facturation (Vendeur)</div>
        <select name='user' class='js-select d-user w-100'>
          <option value='0' disabled selected>Choisir Vendeur</option>
          <?php foreach ($users as $row): ?>
            <option value='<?= $row['user_id'] ?>' <?= ($row['user_id'] == $getUser) ? 'selected' : '' ?>>
              <?= $row['user_name'] ?> - <?= $row['user_owner'] ?> (<?= $row['total_orders'] ?> commandes)
            </option>
          <?php endforeach; ?>
        </select>
    </div>
</div>

<!-- فلتر التاريخ يظهر فقط إذا تم اختيار بائع -->
<?php if (!empty($getUser)): ?>
    <div class="col-sm-6">
        <div class="my-3">
            <label for="filter-date-from" class="form-label">Date de livraison (De)</label>
            <input type="date" id="filter-date-from" class="form-control" value="<?= $dateFrom ?>">
        </div>
    </div>
    <div class="col-sm-6">
        <div class="my-3">
            <label for="filter-date-to" class="form-label">Date de livraison (À)</label>
            <input type="date" id="filter-date-to" class="form-control" value="<?= $dateTo ?>">
        </div>
    </div>
<?php endif; ?>

<div class="col-sm-12">
    <h5 class='my-3'><b>Colis pouvant être facturés</b></h5> 
</div>
<hr>
<div class="col-sm-12">

<?php include get_file("files/sql/get/fetch_orders"); ?>

<?php if (empty($getUser)): ?>
    <!-- عرض المستخدمين بدون اختيار -->
    <div class="col-sm-12 text-center">
        <div class="row my-3">
            <div class="col-4"><b>Vendeur</b></div>
            <div class="col-4"><b>Colis</b></div>
            <div class="col-4"><b>Gérer</b></div>
        </div>
        <?php foreach ($users as $row): ?>
        <div class="row my-3">
            <div class="col-4"><?= $row['user_name'] ?> - <?= $row['user_owner'] ?></div>
            <div class="col-4">(<?= $row['total_orders'] ?>)</div>
            <div class="col-4">
                <a href='invoice?do=new&user=<?= $row['user_id'] ?>' class='btn btn-dark btn-sm'>Créer</a>
            </div>
        </div>
        <hr>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

</div>

<!-- بطاقة التحكم -->
<div class="position-fixed bottom-0 start-0 end-0 bg-white shadow-lg border-top py-3 px-4" style="z-index: 1055;">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">

        <div class="col-sm-12 text-center">
            <div class="my-3"><div id ='<?= $result ?>'></div></div>
        </div>

        <a id="selectAllBtn" class="btn btn-outline-dark">
            <i class="fa-regular fa-square-check"></i> Tout sélectionner
        </a>

        <button onclick="return collectSelectedIds('formId')" class="btn my-3 btn-primary">Valider</button>
    </div>
</div>

</div>
</div>
</div>

<?php formAwdEnd(); ?>

<div style='margin:100px'></div>

<script>
let selectAllBtn = document.getElementById('selectAllBtn');
let allSelected = false;

function updateSelectAllBtnText() {
    let checkboxes = document.querySelectorAll('.order-checkbox');
    let checkedBoxes = Array.from(checkboxes).filter(cb => cb.checked);
    allSelected = checkedBoxes.length === checkboxes.length && checkboxes.length > 0;

    if (allSelected) {
        selectAllBtn.textContent = `Désélectionner tout (${checkedBoxes.length})`;
    } else if (checkedBoxes.length > 0) {
        selectAllBtn.textContent = `Sélectionner (${checkedBoxes.length})`;
    } else {
        selectAllBtn.textContent = 'Tout sélectionner';
    }
}

selectAllBtn.addEventListener('click', function() {
    let checkboxes = document.querySelectorAll('.order-checkbox');
    allSelected = !allSelected;
    checkboxes.forEach(cb => cb.checked = allSelected);
    updateSelectAllBtnText();
});

document.querySelectorAll('.order-checkbox').forEach(cb => {
    cb.addEventListener('change', updateSelectAllBtnText);
});
updateSelectAllBtnText();

function collectSelectedIds(formId) {
    let selectedIds = [];
    document.querySelectorAll('.order-checkbox:checked').forEach(cb => {
        selectedIds.push(cb.value);
    });
    document.querySelector(`#${formId} input[name="order_id"]`).value = selectedIds.join(',');
    return true;
}

$('.d-user').change(function() {
    let id = $(this).val();
    window.location.href = `?do=new&user=${id}`;
});

document.getElementById('filter-date-from')?.addEventListener('change', updateDateFilters);
document.getElementById('filter-date-to')?.addEventListener('change', updateDateFilters);

function updateDateFilters() {
    let fromDate = document.getElementById('filter-date-from').value || '';
    let toDate   = document.getElementById('filter-date-to').value || '';
    let url = new URL(window.location.href);

    let user = url.searchParams.get('user');
    if (user) url.searchParams.set('user', user);

    if (fromDate) url.searchParams.set('date_from', fromDate); else url.searchParams.delete('date_from');
    if (toDate)   url.searchParams.set('date_to', toDate);     else url.searchParams.delete('date_to');

    window.location.href = url.toString();
}
</script>

<?php endif; ?>
<?php
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