<?php 

$bootstrap = new Bootstrap();
$bootstrap->runBootstrap();

include get_file("files/sql/get/os_settings");
include get_file("files/sql/get/session");
include get_file("files/sql/get/functions");
if ($loginRank == "admin"){
include get_file("Admin/admin_header");

define ("page_title","Paramètres");





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

<nav class="navbar navbar-expand-lg bg-body-tertiary">

<div class="container-fluid">

<a class="navbar-brand">Configuration</a>
<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
<span class="navbar-toggler-icon"></span>
</button>

<div class="collapse navbar-collapse" id="navbarNav">

<ul class="navbar-nav">

<li class="nav-item">
<a class="nav-link active" aria-current="page" href="app_settings">Paramètre</a>
</li>



<li class="nav-item">
<a class="nav-link" href="?do=city">Villes</a>
</li>


<li class="nav-item">
<a class="nav-link" href="?do=state">États</a>
</li>


<li class="nav-item">
<a class="nav-link" href="?do=boxing">Emballages</a>
</li>



<li class="nav-item">
<a class="nav-link" href="?do=type">Type de vente</a>
</li>


<li class="nav-item">
<a class="nav-link" href="?do=category">Catégories</a>
</li>



<li class="nav-item">
<a class="nav-link" href="?do=sub_category">Sous-Catégories</a>
</li>


<li class="nav-item">
<a class="nav-link" href="?do=brand">Marques</a>
</li>


<li class="nav-item">
<a class="nav-link" href="?do=news">Actualités</a>
</li>




</ul>

</div>

</div>

</nav>



<?php

$do = isset ($_GET['do']) ? $_GET ['do'] : 'Manage' ;
if ($do == 'Manage'){
if (hasUserPermission($con, $loginId, 37 ,'admin')){
$id = "formId";
$result = "data_result";
$action = "editSettings";
$method = "post";
formAwdStart ($id,$result,$action,$method); 

?>


<div class="card" style="border-radius:0rem">
<div class="card-body">
<div class="row">



<div class="col-sm-4">
<div class="my-3">
<div class="input">Titre de site</div>
<input name="name" type="text" class="form-control" value="<?=$set_name;?>" placeholder=""/>
</div>
</div>


<div class="col-sm-4">
<div class="my-3">
<div class="input">Description</div>
<input name="note" type="text" class="form-control" value="<?=$set_note;?>" placeholder=""/>
</div>
</div>


<div class="col-sm-4">
<div class="my-3">
<div class="input">Téléphone</div>
<input name="phone" type="text" class="form-control" value="<?=$set_phone;?>" placeholder=""/>
</div>
</div>


<div class="col-sm-4">
<div class="my-3">
<div class="input">Whatsapp</div>
<input name="whatsapp" type="text" class="form-control" value="<?=$set_whatsapp;?>" placeholder=""/>
</div>
</div>



<div class="col-sm-4">
<div class="my-3">
<div class="input">Email</div>
<input name="email" type="text" class="form-control" value="<?=$set_email;?>" placeholder=""/>
</div>
</div>



<div class="col-sm-4">
<div class="my-3">
<div class="input">Ice</div>
<input name="id_number" type="text" class="form-control" value="<?=$set_id_number;?>" placeholder=""/>
</div>
</div>



<div class="col-sm-12">
<div class="my-3">
<div class="input">Position</div>
<input name="location" type="text" class="form-control" value="<?=$set_location;?>" placeholder=""/>
</div>
</div>

<div class="col-sm-12">
<div class="input">Papier à en-tête</div>
<textarea class='editor' name="bottom"><?=$set_bottom_paper;?></textarea>
</div>



<div class="col-sm-6 my-3">
<label for="formFile" class="form-label">Logo</label>
<input class="form-control my-3" name="logo" type="file" id="logo">
<?php 
if (!empty($set_logo)){
    print "<img src='uploads/$set_logo' class='' style='max-width:150px'/>";
}
?>
</div>


<div class="col-sm-6 my-3">
<label for="formFile" class="form-label">Favicon</label>
<input class="form-control my-3" name="favicon" type="file" id="favicon">
<?php 
if (!empty($set_favicon)){
    print "<img src='uploads/$set_favicon' class='' style='max-width:150px'/>";
}
?>
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




<?php
formAwdEnd ();
}
}elseif($do == "city"){
if (hasUserPermission($con, $loginId, 38 ,'admin')){
?>


<div style="text-align: right;">
<a data-bs-toggle='modal' data-bs-target='#addPop' class="btn btn-primary my-3 btn-sm">Ajouter</a>
</div>


<div class="card" style="border-radius:0rem">
<div class="card-body">


<div class="row">


<div class='col-6' style="text-align: left;">
<h6>Recherche</h6>
<input type="text" class="searchbox form-control mb-3" style="width:100%"/>
</div>


<div class='col-6' style="">
<h6>Afficher</h6>
<select class="display form-select">
<option value="10">10</option>
<option value="50">50</option>
<option value="100">100</option>
<option value="200">200</option>
</select>
</div>


</div>

<hr>

<?php




echo "<div class='modal fade' id='addPop' tabindex='-1' aria-hidden='true'>";
echo "<div class='modal-dialog modal-dialog-centered'>";
echo "<div class='modal-content'>";
echo "<div class='modal-header'>";
echo "<h5 class='modal-title'>Ajouter Ville</h5>";
echo "<button type='button' class='btn-close updatedata' data-bs-dismiss='modal' aria-label='Close'></button>";
echo "</div>";


$id = "formId";
$result = "data_result";
$action = "newCity";
$method = "post";
formAwdStart ($id,$result,$action,$method); 



echo "<div class='modal-body'>";

print '
<div class="col-sm-12">
<div class="my-3">
<div class="input">Le nom de ville</div>
<input name="city" type="text" class="form-control" value="" placeholder=""/>
</div>
</div>
';

echo "<div class='col-sm-12 text-center my-2'>";
echo "<div id='".$result."'></div>";
echo "</div>";


echo "<div class='col-sm-12 text-center my-2'>";
echo "<button class='btn btn-primary' type='submit'>Ajouter</button>";
echo "</div>";


echo "</div>";



formAwdEnd ();


echo "</div>";
echo "</div>";
echo "</div>";


?>



<div class="loader"></div>
<div id="dynamic_content"></div>

<script>
$(document).ready(function () {
  // تحميل البيانات لأول مرة
  loadData(1);

  // الدالة الموحدة لتحميل البيانات
  function loadData(page = 1) {
    const search = $('.searchbox').val() || '';
    const display = $('.display').val() || '';

    $.ajax({
      url: 'getCity',
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

  // تغيير الصفحة مع الحفاظ على الفلاتر
  $(document).on('click', '.page-link', function(event) {
    event.preventDefault();
    const page = $(this).data('page');
    if (page) {
      loadData(page);
    }
  });

  // التحديث عند تغير الفلاتر (بحث، عرض...)
  $('.searchbox, .display').on('change keyup', function () {
    loadData(1);
  });

  // زر التحديث اليدوي إن وجد
  $(document).on('click', '.updatedata', function () {
    const page = $(this).val() || 1;
    loadData(page);
  });
});
</script>





</div>
</div>




<?php
}
}elseif($do == "state"){
if(hasUserPermission($con, $loginId, 39 ,'admin')){
?>
















<div style="text-align: right;">
<a data-bs-toggle='modal' data-bs-target='#addPop' class="btn btn-primary my-3 btn-sm">Ajouter</a>
</div>


<div class="card" style="border-radius:0rem">
<div class="card-body">


<div class="row">


<div class='col-6' style="text-align: left;">
<h6>Recherche</h6>
<input type="text" class="searchbox form-control mb-3" style="width:100%"/>
</div>


<div class='col-6' style="">
<h6>Afficher</h6>
<select class="display form-select">
<option value="10">10</option>
<option value="50">50</option>
<option value="100">100</option>
<option value="200">200</option>
</select>
</div>


</div>

<hr>

<?php




echo "<div class='modal fade' id='addPop' tabindex='-1' aria-hidden='true'>";
echo "<div class='modal-dialog modal-dialog-centered'>";
echo "<div class='modal-content'>";
echo "<div class='modal-header'>";
echo "<h5 class='modal-title'>Ajouter état</h5>";
echo "<button type='button' class='btn-close updatedata' data-bs-dismiss='modal' aria-label='Close'></button>";
echo "</div>";


$id = "formId";
$result = "data_result";
$action = "newState";
$method = "post";
formAwdStart ($id,$result,$action,$method); 



echo "<div class='modal-body'>";

print '
<div class="col-sm-12">
<div class="my-3">
<div class="input">état</div>
<input name="state" type="text" class="form-control" value="" placeholder=""/>
</div>
</div>
';

print '
<div class="col-sm-12">
<div class="my-3">
<div class="input">Couleur</div>
<input name="color" type="color" class="form-control" value="" placeholder=""/>
</div>
</div>
';

print '
<div class="col-sm-12">
<div class="my-3">
<div class="input">Background</div>
<input name="background" type="color" class="form-control" value="" placeholder=""/>
</div>
</div>
';


print '
<div class="col-sm-12">
<div class="my-3">
<label>Rang</label>
<select name="rank" class="form-select">
<option value="0" disabled>Choisir rang</option>
<option value="admin">Admin</option>
<option value="delivery">Livreur</option>
</select>
</div>
</div>
';


echo "<div class='col-sm-12 text-center my-2'>";
echo "<div id='".$result."'></div>";
echo "</div>";


echo "<div class='col-sm-12 text-center my-2'>";
echo "<button class='btn btn-primary' type='submit'>Ajouter</button>";
echo "</div>";


echo "</div>";



formAwdEnd ();


echo "</div>";
echo "</div>";
echo "</div>";


?>



<div class="loader"></div>
<div id="dynamic_content"></div>


<script>
$(document).ready(function () {
  // تحميل البيانات لأول مرة
  loadData(1);

  // دالة التحميل
  function loadData(page = 1) {
    const search = $('.searchbox').val();
    const display = $('.display').val();

    $.ajax({
      url: 'getState',  // تأكد من اسم الملف الصحيح
      method: 'POST',      // إذا تفضل GET غيّرها لـ 'GET' وعدل الـ PHP
      data: {
        page: page,
        search: search,
        display: display      },
      beforeSend: function () {
        $('.loader').html('<span class="spinner-border spinner-border-sm"></span> Chargement...');
      },
      success: function (data) {
        $('#dynamic_content').html(data);
        $('.loader').html('');
      }
    });
  }

  // إعادة تحميل الصفحة عند تغير الفلاتر مع إعادة تعيين الصفحة للصفحة 1
  const triggerLoad = () => loadData(1);

  // التصفح عبر روابط الصفحات
  $(document).on('click', '.page-link', function (e) {
    e.preventDefault();
    const page = $(this).data('page');  // تأكد من أن في renderPagination تستخدم data-page
    if (page) loadData(page);
  });

  // البحث الحي وتغير الفلاتر
  $('.searchbox').on('input', triggerLoad);
  $('.display').on('change', triggerLoad);
});

</script>




</div>
</div>







































<?php
}
}elseif($do == "boxing"){
if (hasUserPermission($con, $loginId, 40 ,'admin')){
?>










<div style="text-align: right;">
<a data-bs-toggle='modal' data-bs-target='#addPop' class="btn btn-primary my-3 btn-sm">Ajouter</a>
</div>


<div class="card" style="border-radius:0rem">
<div class="card-body">


<div class="row">


<div class='col-6' style="text-align: left;">
<h6>Recherche</h6>
<input type="text" class="searchbox form-control mb-3" style="width:100%"/>
</div>


<div class='col-6' style="">
<h6>Afficher</h6>
<select class="display form-select">
<option value="10">10</option>
<option value="50">50</option>
<option value="100">100</option>
<option value="200">200</option>
</select>
</div>


</div>

<hr>

<?php

echo "<div class='modal fade' id='addPop' tabindex='-1' aria-hidden='true'>";
echo "<div class='modal-dialog modal-dialog-centered'>";
echo "<div class='modal-content'>";
echo "<div class='modal-header'>";
echo "<h5 class='modal-title'>Ajouter Emballage</h5>";
echo "<button type='button' class='btn-close updatedata' data-bs-dismiss='modal' aria-label='Close'></button>";
echo "</div>";

$id = "formId";
$result = "data_result";
$action = "newBoxing"; // لاحظ ملف التنفيذ
$method = "post";

formAwdStart($id, $result, $action, $method); 

echo "<div class='modal-body'>";

print '
<div class="col-sm-12">
  <div class="my-3">
    <div class="input">Emballage</div>
    <input name="boxing" type="text" class="form-control" value="" placeholder=""/>
  </div>
</div>
';

print '
<div class="col-sm-12">
  <div class="my-3">
    <div class="input">Prix</div>
    <input name="price" type="number" class="form-control" value="" placeholder=""/>
  </div>
</div>
';

print '
<div class="col-sm-12">
  <div class="my-3">
    <div class="input">Type</div>
    <input name="type" type="text" class="form-control" value="" placeholder=""/>
  </div>
</div>
';

print '
<div class="col-sm-12">
  <div class="my-3">
    <div class="input">Photo d\'emballage</div>
    <input name="photo" type="file" class="form-control" accept="image/*" />
  </div>
</div>
';

echo "<div class='col-sm-12 text-center my-2'>";
echo "<div id='".$result."'></div>";
echo "</div>";

echo "<div class='col-sm-12 text-center my-2'>";
echo "<button class='btn btn-primary' type='submit'>Ajouter</button>";
echo "</div>";

echo "</div>";

formAwdEnd();

echo "</div>";
echo "</div>";
echo "</div>";

?>



<div class="loader"></div>
<div id="dynamic_content"></div>




<script>
$(document).ready(function () {
  // تحميل البيانات لأول مرة
  loadData(1);

  // دالة التحميل مع باراميتر الصفحة (افتراضياً 1)
  function loadData(page = 1) {
    const search = $('.searchbox').val();
    const display = $('.display').val();

    $.ajax({
      url: 'getBoxing',
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
  $('.searchbox, .display').on('input change keyup', function () {
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





</div>
</div>











<?php
}
}elseif($do == "type"){
if (hasUserPermission($con, $loginId, 41 ,'admin')){
?>










<div style="text-align: right;">
<a data-bs-toggle='modal' data-bs-target='#addPop' class="btn btn-primary my-3 btn-sm">Ajouter</a>
</div>


<div class="card" style="border-radius:0rem">
<div class="card-body">


<div class="row">


<div class='col-6' style="text-align: left;">
<h6>Recherche</h6>
<input type="text" class="searchbox form-control mb-3" style="width:100%"/>
</div>


<div class='col-6' style="">
<h6>Afficher</h6>
<select class="display form-select">
<option value="10">10</option>
<option value="50">50</option>
<option value="100">100</option>
<option value="200">200</option>
</select>
</div>


</div>

<hr>

<?php




echo "<div class='modal fade' id='addPop' tabindex='-1' aria-hidden='true'>";
echo "<div class='modal-dialog modal-dialog-centered'>";
echo "<div class='modal-content'>";
echo "<div class='modal-header'>";
echo "<h5 class='modal-title'>Ajouter Type de vente</h5>";
echo "<button type='button' class='btn-close updatedata' data-bs-dismiss='modal' aria-label='Close'></button>";
echo "</div>";


$id = "formId";
$result = "data_result";
$action = "newType";
$method = "post";
formAwdStart ($id,$result,$action,$method); 



echo "<div class='modal-body'>";

print '
<div class="col-sm-12">
<div class="my-3">
<div class="input">Le nom de type</div>
<input name="type" type="text" class="form-control" value="" placeholder=""/>
</div>
</div>
';


print '
<div class="col-sm-12 my-3">
<label for="formFile" class="form-label">Images</label>
<input class="form-control my-3" name="image" type="file" id="image">
</div>
';


echo "<div class='col-sm-12 text-center my-2'>";
echo "<div id='".$result."'></div>";
echo "</div>";


echo "<div class='col-sm-12 text-center my-2'>";
echo "<button class='btn btn-primary' type='submit'>Ajouter</button>";
echo "</div>";


echo "</div>";



formAwdEnd ();


echo "</div>";
echo "</div>";
echo "</div>";


?>



<div class="loader"></div>
<div id="dynamic_content"></div>


<script>
$(document).ready(function () {
  // تحميل البيانات لأول مرة
  loadData(1);

  // دالة التحميل مع باراميتر الصفحة (افتراضياً 1)
  function loadData(page = 1) {
    const search = $('.searchbox').val();
    const display = $('.display').val();

    $.ajax({
      url: 'getType',
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
  $('.searchbox, .display').on('input change keyup', function () {
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







</div>
</div>











<?php
}
}elseif($do == "category"){
if (hasUserPermission($con, $loginId, 42 ,'admin')){
$stmt = $con->prepare("SELECT * FROM type WHERE type_unlink = '0' ORDER BY type_name");
$stmt->execute();
$typeCount = $stmt->rowCount();
$type = $stmt->fetchAll();

?>










<div style="text-align: right;">
<a data-bs-toggle='modal' data-bs-target='#addPop' class="btn btn-primary my-3 btn-sm">Ajouter</a>
</div>


<div class="card" style="border-radius:0rem">
<div class="card-body">


<div class="row">


<div class='col-6' style="text-align: left;">
<h6>Recherche</h6>
<input type="text" class="searchbox form-control mb-3" style="width:100%"/>
</div>


<div class='col-6' style="">
<h6>Afficher</h6>
<select class="display form-select">
<option value="10">10</option>
<option value="50">50</option>
<option value="100">100</option>
<option value="200">200</option>
</select>
</div>


</div>

<hr>

<?php




echo "<div class='modal fade' id='addPop' tabindex='-1' aria-hidden='true'>";
echo "<div class='modal-dialog modal-dialog-centered'>";
echo "<div class='modal-content'>";
echo "<div class='modal-header'>";
echo "<h5 class='modal-title'>Ajouter catégorie</h5>";
echo "<button type='button' class='btn-close updatedata' data-bs-dismiss='modal' aria-label='Close'></button>";
echo "</div>";


$id = "formId";
$result = "data_result";
$action = "newCategory";
$method = "post";
formAwdStart ($id,$result,$action,$method); 



echo "<div class='modal-body'>";

print '
<div class="col-sm-12">
<div class="my-3">
<div class="input">Le nom de catégorie</div>
<input name="category" type="text" class="form-control" value="" placeholder=""/>
</div>
</div>
';

?>
<div class="col-sm-12">
<div class="my-3">
<div class="input">Type de vente</div>
<select name='type' class=' w-100'>
<option value='0' disabled selected>Choisir type</option>
<?php
foreach ($type as $row){
print "<option value='{$row['type_id']}'>{$row['type_name']}</option>";
}
?>
</select>
</div>
</div>
<?php


print '
<div class="col-sm-12 my-3">
<label for="formFile" class="form-label">Images</label>
<input class="form-control my-3" name="image" type="file" id="image">
</div>
';


echo "<div class='col-sm-12 text-center my-2'>";
echo "<div id='".$result."'></div>";
echo "</div>";


echo "<div class='col-sm-12 text-center my-2'>";
echo "<button class='btn btn-primary' type='submit'>Ajouter</button>";
echo "</div>";


echo "</div>";



formAwdEnd ();


echo "</div>";
echo "</div>";
echo "</div>";


?>



<div class="loader"></div>
<div id="dynamic_content"></div>



<script>
$(document).ready(function () {
  // تحميل البيانات لأول مرة
  loadData(1);

  // دالة التحميل مع باراميتر الصفحة (افتراضياً 1)
  function loadData(page = 1) {
    const search = $('.searchbox').val();
    const display = $('.display').val();

    $.ajax({
      url: 'getCategory',
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
  $('.searchbox, .display').on('input change keyup', function () {
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




</div>
</div>






<?php
}
}elseif($do == "sub_category"){
if (hasUserPermission($con, $loginId, 43 ,'admin')){
$stmt = $con->prepare("SELECT * FROM classes WHERE c_unlink = '0' ORDER BY c_name");
$stmt->execute();
$category_Count = $stmt->rowCount();
$category = $stmt->fetchAll();

?>










<div style="text-align: right;">
<a data-bs-toggle='modal' data-bs-target='#addPop' class="btn btn-primary my-3 btn-sm">Ajouter</a>
</div>


<div class="card" style="border-radius:0rem">
<div class="card-body">


<div class="row">


<div class='col-6' style="text-align: left;">
<h6>Recherche</h6>
<input type="text" class="searchbox form-control mb-3" style="width:100%"/>
</div>


<div class='col-6' style="">
<h6>Afficher</h6>
<select class="display form-select">
<option value="10">10</option>
<option value="50">50</option>
<option value="100">100</option>
<option value="200">200</option>
</select>
</div>


</div>

<hr>

<?php




echo "<div class='modal fade' id='addPop' tabindex='-1' aria-hidden='true'>";
echo "<div class='modal-dialog modal-dialog-centered'>";
echo "<div class='modal-content'>";
echo "<div class='modal-header'>";
echo "<h5 class='modal-title'>Ajouter catégorie</h5>";
echo "<button type='button' class='btn-close updatedata' data-bs-dismiss='modal' aria-label='Close'></button>";
echo "</div>";


$id = "formId";
$result = "data_result";
$action = "newSubCategory";
$method = "post";
formAwdStart ($id,$result,$action,$method); 



echo "<div class='modal-body'>";

print '
<div class="col-sm-12">
<div class="my-3">
<div class="input">Le nom de Sous-catégorie</div>
<input name="sub_category" type="text" class="form-control" value="" placeholder=""/>
</div>
</div>
';

?>
<div class="col-sm-12">
<div class="my-3">
<div class="input">Catégories</div>
<select name='category' class=' w-100'>
<option value='0' disabled selected>Choisir catégorie</option>
<?php
foreach ($category as $row){
print "<option value='{$row['c_id']}'>{$row['c_name']}</option>";
}
?>
</select>
</div>
</div>
<?php




echo "<div class='col-sm-12 text-center my-2'>";
echo "<div id='".$result."'></div>";
echo "</div>";


echo "<div class='col-sm-12 text-center my-2'>";
echo "<button class='btn btn-primary' type='submit'>Ajouter</button>";
echo "</div>";


echo "</div>";



formAwdEnd ();


echo "</div>";
echo "</div>";
echo "</div>";


?>



<div class="loader"></div>
<div id="dynamic_content"></div>




<script>
$(document).ready(function () {
  // تحميل البيانات لأول مرة
  loadData(1);

  // دالة التحميل مع باراميتر الصفحة (افتراضياً 1)
  function loadData(page = 1) {
    const search = $('.searchbox').val();
    const display = $('.display').val();

    $.ajax({
      url: 'getSubCategory',
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
  $('.searchbox, .display').on('input change keyup', function () {
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




</div>
</div>









<?php
}
}elseif($do == "brand"){
if(hasUserPermission($con, $loginId, 44 ,'admin')){
$stmt = $con->prepare("SELECT * FROM brand WHERE brand_unlink = '0' ORDER BY brand_name");
$stmt->execute();
$brand_Count = $stmt->rowCount();
$brand = $stmt->fetchAll();

?>










<div style="text-align: right;">
<a data-bs-toggle='modal' data-bs-target='#addPop' class="btn btn-primary my-3 btn-sm">Ajouter</a>
</div>


<div class="card" style="border-radius:0rem">
<div class="card-body">


<div class="row">


<div class='col-6' style="text-align: left;">
<h6>Recherche</h6>
<input type="text" class="searchbox form-control mb-3" style="width:100%"/>
</div>


<div class='col-6' style="">
<h6>Afficher</h6>
<select class="display form-select">
<option value="10">10</option>
<option value="50">50</option>
<option value="100">100</option>
<option value="200">200</option>
</select>
</div>


</div>

<hr>

<?php




echo "<div class='modal fade' id='addPop' tabindex='-1' aria-hidden='true'>";
echo "<div class='modal-dialog modal-dialog-centered'>";
echo "<div class='modal-content'>";
echo "<div class='modal-header'>";
echo "<h5 class='modal-title'>Ajouter Marque</h5>";
echo "<button type='button' class='btn-close updatedata' data-bs-dismiss='modal' aria-label='Close'></button>";
echo "</div>";


$id = "formId";
$result = "data_result";
$action = "newBrand";
$method = "post";
formAwdStart ($id,$result,$action,$method); 



echo "<div class='modal-body'>";

print '
<div class="col-sm-12">
<div class="my-3">
<div class="input">Le nom de marque</div>
<input name="brand" type="text" class="form-control" value="" placeholder=""/>
</div>
</div>
';


print '
<div class="col-sm-12 my-3">
<label for="formFile" class="form-label">Images</label>
<input class="form-control my-3" name="image" type="file" id="image">
</div>
';

echo "<div class='col-sm-12 text-center my-2'>";
echo "<div id='".$result."'></div>";
echo "</div>";


echo "<div class='col-sm-12 text-center my-2'>";
echo "<button class='btn btn-primary' type='submit'>Ajouter</button>";
echo "</div>";


echo "</div>";



formAwdEnd ();


echo "</div>";
echo "</div>";
echo "</div>";


?>



<div class="loader"></div>
<div id="dynamic_content"></div>




<script>
$(document).ready(function () {
  // تحميل البيانات لأول مرة
  loadData(1);

  // دالة التحميل مع باراميتر الصفحة (افتراضياً 1)
  function loadData(page = 1) {
    const search = $('.searchbox').val();
    const display = $('.display').val();

    $.ajax({
      url: 'getBrand',
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
  $('.searchbox, .display').on('input change keyup', function () {
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





</div>
</div>







<?php
}
}elseif($do == "news"){
if(hasUserPermission($con, $loginId, 59 ,'admin')){


?>










<div style="text-align: right;">
<a data-bs-toggle='modal' data-bs-target='#addPop' class="btn btn-primary my-3 btn-sm">Ajouter</a>
</div>


<div class="card" style="border-radius:0rem">
<div class="card-body">


<div class="row">


<div class='col-6' style="text-align: left;">
<h6>Recherche</h6>
<input type="text" class="searchbox form-control mb-3" style="width:100%"/>
</div>


<div class='col-6' style="">
<h6>Afficher</h6>
<select class="display form-select">
<option value="10">10</option>
<option value="50">50</option>
<option value="100">100</option>
<option value="200">200</option>
</select>
</div>


</div>

<hr>

<?php




echo "<div class='modal fade' id='addPop' tabindex='-1' aria-hidden='true'>";
echo "<div class='modal-dialog modal-fullscreen modal-dialog-centered'>";
echo "<div class='modal-content'>";
echo "<div class='modal-header'>";
echo "<h5 class='modal-title'>Ajouter actualités</h5>";
echo "<button type='button' class='btn-close updatedata' data-bs-dismiss='modal' aria-label='Close'></button>";
echo "</div>";


$id = "formId";
$result = "data_result";
$action = "newNews";
$method = "post";
formAwdStart ($id,$result,$action,$method); 



echo "<div class='modal-body'>";

print '
<div class="col-sm-12">
<div class="my-3">
<div class="input">Couleur</div>
<input name="color" type="color" class="form-control" value="" placeholder=""/>
</div>
</div>
';


print '
<div class="col-sm-12">
<div class="my-3">
<div class="input">Background</div>
<input name="background" type="color" class="form-control" value="" placeholder=""/>
</div>
</div>
';


print '
<div class="col-sm-12">
<div class="my-3">
<label>Pour</label>
<select name="rank" class="form-select">
<option value="0" disabled>Choisir rang</option>
<option value="user">Clients</option>
<option value="delivery">Livreurs</option>
</select>
</div>
</div>
';


print '
<div class="col-sm-12">
<div class="my-3">
<label>Type</label>
<select name="type" class="form-select">
<option value="0" disabled>Choisir Type</option>
<option value="pop">POP</option>
<option value="alert">Alert</option>
</select>
</div>
</div>
';



// for pop
print '
<div class="col-sm-12 my-3">
<label for="formFile" class="form-label">Images POP</label>
<input class="form-control my-3" name="image" type="file" id="image">
</div>
';


// alert
print '
<div class="col-sm-12">
<div class="input">Détails de Alert</div>
<textarea class="editor w-100" name="details"></textarea>
</div>
';

echo "<div class='col-sm-12 text-center my-2'>";
echo "<div id='".$result."'></div>";
echo "</div>";


echo "<div class='col-sm-12 text-center my-2'>";
echo "<button class='btn btn-primary' type='submit'>Ajouter</button>";
echo "</div>";


echo "</div>";



formAwdEnd ();


echo "</div>";
echo "</div>";
echo "</div>";


?>



<div class="loader"></div>
<div id="dynamic_content"></div>




<script>
$(document).ready(function () {
  // تحميل البيانات لأول مرة
  loadData(1);

  // دالة التحميل مع باراميتر الصفحة (افتراضياً 1)
  function loadData(page = 1) {
    const search = $('.searchbox').val();
    const display = $('.display').val();

    $.ajax({
      url: 'getNews',
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
  $('.searchbox, .display').on('input change keyup', function () {
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





</div>
</div>









<?php
}
}
?>






</div>
</div>



































</main>

<?php include get_file("Admin/admin_footer");?>
<?php
}
?>