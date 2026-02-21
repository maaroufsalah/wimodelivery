<?php 

$bootstrap = new Bootstrap();
$bootstrap->runBootstrap();

include get_file("files/sql/get/os_settings");
include get_file("files/sql/get/session");
include_once get_file("files/sql/get/functions");

if ((hasUserPermission($con, $loginId, 21 ,'admin')) || ($loginRank == "user") || hasUserPermissionAide($con, $loginId, 50 ,'user')){

include get_file("Admin/admin_header");



define ("page_title","Bon de Retour client");


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
if ((hasUserPermission($con, $loginId, 21 ,'admin')) || ($loginRank == "user" || hasUserPermissionAide($con, $loginId, 50 ,'user'))){




$stmt = $con->prepare("SELECT * FROM users WHERE user_unlink = '0' AND user_rank = 'user' ORDER BY user_name ASC");
$stmt->execute();
$user = $stmt->fetchAll();
?>

<!-- HTML -->
<div style="text-align: right;">
<a href='?do=new' class="btn btn-primary my-3 btn-sm">Ajouter</a>
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
<!-- حقل اختيار المستخدم (الـ "livreur") -->
<div class="col-sm-12">
<div class="my-3">
<div class="input">Vendeur</div>
<select name="user" class="js-select w-100 user">
<option value="0" disabled selected>Choisir Vendeur</option>
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
$(document).ready(function(){
load_data(1); // تحميل البيانات افتراضيًا عند تحميل الصفحة

function load_data(page, search = '', display = '', user = '') {
console.log("📤 إرسال البيانات:", {
page: page,
search: search,
display: display,
user: user
});

$.ajax({
url: 'getLogs?do=outlog_user', // سكربت PHP الذي سيعالج الطلب
method: 'POST',
data: { 
page: page,
search: search,
display: display,
user: user 
},
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
var froms = $('.from').val();
load_data(1, search, display ,user);
});

// عند تغيير قيمة عرض العناصر
$('.display').change(function() {
var display = $(this).val();
var search = $('.searchbox').val();
var user = $('.user').val();
load_data(1, search, display ,user);
});

// عند تغيير المستخدم (livreur)
$('.user').change(function() {
var display = $('.display').val();
var search = $('.searchbox').val();
var user = $(this).val();
load_data(1, search, display ,user);
});



// التعامل مع الصفحات
$(document).on('click', '.page-link', function(event) {
event.preventDefault(); // منع إعادة تحميل الصفحة
let page = $(this).attr('data-page'); // التقاط رقم الصفحة
let search = $('.searchbox').val();
let display = $('.display').val();
load_data(page, search, display ,user); // ✅ إغلاق القوس بشكل صحيح
});
});
</script>

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
window.location.href = `?do=new&user=${id}`;
});
</script>


<script>
let lastScannedCode = "";
let typingTimer;
const delay = 500;

function processCode(code) {
    if (code === lastScannedCode) return;
    lastScannedCode = code;

    // ✅ تحديد الـ checkbox المرتبط
    const checkbox = document.getElementById('cb_' + code);
    if (checkbox) {
        checkbox.checked = true;
        checkbox.scrollIntoView({ behavior: "smooth", block: "center" });
    } else {
        console.warn("Order ID non trouvé: " + code);
    }

    // تفريغ الحقل
    document.getElementById('scan_input').value = '';
}

// مسح QR
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
    console.error("Erreur caméra : " + err);
});

// إدخال يدوي
document.getElementById('scan_input').addEventListener('input', function () {
    clearTimeout(typingTimer);
    typingTimer = setTimeout(() => {
        let manualCode = this.value.trim();
        if (manualCode !== "" && manualCode !== lastScannedCode) {
            processCode(manualCode);
        }
    }, delay);
});

</script>


<?php
}
}elseif($do == "new"){

if ((hasUserPermission($con, $loginId, 21 ,'admin')) || ($loginRank == "user") || hasUserPermissionAide($con, $loginId, 50 ,'user')){


if ($loginRank == "admin"){
$userId = isset ($_GET['user']) ? $_GET ['user'] : 0;
}else{
$userId = $loginId;   
}

?>
<div class='row'>


<div class='col-sm-3'>
<!-- scan  -->
<div class="card" style="border-radius:0rem">
<div class="card-body">
<h5>Scanner QR ou Code-Barres</h5>
<input type="text" id="scan_input" placeholder="Scannez un code..." style="width: 100%; padding: 10px; font-size: 18px;">
<div id="reader"></div>
</div>
</div>
</div>



<div class='col-sm-9'>
<?php
$id = "formId";  // معرّف النموذج
$result = "data_result"; 
$action = "newLog?do=outlog_user"; 
$method = "post"; 
formAwdStart($id, $result, $action, $method); 

$stmt = $con->prepare("SELECT * FROM users WHERE user_unlink = '0' AND user_rank = 'user' ORDER BY user_name");
$stmt->execute();
$user = $stmt->fetchAll();
?>


<?php if ($loginRank == "admin"):?>
<div class="col-sm-12">
<div class="my-3">
<div class="input">Vendeur</div>
<select name='user' class='js-select user d-user w-100'>
<option value='0' disabled selected>Choisir Vendeur</option>
<?php foreach ($user as $row): ?>
<option value='<?= $row['user_id'] ?>' <?php if ($row['user_id'] == $userId){echo "selected";} ?>><?= $row['user_name'] ?></option>
<?php endforeach; ?>
</select>
</div>
</div>
<?php elseif ($loginRank == "user"): ?>
<input type='hidden' name='user' value='<?=$loginId;?>'/>
<?php endif; ?>



<input type="hidden" name="order_id" value="">

<?php



$stmt = $con->prepare("
SELECT * FROM orders 
WHERE 
or_unlink = '0' AND or_state_delivery = '4' AND or_trade = '$userId'
ORDER BY or_id DESC ");
$stmt->execute();
$orders = $stmt->fetchAll();

if (count($orders)>0){

?>



<div class="card">
<div class="card-header">
<h5><b>Ajouter Un Bon</b></h5>
</div>
<div class="card-body">
<div class="row">








<div class="col-sm-12">
<h5>Colis : <b> Retourné</b></h5> 
</div>

<div class="col-sm-12">

<?php include get_file("files/sql/get/fetch_orders");?>



</div>

<?php
}else{

print "
<div class='alert alert-warning'>Aucun résultat trouvé</div>
";
}
?>



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

</div>
</div>






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
window.location.href = `?do=new&user=${id}`;
});
</script>





<?php
}



}elseif($do == "scan"){





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