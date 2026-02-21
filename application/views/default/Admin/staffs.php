<?php 

$bootstrap = new Bootstrap();
$bootstrap->runBootstrap();

include get_file("files/sql/get/os_settings");
include get_file("files/sql/get/session");
include get_file("files/sql/get/functions");

if (hasUserPermission($con, $loginId, 26 ,'user')){

include get_file("Admin/admin_header");


define ("page_title","Équipes");


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


$stmt = $con->prepare("SELECT * FROM users WHERE user_unlink = '0' AND user_aide = '$loginId' ORDER BY user_name ASC");
$stmt->execute();
$user = $stmt->fetchAll();
?>

<!-- HTML -->
<div style="text-align: right;">
<a href='?do=new' class="btn btn-primary my-3 btn-sm">Ajouter Compte</a>
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



<?php if ($loginRank == "user"):?>
<div class="col-sm-412">
<div class="my-3">
<div class="input"></div>
<select name="user" class="js-select w-100 user">
<option value="0" disabled selected>Choisir compte</option>
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
const user = $('.user').val();

$.ajax({
url: 'get_staffs',
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
$('.searchbox, .display, user ').on('input change keyup', function () {
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



if($loginRank == "user"){

$rank = "aide" ?? '';

$stmt = $con->prepare("SELECT * FROM city WHERE city_unlink = '0' ORDER BY city_name ASC");
$stmt->execute();
$cities = $stmt->fetchAll(PDO::FETCH_ASSOC);



// استلام بيانات الـ rank من الـ URL أو متغير آخر
$user_rank = "aide" ?? '';  // هنا نفترض أنك تستقبل rank عبر الرابط

// استعلام لجلب المدن
$stmt = $con->prepare("SELECT * FROM city WHERE city_unlink = '0' ORDER BY city_name ASC");
$stmt->execute();
$cities = $stmt->fetchAll(PDO::FETCH_ASSOC);

// معرّف النموذج وبياناته
$id = "formId";
$result = "data_result";
$action = "newUserAide";
$method = "post";

// بدء النموذج
formAwdStart($id, $result, $action, $method);
?>
<input type='hidden' name='rank' value='<?= htmlspecialchars($user_rank); ?>'/>

<div class="card">
<div class="card-header">
<h5><b>Ajouter compte</b></h5>
</div>
<div class="card-body">
<div class="row">

<!-- الاسم الكامل -->
<div class="col-sm-6">
<div class="my-3">
<label>Nom et prénom</label>
<input name="owner" type="text" class="form-control" value=""/>
</div>
</div>

<!-- الاسم التجاري -->
<div class="col-sm-6">
<div class="my-3">
<label>Nom commercial</label>
<input name="name" type="text" class="form-control" value=""/>
</div>
</div>

<!-- رقم الهاتف -->
<div class="col-sm-6">
<div class="my-3">
<label>Téléphone</label>
<input name="phone" type="number" class="form-control" value=""/>
</div>
</div>

<!-- البريد الإلكتروني -->
<div class="col-sm-6">
<div class="my-3">
<label>Email</label>
<input name="email" type="email" class="form-control" value=""/>
</div>
</div>

<!-- هاتف المتجر -->
<div class="col-sm-6">
<div class="my-3">
<label>Télé - Commercial</label>
<input name="phone_store" type="number" class="form-control" value=""/>
</div>
</div>

<!-- المدينة -->
<div class="col-sm-6">
<div class="my-3">
<label>Ville</label>
<select name="city" class="form-select">
<option disabled selected>Choisir Ville</option>
<?php foreach ($cities as $city): ?>
<option value="<?= $city['city_id']; ?>">
<?= htmlspecialchars($city['city_name']); ?>
</option>
<?php endforeach; ?>
</select>
</div>
</div>

<!-- العنوان -->
<div class="col-sm-12">
<div class="my-3">
<label>Adresse</label>
<input name="location" type="text" class="form-control" value=""/>
</div>
</div>



<!-- حالة الحساب -->
<div class="col-sm-12">
<div class="my-3">
<label>État du compte</label>
<select name="state" class="form-select">
<option value="0">Inactif</option>
<option value="1">Actif</option>
</select>
</div>
</div>



<?php						
print "<div class='col-sm-12'>";

$stmt = $con->prepare ("SELECT * FROM permission WHERE per_rank = 'user' ORDER BY per_id ASC");
$stmt->execute();
$perCount = $stmt->rowCount();
$d_p = $stmt->fetchAll();
		
if ($perCount>0){
foreach ($d_p as $row){
$data = $row['per_id'];
		




print "
<div class='my-3'>
<div class='form-check form-switch'>
<input class='form-check-input' type='checkbox' name='via[]' value='".$row['per_id']."' id='flexSwitchCheckChecked".$row['per_id']."'>
<label class='form-check-label' for='flexSwitchCheckChecked".$row['per_id']."'>".$row['per_name']." - ".$row['per_id']."</label>
</div>
</div>
";



		

}
}		

print "</div>";

?>
		


<!-- صورة الملف الشخصي -->
<div class="col-sm-12">
<div class="my-3">
<label>Photo de profil</label>
<input type="file" name="image" class="form-control" accept="image/*">
</div>
</div>

<!-- كلمة المرور الجديدة -->
<div class="col-sm-6">
<div class="form-group">
<label for="new_password">Nouveau mot de passe</label>
<input type="password" name="new_password" class="form-control" required>
</div>
</div>

<!-- تأكيد كلمة المرور -->
<div class="col-sm-6">
<div class="form-group">
<label for="confirm_password">Confirmer le mot de passe</label>
<input type="password" name="confirm_password" class="form-control" required>
</div>
</div>

<!-- زر الحفظ -->
<div class="col-sm-12 text-center">
<div id='<?= $result; ?>'></div>
<button class="btn my-3 btn-primary">Valider</button>
</div>

</div>
</div>
</div>

<?php
formAwdEnd();

}






}elseif($do == "edit"){


if ($loginRank == "user") {


$id_url = $_GET['id'] ?? '';
$stmt = $con->prepare("SELECT * FROM users WHERE md5(user_id) = :id");
$stmt->bindParam(':id', $id_url, PDO::PARAM_STR);
$stmt->execute();
$aide = $stmt->fetch(PDO::FETCH_ASSOC);

if (!empty($aide)) {

if (count($aide)>0){
if ($loginId = $aide['user_id']) {
$data_id = md5($aide['user_id']);
} else {
$data_id = md5($loginId); // fallback to own data only
}
}else{
    $data_id = 0;
}
}
}

if (!empty($data_id)) {
$stmt = $con->prepare("SELECT * FROM users WHERE md5(user_id) = :id");
$stmt->bindParam(':id', $data_id, PDO::PARAM_STR);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (count($user)>0){

$uId = $user['user_id'];

$stmt = $con->prepare("SELECT * FROM city WHERE city_unlink = '0' ORDER BY city_name ASC");
$stmt->execute();
$cities = $stmt->fetchAll(PDO::FETCH_ASSOC);



$id = "formId";  // معرّف النموذج
$result = "data_result"; 
$action = "editUser"; 
$method = "post"; 
formAwdStart($id, $result, $action, $method); 
?>

<input type='hidden' name='id' value='<?= htmlspecialchars($data_id); ?>'/>


<div class="card">
<div class="card-header">
<h5><b>Modifier le compte</b></h5>
</div>
<div class="card-body">
<div class="row">

<!-- الاسم الكامل -->
<div class="col-sm-6">
<div class="my-3">
<label>Nom et prénom</label>
<input name="owner" type="text" class="form-control" value="<?= htmlspecialchars($user['user_owner']); ?>"/>
</div>
</div>

<!-- الاسم التجاري -->
<div class="col-sm-6">
<div class="my-3">
<label>Nom commercial</label>
<input name="name" type="text" class="form-control" value="<?= htmlspecialchars($user['user_name']); ?>"/>
</div>
</div>

<!-- رقم الهاتف -->
<div class="col-sm-6">
<div class="my-3">
<label>Téléphone</label>
<input name="phone" type="number" class="form-control" value="<?= htmlspecialchars($user['user_phone']); ?>"/>
</div>
</div>

<!-- البريد الإلكتروني -->
<div class="col-sm-6">
<div class="my-3">
<label>Email</label>
<input name="email" type="email" class="form-control" value="<?= htmlspecialchars($user['user_email']); ?>"/>
</div>
</div>

<!-- هاتف المتجر -->
<div class="col-sm-6">
<div class="my-3">
<label>Télé - Commercial</label>
<input name="phone_store" type="number" class="form-control" value="<?= htmlspecialchars($user['user_phone_store']); ?>"/>
</div>
</div>

<!-- المدينة -->
<div class="col-sm-6">
<div class="my-3">
<label>Ville</label>
<select name="city" class="form-select">
<option disabled selected>Choisir Ville</option>
<?php foreach($cities as $city): ?>
<option value="<?= $city['city_id']; ?>" <?= $city['city_id'] == $user['user_city'] ? 'selected' : '' ?>>
<?= htmlspecialchars($city['city_name']); ?>
</option>
<?php endforeach; ?>
</select>
</div>
</div>

<!-- العنوان -->
<div class="col-sm-12">
<div class="my-3">
<label>Adresse</label>
<input name="location" type="text" class="form-control" value="<?= htmlspecialchars($user['user_location']); ?>"/>
</div>
</div>

<div class='' style='display:none'>
<div class="col-sm-6" >
<div class="my-3">
<label>Numéro CIN</label>
<input name="cin" type="text" class="form-control" value="<?= htmlspecialchars($user['user_cin']); ?>"/>
</div>
</div>



<div class="col-sm-6">
<div class="my-3">
<label>Numéro du compte bancaire</label>
<input name="bank_number" type="text" class="form-control" value="<?= htmlspecialchars($user['user_bank_number']); ?>"/>
</div>
</div>
</div>



<div class="col-sm-12">
<div class="my-3">
<label>État du compte</label>
<select name="state" class="form-select">
<option value="0" <?= $user['user_state'] == 0 ? 'selected' : '' ?>>Inactif</option>
<option value="1" <?= $user['user_state'] == 1 ? 'selected' : '' ?>>Actif</option>
</select>
</div>
</div>






<!-- صورة الملف الشخصي -->
<div class="col-sm-12">
<div class="my-3">
<label>Photo de profil</label>
<input type="file" name="image" class="form-control" accept="image/*">
<?php if (!empty($user['user_image'])): ?>
<img src="uploads/users/<?= $user['user_image']; ?>" class="img-thumbnail mt-2" style="width: 100px;">
<?php endif; ?>
</div>
</div>






<!-- زر الحفظ -->
<div class="col-sm-12 text-center">
<div id ='<?= $result; ?>'></div>
<button class="btn my-3 btn-primary">Valider</button>
</div>

</div>
</div>
</div>




<?php
formAwdEnd();
?>





<?php		
		
if (($loginRank == "user") && ($user['user_rank'] == "aide")){

print "<div class='col-lg-12'>";
print "<div class='card my-3'>";

print "<div class='card-header'>";
print "<h5 class='card-title'><b>Autorisations</b></h5>";
print "</div>";

print "<div class='card-body'>";

$id = "formId_editPermission";
$result = "data_result_editPermission"; 
$action = "editPermission"; 
$method = "post"; 
formAwdStart($id, $result, $action, $method); 

print "<input type='hidden' name='uid' value='".md5($uId)."'/>";

									
print "<div class='col-sm-12'>";

$stmt = $con->prepare ("SELECT * FROM permission WHERE per_rank = 'user' ORDER BY per_id ASC");
$stmt->execute();
$perCount = $stmt->rowCount();
$d_p = $stmt->fetchAll();
		
if ($perCount>0){
foreach ($d_p as $row){
$data = $row['per_id'];
		

$statement = $con->prepare ("SELECT * FROM permission_checker WHERE pc_user='$uId' AND pc_via = '$data'");
$statement ->execute();
$per_user = $statement ->rowCount();
if($per_user == 1){		
		
		

print "
<div class='my-3'>
<div class='form-check form-switch'>
<input class='form-check-input' type='checkbox' name='via[]' value='".$row['per_id']."' id='flexSwitchCheckChecked".$row['per_id']."' checked='checked'>
<label class='form-check-label' for='flexSwitchCheckChecked".$row['per_id']."'>".$row['per_name']." - ".$row['per_id']."</label>
</div>
</div>
";	

}else{

print "
<div class='my-3'>
<div class='form-check form-switch'>
<input class='form-check-input' type='checkbox' name='via[]' value='".$row['per_id']."' id='flexSwitchCheckChecked".$row['per_id']."'>
<label class='form-check-label' for='flexSwitchCheckChecked".$row['per_id']."'>".$row['per_name']." - ".$row['per_id']."</label>
</div>
</div>
";


}
		

}
}		

print "</div>";


										


print "<div class='col-sm-12 my-3 text-center'>";
print "<div id='$result'></div>";		
print "</div>";


print "<div class='col-sm-12 my-3 text-center'>";
print "<button type='submit' class='btn btn-primary px-5 radius-30' style='width: initial'>Valider</button>";
print "</div>";




		
			


formAwdEnd();





print "</div>";
print "</div>";
print "</div>";



}


?>











<?php



}

}


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