<?php 

$bootstrap = new Bootstrap();
$bootstrap->runBootstrap();

include get_file("files/sql/get/os_settings");
include get_file("files/sql/get/session");
include get_file("Admin/admin_header");

define ("page_title","Tableau de bord");

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
if ($loginRank == "admin"){

if ((hasUserPermission($con, $loginId, 1 ,'admin'))){

    include get_file("files/sql/get/admin_dashboard");

}



}elseif($loginRank == "user"){

include get_file("files/sql/get/user_alert");
include get_file("files/sql/get/user_dashboard");


}elseif($loginRank == "aide"){

include get_file("files/sql/get/aide_alert");
include get_file("files/sql/get/aide_dashboard");


}elseif($loginRank == "delivery"){

include get_file("files/sql/get/delivery_alert");
include get_file("files/sql/get/delivery_dashboard");

		
		
}else{


}
?>


</div>
</div>



































</main>

<?php include get_file("Admin/admin_footer");?>