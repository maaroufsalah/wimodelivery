<?php 
$bootstrap = new Bootstrap();
$bootstrap->runBootstrap();

require_once 'vendor/autoload.php'; // مسار autoload الخاص بـ PhpSpreadsheet



include get_file("files/sql/get/os_settings");
include get_file("files/sql/get/session");
include get_file("files/sql/get/functions");
include get_file("Admin/admin_header");

define ("page_title","Colis");

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

$stateId = $_GET['state'] ?? "";

if ($loginRank == "user" || $loginRank == "delivery" || hasUserPermission($con, $loginId, 2, 'admin') || hasUserPermissionAide($con, $loginId, 46, 'user')) {

// جلب المخازن
$stmt = $con->prepare("SELECT * FROM warehouse WHERE wh_unlink = '0' ORDER BY wh_name");
$stmt->execute();
$warehouse = $stmt->fetchAll();

// جلب المستخدمين - بائعين
$stmt = $con->prepare("SELECT * FROM users WHERE user_unlink = '0' AND user_rank = 'user' ORDER BY user_name");
$stmt->execute();
$f_user = $stmt->fetchAll();

// جلب المستخدمين - موصلين
$stmt = $con->prepare("SELECT * FROM users WHERE user_unlink = '0' AND user_rank = 'delivery' ORDER BY user_name");
$stmt->execute();
$d_user = $stmt->fetchAll();

// جلب المدن
$stmt = $con->prepare("SELECT * FROM city WHERE city_unlink = '0' ORDER BY city_name");
$stmt->execute();
$city = $stmt->fetchAll();

// جلب الحالات
$stmt = $con->prepare("SELECT * FROM state WHERE state_unlink = 0 ORDER BY state_name");
$stmt->execute();
$states = $stmt->fetchAll();
?>

<style>
.btn-sm, .btn-group-sm > .btn {
font-size: 10px;
}
</style>

<!-- بطاقة التحكم الثابتة -->
<div class="position-fixed bottom-0 start-auto end-0 bg-white shadow-lg border-top py-3 px-4 zindex-tooltip" style="z-index: 1055;">
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3">

<!-- زر تحديد الكل -->
<button id="selectAllBtn" class="btn btn-sm btn-outline-dark">
<i class="fa-regular fa-square-check"></i> Tout sélectionner
</button>

<?php if ((hasUserPermission($con, $loginId, 6 ,'admin')) || ($loginRank == "delivery")):?>
<!-- تغيير الحالة -->
<button id="transferBtn" data-bs-toggle="modal" data-bs-target="#modal_state" class="btn btn-sm btn-warning">
<i class="fa-solid fa-truck"></i> L'état
</button>
<?php endif; ?>

<?php if (($loginRank == "admin")):?>
<!-- API نقل -->
<button data-bs-toggle="modal" data-bs-target="#modal_api" class="btn btn-sm btn-dark">
<i class="fa-solid fa-truck"></i> Api - Transférer
</button>
<?php endif; ?>

<?php if ((hasUserPermission($con, $loginId, 7 ,'admin'))):?>
<!-- تمرير الطرد -->
<button id="transferBtn" data-bs-toggle="modal" data-bs-target="#modal_delivery" class="btn btn-sm btn-info">
<i class="fa-solid fa-truck"></i> Transférer Au Livreur
</button>
<?php endif; ?>


<?php if ((hasUserPermission($con, $loginId, 8 ,'admin')) || ($loginRank == "user") || hasUserPermissionAide($con, $loginId, 56 ,"aide") || ($loginRank == "delivery")):?>
<!-- طباعة الملصق -->
<button id="printBtn" data-bs-toggle="modal" data-bs-target="#modal_print" class="btn btn-sm btn-success">
<i class="fa-solid fa-print"></i> Imprimer L'autocollant
</button>
<?php endif; ?>

<button id="printBtn" data-bs-toggle="modal" data-bs-target="#modal_export" class="btn btn-sm btn-success">
<i class="fa fa-file-excel"></i> Exporter
</button>

<?php if ((hasUserPermission($con, $loginId, 11 ,'admin'))):?>
<button id="printBtn" data-bs-toggle="modal" data-bs-target="#modal_unlink" class="btn btn-sm btn-danger">
<i class="fa fa-trash-alt"></i> Supprimer
</button>
<?php endif; ?>




</div>
</div>

<!-- مودالات -->
<?php if ($loginRank == "admin"): ?>
<div class="modal fade" id="modal_api" tabindex="-1">
<div class="modal-dialog modal-dialog-centered">
<div class="modal-content">
<div class="modal-header">
<h5 class="modal-title">Envoyer Api</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body text-center">
<form id="api_form" onsubmit="return collectSelectedIds('api_form')">
<input type="hidden" name="order_id" value="">
<label for="api_select">Choisir Api :</label>
<select class="js-select w-100" name="api_id" required>
<option value="">Sélectionner</option>
<?php
$stmt = $con->prepare("SELECT * FROM api");
$stmt->execute();
$apis = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($apis as $api) {
echo '<option value="' . $api['api_id'] . '">' . ($api['api_name']) . '</option>';
}
?>
</select>
<button type="submit" class="btn btn-primary mt-3">Envoyer</button>
</form>
<div id="result_api"></div>
</div>
</div>
</div>
</div>
<?php endif; ?>

<?php if ((hasUserPermission($con, $loginId, 6 ,'admin')) || ($loginRank == "agency") || ($loginRank == "delivery")): ?>
<div class="modal fade" id="modal_state" tabindex="-1">
<div class="modal-dialog modal-dialog-centered">
<div class="modal-content">
<div class="modal-header">
<h5 class="modal-title">Changer l'état de livraison</h5>
<button type="button" class="btn-close ud" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body text-center">
<form id="state_form" onsubmit="return collectSelectedIds('state_form')">
<input type="hidden" name="order_id" value="">
<label for="state_select">Choisir une nouvelle état:</label>
<select class="js-select state_select w-100" name="state_id" required>
<option value="">Sélectionner une état</option>
<option value="0">En attente</option>
<?php
$stmt = $con->prepare("SELECT * FROM state WHERE state_unlink = '0' ORDER BY state_name ASC");
$stmt->execute();
$statesModal = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($statesModal as $state) {
echo '<option value="' . $state['state_id'] . '">' . ($state['state_name']) . '</option>';
}
?>
</select>


<div id="report_date_div" style="display: none;">
<label for="postponed_date">Nouvelle date de report:</label>
<input type="date" id="postponed_date" name="postponed_date">
</div>

<div id="programmed_date_div<?= $row['or_id']; ?>" style="display: none;">
    <label for="programmed_date<?= $row['or_id']; ?>">Date programmé :</label>
    <input type="date" id="programmed_date<?= $row['or_id']; ?>" name="programmed_date" class="form-control">
</div>


<label for="note">Note / Justification (toutes les autres situations):</label>
<input type="text" id="note" name="note" placeholder="Ex: Client a demandé un report">




<button type="submit" class="btn btn-primary mt-3">Mettre à jour la commande</button>



</form>
<div id="result_state"></div>
</div>
</div>
</div>
</div>
<?php endif; ?>

<?php if ((hasUserPermission($con, $loginId, 7 ,'admin')) || ($loginRank == "agency")): ?>
<div class="modal fade" id="modal_delivery" tabindex="-1">
<div class="modal-dialog modal-dialog-centered">
<div class="modal-content">
<div class="modal-header">
<h5 class="modal-title">Transférer Au Livreur</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body text-center">
<form id="delivery_form" onsubmit="return collectSelectedIds('delivery_form')">
<input type="hidden" name="order_id" value="">
<label for="delivery_select">Choisir livreur</label>
<select class="js-select w-100" name="delivery_id" required>
<option value="">Sélectionner un livreur</option>
<?php
$stmt = $con->prepare("SELECT * FROM users WHERE user_unlink = '0' AND user_rank = 'delivery' ORDER BY user_name ASC");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($users as $user) {
echo '<option value="' . $user['user_id'] . '">' . ($user['user_name']) . '</option>';
}
?>
</select>
<button type="submit" class="btn btn-primary mt-3">Mettre à jour</button>
</form>
<div id="result_delivery"></div>
</div>
</div>
</div>
</div>
<?php endif; ?>

<?php if ((hasUserPermission($con, $loginId, 8 ,'admin')) || ($loginRank == "user") || hasUserPermissionAide($con, $loginId, 56, "aide") || ($loginRank == "delivery")): ?>
<div class="modal fade" id="modal_print" tabindex="-1">
<div class="modal-dialog modal-dialog-centered">
<div class="modal-content">
<div class="modal-header">
<h5 class="modal-title">Imprimer ticket</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body text-center">
<form id="print_form" onsubmit="return collectSelectedIds('print_form')">
<input type="hidden" name="order_id" value="">
<div class="col-sm-12">
<label for="print_select">Choisir Taille</label>
<select class="js-select w-100" name="print_id" required>
<option value="0">Sélectionner Taille</option>
<option value="a4">A4</option>
<option value="10">10x10</option>
</select>
</div>



<?php if ($loginRank == "admin"): ?>
<div class="col-sm-12">
<div class="form-check mx-3 form-switch form-check-inline">
<input value="0" class="form-check-input" type="checkbox" role="switch" name="print_fee_checkbox" id="print_fee_checkbox">
<label class="form-check-label" for="print_fee_checkbox">Insérer frais des autocollants.</label>
</div>
</div>
<div class="col-sm-12" id="print_div" style="display:none;">
<div class="my-3">
<div class="input">Frais</div>
<input name="print_fee" id="print_fee" type="text" class="form-control" value="" placeholder=""/>
</div>
</div>
<?php endif; ?>

<div class="col-sm-12">
<button type="submit" class="btn btn-primary mt-3">Imprimer</button>
</div>
</form>
<div id="result_print"></div>
</div>
</div>
</div>
</div>
<?php endif; ?>







<?php if ((hasUserPermission($con, $loginId, 8 ,'admin')) || ($loginRank == "user") || hasUserPermissionAide($con, $loginId, 56, "aide") || ($loginRank == "delivery")): ?>
<div class="modal fade" id="modal_export" tabindex="-1">
<div class="modal-dialog modal-dialog-centered">
<div class="modal-content">
<div class="modal-header">
<h5 class="modal-title">Exporter</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body text-center">
<form id="export_form" onsubmit="return collectSelectedIds('export_form')">
<input type="hidden" name="order_id" value="">
<div class="col-sm-12">
<button type="submit" class="btn btn-primary mt-3">Télécharger</button>
</div>
</form>
<div id="result_export"></div>
</div>
</div>
</div>
</div>
<?php endif; ?>






<?php if ((hasUserPermission($con, $loginId, 11 ,'admin'))): ?>
<div class="modal fade" id="modal_unlink" tabindex="-1">
<div class="modal-dialog modal-dialog-centered">
<div class="modal-content">
<div class="modal-header">
<h5 class="modal-title">Supprimer</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body text-center">
<form id="unlink_form" onsubmit="return collectSelectedIds('unlink_form')">
<input type="hidden" name="order_id" value="">
<div class="col-sm-12">
<button type="submit" class="btn btn-primary mt-3">Supprimer</button>
</div>
</form>
<div id="result_unlink"></div>
</div>
</div>
</div>
</div>
<?php endif; ?>






<!-- أزرار وإضافات -->
<div style="text-align: right;">
<?php if ($loginRank == "admin" || $loginRank == "user" || $loginRank == "aide"): ?>

<?php if ($loginRank == "user" || hasUserPermission($con, $loginId, 3, 'admin') || hasUserPermissionAide($con, $loginId, 53, "aide")): ?>
<a href='?do=new' class="btn btn-primary my-3 btn-sm">Ajouter Colis</a>
<?php endif; ?>

<?php if ($loginRank == "user" || hasUserPermission($con, $loginId, 4, 'admin') || hasUserPermissionAide($con, $loginId, 54, "aide")): ?>
<a href='?do=nvs' class="btn btn-primary my-3 btn-sm">Ajouter Colis/Stock</a>
<?php endif; ?>

<?php if ($loginRank == "user" || hasUserPermission($con, $loginId, 5, 'admin') || hasUserPermissionAide($con, $loginId, 55, "aide")): ?>
<a href='?do=import' class="btn btn-dark my-3 btn-sm">Importer Colis</a>
<?php endif; ?>

<?php endif; ?>

<a id="exportExcel" class="btn btn-success export my-3 btn-sm">Exporter</a>

</div>

<div class="card-body">
<div class="row">



<div class="col-6" style="text-align: left;">
<h6>Recherche</h6>
<input type="text" class="searchbox form-control mb-3" style="width:100%" />
</div>




<div class="col-6">
<h6>Afficher</h6>
<select class="display form-select">
<option value="10">10</option>
<option value="50" selected>50</option>
<option value="100">100</option>
<option value="200">200</option>
<option value="600">600</option>
</select>
</div>


<?php if ($loginRank == "admin") : ?>
<div class="col-sm-3">
<div class="my-3">
<div class="input">Vendeur</div>
<select name="user[]" class="js-select w-100" multiple>
<option value="int" disabled>Choisir Vendeur</option>
<?php foreach ($f_user as $row) : ?>
<option value="<?= $row['user_id'] ?>"><?= ($row['user_owner'])." - ".($row['user_name']) ?></option>
<?php endforeach; ?>
</select>
</div>
</div>
<?php endif; ?>



<?php if ($loginRank == "admin") : ?>
<div class="col-sm-3">
<div class="my-3">
<div class="input">Livreur</div>
<select name="duser[]" class="js-select w-100" multiple>
<option value="int" disabled>Choisir Livreur</option>
<?php foreach ($d_user as $row) : ?>
<option value="<?= $row['user_id'] ?>"><?= ($row['user_name']) ?></option>
<?php endforeach; ?>
</select>
</div>
</div>
<?php endif; ?>

<div class="col-sm-3">
<div class="my-3">
<div class="input">Sélectionner la Ville</div>
<select name="city[]" class="js-select w-100 city" multiple>
<option value="0" disabled>Choisir Ville</option>
<?php foreach ($city as $row) : ?>
<option value="<?= $row['city_id'] ?>"><?= ($row['city_name']) ?></option>
<?php endforeach; ?>
</select>
</div>
</div>

<?php if ($loginRank == "admin") : ?>
<div class="col-sm-3">
<div class="my-3">
<div class="input">Sélectionner l'Agence</div>
<select name="warehouse[]" class="js-select w-100 warehouse" multiple>
<option value="0" disabled>Choisir Agence</option>
<?php foreach ($warehouse as $row) : ?>
<option value="<?= $row['wh_id'] ?>"><?= ($row['wh_name']) ?></option>
<?php endforeach; ?>
</select>
</div>
</div>
<?php endif; ?>

<div class="col-sm-12">
<div class="my-3">
<div class="input">État du colis</div>
<select name="state[]" class="js-select w-100 state" multiple>
<option value="0" disabled>Choisir État</option>
<?php if ($loginRank == "admin") : ?>
<option value="unlink">Supprimé</option>
<?php endif; ?>
<option value="int" <?= ($stateId == "int") ? "selected" : "" ?>>En attente</option>
<?php foreach ($states as $row) : ?>
<option value="<?= $row['state_id'] ?>" <?= ($stateId == $row['state_id']) ? "selected" : "" ?>><?= ($row['state_name']) ?></option>
<?php endforeach; ?>
</select>
</div>
</div>

</div>
</div>

<?php
// عرض أزرار الحالات المختارة مع عدد الطلبيات
$stmt = $con->prepare("SELECT * FROM state WHERE state_unlink = 0 AND state_id IN (51,62,57,54,5) ORDER BY state_name");
$stmt->execute();
$statesIn = $stmt->fetchAll();

foreach ($statesIn as $row) {
if ($loginRank == "admin") {
$login_sql = " or_unlink = '0' ";
} elseif ($loginRank == "user") {
$login_sql = " or_unlink = '0' AND or_trade = '$loginId' ";
} elseif ($loginRank == "delivery") {
$login_sql = " or_unlink = '0' AND or_delivery_user = '$loginId' AND or_delivery_invoice = '0' ";
} elseif ($loginRank == "aide") {
$login_sql = " or_unlink = '0' AND or_trade = '" . $loginUser['user_aide'] . "' ";
} else {
$login_sql = " or_unlink = '10' ";
}

$stmtCount = $con->prepare("SELECT COUNT(*) FROM orders WHERE $login_sql AND or_state_delivery = :state_id");
$stmtCount->execute(['state_id' => $row['state_id']]);
$ordersCount = $stmtCount->fetchColumn();

$stmtCount_change = $con->prepare("SELECT COUNT(*) FROM orders WHERE $login_sql AND or_change = '1' AND or_state_delivery = '0'");
$stmtCount_change->execute();
$changeCount = $stmtCount_change->fetchColumn();

$badge = $ordersCount > 0
? "<span class='spinner-grow spinner-grow-sm'></span> <span class='badge p-2' style='background:black'>{$ordersCount}</span>"
: "<span class='badge bg-dark p-2'>0</span>";

echo '<a href="packages?state=' . $row['state_id'] . '" class="btn btn-sm" style="background:' . ($row['state_background']) . '; border-radius:50rem">'
. ($row['state_name']) . ' ' . $badge .
'</a> ';
}
?>

<a class='btn btn-sm change' style="background:orange; border-radius:50rem">Échange <span class='badge bg-dark p-2'><?=$changeCount?></span></a>

<input type="hidden" name="change" value="1">

<hr>
<div class="loader"></div>
<div id="dynamic_content"></div>
<a class="update_data"></a>

<script>
document.addEventListener("DOMContentLoaded", function() {
const updateLink = document.querySelector("a.ud");
if (updateLink) updateLink.click();
});

$(document).ready(function() {

let exportMode = false;

function getFilters() {
return {
search: $('.searchbox').val() || '',
display: $('.display').val() || '',
city: $('[name="city[]"]').val() || [],
warehouse: $('[name="warehouse[]"]').val() || [],
state: $('[name="state[]"]').val() || [],
user: $('[name="user[]"]').val() || [],
duser: $('[name="duser[]"]').val() || [],
change: 0 // <- ستصبح 1 عند الضغط
};
}

function updateSelectAllBtnText() {
let checkboxes = document.querySelectorAll('.order-checkbox');
if (checkboxes.length === 0) {
$('#selectAllBtn').text('Sélectionner tout');
return;
}
let checkedBoxes = Array.from(checkboxes).filter(cb => cb.checked);
let allChecked = checkedBoxes.length === checkboxes.length;

if (allChecked) {
$('#selectAllBtn').text(`Désélectionner tout (${checkedBoxes.length})`);
} else if (checkedBoxes.length > 0) {
$('#selectAllBtn').text(`Sélectionner (${checkedBoxes.length})`);
} else {
$('#selectAllBtn').text('Sélectionner tout');
}
}


function updateHiddenField() {
let selectedIds = $('.order-checkbox:checked').map(function() {
return $(this).val();
}).get().join(',');
console.log("Selected IDs:", selectedIds);
$('input[name="order_id"]').val(selectedIds);
}

function onOrdersListUpdated() {
console.log("onOrdersListUpdated called");
updateSelectAllBtnText();
updateHiddenField();
$('.js-select').select2('destroy').select2();
}

if ($('.js-select').length) $('.js-select').select2();

load_data(1, getFilters());

function load_data(page, filters = {}) {
$.ajax({
url: 'getPackage',
method: 'POST',
data: {
page,
...filters,
exports: exportMode ? 'true' : ''
},
cache: false,
beforeSend: function() {
$('.loader').html('<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>');
},
success: function(data) {
$('#dynamic_content').html(data);
$('.loader').empty();
onOrdersListUpdated();
exportMode = false;
},
error: function(xhr, status, error) {
console.error(error);
$('.loader').html('<div class="text-danger">فشل تحميل البيانات: ' + error + '</div>');
}
});
}

$(document).on('click', '#exportExcel', function(event) {
event.preventDefault();
if (exportMode) return;
exportMode = true;
load_data(1, getFilters());
});

$(document).on('click', '.page-link', function(event) {
event.preventDefault();
let page = $(this).data('page');
load_data(page, getFilters());
});







$(document).on('click', '.update_data', function(event) {
    event.preventDefault(); // منع أي redirect
    load_data(page, getFilters()); // استخدام الصفحة الحالية
});



$('.change').on('click', function() {
    // تحديث قيمة change إلى 1
    $('[name="change"]').val(1);

    // ثم استدعاء تحميل البيانات مع الفلاتر الجديدة
    load_data(1, getFilters());
});

$('.searchbox, .display, .city, .warehouse, .state, [name="user[]"], [name="duser[]"]').on('change keyup', function() {
load_data(1, getFilters());
});

$(document).on('click', '.ud', function(event) {
event.preventDefault();
load_data(1, getFilters());
});

$(document).on('click', '#selectAllBtn', function() {
let checkboxes = document.querySelectorAll('.order-checkbox');
if (checkboxes.length === 0) return;
let allChecked = Array.from(checkboxes).every(cb => cb.checked);
checkboxes.forEach(cb => cb.checked = !allChecked);
updateSelectAllBtnText();
updateHiddenField();
});

$(document).on('change', '.order-checkbox', function() {
updateSelectAllBtnText();
updateHiddenField();
});

function ajaxFormSubmit(formSelector, resultSelector, url) {
$(formSelector).on('submit', function(e) {
e.preventDefault();
let form = $(this);
form.find('button[type=submit]').prop('disabled', true);
$.ajax({
url: url,
method: 'POST',
data: form.serialize(),
success: function(response) {
$(resultSelector).html('<div class="text-success">' + response + '</div>');
},
error: function() {
$(resultSelector).html('<div class="text-danger">حدث خطأ في الفورم</div>');
},
complete: function() {
form.find('button[type=submit]').prop('disabled', false);
}
});
});
}

ajaxFormSubmit('#api_form', '#result_api', 'api_list');
ajaxFormSubmit('#state_form', '#result_state', 'config_orders?do=state');
ajaxFormSubmit('#delivery_form', '#result_delivery', 'config_orders?do=delivery');
ajaxFormSubmit('#print_form', '#result_print', 'config_orders?do=print');
ajaxFormSubmit('#export_form', '#result_export', 'config_orders?do=export');
ajaxFormSubmit('#unlink_form', '#result_unlink', 'config_orders?do=unlink');



$(document).on('change', '.state_select', function() {
let stateId = $(this).val();
if (stateId == 5) {
$('#report_date_div').show();
} else {
$('#report_date_div').hide();
}
});



$(document).on('change', '.state_select', function() {
let stateId = $(this).val();
if (stateId == 54) {
$('#programmed_date_div').show();
} else {
$('#programmed_date_div').hide();
}
});


$('#print_fee_checkbox').on('change', function() {
    if ($(this).is(':checked')) {
        $('#print_div').show();
        $('#print_fee')
            .attr('required', 'required')
            .val('0.3'); // القيمة الافتراضية عند التحديد
    } else {
        $('#print_div').hide();
        $('#print_fee')
            .removeAttr('required')
            .val('');
    }
});



});
</script>

<?php
} // نهاية if صلاحيات المستخدم

}elseif($do == "new"){

if (hasUserPermission($con, $loginId, 3 ,'admin') || $loginRank == "user" || hasUserPermissionAide($con, $loginId, 53 ,"aide")){

$id = "formId";
$result = "data_result";
$action = "newPackage";
$method = "post";
formAwdStart ($id,$result,$action,$method); 

// select warehouse
$stmt = $con->prepare("SELECT * FROM warehouse WHERE wh_unlink = '0' ORDER BY wh_name");
$stmt->execute();
$warehouseCount = $stmt->rowCount();
$warehouse = $stmt->fetchAll();

// select user
$stmt = $con->prepare("SELECT * FROM users WHERE user_unlink = '0' AND user_rank = 'user' ORDER BY user_name");
$stmt->execute();
$userCount = $stmt->rowCount();
$user = $stmt->fetchAll();

// select city
$stmt = $con->prepare("SELECT * FROM city WHERE city_unlink = '0' ORDER BY city_name");
$stmt->execute();
$cityCount = $stmt->rowCount();
$city = $stmt->fetchAll();

// select box
$stmt = $con->prepare("SELECT * FROM box WHERE box_unlink = '0' ORDER BY box_name");
$stmt->execute();
$boxCount = $stmt->rowCount();
$box = $stmt->fetchAll();


?>
<div class="card">

<div class="card-header">
<h5><b>+</b> Ajouter Colis</h5>
</div>

<div class="card-body">
<div class="row">






<div class="col-sm-6">
<div class="my-3">
<div class="input">Agence</div>
<select name='warehouse' class='js-select w-100'>
<option value='0' disabled selected>Choisir Agence</option>
<?php
foreach ($warehouse as $row){
?>
<option value='<?= $row['wh_id'] ?>' <?php if ($row['wh_id'] == 1){print "selected";} ?>><?= $row['wh_name'] ?></option>

<?php
}
?>
</select>
</div>
</div>


<div class="col-sm-6">
<div class="my-3">
<div class="input">Ville</div>
<select name='city' class='js-select w-100'>
<option value='0' disabled selected>Choisir Ville</option>
<?php
foreach ($city as $row){
print "<option value='{$row['city_id']}'>{$row['city_name']}</option>";
}
?>
</select>
</div>
</div>

<div class="col-12">
<div class="alert alert-info mt-2">
Frais de livraison estimés: <strong id="shipping_price_display">--</strong>
</div>
</div>



<?php if ($loginRank == "admin"):?>
<div class="col-sm-12">
<div class="my-3">
<div class="input">Vendeur</div>
<select name='user' class='js-select w-100'>
<option value='0' disabled selected>Choisir Vendeur</option>
<?php
foreach ($user as $row){
print "<option value='{$row['user_id']}'>{$row['user_name']}</option>";
}
?>
</select>
</div>
</div>
<?php elseif ($loginRank == "user"): ?>
<input type='hidden' name='user' value='<?=$loginId;?>'/>
<?php endif; ?>




<div class="col-8">
<div class="my-3">
<div class="input">Produit</div>
<input name="item" type="text" class="form-control" value="" placeholder=""/>
</div>
</div>



<div class="col-4">
<div class="my-3">
<div class="input">Qté</div>
<input name="qty" type="number" class="form-control" value="" placeholder=""/>
</div>
</div>






<div class="col-sm-6">
<div class="my-3">
<div class="input">Prix de colis</div>
<input name="price" type="number" class="form-control" value="" placeholder=""/>
</div>
</div>







<div class="col-sm-6">
<div class="my-3">
<div class="input">Destinataire</div>
<input name="name" type="text" class="form-control" value="" placeholder=""/>
</div>
</div>


<div class="col-sm-6">
<div class="my-3">
<div class="input">Téléphone</div>
<input name="phone" type="number" class="form-control" value="" placeholder=""/>
</div>
</div>

<div class="col-sm-6">
<div class="my-3">
<div class="input">Adresse</div>
<input name="location" type="text" class="form-control" value="" placeholder=""/>
</div>
</div>


<div class="col-sm-6">
<div class="my-3">
<div class="input">Note</div>
<input name="note" type="text" class="form-control" value="" placeholder=""/>
</div>
</div>



<div class="col-sm-6">
<div class="my-3">
<div class="input">Emballage</div>
<select name='box' class='js-select w-100'>
<option value='0' selected>Sans Embalage - 0 Dhs</option>
<?php
foreach ($box as $row){
print "<option value='{$row['box_id']}'>{$row['box_name']} | {$row['box_price']}</option>";
}
?>
</select>
</div>
</div>






<div class="col-sm-6" style='display:none;'>
<div class="my-3">
<div class="input">Date de ramassage</div>
<input name="pickup" type="date" class="form-control" value="" placeholder=""/>
</div>
</div>


<!-- Options -->
<div class="col-sm-12">
<div class="form-check mx-3 form-switch form-check-inline" style="display:none">
<input value='5' class="form-check-input" type="checkbox" role="switch" name="fragile" id="fragile">
<label class="form-check-label" for="fragile">Fragile | +5 Dhs</label>
</div>
<div class="form-check mx-3 form-switch form-check-inline">
<input value='1' class="form-check-input" type="checkbox" role="switch" name="try" id="try">
<label class="form-check-label" for="try">Essayage</label>
</div>
<div class="form-check mx-3 form-switch form-check-inline">
<input value='1' class="form-check-input" type="checkbox" role="switch" name="open" id="open" checked>
<label class="form-check-label" for="open">Autorisation D'ouvrir</label>
</div>
<div class="form-check mx-3 form-switch form-check-inline">
<input value='1' class="form-check-input" type="checkbox" role="switch" name="change" id="change">
<label class="form-check-label" for="change">Échange</label>
</div>
</div>



<div class="col-sm-12" id="change_code_div" style="display:none;">
<div class="my-3">
<div class="input">Code Colis Changé</div>
<input name="change_code" id="change_code" type="text" class="form-control" value="" placeholder=""/>
</div>
</div>

<div class="col-sm-12 text-center">
<div id='<?php print $result ;?>'></div>
</div>


<div class="col-sm-12 text-center">
<button class="btn my-3 btn-primary">Valider</button>
</div>











</div>
</div>
</div>


<?php if ($loginRank == "admin"): ?>
<script>
$(document).ready(function () {
  function fetchShippingCharge() {
    let warehouse = $("select[name='warehouse']").val();
    let city = $("select[name='city']").val();
    let user = $("select[name='user']").val(); // ✅ admin: من select

    if (warehouse > 0 && city > 0 && user > 0) {
      $.getJSON("get_shipping_price", { warehouse, city, user }, function (data) {
        if (data.delivery_type === "user" && data.up_delivery !== null) {
          $("#shipping_price_display").text(data.up_delivery + " DH (tarif personnalisé)");
        } else if (data.delivery_type === "default" && data.sc_delivery !== null) {
          $("#shipping_price_display").text(data.sc_delivery + " DH (tarif standard)");
        } else {
          $("#shipping_price_display").text("Frais non trouvés");
        }
      });
    } else {
      $("#shipping_price_display").text("Sélection incomplète");
    }
  }

  $("select[name='warehouse'], select[name='city'], select[name='user']").on("change", fetchShippingCharge);
  fetchShippingCharge();
});
</script>

<?php elseif ($loginRank == "user"): ?>
<script>
$(document).ready(function () {
  function fetchShippingCharge() {
    let warehouse = $("select[name='warehouse']").val();
    let city = $("select[name='city']").val();
    let user = $("input[name='user']").val(); // ✅ user: من input مخفي

    if (warehouse > 0 && city > 0 && user > 0) {
      $.getJSON("get_shipping_price", { warehouse, city, user }, function (data) {
        if (data.delivery_type === "user" && data.up_delivery !== null) {
          $("#shipping_price_display").text(data.up_delivery + " DH (tarif personnalisé)");
        } else if (data.delivery_type === "default" && data.sc_delivery !== null) {
          $("#shipping_price_display").text(data.sc_delivery + " DH (tarif standard)");
        } else {
          $("#shipping_price_display").text("Frais non trouvés");
        }
      });
    } else {
      $("#shipping_price_display").text("Sélection incomplète");
    }
  }

  $("select[name='warehouse'], select[name='city']").on("change", fetchShippingCharge);
  fetchShippingCharge();
});
</script>
<?php endif; ?>



<script>
document.getElementById('change').addEventListener('change', function() {
const div = document.getElementById('change_code_div');
const input = document.getElementById('change_code');
if (this.checked) {
div.style.display = 'block';
input.setAttribute('required', 'required');
} else {
div.style.display = 'none';
input.removeAttribute('required');
input.value = ''; // اختياري: لمسح القيمة عند الإخفاء
}
});
</script>


<?php




formAwdEnd ();
}
}elseif($do == "nvs"){
if (hasUserPermission($con, $loginId, 4 ,'admin') || $loginRank == "user" || hasUserPermissionAide($con, $loginId, 54 ,"aide")){

$getUser = $_GET['user'] ?? 0;

$id = "formId";
$result = "data_result";
$action = "new_stock_package";
$method = "post";
formAwdStart($id, $result, $action, $method);

// جلب البيانات من قاعدة البيانات
$stmt = $con->prepare("SELECT * FROM warehouse WHERE wh_unlink = '0' ORDER BY wh_name");
$stmt->execute();
$warehouse = $stmt->fetchAll();

$stmt = $con->prepare("SELECT * FROM users WHERE user_unlink = '0' AND user_rank = 'user' ORDER BY user_name");
$stmt->execute();
$user = $stmt->fetchAll();

$stmt = $con->prepare("SELECT * FROM city WHERE city_unlink = '0' ORDER BY city_name");
$stmt->execute();
$city = $stmt->fetchAll();



// select box
$stmt = $con->prepare("SELECT * FROM box WHERE box_unlink = '0' ORDER BY box_name");
$stmt->execute();
$boxCount = $stmt->rowCount();
$box = $stmt->fetchAll();

// استرجاع السلة من الكوكيز
$cart = isset($_COOKIE['cart']) ? json_decode($_COOKIE['cart'], true) : [];

function get_product_image($product_id) {
global $con;
$query = "SELECT image_url FROM product_images WHERE product_id = ? AND is_main = '1'";
$stmt = $con->prepare($query);
$stmt->execute([$product_id]);
$image = $stmt->fetchColumn();
return $image ? 'uploads/products/' . $image : 'uploads/app/default.jpg';
}

?>



<div class="card">
<div class="card-header"><h5><b>+</b> Ajouter Colis / stocks</h5></div>
<div class="card-body">
<div class="row">

<?php if ($loginRank == "admin"):?>

<!-- Vendeur -->
<div class="col-sm-12">
<div class="my-3">
<div class="input">Vendeur</div>
<select name='user' class='js-select user-stock w-100' required>
<option value='0' disabled selected>Choisir Vendeur</option>
<?php foreach ($user as $row) { ?>
<option value='<?php echo $row['user_id']; ?>' <?php if($row['user_id'] == $getUser){print 'selected';} ?>><?php echo $row['user_name']; ?></option>
<?php } ?>
</select>
</div>
</div>
<?php elseif ($loginRank == "user"): ?>
<input type='hidden' name='user' class='user-stock' value='<?=$loginId;?>'/>
<?php endif; ?>






<!-- Agence -->
<div class="col-sm-6">
<div class="my-3">
<div class="input">Agence</div>
<select name='warehouse' class='js-select w-100' required>
<option value='0' disabled selected>Choisir Agence</option>
<?php foreach ($warehouse as $row) { ?>
<option value='<?php echo $row['wh_id']; ?>'><?php echo $row['wh_name']; ?></option>
<?php } ?>
</select>
</div>
</div>



<div class="col-sm-6">
<div class="my-3">
<div class="input">Ville</div>
<select name='city' class='js-select w-100' required>
<option value='0' disabled selected>Choisir Ville</option>
<?php foreach ($city as $row) { ?>
<option value='<?php echo $row['city_id']; ?>'><?php echo $row['city_name']; ?></option>
<?php } ?>
</select>
</div>
</div>




<div class="col-12">
<div class="alert alert-info mt-2">
Frais de livraison estimés: <strong id="shipping_price_display">--</strong>
</div>
</div>




<!-- Produits -->
<div class="col-sm-12">
<div class="my-3">
<h6><b>Produits En Stocks</b></h6>



<?php
print "<a data-bs-toggle='modal' data-bs-target='#add_to_package' class='btn btn-secondary'>+ Ajouter produit</a>";
?>


<?php if (empty($cart)): ?>
<div class="alert alert-info my-3">Votre Colis est vide. </div>
<?php else: ?>
<?php
$total_general = 0;
?>
<div class="list-group">
<?php foreach ($cart as $index => $item):

$product_name = $item['product_name'] ?? 'Produit inconnu';
$quantity = $item['quantity'] ?? 1;
$base_price = $item['base_price'] ?? 0;
$final_price = $item['final_price'] ?? 0;
$total = $base_price  * $quantity;
$total_general += $total;


?>
<div class="list-group-item d-flex align-items-start justify-content-between cart-item" data-index="<?= $index ?>" data-price="<?= $final_price ?>">
<div class="d-flex">
<img src="<?= get_product_image($item['product_id']); ?>" alt="" width="80" class="me-3 rounded">
<div>
<div class="fw-bold"><?= $product_name ?></div>
<small>
<?php
if (!empty($item['options'])) {
foreach ($item['options'] as $opt) {
echo ($opt['name']) . ": " . $opt['value_name'] . " (+" . number_format($opt['value_price'], 2) . " Dhs)<br>";
}
} else {
echo "Aucune option";
}
?>
</small>
<div class="text-muted">P.U: <?= number_format($base_price, 2) ?> Dhs</div>
<div class="d-flex align-items-center mt-2">
<label class="me-2">Quantité:</label>
<select class="form-select form-select-sm qty-select" data-index="<?= $index ?>" style="width: 70px;">
<?php for ($i = 1; $i <= 10; $i++): ?>
<option value="<?= $i ?>" <?= $i == $quantity ? 'selected' : '' ?>><?= $i ?></option>
<?php endfor; ?>
</select>
</div>
</div>
</div>
<div class="text-end">
<div><strong><?= $total; ?> Dhs</strong></div>
<button class="btn btn-sm btn-outline-danger mt-2 remove-btn" data-index="<?= $index ?>">Supprimer</button>
</div>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>






</div>
</div>






<!-- Prix, Ville, Client -->
<div class="col-sm-6">
<div class="my-3">
<div class="input">Prix de colis</div>
<input name="total" type="number" class="form-control" value="<?=$total_general;?>" required />
</div>
</div>


<div class="col-sm-6">
<div class="my-3">
<div class="input">Destinataire</div>
<input name="name" type="text" class="form-control" required />
</div>
</div>

<div class="col-sm-6">
<div class="my-3">
<div class="input">Téléphone</div>
<input name="phone" type="number" class="form-control" required />
</div>
</div>

<div class="col-sm-6">
<div class="my-3">
<div class="input">Adresse</div>
<input name="location" type="text" class="form-control" required />
</div>
</div>



<div class="col-sm-6">
<div class="my-3">
<div class="input">Emballage</div>
<select name='box' class='js-select w-100'>
<option value='0' selected>Sans Embalage - 0 Dhs - 0 Dhs</option>
<?php
foreach ($box as $row){
print "<option value='{$row['box_id']}'>{$row['box_name']} | {$row['box_price']}</option>";
}
?>
</select>
</div>
</div>

<!-- Options -->
<div class="col-sm-12">
<div class="form-check form-switch form-check-inline" style='display:none'>
<input value='5' class="form-check-input" type="checkbox" role="switch" name="fragile" id="fragile">
<label class="form-check-label" for="fragile">Fragile | +5 Dhs</label>
</div>
<div class="form-check form-switch form-check-inline">
<input value='1' class="form-check-input" type="checkbox" role="switch" name="try" id="try">
<label class="form-check-label" for="try">Essayage</label>
</div>
<div class="form-check form-switch form-check-inline">
<input value='1' class="form-check-input" type="checkbox" role="switch" name="open" id="open">
<label class="form-check-label" for="open">Autorisation D'ouvrir</label>
</div>
<div class="form-check form-switch form-check-inline">
<input value='1' class="form-check-input" type="checkbox" role="switch" name="change" id="change">
<label class="form-check-label" for="change">Échange</label>
</div>
</div>



<div class="col-sm-12" id="change_code_div" style="display:none;">
<div class="my-3">
<div class="input">Code Colis Changé</div>
<input name="change_code" id="change_code" type="text" class="form-control" value="" placeholder=""/>
</div>
</div>


<!-- Submit -->
<div class="col-sm-12 text-center">
<div id='<?=$result;?>'></div>
<button class="btn my-3 btn-primary">Valider</button>
</div>

</div>
</div>
</div>






<?php

if ($loginRank == "admin" || $loginRank == "user" || hasUserPermissionAide($con, $loginId, 54 ,"aide")) {



echo "<div class='modal fade' id='add_to_package' tabindex='-1' aria-hidden='true'>";
echo "<div class='modal-dialog modal-xl '>";
echo "<div class='modal-content'>";


echo "<div class='modal-header'>";
echo "<h5 class='modal-title'>Ajouter Au colis</h5>";
echo "<button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>";
echo "</div>";

echo "<div class='modal-body'>";

?>


<div class='row'>

<div class='col-8' style="text-align: left;">
<h6>Recherche</h6>
<input type="text" class="searchbox form-control mb-3" style="width:100%"/>
</div>

<div class='col-4' style="">
<h6>Afficher</h6>
<select class="display form-select">
<option value="10">10</option>
<option value="50">50</option>
<option value="100">100</option>
<option value="200">200</option>
</select>
</div>

</div>


<?php
print '<div class="loader"></div>';
print '<div id="dynamic_content"></div>';

echo "</div>";


echo "</div>";
echo "</div>";
echo "</div>";

}


?>


<script>
$(document).ready(function () {
// تحديث الكمية
$('.qty-select').change(function () {
const index = $(this).data('index');
const quantity = $(this).val();
const parent = $(this).closest('.cart-item');

$.post('update_cart', {
action: 'update',
index: index,
quantity: quantity
}, function (response) {
location.reload(); // تحديث كامل
});
});

// حذف منتج
$('.remove-btn').click(function () {
const index = $(this).data('index');
$.post('update_cart', {
action: 'remove',
index: index
}, function (response) {
location.reload(); // تحديث الصفحة
});
});
});


$(document).ready(function() {
$('.js-select').select2();

// تحميل الصفحة الأولى عند البداية
load_data(1);

function load_data(page = 1, search = '', display = '', user = '') {
console.log("📤 إرسال البيانات:", { page, search, display, user });

$.ajax({
url: 'search_stock',
method: 'POST',
data: { page, search, display, user },
dataType: 'html',
cache: false,
beforeSend: function () {
$('.loader').html('<div class="progress" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"><div class="progress-bar progress-bar-striped progress-bar-animated" style="width: 100%"></div></div>');
},
success: function (data) {
$('#dynamic_content').html(data);
$('.loader').html('');
$('html, body').animate({ scrollTop: $('#dynamic_content').offset().top }, 300);
},
error: function (xhr, status, error) {
console.error('⚠️ خطأ في AJAX:', error);
}
});
}
let search = $('.searchbox').val();
let display = $('.display').val();
let user = $('.user-stock').val();
load_data(1, search, display, user);
// حدث عند الكتابة في مربع البحث - نعيد التحميل من الصفحة 1
$('.searchbox').keyup(function() {
let search = $(this).val();
let display = $('.display').val();
let user = $('.user-stock').val();
load_data(1, search, display, user);
});

// حدث تغيير عدد العرض - نعيد التحميل من الصفحة 1
$('.display').change(function() {
let search = $('.searchbox').val();
let display = $(this).val();
let user = $('.user-stock').val();
load_data(1, search, display, user);
});

// حدث تغيير المستخدم
$('.user-stock').change(function() {
let user = $(this).val();
// التوجيه إلى رابط جديد، لا حاجة لاستدعاء load_data بعدها
window.location.href = `packages?do=nvs&user=${user}`;
});

});
</script>

<script>
$(document).ready(function () {
function fetchShippingCharge() {
let warehouse = $("select[name='warehouse']").val();
let city = $("select[name='city']").val();

if (warehouse > 0 && city > 0) {
$.getJSON("get_shipping_price", { warehouse, city }, function (data) {
if (data && data.sc_delivery !== null) {
$("#shipping_price_display").text(data.sc_delivery + " DH");
} else {
$("#shipping_price_display").text("Frais non trouvés");
}
});

}
}

$("select[name='warehouse'], select[name='city']").on("change", fetchShippingCharge);
});
</script>

<script>
document.getElementById('change').addEventListener('change', function() {
const div = document.getElementById('change_code_div');
const input = document.getElementById('change_code');
if (this.checked) {
div.style.display = 'block';
input.setAttribute('required', 'required');
} else {
div.style.display = 'none';
input.removeAttribute('required');
input.value = ''; // اختياري: لمسح القيمة عند الإخفاء
}
});
</script>



<?php
formAwdEnd ();

}
}elseif($do == "edit"){
if (hasUserPermission($con, $loginId, 9 ,'admin') || $loginRank == "user" || hasUserPermissionAide($con, $loginId, 57 ,'user')){


$id = "formId";  // معرّف النموذج
$result = "data_result"; 
$action = "editPackage"; 
$method = "post"; 
formAwdStart($id, $result, $action, $method); 

// استلام معرّف السجل الذي سيتم تعديله
$orderId = $_GET['order_id']?? ''; // التأكد من وجود معرّف السجل في الطلب

// إذا تم إرسال المعرف عبر GET أو POST، نسترجع البيانات من قاعدة البيانات
if ($orderId !== "") {
$stmt = $con->prepare("SELECT * FROM orders WHERE md5(or_id) = :order_id");
$stmt->bindParam(':order_id', $orderId, PDO::PARAM_STR);
$stmt->execute();
$order = $stmt->fetch(PDO::FETCH_ASSOC);

// التأكد من أن السجل موجود في قاعدة البيانات
if (!$order) {
echo "<div class='alert alert-danger'>Le colis spécifié n'existe pas.</div>";
}


// استعلام لاختيار البيانات المتاحة لحقول الاختيار (مثل الوكالات، المستخدمين، المدن، الخ)
$stmt = $con->prepare("SELECT * FROM warehouse WHERE wh_unlink = '0' ORDER BY wh_name");
$stmt->execute();
$warehouse = $stmt->fetchAll();

$stmt = $con->prepare("SELECT * FROM users WHERE user_unlink = '0' AND user_rank = 'user' ORDER BY user_name");
$stmt->execute();
$user = $stmt->fetchAll();

$stmt = $con->prepare("SELECT * FROM city WHERE city_unlink = '0' ORDER BY city_name");
$stmt->execute();
$city = $stmt->fetchAll();

$stmt = $con->prepare("SELECT * FROM box WHERE box_unlink = '0' ORDER BY box_name");
$stmt->execute();
$box = $stmt->fetchAll();

?>
<input type="hidden" value="<?php echo $order['or_id']; ?>" name="order_id"/>
<div class="card">
<div class="card-header">
<h5><b>Modifier Colis</b></h5>
</div>

<div class="card-body">
<div class="row">
<div class="col-sm-12">
<div class="my-3">
<div class="input">Agence</div>
<select name="warehouse" class="js-select w-100">
<option value="0" disabled selected>Choisir Agence</option>
<?php
foreach ($warehouse as $row) {
$selected = ($row['wh_id'] == $order['or_warehouse']) ? 'selected' : '';
echo "<option value='{$row['wh_id']}' {$selected}>{$row['wh_name']}</option>";
}
?>
</select>
</div>
</div>

<?php if ($loginRank == "admin"):?>

<div class="col-sm-12">
<div class="my-3">
<div class="input">Vendeur</div>
<select name="user" class="js-select w-100">
<option value="0" disabled selected>Choisir Vendeur</option>
<?php
foreach ($user as $row) {
$selected = ($row['user_id'] == $order['or_trade']) ? 'selected' : '';
echo "<option value='{$row['user_id']}' {$selected}>{$row['user_name']}</option>";
}
?>
</select>
</div>
</div>
<?php elseif ($loginRank == "user"): ?>
<input type='hidden' name='user' value='<?=$loginId;?>'/>
<?php elseif ($loginRank == "aide"): ?>
<input type='hidden' name='user' value='<?=$order['or_trade'];?>'/>
<?php endif; ?>




<div class="col-8">
<div class="my-3">
<div class="input">Produit</div>
<input name="item" type="text" class="form-control" value="<?php echo $order['or_item']; ?>" placeholder=""/>
</div>
</div>



<div class="col-4">
<div class="my-3">
<div class="input">Qté</div>
<input name="qty" type="number" class="form-control" value="<?php echo $order['or_qty']; ?>" placeholder=""/>
</div>
</div>




<div class="col-sm-6">
<div class="my-3">
<div class="input">Prix de colis</div>
<input name="price" type="number" class="form-control" value="<?php echo $order['or_total']; ?>" placeholder=""/>
</div>
</div>

<div class="col-sm-6">
<div class="my-3">
<div class="input">Ville</div>
<select name="city" class="js-select w-100">
<option value="0" disabled selected>Choisir Ville</option>
<?php
foreach ($city as $row) {
$selected = ($row['city_id'] == $order['or_city']) ? 'selected' : '';
echo "<option value='{$row['city_id']}' {$selected}>{$row['city_name']}</option>";
}
?>
</select>
</div>
</div>

<div class="col-sm-6">
<div class="my-3">
<div class="input">Destinataire</div>
<input name="name" type="text" class="form-control" value="<?php echo $order['or_name']; ?>" placeholder=""/>
</div>
</div>

<div class="col-sm-6">
<div class="my-3">
<div class="input">Téléphone</div>
<input name="phone" type="number" class="form-control" value="<?php echo $order['or_phone']; ?>" placeholder=""/>
</div>
</div>

<div class="col-sm-6">
<div class="my-3">
<div class="input">Adresse</div>
<input name="location" type="text" class="form-control" value="<?php echo $order['or_address']; ?>" placeholder=""/>
</div>
</div>

<div class="col-sm-6">
<div class="my-3">
<div class="input">Note</div>
<input name="note" type="text" class="form-control" value="<?php echo $order['or_note']; ?>" placeholder=""/>
</div>
</div>



<div class="col-sm-12">
<div class="my-3">
<div class="input">Emballage</div>
<select name="box" class="js-select w-100">
<option value="0" selected>Sans Embalage - 0 Dhs</option>
<?php
foreach ($box as $row) {
$selected = ($row['box_id'] == $order['or_box']) ? 'selected' : '';
echo "<option value='{$row['box_id']}' {$selected}>{$row['box_name']} | {$row['box_price']}</option>";
}
?>
</select>
</div>
</div>




<div class="col-sm-6" style='display:none'>
<div class="my-3">
<div class="input">Date de ramassage</div>
<input name="pickup" type="date" class="form-control" value="<?php echo $order['or_pickup_date']; ?>" placeholder=""/>
</div>
</div>


<div class="col-sm-12">
<div class="form-check form-switch form-check-inline" style='display:none'>
<input value="5" class="form-check-input" type="checkbox" name="fragile" id="fragile" <?php echo ($order['or_fragile'] == 5) ? 'checked' : ''; ?>>
<label class="form-check-label" for="fragile">Fragile | +5 Dhs</label>
</div>
<div class="form-check form-switch form-check-inline">
<input value="1" class="form-check-input" type="checkbox" name="try" id="try" <?php echo ($order['or_try'] == 1) ? 'checked' : ''; ?>>
<label class="form-check-label" for="try">Essayage</label>
</div>
<div class="form-check form-switch form-check-inline">
<input value="1" class="form-check-input" type="checkbox" name="open" id="open" <?php echo ($order['or_open_package'] == 1) ? 'checked' : ''; ?>>
<label class="form-check-label" for="open">Autorisation D'ouvrir</label>
</div>
<div class="form-check form-switch form-check-inline" style='display:none'>
<input value="1" class="form-check-input" type="checkbox" name="change" id="change" <?php echo ($order['or_change'] == 1) ? 'checked' : ''; ?>>
<label class="form-check-label" for="change">Échange</label>
</div>
</div>



<div class="col-sm-12 text-center">
<div id='<?php print $result; ?>'></div>
</div>

<div class="col-sm-12 text-center">
<button class="btn my-3 btn-primary">Valider</button>
</div>
</div>
</div>
</div>

<?php

} else {
echo "<div class='alert alert-danger'>Aucun identifiant spécifié.</div>";
exit();
}
formAwdEnd();

}

}elseif($do == "change"){
if (hasUserPermission($con, $loginId, 9 ,'admin') || $loginRank == "user" || hasUserPermissionAide($con, $loginId, 57 ,'user')){


$id = "formId";  // معرّف النموذج
$result = "data_result"; 
$action = "change_location"; 
$method = "post"; 
formAwdStart($id, $result, $action, $method); 

// استلام معرّف السجل الذي سيتم تعديله
$orderId = $_GET['order_id']?? ''; // التأكد من وجود معرّف السجل في الطلب

// إذا تم إرسال المعرف عبر GET أو POST، نسترجع البيانات من قاعدة البيانات
if ($orderId !== "") {
$stmt = $con->prepare("SELECT * FROM orders WHERE md5(or_id) = :order_id");
$stmt->bindParam(':order_id', $orderId, PDO::PARAM_STR);
$stmt->execute();
$order = $stmt->fetch(PDO::FETCH_ASSOC);

// التأكد من أن السجل موجود في قاعدة البيانات
if (!$order) {
echo "<div class='alert alert-danger'>Le colis spécifié n'existe pas.</div>";
}


// استعلام لاختيار البيانات المتاحة لحقول الاختيار (مثل الوكالات، المستخدمين، المدن، الخ)
$stmt = $con->prepare("SELECT * FROM warehouse WHERE wh_unlink = '0' ORDER BY wh_name");
$stmt->execute();
$warehouse = $stmt->fetchAll();

$stmt = $con->prepare("SELECT * FROM users WHERE user_unlink = '0' AND user_rank = 'user' ORDER BY user_name");
$stmt->execute();
$user = $stmt->fetchAll();

$stmt = $con->prepare("SELECT * FROM city WHERE city_unlink = '0' ORDER BY city_name");
$stmt->execute();
$city = $stmt->fetchAll();

$stmt = $con->prepare("SELECT * FROM box WHERE box_unlink = '0' ORDER BY box_name");
$stmt->execute();
$box = $stmt->fetchAll();

?>
<input type="hidden" value="<?php echo md5($order['or_id']); ?>" name="order_id"/>
<div class="card">
<div class="card-header">
<h5><b>changement d'adresse </b></h5>
</div>

<div class="card-body">
<div class="row">


<?php if ($loginRank == "admin"):?>

<div class="col-sm-12">
<div class="my-3">
<div class="input">Vendeur</div>
<select name="user" class="js-select w-100">
<option value="0" disabled selected>Choisir Vendeur</option>
<?php
foreach ($user as $row) {
$selected = ($row['user_id'] == $order['or_trade']) ? 'selected' : '';
echo "<option value='{$row['user_id']}' {$selected}>{$row['user_name']}</option>";
}
?>
</select>
</div>
</div>
<?php elseif ($loginRank == "user"): ?>
<input type='hidden' name='user' value='<?=$loginId;?>'/>
<?php elseif ($loginRank == "aide"): ?>
<input type='hidden' name='user' value='<?=$order['or_trade'];?>'/>
<?php endif; ?>





<div class="col-sm-6">
<div class="my-3">
<div class="input">Destinataire</div>
<input name="name" type="text" class="form-control" value="<?php echo $order['or_name']; ?>" placeholder=""/>
</div>
</div>

<div class="col-sm-6">
<div class="my-3">
<div class="input">Téléphone</div>
<input name="phone" type="number" class="form-control" value="<?php echo $order['or_phone']; ?>" placeholder=""/>
</div>
</div>

<div class="col-sm-12">
<div class="my-3">
<div class="input">Adresse</div>
<input name="location" type="text" class="form-control" value="<?php echo $order['or_address']; ?>" placeholder=""/>
</div>
</div>





<div class="col-sm-12 text-center">
<div id='<?php print $result; ?>'></div>
</div>

<div class="col-sm-12 text-center">
<button class="btn my-3 btn-primary">Valider</button>
</div>
</div>
</div>
</div>

<?php

} else {
echo "<div class='alert alert-danger'>Aucun identifiant spécifié.</div>";
exit();
}
formAwdEnd();

}

}elseif($do == "fee"){
if ($loginRank == "admin"){


$id = "formId";  // معرّف النموذج
$result = "data_result"; 
$action = "change_fee"; 
$method = "post"; 
formAwdStart($id, $result, $action, $method); 

// استلام معرّف السجل الذي سيتم تعديله
$orderId = $_GET['order_id']?? ''; // التأكد من وجود معرّف السجل في الطلب

// إذا تم إرسال المعرف عبر GET أو POST، نسترجع البيانات من قاعدة البيانات
if ($orderId !== "") {
$stmt = $con->prepare("SELECT * FROM orders WHERE md5(or_id) = :order_id");
$stmt->bindParam(':order_id', $orderId, PDO::PARAM_STR);
$stmt->execute();
$order = $stmt->fetch(PDO::FETCH_ASSOC);

// التأكد من أن السجل موجود في قاعدة البيانات
if (!$order) {
echo "<div class='alert alert-danger'>Le colis spécifié n'existe pas.</div>";
}


// استعلام لاختيار البيانات المتاحة لحقول الاختيار (مثل الوكالات، المستخدمين، المدن، الخ)
$stmt = $con->prepare("SELECT * FROM warehouse WHERE wh_unlink = '0' ORDER BY wh_name");
$stmt->execute();
$warehouse = $stmt->fetchAll();

$stmt = $con->prepare("SELECT * FROM users WHERE user_unlink = '0' AND user_rank = 'user' ORDER BY user_name");
$stmt->execute();
$user = $stmt->fetchAll();

$stmt = $con->prepare("SELECT * FROM city WHERE city_unlink = '0' ORDER BY city_name");
$stmt->execute();
$city = $stmt->fetchAll();

$stmt = $con->prepare("SELECT * FROM box WHERE box_unlink = '0' ORDER BY box_name");
$stmt->execute();
$box = $stmt->fetchAll();

if($order['or_fpc'] > 0){
$fpc_checked = "checked";
}else{
$fpc_checked = "";
}


?>
<input type="hidden" value="<?php echo md5($order['or_id']); ?>" name="order_id"/>
<div class="card">
<div class="card-header">
<h5><b>Frais ajoutés Pour colis N° : <?=$order['or_id'];?> </b></h5>
</div>

<div class="card-body">
<div class="row">



<div class="col-sm-12">
<div class="my-3">
<div class="input">Frais - GC - Grand colis</div>
<input name="fee" type="text" class="form-control" value="<?php echo $order['or_fee']; ?>" placeholder=""/>
</div>
</div>


<div class="col-sm-12">
<div class="my-3">
<div class="input">Frais des autocollants.</div>
<input name="print" type="text" class="form-control" value="<?php echo $order['or_print']; ?>" placeholder=""/>
</div>
</div>

<div class="col-sm-12">
<div class="form-check mx-3 form-switch form-check-inline">
<input value='1' class="form-check-input" type="checkbox" role="switch" name="fpc" id="fpc" <?=$fpc_checked;?>>
<label class="form-check-label" for="fpc">FPC - Frais payé cash</label>
</div>
</div>



<div class="col-sm-12 text-center">
<div id='<?php print $result; ?>'></div>
</div>

<div class="col-sm-12 text-center">
<button class="btn my-3 btn-primary">Valider</button>
</div>
</div>
</div>
</div>

<?php

} else {
echo "<div class='alert alert-danger'>Aucun identifiant spécifié.</div>";
exit();
}
formAwdEnd();

}













}elseif($do == "import"){
if (hasUserPermission($con, $loginId, 5 ,'admin') || $loginRank == "user" || hasUserPermissionAide($con, $loginId, 55 ,"aide")){

?>
<div class="card">

<div class="card-header">
<h5><b>+</b> Importer des Colis (Excel)</h5>
</div>

<div class="card-body">
<div>Télécharger : <a class='btn btn-success btn-sm' download href='./uploads/colis.xlsx'>colis.xlsx</a></div>
</div>

<hr>

<div class="card-body">
<div class="row">


<?php
include get_file("Admin/import");
?>

</div>
</div>
</div>


<?php
}

}elseif($do == "check"){









}else{










}




?>






</div>
</div>



































</main>

<?php include get_file("Admin/admin_footer");?>