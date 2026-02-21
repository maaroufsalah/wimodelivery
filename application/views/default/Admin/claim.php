<?php 

$bootstrap = new Bootstrap();
$bootstrap->runBootstrap();
global $con;
include get_file("files/sql/get/os_settings");
include get_file("files/sql/get/session");
include get_file("files/sql/get/functions");
include get_file("Admin/admin_header");


define ("page_title","Réclamations");


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
<?php if ((hasUserPermission($con, $loginId, 36 ,'admin')) || hasUserPermissionAide($con, $loginId, 52 ,'user') || ($loginRank == "user")):?>

<?php
// جلب المستخدمين
$stmt = $con->prepare("SELECT * FROM users WHERE user_unlink = '0' AND user_rank = 'user' ORDER BY user_name ASC");
$stmt->execute();
$users = $stmt->fetchAll();
?>

<!-- زر إنشاء فاتورة -->
<div style="text-align: right;">
<?php if ($loginRank == "admin"):?>
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
<option value="" disabled selected>Choisir</option>
<option value='0'>En Attente</option>
<option value='1'>En Traitement</option>
<option value='2'>Traité</option>
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
url: 'getClaim', // اسم سكربت PHP الذي يُعالج الطلب
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
if ($loginRank == "user" || hasUserPermission($con, $loginId, 36 ,'admin') || hasUserPermissionAide($con, $loginId, 52 ,'user')){	
		

$id = $_GET ["id"] ?? '';
$stmt = $con->prepare ("SELECT * FROM orders  WHERE md5(or_id) = '$id'  LIMIT 1");
$stmt->execute();
$orders = $stmt->fetch();

if (count($orders)>0){


print "<div class='card'>";

print "<div class='card-body'>";
print "<h5>Créer réclamation Pour Colis N° : ".$orders['or_id']."</h5>";		
print "</div>";

print "<hr>";
print "<div class='card-body'>";




// استعلام المستخدمين
$stmt = $con->prepare("SELECT * FROM users WHERE user_unlink = '0' AND user_rank = 'user' ORDER BY user_name");
$stmt->execute();
$user = $stmt->fetchAll();


$id = "formId";
$result = "data_result"; 
$action = "newClaim"; 
$method = "post"; 
formAwdStart($id, $result, $action, $method); 
print "<input type='hidden' name='orderId' value='".md5($orders['or_id'])."'/>";		

?>


<div class='col-sm-12' style='display:none'>
<label for='inputName' class='form-label'>L'objet</label>
<div class='input-group'>
<input type='text' name='name' class='form-control' id='inputName' value='' placeholder=''/>
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


<?php
formAwdEnd();
}

print "</div>";
print "</div>";


}

}elseif($do == "open"){


}else{






}
?>






</div>
</div>

























</main>

<?php include get_file("Admin/admin_footer");?>