<?php 
global $con;

include get_file("files/sql/get/os_settings");
include get_file("files/sql/get/session");



?>

<div class="table-responsive">
<table class="table table-bordered table-hover align-middle text-center">
<thead class="table-dark">
<tr>
<th>ID</th>
<th>Vendeur</th>
<th>Destinataire</th>
<th>Prix</th>
<th>Ville</th>
<th>Livreur</th>
<th>Emballage</th>
<th>État</th>
<?php if(($loginRank == "admin")||($loginRank == "delivery")):?>
<th>Facture Livreur</th>
<?php endif;?>
<?php if(($loginRank == "admin")||($loginRank == "user")):?>
<th>Facture Vendeur</th>
<?php endif;?>
<th>Actions</th>
</tr>
</thead>
<tbody>

<?php foreach ($orders as $row): ?>
<?php

$created_date = new DateTime($row['or_created']);
$now = new DateTime(); // pas besoin de date("Y-m-d")

$interval = $created_date->diff($now);




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

if (($row['or_box'])>0){

$boxStmt = $con->prepare("SELECT * FROM box WHERE box_id = ? LIMIT 1");
$boxStmt->execute([$row['or_box']]);
$box = $boxStmt->fetch() ?: [];

}

if (($row['or_invoice'])>0){
$iStmt = $con->prepare("SELECT * FROM invoice WHERE in_id = ? LIMIT 1");
$iStmt->execute([$row['or_invoice']]);
$invoice = $iStmt->fetch() ?: [];
}

if (($row['or_delivery_invoice'])>0){
$idStmt = $con->prepare("SELECT * FROM delivery_invoice WHERE d_in_id = ? LIMIT 1");
$idStmt->execute([$row['or_delivery_invoice']]);
$delivery_invoice = $idStmt->fetch() ?: [];
}

$app_whatsapp = urlencode("مرحبًا ، أتواصل معك بخصوص الطلب الذي اشتريته من البائع " . ($trade['user_name'] ?? ''));

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
<tr  style="border-left: 2px solid <?= $state['state_background'] ?? '#ccc' ?>;">



<td>

<?php
$showCheckbox = (
    // الشرط الخاص بـ delivery
    ($loginRank == "delivery" &&
     $row['or_invoice'] == "0" &&
     $row['or_delivery_invoice'] == "0" &&
     $row['or_state_delivery'] != 1)
) || (
    // الشرط الخاص بـ admin
    $loginRank == "admin"

);
?>

<?php if ($showCheckbox): ?>
<label for='cb_<?= $row['or_id']; ?>'><b><?= $row['or_id']; ?></b></label>
<input onclick="updateHiddenField();" type="checkbox" class="bulk-check order-checkbox" id='cb_<?= $row['or_id']; ?>' value="<?= $row['or_id']; ?>">
<?php else: ?>
<b><?= $row['or_id']; ?></b>
<?php endif; ?>


<div><?= $interval->days == 0 ? "Aujourd'hui" : $interval->days . " Jours" ?></div>
<div><?= $row['or_created'] ?></div>



<?php if (($row['or_exp_date']) > 0): ?>
<div><i class="fa-solid fa-share-from-square"></i> <?= $row['or_exp_date'] ?></div>
<?php endif; ?>




<?php if (($row['or_change']) > 0): ?>
<div class="badge bg-warning">Change (colis N° : <?=$row['or_change_code']?>)</div>
<?php endif; ?>


<?php if (($row['or_fee_change'] > 0) && ($row['or_fee'] > 0)): ?>
    <div class="badge bg-success">FC</div>
<?php elseif ($row['or_fee'] > 0): ?>
    <div class="badge bg-info">GC</div>
<?php elseif ($row['or_fee_change'] > 0): ?>
    <div class="badge bg-success">FC</div>
<?php endif; ?>


<?php if (($row['or_fpc']) > 0): ?>
<div class="badge bg-danger">FPC</div>
<?php endif; ?>







</td>







<td><?= $trade['user_name'] ?? '—' ?><br>(<?= $trade['user_owner'] ?? '—' ?>)<br><br><?= $trade['user_phone'] ?? '—' ?>
<a class="text-success mx-1" target="_blank" href="https://wa.me/+212<?= $trade['user_phone']; ?>">
<i class="fa-brands fa-whatsapp"></i> WhatsApp
</a>
</td>
<td>
<b><?= $row['or_name']; ?><br>(<?= $row['or_phone']; ?>)</b><br>
<a class="text-success mx-1" target="_blank" href="https://wa.me/+212<?= $row['or_phone']; ?>">
<i class="fa-brands fa-whatsapp"></i> WhatsApp
</a>
</td>
<td><b><?= $row['or_total']; ?> Dhs</b>
<?php if ($row['or_state_delivery'] == 60): ?>
<div style="color: blue; font-weight: bold;"><i class="fa-solid fa-repeat"></i> Échange</div>
<?php endif; ?>
</td>
<td>
<?= $city['city_name'] ?? '—'; ?>
<?php if ($loginRank == "user" || $loginRank == "admin"): ?>
<br><a class="text-success btn btn-sm my-2" href="claim?do=new&id=<?= md5($row['or_id']); ?>">
<i class="fa-solid fa-headset"></i> Réclamer
</a>
<?php endif; ?>
</td>


