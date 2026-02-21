<?php 

$bootstrap = new Bootstrap();
$bootstrap->runBootstrap();

include get_file("files/sql/get/os_settings");
include get_file("files/sql/get/session");
include get_file("files/sql/get/functions");

if (hasUserPermission($con, $loginId, 35 ,'admin')){


include get_file("Admin/admin_header");



$page_name = "Tarifs";




define ("page_title", $page_name);
$do = isset ($_GET['do']) ? $_GET ['do'] : 'Manage' ;


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
<div class="card">
<div class="card-body">

<ul class="nav justify-content-center">
<li class="nav-item">
<a class="nav-link  <?php if($do =='Manage'){print "active text-dark";}?>" href="pricing">Globale</a>
</li>
<li class="nav-item">
<a class="nav-link <?php if($do =='delivery'){print "active text-dark";}?>" href="?do=delivery">Livreur</a>
</li>
<li class="nav-item">
<a class="nav-link <?php if($do =='user'){print "active text-dark";}?>" href="?do=user">Client</a>
</li>
</ul>

<?php

if ($do == 'Manage'){
// global tarif
include get_file("files/sql/get/gp");
}elseif($do == "delivery"){
// delivery tarif
include get_file("files/sql/get/dp");
}elseif($do == "user"){
// client tarif
include get_file("files/sql/get/upl");
}else{
}
?>

</div>
</div>
</div>
</div>

























</main>

<?php include get_file("Admin/admin_footer");?>

<?php
}
?>