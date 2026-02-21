<?php 

$bootstrap = new Bootstrap();
$bootstrap->runBootstrap();

include get_file("files/sql/get/os_settings");
include get_file("files/sql/get/session");
include get_file("files/sql/get/functions");
include get_file("Admin/admin_header");


define ("page_title","Demande de Ramassage");


?>






<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
<!--begin::App Wrapper-->
<div class="app-wrapper">
<!--begin::Header-->





<?php include get_file("Admin/admin_nav_top");?>
<?php include get_file("Admin/admin_nav_left");?>












<!-- بطاقة التحكم الثابتة -->
<div class="position-fixed bottom-0 start-auto end-0 bg-white shadow-lg border-top py-3 px-4 zindex-tooltip" style="z-index: 1055;">
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3">

<!-- زر تحديد الكل -->
<button id="selectAllBtn" class="btn btn-sm btn-outline-dark">
<i class="fa-regular fa-square-check"></i> Tout sélectionner
</button>


<?php if (($loginRank == "admin") || ($loginRank == "delivery")):?>
<!-- تغيير الحالة -->
<button id="transferBtn"  data-bs-toggle='modal' data-bs-target='#modal_state'  class="btn btn-sm btn-warning">
<i class="fa-solid fa-truck"></i> L'état
</button>
<?php endif; ?>



<?php if ($loginRank == "admin"):?>
<!-- تمرير الطرد -->
<button id="transferBtn"  data-bs-toggle='modal' data-bs-target='#modal_delivery'  class="btn btn-sm btn-info">
<i class="fa-solid fa-truck"></i> Transférer Au Livreur
</button>
<?php endif; ?>


</div>
</div>










<?php if (($loginRank == "admin") || ($loginRank == "delivery")):?>

<div class='modal fade' id='modal_state' tabindex='-1'>
<div class='modal-dialog modal-dialog-centered'>
<div class='modal-content'>
<div class='modal-header'>
<h5 class='modal-title'>Changer l'état de livraison</h5>
<button type='button' class='btn-close' data-bs-dismiss='modal'></button>
</div>

<div class='modal-body text-center'>
<form id="state_form" onsubmit="return collectSelectedIds('state_form')">
<input type="hidden" name="order_id" value="">


<label for="state_select">Choisir une nouvelle état:</label>
<select class='js-select state_select w-100' name="state_id" required>
<option value="">Sélectionner une état</option>
<?php
$stmt = $con->prepare("SELECT * FROM state WHERE state_unlink = '0' ORDER BY state_name ASC");
$stmt->execute();
$states = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($states as $state) {
echo '<option value="' . $state['state_id'] . '">' . $state['state_name'] . '</option>';
}
?>
</select>

<div id="report_date_div" style="display: none;">
<label for="postponed_date">Nouvelle date de report:</label>
<input type="date" id="postponed_date" name="postponed_date">
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









<?php if ($loginRank == "admin"):?>

<div class='modal fade' id='modal_delivery' tabindex='-1'>
<div class='modal-dialog modal-dialog-centered'>
<div class='modal-content'>
<div class='modal-header'>
<h5 class='modal-title'>Transférer Au Livreur</h5>
<button type='button' class='btn-close' data-bs-dismiss='modal'></button>
</div>

<div class='modal-body text-center'>
<form id="delivery_form" onsubmit="return collectSelectedIds('delivery_form')">
<input type="hidden" name="order_id" value="">

