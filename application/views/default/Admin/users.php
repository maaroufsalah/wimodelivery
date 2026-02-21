<?php 

$bootstrap = new Bootstrap();
$bootstrap->runBootstrap();

include get_file("files/sql/get/os_settings");
include get_file("files/sql/get/session");
include get_file("files/sql/get/functions");
include get_file("Admin/admin_header");

if (isset($_GET['rank'])) {
if ($_GET['rank'] == 'user') {
$page_name = "Vendeurs - Clients";
} elseif ($_GET['rank'] == 'Livreurs') {
$page_name = "Vendeur - Client";
} elseif ($_GET['rank'] == 'admin') {
$page_name = "administrateurs";
} elseif ($_GET['rank'] == 'agency') {
$page_name = "Agences";
} else {
$page_name = "";
}
} else {
$page_name = "";
}




define ("page_title", $page_name);


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
if (hasUserPermission($con, $loginId, 28 ,'admin') || hasUserPermission($con, $loginId, 61 ,'admin')){

$stateUser = $_GET['state'] ?? -1;
// get city
$stmt = $con->prepare("SELECT * FROM city WHERE city_unlink = '0' ORDER BY city_name ASC");
$stmt->execute();
$city = $stmt->fetchAll();

?>

<?php if (hasUserPermission($con, $loginId, 29 ,'admin')):?>

<!-- HTML لتصميم الواجهة -->
<div style="text-align: right;">
<a href='?do=new&rank=<?=$_GET['rank'];?>' class="btn btn-primary my-3 btn-sm">Ajouter Compte</a>
</div>

<?php endif; ?>




<!-- البحث والفلاتر -->
<div class="row mb-3">


<div class="col-md-6">
<h6>Recherche</h6>
<input type="text" class="searchbox form-control" placeholder="Rechercher...">
</div>

<div class='col-md-6' style="">
<h6>Afficher</h6>
<select class="display form-select">
<option value="50">50</option>
<option value="100">100</option>
<option value="200">200</option>
</select>
</div>



<div class="col-md-6 my-2">
<h6>Ville</h6>
<?php if(count($city)>0):?>
<select class="form-select filter-city">
<option value="0" disabled selected>Choisir Ville</option>
<?php foreach ($city as $row):?>
<option value="<?=$row['city_id'];?>"><?=$row['city_name'];?></option>
<?php endforeach ;?>
</select>
<?php endif ;?>
</div>





<div class="col-md-6 my-2">
<h6>État du compte</h6>
<select class="form-select filter-activation">
<option value="-1">Activation</option>
<option value="1" <?php if ($stateUser == 1){echo "selected";}?>>Activé</option>
<option value="2">Suspendu</option>
<option value="0" <?php if ($stateUser == 0){echo "selected";}?>>Non Activé - En cours</option>
</select>
</div>







</div>


<div id='dynamic_content'></div>

<script>


$(document).ready(function() {
let rank = "<?php echo isset($_GET['rank']) ? $_GET['rank'] : ''; ?>";

// تعيين قيمة فلتر Type حسب rank
if (['admin', 'user', 'agency', 'delivery'].includes(rank)) {
$('.filter-admin').val(rank);
// إخفاء فلتر "Type" إذا كان موجود مسبقًا في الرابط
$('.filter-admin').closest('.col-md-2').hide();
} else {
$('.filter-admin').val('');
}

// تحميل البيانات عند فتح الصفحة
load_data(1);

function load_data(page = 1) {
let search = $('.searchbox').val();
let display = $('.display').val();
let city = $('.filter-city').val();
let activation = $('.filter-activation').val();
let admin = $('.filter-admin').val();

$.ajax({
url: 'getUser',
method: 'POST',
data: { 
page: page, 
search: search, 
display: display, 
city: city,
activation: activation,
admin: admin,
rank: rank 
},
dataType: 'html',
cache: false,
beforeSend: function () {
$('.loader').html(`
<div class="progress">
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

// البحث
$('.searchbox').on('keyup', function() {
load_data(1);
});

// تغيير العدد المعروض
$('.display').on('change', function() {
load_data(1);
});

// تغيير الفلاتر
$('.filter-city, .filter-activation, .filter-admin').on('change', function() {
load_data(1);
});

// التنقل بين الصفحات
$(document).on('click', '.page-link', function(e) {
e.preventDefault();
let page = $(this).data('page');
load_data(page);
});
});


</script>



<?php 
}
}elseif($do == "new"){
if(hasUserPermission($con, $loginId, 29 ,'admin')){

$rank = $_GET['rank'] ?? '';

$stmt = $con->prepare("SELECT * FROM city WHERE city_unlink = '0' ORDER BY city_name ASC");
$stmt->execute();
$cities = $stmt->fetchAll(PDO::FETCH_ASSOC);



// استلام بيانات الـ rank من الـ URL أو متغير آخر
$user_rank = $_GET['rank'] ?? '';  // هنا نفترض أنك تستقبل rank عبر الرابط

// استعلام لجلب المدن
$stmt = $con->prepare("SELECT * FROM city WHERE city_unlink = '0' ORDER BY city_name ASC");
$stmt->execute();
$cities = $stmt->fetchAll(PDO::FETCH_ASSOC);

// معرّف النموذج وبياناته
$id = "formId";
$result = "data_result";
$action = "newUser";
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
<?= $city['city_name']; ?>
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

<!-- رقم CIN -->
<div class="col-sm-6">
<div class="my-3">
<label>Numéro CIN</label>
<input name="cin" type="text" class="form-control" value=""/>
</div>
</div>

<!-- رقم الحساب البنكي -->
<div class="col-sm-6">
<div class="my-3">
<label>Numéro du compte bancaire</label>
<input name="bank_number" type="text" class="form-control" value=""/>
</div>
</div>

<!-- حالة الحساب -->
<div class="col-sm-12">
<div class="my-3">
<label>État du compte</label>
<select name="state" class="form-select">
<option value="0">Inactif</option>
<option value="1">Actif</option>
<option value="2">Suspendu</option>
</select>
</div>
</div>

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

if (hasUserPermission($con, $loginId, 31 ,'admin')){
$data_id = $_GET['id'] ?? '';
}else{
$data_id = md5($loginId);
}

if (!empty($data_id)) {
$stmt = $con->prepare("SELECT * FROM users WHERE md5(user_id) = :id");
$stmt->bindParam(':id', $data_id, PDO::PARAM_STR);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$uId = $user['user_id'];

$stmt = $con->prepare("SELECT * FROM city WHERE city_unlink = '0' ORDER BY city_name ASC");
$stmt->execute();
$cities = $stmt->fetchAll(PDO::FETCH_ASSOC);


?>
<div class="row">





<div class="col-sm-8">
<?php
$id = "formId";  // معرّف النموذج
$result = "data_result"; 
$action = "editUser"; 
$method = "post"; 
formAwdStart($id, $result, $action, $method); 

if(hasUserPermission($con, $loginId, 31 ,'admin')){
$i_disabled = "";
}else{
$i_disabled = "disabled";
}

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
<select name="city" class="form-select" <?=$i_disabled;?>>
<option disabled selected>Choisir Ville</option>
<?php foreach($cities as $city): ?>
<option value="<?= $city['city_id']; ?>" <?= $city['city_id'] == $user['user_city'] ? 'selected' : '' ?>>
<?= $city['city_name']; ?>
</option>
<?php endforeach; ?>
</select>
</div>
</div>
<?php if(hasUserPermission($con, $loginId, 31 ,'admin')): ?>
<?php else: ?>
<input type='hidden' name='city' value='<?= htmlspecialchars($user['user_city']); ?>'>
<?php endif; ?>



<!-- العنوان -->
<div class="col-sm-12">
<div class="my-3">
<label>Adresse</label>
<input name="location" type="text" class="form-control" value="<?= htmlspecialchars($user['user_location']); ?>"/>
</div>
</div>


<div class="col-sm-6">
<div class="my-3">
<label>Numéro CIN</label>
<input name="cin" type="text" class="form-control" value="<?= htmlspecialchars($user['user_cin']); ?>" <?=$i_disabled;?>/>
</div>
</div>
<?php if(hasUserPermission($con, $loginId, 31 ,'admin')): ?>
<?php else: ?>
<input type='hidden' name='cin' value='<?= htmlspecialchars($user['user_cin']); ?>'>
<?php endif; ?>

<div class="col-sm-6">
<div class="my-3">
<label>Numéro du compte bancaire</label>
<input name="bank_number" type="text" class="form-control" value="<?= htmlspecialchars($user['user_bank_number']); ?>" <?=$i_disabled;?>/>
</div>
</div>
<?php if(hasUserPermission($con, $loginId, 31 ,'admin')): ?>

<?php else: ?>
<input type='hidden' name='bank_number' value='<?= htmlspecialchars($user['user_bank_number']); ?>'>
<?php endif; ?>



<?php if(hasUserPermission($con, $loginId, 31 ,'admin')): ?>
<div class="col-sm-12">
  <div class="my-3">
    <label>État du compte</label>
    <select name="state" class="form-select">
      <option value="0" <?= $user['user_state'] == 0 ? 'selected' : '' ?>>Inactif - En cours</option>
      <option value="1" <?= $user['user_state'] == 1 ? 'selected' : '' ?>>Actif</option>
      <option value="2" <?= $user['user_state'] == 2 ? 'selected' : '' ?>>Suspendu</option>

    </select>
  </div>
</div>
<?php else: ?>
<input type='hidden' name='state' value='1'>
<?php endif; ?>





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


<?php if($loginRank == "admin"): ?>

<div class="col-sm-12">
<div class="form-check form-switch form-check-inline">
<input value="1" class="form-check-input" type="checkbox" name="identity" id="identity" <?php echo ($user['user_identity'] == 1) ? 'checked' : ''; ?>>
<label class="form-check-label" for="identity">Validation de l'identité de l'utilisateu</label>
</div>
</div>

<?php else: ?>
<input type='hidden' name='identity' value=' <?php echo $user['user_identity']; ?>'>
<?php endif; ?>



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

</div>










<div class="col-sm-4">




<?php if(hasUserPermission($con, $loginId, 31 ,'admin')): ?>

<div class="card">
<div class="card-header">
<h5><b>Documents</b></h5>
</div>
<div class="card-body">
<div class="row">
<?php
$id = "formId_doc";  // معرّف النموذج
$result = "data_result_doc"; 
$action = "saveUserDocuments"; 
$method = "post"; 
formAwdStart($id, $result, $action, $method); 
?>

<input type='hidden' name='id' value='<?= htmlspecialchars($data_id); ?>'/>


<div class="col-sm-12">
<?php if (!empty($user['user_ice_one'])): ?>
<a href="uploads/docs/<?= $user['user_ice_one']; ?>" target="_blank" class="d-block mt-2">Voir Carte CIN (avant)</a>
<?php endif; ?>

<div class="form-group">
<label for="ice_one">Carte CIN (face avant)</label>
<input type="file" name="ice_one" id="ice_one" class="form-control">
</div>
</div>

<div class="col-sm-12">
<?php if (!empty($user['user_ice_tow'])): ?>
<a href="uploads/docs/<?= $user['user_ice_tow']; ?>" target="_blank" class="d-block mt-2">Voir Carte CIN (arrière)</a>
<?php endif; ?>
<div class="form-group">
<label for="ice_tow">Carte CIN (face arrière)</label>
<input type="file" name="ice_tow" id="ice_tow" class="form-control">
</div>
</div>

<div class="col-sm-12">
<?php if (!empty($user['user_ice_bank'])): ?>
<a href="uploads/docs/<?= $user['user_ice_bank']; ?>" target="_blank" class="d-block mt-2">Voir RIB</a>
<?php endif; ?>
<div class="form-group">
<label for="ice_bank">Relevé bancaire (RIB)</label>
<input type="file" name="ice_bank" id="ice_bank" class="form-control">
</div>
</div>





<!-- زر الحفظ -->
<div class="col-sm-12 text-center">
<div id ='<?= $result; ?>'></div>
<button class="btn my-3 btn-primary">Valider</button>
</div>
<?php
formAwdEnd();
?>

</div>
</div>
</div>



<?php else: ?>
<div class="card">
  <div class="card-header">
    <h5><b>Documents</b></h5>
  </div>
  <div class="card-body">
    <div class="row">
      <?php
      $id = "formId_doc";  // معرّف النموذج
      $result = "data_result_doc"; 
      $action = "saveUserDocuments"; 
      $method = "post"; 
      formAwdStart($id, $result, $action, $method); 
      ?>

      <input type='hidden' name='id' value='<?= htmlspecialchars($data_id); ?>'/>

      <div class="col-sm-12">
        <?php if (!empty($user['user_ice_one'])): ?>
          <a href="uploads/docs/<?= htmlspecialchars($user['user_ice_one']); ?>" target="_blank" class="d-block mt-2">Voir Carte CIN (avant)</a>
        <?php else: ?>
          <div class="form-group">
            <label for="ice_one">Carte CIN (face avant)</label>
            <input type="file" name="ice_one" id="ice_one" class="form-control">
          </div>
        <?php endif; ?>
      </div>

      <div class="col-sm-12">
        <?php if (!empty($user['user_ice_tow'])): ?>
          <a href="uploads/docs/<?= htmlspecialchars($user['user_ice_tow']); ?>" target="_blank" class="d-block mt-2">Voir Carte CIN (arrière)</a>
        <?php else: ?>
          <div class="form-group">
            <label for="ice_tow">Carte CIN (face arrière)</label>
            <input type="file" name="ice_tow" id="ice_tow" class="form-control">
          </div>
        <?php endif; ?>
      </div>

      <div class="col-sm-12">
        <?php if (!empty($user['user_ice_bank'])): ?>
          <a href="uploads/docs/<?= htmlspecialchars($user['user_ice_bank']); ?>" target="_blank" class="d-block mt-2">Voir RIB</a>
        <?php else: ?>
          <div class="form-group">
            <label for="ice_bank">Relevé bancaire (RIB)</label>
            <input type="file" name="ice_bank" id="ice_bank" class="form-control">
          </div>
        <?php endif; ?>
      </div>

   <?php if (empty($user['user_ice_one']) || empty($user['user_ice_tow']) || empty($user['user_ice_bank'])): ?>

      <div class="col-sm-12 text-center">
        <div id ='<?= $result; ?>'></div>
        <button class="btn my-3 btn-primary" type="submit">Valider</button>
      </div>
      <?php endif; ?>

      <?php formAwdEnd(); ?>
    </div>
  </div>
</div>

<?php endif; ?>














<div class="card mt-4">
<div class="card-header">
<h5><b>Modifier le mot de passe</b></h5>
</div>
<div class="card-body">
<div class="row">
<?php
$id = "formId_pass";  // معرف النموذج
$result = "data_result_pass"; 
$action = "editPassword"; 
$method = "post"; 
formAwdStart($id, $result, $action, $method); 
?>
<input type='hidden' name='id' value='<?= htmlspecialchars($data_id); ?>'/>

<div class="col-sm-12">
<div class="form-group">
<label for="new_password">Nouveau mot de passe</label>
<input type="password" name="new_password" class="form-control" required>
</div>
</div>

<div class="col-sm-12">
<div class="form-group">
<label for="confirm_password">Confirmer le mot de passe</label>
<input type="password" name="confirm_password" class="form-control" required>
</div>
</div>

<div class="col-sm-12 text-center">
<div id='<?= $result; ?>'></div>
<button class="btn my-3 btn-warning">Changer le mot de passe</button>
</div>

<?php formAwdEnd(); ?>
</div>
</div>
</div>







</div>




<?php		
		
if (($loginRank == "admin") && ($user['user_rank'] == "admin") && ($loginId == 1)){

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

$stmt = $con->prepare ("SELECT * FROM permission WHERE per_rank = 'admin' ORDER BY per_id ASC");
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








































</div>


<?php
} else {
echo "<div class='alert alert-danger'>Aucun identifiant spécifié.</div>";
}
?>




<?php
}else{






}
?>






</div>
</div>

























</main>

<?php include get_file("Admin/admin_footer");?>