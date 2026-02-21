<?php 

$bootstrap = new Bootstrap();
$bootstrap->runBootstrap();

include get_file("files/sql/get/os_settings");
include get_file("files/sql/get/session");
include get_file("files/sql/get/functions");
include get_file("Admin/admin_header");


define ("page_title","Emballages");


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

<div class='col-sm-12' style="">
<div class='container' style="">



<div class="collapse" id="category">
<div class="row gx-4 gx-lg-5 row-cols-2 row-cols-md-3 row-cols-xl-4 justify-content-center">


</div>
</div>
</div>
</div>

</div>
<hr>
<div class="loader"></div>
<div id="dynamic_content"></div>
</div>
</div>





<script>
$(document).ready(function(){
load_data(1); // تحميل البيانات افتراضيًا

function load_data(page, search = '', display = '') {
console.log("📤 إرسال البيانات:", {
page: page,
search: search,
display: display
});


$.ajax({
url: 'get_boxing',
method: 'POST',
data: { page: page, search: search, display: display },
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
load_data(1, search, display); // ✅ إغلاق القوس بشكل صحيح
});

// عند تغيير قيمة عرض المنتجات
$('.display').change(function() {
var display = $(this).val();
var search = $('.searchbox').val();
load_data(1, search, display); // ✅ إغلاق القوس بشكل صحيح
});





$(document).on('click', '.page-link', function(event) {
event.preventDefault(); // منع إعادة تحميل الصفحة
let page = $(this).attr('data-page'); // التقاط رقم الصفحة
let search = $('.searchbox').val();
let display = $('.display').val();
load_data(page, search, display); // تحميل الصفحة المناسبة
});

});
</script>


<?php
}elseif($do == "new"){






}elseif($do == "edit"){
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