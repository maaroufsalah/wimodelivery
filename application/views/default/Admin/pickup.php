<?php 

$bootstrap = new Bootstrap();
$bootstrap->runBootstrap();

include get_file("files/sql/get/os_settings");
include get_file("files/sql/get/session");
include get_file("files/sql/get/functions");

if ((hasUserPermission($con, $loginId, 14 ,'admin')) || ($loginRank == "user") || hasUserPermissionAide($con, $loginId, 48 ,'user')){

include get_file("Admin/admin_header");



define ("page_title","Bon de Ramassage");


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
      url: 'getLogs?do=pickup',
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
        $('.loader').html('<div class="progress" role="progressbar"><div class="progress-bar progress-bar-striped progress-bar-animated" style="width: 100%"></div></div>');
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
    var user = $('.user').val(); // ⬅️ جلب قيمة المستخدم
    load_data(1, search, display ,user);
  });

  // عند تغيير قيمة عرض العناصر
  $('.display').change(function() {
    var display = $(this).val();
    var search = $('.searchbox').val();
    var user = $('.user').val(); // ⬅️ جلب قيمة المستخدم
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
    event.preventDefault();
    let page = $(this).attr('data-page');
    let search = $('.searchbox').val();
    let display = $('.display').val();
    let user = $('.user').val(); // ⬅️ مهم
    load_data(page, search, display ,user);
  });
});
</script>


<?php
}elseif($do == "new"){

if (($loginRank == "admin") || ($loginRank == "user") || hasUserPermissionAide($con, $loginId, 48 ,'user')){


if ($loginRank == "admin"){
$userId = isset ($_GET['user']) ? $_GET ['user'] : 0;
}elseif ($loginRank == "aide"){
$userId = $loginUser['user_aide'];
}else{
$userId = $loginId;   
}

$id = "formId";  // معرّف النموذج
$result = "data_result"; 
$action = "newLog?do=pickup"; 
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
or_unlink = '0' AND  or_invoice = '0' AND or_state_delivery = '0' AND or_trade = '$userId'
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
<h5>Colis : <b> De Ramassage</b></h5> 
</div>

<div class="col-sm-12">

<?php



foreach ($orders as $row) {

// جلب بيانات المستخدم والمدينة والحالة
$tradeStmt = $con->prepare("SELECT * FROM users WHERE user_id = ? AND user_rank = 'user' LIMIT 1");
$tradeStmt->execute([$row['or_trade']]);
$trade = $tradeStmt->fetch() ?: [];

$cityStmt = $con->prepare("SELECT * FROM city WHERE city_id = ? LIMIT 1");
$cityStmt->execute([$row['or_city']]);
$city = $cityStmt->fetch() ?: [];

$stateStmt = $con->prepare("SELECT * FROM state WHERE state_id = ? LIMIT 1");
$stateStmt->execute([$row['or_state_delivery']]);
$state = $stateStmt->fetch() ?: [];

// المنتجات داخل الطلب
$stmt = $con->prepare("SELECT * FROM order_items WHERE order_id = ?");
$stmt->execute([$row['or_id']]);
$items = $stmt->fetchAll();


if (!empty($row['or_delivery_user'])) {
$dmuStmt = $con->prepare("SELECT * FROM users WHERE user_id = '{$row['or_delivery_user']}' AND user_rank = 'delivery' LIMIT 1");
$dmuStmt->execute();
$deliveryUser = $dmuStmt->fetch();
} else {
$deliveryUser = null; // في حال كانت القيمة فارغة
}
?>
<!-- كارت الطلب المختصر -->
<div class='card mt-2' style='border-left: 2px solid <?= $state['state_background'] ?? "#ccc" ?>;'>
<div class='card-body my-1'>
<div class='row align-items-center text-center my-2'>
<div class='col-sm-2'>

<?php if (($row['or_invoice'] == "0") || ($row['or_delivery_invoice'] == "0")): ?>
<label for = 'cb_<?= $row['or_id']; ?>'><?= $row['or_id']; ?></label>
<input type="checkbox" class="bulk-check order-checkbox" id= 'cb_<?= $row['or_id']; ?>' value="<?= $row['or_id']; ?>">
<?php else: ?>
<h6>ID : <b><?= $row['or_id']; ?></b></h6>
<?php endif; ?>


<?php if (!empty($trade['user_name'])): ?>
<h6>Vendeur : <b><?= $trade['user_name']; ?></b></h6>
<?php endif; ?>


<?php if (!empty($deliveryUser) && isset($deliveryUser['user_name'])): ?>
<h6>Livreur : <b><?= $deliveryUser['user_name']; ?></b></h6>
<?php endif; ?>


</div>
<div class='col-sm-3'>
<h6>Destinataire : <b><?= $row['or_name']; ?><br>(<?= $row['or_phone']; ?>)</b></h6>
</div>
<div class='col-sm-2'>
<h6>Prix Colis : <b><?= $row['or_total']; ?> Dhs</b></h6>
</div>
<div class='col-sm-2'>
<h6>Ville : <b><?= $city['city_name'] ?? '—'; ?></b></h6>
</div>



<div class='col-sm-2'>
<?php if ($row['or_state_delivery'] == 0): ?>
<a class='btn btn-sm' style='background:red;color:black'><b>En Attente</b></a>
<?php else: ?>
<a  class='btn btn-sm' style='background:<?= $state['state_background']; ?>;color:<?= $state['state_color']; ?>'><b><?= $state['state_name']; ?></b></a>
<?php endif; ?>
<?php
if (!empty($row['or_postponed'])){
print "<div class='text-info'>Reporter : <b>{$row['or_postponed']}</b></div>";
}
?>
</div>




<div class='col-sm-1'>
<a target='_blank' href='packages?do=edit&order_id=<?php print md5($row['or_id']) ;?>' class='text-dark'>
<i class="fa-solid fa-arrow-up-right-from-square"></i>
</a>
</div>

<div class='col-sm-12'>
<?php foreach ($items as $item): 
$stmt = $con->prepare("SELECT * FROM order_item_options WHERE item_id = ?");
$stmt->execute([$item['item_id']]);
$options = $stmt->fetchAll(); ?>
<div>
(x<?= $item['quantity'] ?>) <?= html_entity_decode($item['product_name']) ?>
</div>
<?php endforeach; ?>
</div>



</div>
</div>
</div>










<?php 
}
?>
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