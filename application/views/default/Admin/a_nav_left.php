<?php
include get_file("files/sql/get/os_settings");
include get_file("files/sql/get/session");
include_once get_file("files/sql/get/functions");



$count_orders_all = $con->query("SELECT COUNT(*) FROM orders WHERE or_unlink = '0' AND or_state_delivery > 0 ")->fetchColumn();
$new_orders_all = $con->query("SELECT COUNT(*) FROM orders WHERE or_unlink = '0' AND or_state_delivery = 0 ")->fetchColumn();
$count_orders = $con->query("SELECT COUNT(*) FROM orders WHERE or_seen = 0 AND or_unlink = '0' ")->fetchColumn();
$count_claims = $con->query("SELECT COUNT(*) FROM claim WHERE claim_state = 0 AND claim_unlink = '0'")->fetchColumn();
$count_users = $con->query("SELECT COUNT(*) FROM users WHERE user_seen = 0 AND user_unlink = '0'")->fetchColumn();


$stmt = $con->prepare("SELECT * FROM state WHERE state_unlink = 0 AND state_id IN (51,1,62,57,54,51,5,2) ORDER BY state_name");
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

<?php if (hasUserPermission($con, $loginId, 1 ,"admin")): ?>
<li class="nav-item">
<a href="dashboard" class="nav-link">
<span class="material-symbols-outlined icon">timeline</span>
<span class="text">Dashboard</span>
</a>
</li>
<?php endif; ?>





<li class="nav-item has-treeview">
<a class="nav-link">
<span class="material-symbols-outlined icon">add_circle</span>
<span class="text">Ajouter Colis</span>
<span class="material-symbols-outlined arrow">chevron_right</span>
</a>

<ul class="nav nav-treeview">

<?php if (hasUserPermission($con, $loginId, 3 ,"admin")): ?>
<li class="nav-item">
<a href="packages?do=new" class="nav-link menu-size-tree">
<span class="material-symbols-outlined icon">radio_button_checked</span>
<span class="text">Colis</span>
</a>
</li>
<?php endif ;?>

<?php if (hasUserPermission($con, $loginId, 4 ,"admin")): ?>
<li class="nav-item">
<a href="packages?do=nvs" class="nav-link menu-size-tree">
<span class="material-symbols-outlined icon">radio_button_checked</span>
<span class="text">Colis stock</span>
</a>
</li>
<?php endif ;?>

<?php if (hasUserPermission($con, $loginId, 5 ,"admin")): ?>
<li class="nav-item">
<a href="packages?do=import" class="nav-link menu-size-tree">
<span class="material-symbols-outlined icon">radio_button_checked</span>
<span class="text">Importer colis</span>
</a>
</li>
<?php endif ;?>


</ul>
</li>






<?php if (hasUserPermission($con, $loginId, 2 ,"admin")): ?>
<li class="nav-item has-treeview">
<a href="#" class="nav-link">
<span class="material-symbols-outlined icon">inventory_2</span>
<span class="text">Colis <?php if ($count_orders>0){echo "<span class='bg-dark text-white px-3 py-2' style='border-radius:50rem'>".$count_orders."</span>";} ?></span>
<span class="material-symbols-outlined arrow">chevron_right</span>
</a>
<ul class="nav nav-treeview">
<li class="nav-item">
<a href="packages?state=int" class="nav-link">
<span class="material-symbols-outlined icon">fiber_manual_record</span>
<span class="text">Nouveau colis <?php if ($count_orders_all>0){echo "<span class='bg-dark text-white px-1 py-0' style='border-radius:50rem'>".$new_orders_all."</span>";} ?></span>
</a>
</li>
<li class="nav-item">
<a href="packages" class="nav-link">
<span class="material-symbols-outlined icon">fiber_manual_record</span>
<span class="text">Liste des colis <?php if ($count_orders_all>0){echo "<span class='bg-dark text-white px-1 py-0' style='border-radius:50rem'>".$count_orders_all."</span>";} ?></span>
</a>
</li>
<?php foreach ($states as $row): 
$stmt = $con->prepare("SELECT * FROM orders WHERE or_unlink = 0 AND or_state_delivery = '".$row['state_id']."' ORDER BY or_id DESC");
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
<?php endif; ?>



<?php if (hasUserPermission($con, $loginId, 36 ,"admin")): ?>
<li class="nav-item">
<a href="claim" class="nav-link">
<span class="material-symbols-outlined icon">support_agent</span>
<span class="text">Réclamations <?php if ($count_claims>0){echo "<span class='bg-dark text-white px-3 py-2' style='border-radius:50rem'>".$count_claims."</span>";} ?></span>
</a>
</li>
<?php endif; ?>


<?php if (hasUserPermission($con, $loginId, 13 ,"admin")): ?>
<li class="nav-item">
<a href="pickup_client" class="nav-link">
<span class="material-symbols-outlined icon">inbox</span>
<span class="text">Demande Ramassage</span>
</a>
</li>
<?php endif; ?>

<?php if (hasUserPermission($con, $loginId, 14 ,"admin")): ?>
<li class="nav-item">
<a href="pickup" class="nav-link">
<span class="material-symbols-outlined icon">local_shipping</span>
<span class="text">Bon Ramassage</span>
</a>
</li>
<?php endif; ?>

<?php if (hasUserPermission($con, $loginId, 15 ,"admin")): ?>
<li class="nav-item">
<a href="invoice" class="nav-link">
<span class="material-symbols-outlined icon">receipt_long</span>
<span class="text">Facture Client</span>
</a>
</li>
<?php endif; ?>

