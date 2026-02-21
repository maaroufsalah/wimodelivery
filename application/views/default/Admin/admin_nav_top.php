
<nav class="app-header navbar navbar-expand " style='background: #006964;'>
<!--begin::Container-->
<div class="container-fluid">
<!--begin::Start Navbar Links-->
<ul class="navbar-nav">
<li class="nav-item">
<a class="nav-link" data-lte-toggle="sidebar" href="#" role="button">
<i class="bi bi-list"></i>
</a>
</li>
<li class="nav-item d-none d-md-block"><a class="nav-link">
<?php
if ($loginRank == "admin"){
print "Espace Admin";

}elseif($loginRank == "user"){
print "Espace  Vendeur";

}elseif($loginRank == "aide"){
print "Espace  Vendeur - Staff";

}elseif($loginRank == "delivery"){
print "Espace Livreur";
}
?>
</a></li>
</ul>
<!--end::Start Navbar Links-->
<!--begin::End Navbar Links-->
<ul class="navbar-nav ms-auto">










<li class="nav-item">
<a class="nav-link" href="#" data-lte-toggle="fullscreen">
<i data-lte-icon="maximize" class="bi bi-arrows-fullscreen"></i>
<i data-lte-icon="minimize" class="bi bi-fullscreen-exit" style="display: none"></i>
</a>
</li>


<?php
if ($loginRank == "admin"){
?>

<li class="nav-item">
<a class="nav-link" href="app_settings">
<i class="bi bi-gear-fill"></i>
</a>
</li>

<?php
}
?>




<li class="nav-item dropdown user-menu">
<a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
<img
src="uploads/profile/<?= htmlspecialchars($loginUser['user_avatar']) ?>"
class="user-image rounded-circle shadow"
alt="User Image"
/>
<span class="d-none d-md-inline"><?php print $loginName ;?></span>
</a>


<ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end">

<li class="user-header text-bg-primary">
<img src="uploads/profile/<?= htmlspecialchars($loginUser['user_avatar']) ?>" class="rounded-circle shadow" alt="User Image"/>
<p><?php print $loginName ;?><br><?php print $loginOwner ;?></p>
</li>



<li class="user-footer">
<a href="users?do=edit&id=<?php print md5($loginId) ;?>" class="btn btn-default btn-flat">Profile</a>
<a href="logout" class="btn btn-default btn-flat float-end">Sign out</a>
</li>

</ul>






</li>
<!--end::User Menu Dropdown-->
</ul>
<!--end::End Navbar Links-->
</div>
<!--end::Container-->
</nav>


