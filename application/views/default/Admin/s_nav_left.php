<?php
include get_file("files/sql/get/os_settings");
include get_file("files/sql/get/session");
include_once get_file("files/sql/get/functions");
?>



<aside class="app-sidebar bg-white shadow" data-bs-theme="dark">
<div class="sidebar-brand">
<a href="dashboard" class="brand-link">
<img src="uploads/<?php echo $set_logo; ?>" alt="Logo" />
</a>
</div>

<nav class="mt-2">
<ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="menu" data-accordion="false">

<?php if (hasUserPermissionAide($con, $loginId, 45 ,"aide")): ?>
<li class="nav-item">
<a href="dashboard" class="nav-link">
<i class="nav-icon bi bi-speedometer"></i>
<p>Dashboard</p>
</a>
</li>
<?php endif; ?>

<?php if (hasUserPermissionAide($con, $loginId, 47 ,"aide")): ?>
<li class="nav-item">
<a href="pickup_client" class="nav-link">
<i class="nav-icon bi bi-inboxes-fill"></i>
<p>Demande Ramassage</p>
</a>
</li>
<?php endif; ?>

<?php if (hasUserPermissionAide($con, $loginId, 46 ,"aide")): ?>
<li class="nav-item has-treeview">
<a href="#" class="nav-link toggle-treeview">
<i class="nav-icon bi bi-box2"></i>
<p>Colis <i class="nav-arrow bi bi-chevron-right"></i></p>
</a>
<ul class="nav nav-treeview">
<li class="nav-item">
<a href="packages?state=0" class="nav-link menu-size-tree">
<i class="nav-icon bi bi-circle"></i>
<p>Tout</p>
</a>
</li>
</ul>
</li>
<?php endif; ?>

<?php if (hasUserPermissionAide($con, $loginId, 48 ,"aide")): ?>
<li class="nav-item">
<a href="pickup" class="nav-link">
<i class="nav-icon bi bi-truck"></i>
<p>Bon Ramassage</p>
</a>
</li>
<?php endif; ?>

<?php if (hasUserPermissionAide($con, $loginId, 49 ,"aide")): ?>
<li class="nav-item">
<a href="invoice" class="nav-link">
<i class="nav-icon bi bi-cash"></i>
<p>Mes Factures</p>
</a>
</li>
<?php endif; ?>

<?php if (hasUserPermissionAide($con, $loginId, 50 ,"aide")): ?>
<li class="nav-item">
<a href="outLogUser" class="nav-link">
<i class="nav-icon bi bi-arrow-clockwise"></i>
<p>Demande de retour</p>
</a>
</li>
<?php endif; ?>

<?php if (hasUserPermissionAide($con, $loginId, 51 ,"aide")): ?>
<li class="nav-item">
<a href="stocks" class="nav-link">
<i class="nav-icon bi bi-boxes"></i>
<p>Mes Produits stocks</p>
</a>
</li>
<?php endif; ?>

<?php if (hasUserPermissionAide($con, $loginId, 52 ,"aide")): ?>
<li class="nav-item">
<a href="claim" class="nav-link">
<i class="nav-icon bi bi-headphones"></i>
<p>Réclamations</p>
</a>
</li>
<?php endif; ?>

</ul>
</nav>
</aside>


