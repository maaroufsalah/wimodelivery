<?php 

$bootstrap = new Bootstrap();
$bootstrap->runBootstrap();

include get_file("files/sql/get/os_settings");
include get_file("files/sql/get/session");
include_once get_file("files/sql/get/functions");

if ((hasUserPermission($con, $loginId, 26 ,'admin')) || ($loginRank == "delivery")){

include get_file("Admin/admin_header");


define ("page_title","Expéditions");


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

// استعلامات جلب البيانات من قاعدة البيانات
$stmt = $con->prepare("SELECT * FROM warehouse WHERE wh_unlink = '0' ORDER BY wh_name");
$stmt->execute();
$warehouse_from = $stmt->fetchAll();

$stmt = $con->prepare("SELECT * FROM warehouse WHERE wh_unlink = '0' ORDER BY wh_name");
$stmt->execute();
$warehouse_to = $stmt->fetchAll();

// استعلام لاختيار المستخدمين الذين لديهم صلاحية "delivery"
$stmt = $con->prepare("SELECT * FROM users WHERE user_unlink = '0' AND user_rank = 'delivery' ORDER BY user_name ASC");
$stmt->execute();
$user = $stmt->fetchAll();
?>

<!-- HTML -->
<div style="text-align: right;">
<?php if ($loginRank == "admin"):?>
<a href='?do=new' class="btn btn-primary my-3 btn-sm">Ajouter Expédition</a>
<?php endif; ?>
</div>

<div class="card" style="border-radius:0rem">
<div class="card-body">
<div class="row">
<div class='col-6' style="text-align: left;">
<h6>Recherche</h6>
<input type="text" class="searchbox form-control mb-3" style="width:100%"/>
</div>

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
<!-- حقل اختيار وكالة الإرسال -->
<div class="col-sm-4">
<div class="my-3">
<div class="input">L'agence d'envoi</div>
<select name="from" class="js-select w-100 from">
<option value="0" disabled selected>Choisir Agence</option>
<?php foreach ($warehouse_from as $row): ?>
<option value='<?= $row['wh_id'] ?>'><?= $row['wh_name'] ?></option>
<?php endforeach; ?>
</select>
</div>
</div>
<?php endif;?>

<?php if ($loginRank == "admin"):?>
<!-- حقل اختيار وكالة الاستلام -->
<div class="col-sm-4">
<div class="my-3">
<div class="input">L'agence réceptrice</div>
<select name="to" class="js-select w-100 to">
<option value="0" disabled selected>Choisir Agence</option>
<?php foreach ($warehouse_to as $row): ?>
<option value='<?= $row['wh_id'] ?>'><?= $row['wh_name'] ?></option>
<?php endforeach; ?>
</select>
</div>
</div>
<?php endif;?>

<?php if ($loginRank == "admin"):?>
<!-- حقل اختيار المستخدم (الـ "livreur") -->
<div class="col-sm-4">
<div class="my-3">
<div class="input">Livreur</div>
<select name="user" class="js-select w-100 user">
<option value="0" disabled selected>Choisir Livreur</option>
<?php foreach ($user as $row): ?>
<option value='<?= $row['user_id'] ?>'><?= $row['user_name'] ?></option>
<?php endforeach; ?>
</select>
</div>
</div>
</div>
<?php endif;?>
<hr>


<!-- محتوى AJAX سيتم تحميله هنا -->
<div class="loader"></div>
<div id="dynamic_content"></div>
</div>
</div>

<script>
$(document).ready(function () {
// تحميل البيانات لأول مرة
loadData(1);

// دالة التحميل مع باراميتر الصفحة (افتراضياً 1)
function loadData(page = 1) {
const search = $('.searchbox').val();
const display = $('.display').val();
const from = $('.from').val();
const to = $('.to').val();
const user = $('.user').val();

$.ajax({
url: 'get_shipping',
method: 'POST',
data: {
page,
search,
display
},
beforeSend: function () {
$('.loader').html('<span class="spinner-border spinner-border-sm"></span> Chargement...');
},
success: function (data) {
$('#dynamic_content').html(data);
$('.loader').html('');
}
});
}

// إعادة تحميل الصفحة الأولى عند تغييرات الفلتر (البحث، العرض، المدينة)
$('.searchbox, .display, .from, .to, user ').on('input change keyup', function () {
loadData(1);
});

// التصفح عبر روابط الصفحات (يربط رقم الصفحة مباشرة)
$(document).on('click', '.page-link', function (e) {
e.preventDefault();
const page = $(this).data('page');
if (page) {
loadData(page);
}
});

// زر التحديث اليدوي (إذا موجود)
$(document).on('click', '.updatedata', function () {
const page = $(this).val() || 1;
loadData(page);
});
});

</script>



<?php
}elseif($do == "new"){

if ($loginRank == "admin"){

$id = "formId";  // معرّف النموذج
$result = "data_result"; 
$action = "newShipping"; 
$method = "post"; 
formAwdStart($id, $result, $action, $method); 


// جلب بيانات المخازن
$stmt = $con->prepare("SELECT * FROM warehouse WHERE wh_unlink = '0' ORDER BY wh_name");
$stmt->execute();
$warehouse = $stmt->fetchAll();


// استعلام جلب المستخدمين (لا تنسى تمرير $whc في execute)
$stmt = $con->prepare("SELECT * FROM users WHERE user_unlink = '0' AND user_rank = 'delivery'  ORDER BY user_name");
$stmt->execute($whc);
$user = $stmt->fetchAll();


if ($_GET['user']>0){
// استعلام جلب الطلبات
$sql = "SELECT * FROM orders 
WHERE or_unlink = '0' 
AND or_delivery_user = '".$_GET['user']."'
AND or_invoice = '0' 
AND or_shipping = '0' 
AND or_state_delivery != '1' 
ORDER BY or_id DESC";

}else{

$sql = "SELECT * FROM orders 
WHERE or_unlink = '10' 

ORDER BY or_id DESC";


}
$stmt = $con->prepare($sql);
$stmt->execute($whc);
$orders = $stmt->fetchAll();

?>

<input type="hidden" name="order_id" value="">


<div class="card">
<div class="card-header">
<h5><b>Ajouter Expédition</b></h5>
</div>
<div class="card-body">
<div class="row">


<div class="col-sm-6">
<div class="my-3">
<div class="input">L'agence d'envoi</div>
<select name="from" class="js-select w-100 warehouse">
<option value="0" disabled selected>Choisir Agence</option>
<?php foreach ($warehouse as $row): ?>
<option value='<?= $row['wh_id'] ?>'  <?php if ($row['wh_id'] == 1){print "selected";} ?>><?= $row['wh_name'] ?></option>
<?php endforeach; ?>
</select>
</div>
</div>


<div class="col-sm-6">
<div class="my-3">
<div class="input">L'agence réceptrice</div>
<select name="to" class="js-select wh w-100 warehouse">
<option value="0" disabled selected>Choisir Agence</option>
<?php foreach ($warehouse as $row): ?>
<option value='<?= $row['wh_id'] ?>'>
<?= $row['wh_name'] ?>
</option>
<?php endforeach; ?>
</select>
</div>
</div>




<div class="col-sm-12">
<div class="my-3">
<div class="input">Livreur</div>
<select name='user' class='js-select d-user user w-100'>
<option value='0' disabled selected>Choisir Livreur</option>
<?php foreach ($user as $row): ?>
<option value='<?= $row['user_id'] ?>' <?= ($row['user_id'] == ($_GET['user'] ?? '')) ? "selected" : "" ?>><?= $row['user_name'] ?></option>
<?php endforeach; ?>
</select>
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
<h5>Colis : <b>Prêt à expédier</b></h5> 
</div>

<div class="col-sm-12">

<?php


include get_file("files/sql/get/fetch_orders");

?>
</div>



<div style='margin:100px;'></div>




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





$('.user').change(function(){
let id = $(this).val();
window.location.href = `shipping?do=new&user=${id}`;
});
</script>





<?php
}

}elseif($do == "scan"){

if (($loginRank == "admin") || ($loginRank == "delivery"))   {

$id = $_GET['id'] ?? '';

$stmt = $con->prepare("SELECT * FROM expeditions WHERE md5(expedition_id) = '$id' LIMIT 1");
$stmt->execute();
$exp = $stmt->fetch();



if (count($exp)>0){

?>




<div class="row">


<div class='col-sm-4' style="text-align: left;">
<div class="card" style="border-radius:0rem">
<div class="card-body">
<h5>Scanner - Exp : (<b><?=$exp['expedition_code']?></b>)</h5>
<input type="hidden" id="exp" value="<?=$exp['expedition_id']?>"/>
<input type="text" id="scan_input" placeholder="Scannez un code..." style="width: 100%; padding: 10px; font-size: 18px;">
<div id="reader"></div>
</div>
</div>
</div>

<div class='col-sm-8' style="">

<div id="result"></div>
</div>


</div>








<script>
let lastScannedCode = "";
let typingTimer;
const delay = 500;

function processCode(code) {
if (code === lastScannedCode) return;
lastScannedCode = code;

const exp = $('#exp').val();  // ← Récupère l'état sélectionné

$('#scan_input').val(code);
$('#result').html("Code détecté : <strong>" + code + "</strong><br>Chargement...");

$.ajax({
url: 'check_exp',
type: 'POST',
data: { code: code, exp: exp },  // ← Envoie les deux
success: function(response) {
$('#result').html("<strong>Code détecté :</strong> " + code + "<hr>" + response);
$("#scan_input").val('');
},
error: function() {
$('#result').html("<span style='color:red;'>Erreur lors de la requête AJAX.</span>");
}
});
}



function onScanSuccess(decodedText, decodedResult) {
processCode(decodedText);
}

const html5QrCode = new Html5Qrcode("reader");
html5QrCode.start(
{ facingMode: "environment" },
{
fps: 10,
qrbox: { width: 250, height: 250 },
formatsToSupport: [
Html5QrcodeSupportedFormats.QR_CODE,
Html5QrcodeSupportedFormats.EAN_13,
Html5QrcodeSupportedFormats.CODE_128,
Html5QrcodeSupportedFormats.UPC_A
]
},
onScanSuccess
).catch(err => {
$('#result').html("<span style='color:red;'>Erreur caméra : " + err + "</span>");
});

$('#scan_input').on('input', function() {
clearTimeout(typingTimer);
typingTimer = setTimeout(function() {
let manualCode = $('#scan_input').val().trim();
if (manualCode !== "" && manualCode !== lastScannedCode) {
processCode(manualCode);
}
}, delay);
});
</script>



<?php

}


}

}elseif($do == "open"){







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