<td>
<?php if (!empty($deliveryUser['user_name'])): ?>
<div><b><?= $deliveryUser['user_name'] ?></b></div>
<div><b><?= $deliveryUser['user_phone'] ?></b></div>
<a class="text-success mx-1" target="_blank" href="https://wa.me/+212<?= $deliveryUser['user_phone']; ?>">
<i class="fa-brands fa-whatsapp"></i> WhatsApp
</a>
<?php endif; ?>
</td>



<td>
<?php if (!empty($row['or_box'])): ?>
<div><b><?= $box['box_name'] ?> - <?= $row['or_box_price'] ?></b></div>
<?php else : ?>
-
<?php endif; ?>
</td>


<td>
<?php if ($row['or_state_delivery'] == 0): ?>
<a data-bs-toggle='modal' data-bs-target='#modal_state<?= $row['or_id']; ?>'  class='btn btn-sm' style='background:#169dd0;color:black'><b>En Attente</b></a>
<?php else: ?>
<a data-bs-toggle="modal" data-bs-target="#modal_state<?= $row['or_id']; ?>" class="btn btn-sm" style="background:<?= $state['state_background']; ?>;color:<?= $state['state_color']; ?>;">
<b><?= $state['state_name']; ?></b>
</a>
<?php endif; ?>

<?php if ($row['or_state_delivery'] == 5): ?>
<?php if (!empty($row['or_postponed'])): ?>
<div class="text-info" style=''>Reporter : <b><?= $row['or_postponed'] ?></b></div>
<?php endif; ?>
<?php endif; ?>

<?php if ($row['or_state_delivery'] == 1): ?>
<?php if (!empty($row['or_delivered'])): ?>
<div class="text-info">Livré le : <b><?= $row['or_delivered'] ?></b></div>
<?php endif; ?>
<?php endif; ?>



</td>


<td>
<?php if(($loginRank == "admin")||($loginRank == "delivery")):?>
<?php if (($row['or_delivery_invoice']) > 0): ?>
<a target="_blank" class="text-info" href="print_delivery_invoice?id=<?= md5($row['or_delivery_invoice']); ?>">L / Facturé</a>
<?php if ($delivery_invoice['d_in_state'] == "1"): ?>
- <span class="text-success">Payé</span>
<?php endif; ?>
<?php endif; ?>
<?php endif; ?>

</td>

<td>
<?php if(($loginRank == "admin")||($loginRank == "user")):?>
<?php if (($row['or_invoice']) > 0): ?>
<a target="_blank" class="btn btn-sm btn-warning my-1" href="print_invoice?id=<?= md5($row['or_invoice']); ?>">V / Facturé</a>
<?php if ($invoice['in_state'] == "1"): ?>
<div class="text-success">Payé</div>
<?php endif; ?>
<?php endif; ?>
<?php endif; ?>
</td>


<td>
<a data-bs-toggle="modal" data-bs-target="#orderDetailsModal<?= $row['or_id']; ?>" class="text-dark"><i class="fa-solid fa-bars"></i></a>
<a data-bs-toggle='modal' data-bs-target='#tracking<?= $row['or_id']; ?>'  class='mx-2 text-info'><i class="fa-solid fa-location-dot"></i></a>

</td>
</tr>









<!-- مودال التفاصيل -->
<div class="modal fade" id="orderDetailsModal<?= $row['or_id']; ?>" tabindex="-1" aria-labelledby="orderDetailsModalLabel<?= $row['or_id']; ?>" aria-hidden="true">
<div class="modal-dialog modal-lg modal-dialog-scrollable">
<div class="modal-content">
<div class="modal-header">
<h5 class="modal-title" id="orderDetailsModalLabel<?= $row['or_id']; ?>">Détails de la commande #<?= $row['or_id']; ?></h5>
<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body" style="font-size: 14px;">

<div class='row'><div class='col-4'><b>Date :</b></div><div class='col-8'><?= date('d/m/Y H:i', strtotime($row['or_created'])); ?></div></div><hr>

<div class='row'>
<div class='col-4'><b>Statut :</b></div>
<div class='col-8'>
<?php if ($row['or_state_delivery'] == 0): ?>
<a data-bs-toggle='modal' data-bs-target='#modal_state<?= $row['or_id']; ?>'  class='btn btn-sm' style='background:#169dd0;color:black'><b>En Attente</b></a>
<?php else: ?>
<a data-bs-toggle='modal' data-bs-target='#modal_state<?= $row['or_id']; ?>'  class='btn btn-sm' style='background:<?= $state['state_background']; ?>;color:<?= $state['state_color']; ?>'><b><?= $state['state_name']; ?></b></a>
<?php endif; ?>
<a data-bs-toggle='modal' data-bs-target='#tracking<?= $row['or_id']; ?>'  class='text-info'>Suivi</a>
</div>
</div><hr>