<?php if (hasUserPermission($con, $loginId, 18 ,"admin")): ?>
<li class="nav-item">
<a href="deliveryInvoice" class="nav-link">
<span class="material-symbols-outlined icon">receipt_long</span>
<span class="text">Facture Livreur</span>
</a>
</li>
<?php endif; ?>

<?php if (hasUserPermission($con, $loginId, 23 ,"admin")): ?>
<li class="nav-item">
<a href="outLogDelivery" class="nav-link">
<span class="material-symbols-outlined icon">undo</span>
<span class="text">Bon de retour Livreur</span>
</a>
</li>
<?php endif; ?>

<?php if (hasUserPermission($con, $loginId, 21 ,"admin")): ?>
<li class="nav-item">
<a href="outLogUser" class="nav-link">
<span class="material-symbols-outlined icon">undo</span>
<span class="text">Bon de retour Client</span>
</a>
</li>
<?php endif; ?>

<?php if (hasUserPermission($con, $loginId, 26 ,"admin")): ?>
<li class="nav-item">
<a href="shipping" class="nav-link">
<span class="material-symbols-outlined icon">location_on</span>
<span class="text">Expéditions</span>
</a>
</li>
<?php endif; ?>





<?php if (hasUserPermission($con, $loginId, 60 ,"admin")): ?>
<li class="nav-item">
<a href="expenses" class="nav-link">
<span class="material-symbols-outlined icon">fiber_manual_record</span>
<span class="text">Gestion des Dépenses</span>
</a>
</li>
<?php endif; ?>





<?php if (hasUserPermission($con, $loginId, 27 ,"admin")): ?>
<li class="nav-item">
<a href="stocks" class="nav-link">
<span class="material-symbols-outlined icon">inventory</span>
<span class="text">Stock</span>
</a>
</li>
<?php endif; ?>




<li class="nav-item has-treeview">
<a href="#" class="nav-link">
<span class="material-symbols-outlined icon">group</span>
<span class="text">Utilisateurs <?php if ($count_users>0){echo "<span class='bg-dark text-white px-3 py-2' style='border-radius:50rem'>".$count_users."</span>";} ?></span>
<span class="material-symbols-outlined arrow">chevron_right</span>
</a>
<ul class="nav nav-treeview">

<?php if (hasUserPermission($con, $loginId, 61 ,"admin")): ?>

<li class="nav-item"><a href="users?rank=user&state=0" class="nav-link menu-size-tree"><span class="material-symbols-outlined icon">fiber_manual_record</span><span class="text">Nouveaux clients</span></a></li>

<?php endif; ?>
<?php if (hasUserPermission($con, $loginId, 28 ,"admin")): ?>


<li class="nav-item"><a href="users?rank=user&state=1" class="nav-link menu-size-tree"><span class="material-symbols-outlined icon">fiber_manual_record</span><span class="text">Clients</span></a></li>
<li class="nav-item"><a href="users?rank=delivery" class="nav-link menu-size-tree"><span class="material-symbols-outlined icon">fiber_manual_record</span><span class="text">Livreurs</span></a></li>
<li class="nav-item"><a href="agency" class="nav-link menu-size-tree"><span class="material-symbols-outlined icon">fiber_manual_record</span><span class="text">Agences</span></a></li>

<li class="nav-item"><a href="users?rank=aide" class="nav-link menu-size-tree"><span class="material-symbols-outlined icon">fiber_manual_record</span><span class="text">Sous-client</span></a></li>
<?php endif; ?>

</ul>
</li>








<li class="nav-item has-treeview">
<a href="#" class="nav-link">
<span class="material-symbols-outlined icon">qr_code_scanner</span>
<span class="text">Scanner</span>
<span class="material-symbols-outlined arrow">chevron_right</span>
</a>
<ul class="nav nav-treeview">
<?php if (hasUserPermission($con, $loginId, 32 ,"admin")): ?>
<li class="nav-item"><a href="scan" class="nav-link menu-size-tree"><span class="material-symbols-outlined icon">fiber_manual_record</span><span class="text">Colis (BOX)</span></a></li>
<?php endif; ?>
<?php if (hasUserPermission($con, $loginId, 33 ,"admin")): ?>
<li class="nav-item"><a href="scan_by_state" class="nav-link menu-size-tree"><span class="material-symbols-outlined icon">fiber_manual_record</span><span class="text">Par Statut</span></a></li>
<?php endif; ?>
<?php if (hasUserPermission($con, $loginId, 34 ,"admin")): ?>
<li class="nav-item"><a href="scan_by_delivery" class="nav-link menu-size-tree"><span class="material-symbols-outlined icon">fiber_manual_record</span><span class="text">Par Livreur</span></a></li>
<?php endif; ?>
</ul>
</li>

<?php if (hasUserPermission($con, $loginId, 35 ,"admin")): ?>
<li class="nav-item has-treeview">
<a href="#" class="nav-link">
<span class="material-symbols-outlined icon">euro</span>
<span class="text">Tarifs</span>
<span class="material-symbols-outlined arrow">chevron_right</span>
</a>
<ul class="nav nav-treeview">
<li class="nav-item"><a href="pricing" class="nav-link menu-size-tree"><span class="material-symbols-outlined icon">fiber_manual_record</span><span class="text">Tarifs Globaux</span></a></li>
<li class="nav-item"><a href="pricing?do=delivery" class="nav-link menu-size-tree"><span class="material-symbols-outlined icon">fiber_manual_record</span><span class="text">Tarifs Livreurs</span></a></li>
<li class="nav-item"><a href="pricing?do=user" class="nav-link menu-size-tree"><span class="material-symbols-outlined icon">fiber_manual_record</span><span class="text">Tarifs Clients</span></a></li>
</ul>
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