<label for="state_select">Choisir livreur</label>
<select  class='js-select w-100' name="delivery_id" required>
<option value="">Sélectionner un livreur</option>
<?php
$stmt = $con->prepare("SELECT * FROM users WHERE user_unlink = '0' AND user_rank = 'delivery' ORDER BY user_name ASC");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($users as $user) {
echo '<option value="' . $user['user_id'] . '">' . $user['user_name'] . '</option>';
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

if ($loginRank == "user" || hasUserPermissionAide($con, $loginId, 47 ,'user') || $loginRank == "delivery" || hasUserPermission($con, $loginId, 13 ,'admin')){
// تحديد العملية
$do = $_GET['do'] ?? 'Manage';

switch ($do):
case 'Manage':


$stmt = $con->prepare ("SELECT * FROM users WHERE user_unlink = '0' AND user_rank = 'user' ORDER BY user_name ASC");
$stmt->execute();
$nums = $stmt->rowCount();
$user = $stmt->fetchAll();

?>
<div class="">
<div class="row mt-4">
<?php if ($loginRank == "admin" || $loginRank == "user" || $loginRank == "aide"):?>

<div class="col-sm-12 my-4 text-end">
<a href="?do=new" class="btn btn-primary">
<i class="fa fa-plus"></i> Demande de ramassage
</a>
<?php endif ; ?>
</div>




<?php if ($loginRank == "admin"):?>
<div class="col-sm-12">
<div class="my-3">
<div class="input">Vendeur</div>
<select name='user' class='js-select user w-100'>
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
<div class="col-sm-6">
<input type="text" name="searchbox" class="form-control searchbox" placeholder="Recherche">
</div>


<div class="col-sm-6">
<select name="display" class="form-control display">
<option value="10">10</option>
<option value="25">25</option>
<option value="50">50</option>
<option value="100">100</option>
</select>
</div>
</div>



<div class="loader text-center py-3"></div>
<div id="dynamic_content"></div>
</div>

<script>


// عند الضغط على زر تحديد الكل
$('#selectAllBtn').on('click', function() {
  let checkboxes = $('.order-checkbox');
  let allChecked = checkboxes.length && checkboxes.filter(':checked').length === checkboxes.length;

  // إذا الكل محدد ➜ ألغِ التحديد
  if (allChecked) {
    checkboxes.prop('checked', false);
  } else {
    checkboxes.prop('checked', true);
  }

  updateHiddenField();
  updateSelectAllBtnText();
});

// عند تغيير أي Checkbox بشكل يدوي
$(document).on('change', '.order-checkbox', function() {
  updateHiddenField();
  updateSelectAllBtnText();
});


function updateSelectAllBtnText() {
let checkboxes = document.querySelectorAll('.order-checkbox');
if (checkboxes.length === 0) {
$('#selectAllBtn').text('Sélectionner tout');
return;
}
let allChecked = Array.from(checkboxes).every(cb => cb.checked);
$('#selectAllBtn').text(allChecked ? 'Désélectionner tout' : 'Sélectionner tout');
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
// إعادة تهيئة select2 بعد تحميل المحتوى الديناميكي
$('.js-select').select2();
}




// إرسال النماذج عبر AJAX مع تعطيل زر الإرسال أثناء الطلب
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

ajaxFormSubmit('#state_form', '#result_state', 'config_pickup?do=state');
ajaxFormSubmit('#delivery_form', '#result_delivery', 'config_pickup?do=delivery');
ajaxFormSubmit('#print_form', '#result_print', 'config_pickup?do=print');

// إظهار / إخفاء حقل تاريخ التقرير بناءً على اختيار الحالة
$(document).on('change', '.state_select', function() {
let stateId = $(this).val();
if (stateId == 5) {
$('#report_date_div').show();
} else {
$('#report_date_div').hide();
}
});



$(document).ready(function(){
load_data(1);

function load_data(page, search = '', display = '', user = '', rank = ''){
$.ajax({
url:'getPickupClient',
method:'POST',
data:{page:page , search:search , display:display, user:user, rank:rank},
cache: false,
	
	
beforeSend: function(){
$('.loader').html('');	
},  	
	
success:function(data){
$('#dynamic_content').html(data);
$('.loader').html('');
}

});
}
	  
var page = $(this).val();
var search = $('.searchbox').val();
var display = $('.display').val();
var user = $('.user').val();
var rank = $('.rank').val();
load_data(page, search ,display ,user ,rank);


$(document).on('click', '.updatedata', function(){		
var page = $(this).val();
var search = $('.searchbox').val();
var display = $('.display').val();
var user = $('.user').val();
var rank = $('.rank').val();
load_data(page, search ,display ,user ,rank);
});
		
	

$(document).on('click', '.page-link', function(){	
var page = $(this).data('page_number');
var search = $('.searchbox').val();
var display = $('.display').val();
var user = $('.user').val();
var rank = $('.rank').val();
load_data(page, search ,display ,user ,rank);
});
	  
$('.searchbox').keyup(function(){
var page = $(this).data('page_number');
var search = $('.searchbox').val();
var display = $('.display').val();
var user = $('.user').val();
var rank = $('.rank').val();
load_data(page, search ,display ,user ,rank);
});
	  
$('.display').change(function(){
var page = $(this).data('page_number');
var search = $('.searchbox').val();
var display = $('.display').val();
var user = $('.user').val();
var rank = $('.rank').val();
load_data(page, search ,display ,user ,rank);
});

	  
$('.user').change(function(){
var page = $(this).data('page_number');
var search = $('.searchbox').val();
var display = $('.display').val();
var user = $('.user').val();
var rank = $('.rank').val();
load_data(page, search ,display ,user ,rank);
});

	
});
</script>


<?php
break;

case 'new':


if ($loginRank == "user" || $loginRank == "admin" || hasUserPermissionAide($con, $loginId, 47 ,'user')){
		



print "<div class='card'>";

print "<div class='card-body'>";
print "<h5>Ajouter Ramassage</h5>";		
print "</div>";

print "<hr>";
print "<div class='card-body'>";




// استعلام المستخدمين
$stmt = $con->prepare("SELECT * FROM users WHERE user_unlink = '0' AND user_rank = 'user' ORDER BY user_name");
$stmt->execute();
$user = $stmt->fetchAll();


$id = "formId";
$result = "data_result"; 
$action = "new_pickup_client"; 
$method = "post"; 
formAwdStart($id, $result, $action, $method); 

?>
<div class="row">

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

<?php elseif ($loginRank == "aide"): ?>
<input type='hidden' name='user' value='<?=$loginUser['user_aide'];?>'/>



<?php endif; ?>


<div class='col-sm-6'>
<h6>Type de Ramassage</h6>
<select name='type' class='single-select'>
<option value='0' selected>Type de ramassage</option>	
<option value='stock'>stock</option>
<option value='colis'>colis</option>
</select>
</div>


<div class='col-sm-6'>
<label for='inputName' class='form-label'>Téléphone</label>
<div class='input-group'>
<input type='text' name='phone' class='form-control' id='inputName' value='' placeholder=''/>
</div>
</div>


<div class="col-sm-12">
<div class="my-3">
<div class="input">Adresse</div>
<input name="location" type="text" class="form-control" placeholder=""/>
</div>
</div>


<div class="col-sm-12">
<div class="my-3">
<div class="input">Note</div>
<input name="note" type="text" class="form-control" placeholder=""/>
</div>
</div>


<div class="col-sm-12 text-center">
<div id ='<?= htmlspecialchars($result); ?>'></div>
</div>

<div class="col-sm-12 text-center">
<button class="btn my-3 btn-primary">Valider</button>
</div>

</div>

<?php
formAwdEnd();


print "</div>";
print "</div>";


}





break;

default:
echo "<div class='container py-5 text-center text-danger'>Page non trouvée.</div>";
break;
endswitch;

}

?>






</div>
</div>

























</main>

<?php include get_file("Admin/admin_footer");?>