<div class='row'><div class='col-4'><b>Code Colis :</b></div><div class='col-8'><?= $row['or_code']; ?></div></div><hr>

<div class='row'><div class='col-4'><b>Expéditeur :</b></div><div class='col-8'><?= $trade['user_name'] ?? '—'; ?></div></div><hr>

<div class='row'><div class='col-4'><b>Destinataire :</b></div><div class='col-8'><?= $row['or_name'] ?? '—'; ?></div></div><hr>

<?php if (($row['or_box'])>0): ?>
<div class='row'><div class='col-4'><b>Emballage :</b></div><div class='col-8'><?= $box['box_name'] ?? '—'; ?> - ( <?= $row['or_box_price'] ?? '—'; ?> Dhs )</div></div><hr>
<?php endif; ?>

<div class='row'>
<div class='col-4'><b>Téléphone :</b></div>
<div class='col-8'>
<a class='btn btn-info btn-sm' href='tel:<?= $row['or_phone']; ?>'><i class="fa-solid fa-square-phone"></i> <?= $row['or_phone']; ?></a>
<a class='text-success mx-3' href='https://wa.me/+212<?= $row['or_phone']; ?>?text=<?= $app_whatsapp; ?>'><i class="fa-brands fa-2x fa-whatsapp"></i></a>
</div>
</div><hr>

<div class='row'><div class='col-4'><b>Ville :</b></div><div class='col-8'><?= $city['city_name'] ?? '—'; ?></div></div><hr>

<div class='row'><div class='col-4'><b>Adresse :</b></div><div class='col-8'><?= $row['or_address']; ?></div></div><hr>

<div class='row'>
<div class='col-4'><b>Produit :</b></div>
<div class='col-8'>
<?php foreach ($items as $item):
$stmt = $con->prepare("SELECT * FROM order_item_options WHERE item_id = ?");
$stmt->execute([$item['item_id']]);
$options = $stmt->fetchAll(); ?>
<div>x(<?= $item['quantity'] ?>) | <?= html_entity_decode($item['product_name']) ?></div>
<?php endforeach; ?>
</div>
</div><hr>

<h5>Total : <span class="text-primary"><?= number_format($row['or_total'], 2) ?> Dhs</span></h5><hr>

<?php if (($row['or_state_delivery'] == 0) and ($loginRank == "user")): ?>
<div class='text-center mb-3'>
<a class='btn btn-info btn-sm mx-2' href='packages?do=edit&order_id=<?= md5($row['or_id']); ?>'>Modifier</a>
<a data-bs-toggle='modal' data-bs-target='#modalDelete<?= $row['or_id']; ?>' class='btn btn-sm btn-danger mx-2'>Supprimer</a>
</div>
<?php endif; ?>

<div class='text-center mb-3'>
<?php if ((hasUserPermission($con, $loginId, 11 ,'admin'))): ?>
<a class='btn btn-sm btn-dark' href='dataUpdate?do=delivery_unlink&id=<?= md5($row['or_id']); ?>'>Supprimer Au livreur</a>
<?php endif; ?>
<?php if ((hasUserPermission($con, $loginId, 9 ,'admin'))): ?>
<a class='btn btn-info btn-sm mx-2' href='packages?do=edit&order_id=<?= md5($row['or_id']); ?>'>Modifier</a>
<?php endif; ?>
<?php if ((hasUserPermission($con, $loginId, 10 ,'admin'))): ?>
<a data-bs-toggle='modal' data-bs-target='#modalDelete<?= $row['or_id']; ?>' class='btn btn-sm btn-danger mx-2'>Supprimer</a>
<?php endif; ?>
</div>

<hr>


</div>
<div class="modal-footer">
<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
</div>
</div>
</div>
</div>





<?php if (($loginRank == "admin")||($loginRank == "agency")||($loginRank == "delivery")||($loginRank == "user")): ?>
<!-- نافذة الحذف -->
<div class='modal fade' id='tracking<?= $row['or_id']; ?>' tabindex='-1'>
<div class='modal-dialog modal-dialog-centered'>
<div class='modal-content'>
<div class='modal-header'>
<h5 class='modal-title'>Suivi Votre colis</h5>
<button type='button' class='btn-close' data-bs-dismiss='modal'></button>
</div>

<div class='modal-body text-center'>

</div>

</div>
</div>
</div>
<?php endif; ?>




















<?php endforeach; ?>
</tbody>
</table>
</div>


<div style="margin:100px"></div>