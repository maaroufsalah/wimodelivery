<?php
include get_file("files/sql/get/os_settings");
include get_file("files/sql/get/session");

$stmt = $con->prepare("SELECT * FROM state WHERE state_unlink = 0 AND state_id IN (1,62,57,54,51,5,2) ORDER BY state_name");
$stmt->execute();
$states = $stmt->fetchAll();

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
<i class="nav-icon bi bi-speedometer"></i>
<p>Dashboard</p>
</a>
</li>

<li class="nav-item">
<a href="pickup_client" class="nav-link">
<i class="nav-icon bi bi-inboxes-fill"></i>
<p>Ramassage</p>
</a>
</li>

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



<?php foreach ($states as $row): 
$stmt = $con->prepare("SELECT * FROM orders WHERE or_unlink = 0 AND or_state_delivery = '".$row['state_id']."' AND or_delivery_user = '".$loginId."' ORDER BY or_id DESC");
$stmt->execute();
$orders = $stmt->fetchAll();
$orc = count($orders) > 0 
? "<span class='badge' style='background:".$row['state_background']."'>".count($orders)."</span>"
: "<span class='badge bg-danger'>0</span>";
?>
<li class="nav-item">
<a href="packages?state=<?php echo $row['state_id']; ?>" class="nav-link menu-size-tree">
<span class="material-symbols-outlined icon">fiber_manual_record</span>
<span class="text"><?php echo $row['state_name']; ?> <?php echo $orc; ?></span>
</a>
</li>
<?php endforeach; ?>


</ul>
</li>

<li class="nav-item">
<a href="shipping" class="nav-link">
<i class="nav-icon bi bi-pin-map"></i>
<p>Expéditions</p>
</a>
</li>

<li class="nav-item">
<a href="deliveryInvoice" class="nav-link">
<i class="nav-icon bi bi-cash"></i>
<p>Facture Livreur</p>
</a>
</li>

<li class="nav-item">
<a href="outLogDelivery" class="nav-link">
<i class="nav-icon bi bi-arrow-clockwise"></i>
<p>Demande de retour</p>
</a>
</li>

<li class="nav-item has-treeview">
<a href="#" class="nav-link toggle-treeview">
<i class="nav-icon bi bi-upc-scan"></i>
<p>Scanner <i class="nav-arrow bi bi-chevron-right"></i></p>
</a>
<ul class="nav nav-treeview">
<li class="nav-item">
<a href="scan" class="nav-link menu-size-tree">
<i class="nav-icon bi bi-circle"></i>
<p>Colis (BOX)</p>
</a>
</li>
</ul>
</li>

<li class="nav-item">
<a href="api_doc" class="nav-link">
<span class="material-symbols-outlined icon">code</span>
<span class="text">API</span>
</a>
</li>


</ul>
</nav>
</aside>

