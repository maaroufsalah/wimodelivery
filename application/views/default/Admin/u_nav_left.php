<?php
include get_file("files/sql/get/os_settings");
include get_file("files/sql/get/session");
?>



<aside class="app-sidebar bg-white shadow" data-bs-theme="dark">
<div class="sidebar-brand">
<a href="dashboard" class="brand-link">
<img src="uploads/<?php echo $set_logo; ?>" alt="Logo" />
</a>
</div>

<nav class="mt-2">
<ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="menu" data-accordion="false">

<li class="nav-item">
<a href="dashboard" class="nav-link">
<span class="material-symbols-outlined icon">dashboard</span>
<span class="text">Dashboard</span>
</a>
</li>

<li class="nav-item">
<a href="pickup_client" class="nav-link">
<span class="material-symbols-outlined icon">inventory_2</span>
<span class="text">Demande Ramassage</span>
</a>
</li>

<li class="nav-item has-treeview">
<a class="nav-link">
<span class="material-symbols-outlined icon">add_circle</span>
<span class="text">Ajouter Colis</span>
<span class="material-symbols-outlined arrow">chevron_right</span>
</a>
<ul class="nav nav-treeview">
<li class="nav-item">
<a href="packages?do=new" class="nav-link menu-size-tree">
<span class="material-symbols-outlined icon">radio_button_checked</span>
<span class="text">Colis</span>
</a>
</li>
<li class="nav-item">
<a href="packages?do=nvs" class="nav-link menu-size-tree">
<span class="material-symbols-outlined icon">radio_button_checked</span>
<span class="text">Colis stock</span>
</a>
</li>
<li class="nav-item">
<a href="packages?do=import" class="nav-link menu-size-tree">
<span class="material-symbols-outlined icon">radio_button_checked</span>
<span class="text">Importer colis</span>
</a>
</li>
</ul>
</li>


<li class="nav-item">
<a href="packages?state=int" class="nav-link">
<span class="material-symbols-outlined icon">list</span>
<span class="text">Nouveau colis</span>
</a>
</li>


<li class="nav-item">
<a href="packages" class="nav-link">
<span class="material-symbols-outlined icon">list</span>
<span class="text">Liste des Colis</span>
</a>
</li>



<li class="nav-item">
<a href="pickup" class="nav-link">
<span class="material-symbols-outlined icon">local_shipping</span>
<span class="text">Bon Ramassage</span>
</a>
</li>

<li class="nav-item">
<a href="invoice" class="nav-link">
<span class="material-symbols-outlined icon">receipt_long</span>
<span class="text">Mes Factures</span>
</a>
</li>

<li class="nav-item">
<a href="outLogUser" class="nav-link">
<span class="material-symbols-outlined icon">undo</span>
<span class="text">Demande de retour</span>
</a>
</li>

<li class="nav-item has-treeview">
<a class="nav-link">
<span class="material-symbols-outlined icon">warehouse</span>
<span class="text">Gestion de stock</span>
<span class="material-symbols-outlined arrow">chevron_right</span>
</a>
<ul class="nav nav-treeview">
<li class="nav-item">
<a href="stocks" class="nav-link menu-size-tree">
<span class="material-symbols-outlined icon">radio_button_checked</span>
<span class="text">Mes Produits en stock</span>
</a>
</li>
<li class="nav-item">
<a href="data_boxing" class="nav-link menu-size-tree">
<span class="material-symbols-outlined icon">radio_button_checked</span>
<span class="text">Liste D'emballages</span>
</a>
</li>
</ul>
</li>

<li class="nav-item">
<a href="claim" class="nav-link">
<span class="material-symbols-outlined icon">headset_mic</span>
<span class="text">Réclamations</span>
</a>
</li>

<?php if ($loginUser['user_aide'] !== $loginId): ?>
<li class="nav-item">
<a href="staffs" class="nav-link">
<span class="material-symbols-outlined icon">groups</span>
<span class="text">Équipes</span>
</a>
</li>
<?php endif; ?>

<li class="nav-item">
<a href="api_doc" class="nav-link">
<span class="material-symbols-outlined icon">code</span>
<span class="text">API</span>
</a>
</li>

</ul>
</nav>
</aside>


