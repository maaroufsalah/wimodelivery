<style>
.btn-sm, .btn-group-sm > .btn {
    font-size: 10px;
}
</style>

<?php 
$bootstrap = new Bootstrap();
$bootstrap->runBootstrap();

include get_file("files/sql/get/os_settings");
include get_file("files/sql/get/session");
include_once get_file("files/sql/get/functions");


global $con;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['code'])) {
$code = trim($_POST['code']);

if ($code === "") {
echo "❌ Code vide.";
exit;
}

try {

if ($loginRank == "admin") {
    $xoo = " or_unlink = '0' ";
} elseif ($loginRank == "delivery") {
    $xoo = " or_unlink = '0' and or_delivery_user = '$loginId' ";
} else {
    $xoo = " or_unlink = '10' ";
}

$update = $con->prepare("UPDATE orders SET or_scan = 1 WHERE or_id = ? AND $xoo ");
$update->execute([$code]);

// البحث عن الطلبية
$stmt = $con->prepare("SELECT * FROM orders WHERE or_scan = ? AND   $xoo ");
$stmt->execute([1]);
$orders = $stmt->fetchAll();






if ($orders) {







// config package 
?>


<!-- بطاقة التحكم الثابتة -->
<div class="position-fixed bottom-0 start-0 end-0 bg-white shadow-lg border-top py-3 px-4 zindex-tooltip" style="z-index: 1055;">
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3">

<!-- زر تحديد الكل -->
<button id="selectAllBtn" class="btn btn-sm btn-outline-dark">
<i class="fa-regular fa-square-check"></i> Tout sélectionner
</button>

<!-- تغيير الحالة -->
<button id="transferBtn"  data-bs-toggle='modal' data-bs-target='#modal_state'  class="btn btn-sm btn-warning">
<i class="fa-solid fa-truck"></i> L'état
</button>


<?php if ($loginRank == "admin"):?>

<!-- تمرير الطرد -->
<button id="transferBtn"  data-bs-toggle='modal' data-bs-target='#modal_delivery'  class="btn btn-sm btn-info">
<i class="fa-solid fa-truck"></i> Transférer Au Livreur
</button>

<!-- طباعة الملصق -->
<button id="printBtn"  data-bs-toggle='modal' data-bs-target='#modal_print'  class="btn btn-sm btn-success">
<i class="fa-solid fa-print"></i> Imprimer L'autocollant
</button>

<?php endif; ?>


</div>
</div>







<?php if (($loginRank == "admin")||($loginRank == "agency")||($loginRank == "delivery")): ?>

<div class='modal fade' id='modal_state' tabindex='-1'>
<div class='modal-dialog modal-dialog-centered'>
<div class='modal-content'>
<div class='modal-header'>
<h5 class='modal-title'>Changer l'état de livraison</h5>
<button type='button' class='btn-close' data-bs-dismiss='modal'></button>
</div>

<div class='modal-body text-center'>
<form id="state_form" onsubmit="return collectSelectedIds('state_form')">
<input type="hidden" name="order_id" value="">


<label for="state_select">Choisir une nouvelle état:</label>
<select class='js-select state_select w-100' name="state_id" required>
<option value="">Sélectionner une état</option>
<?php
$stmt = $con->prepare("SELECT * FROM state WHERE state_unlink = '0' ORDER BY state_name ASC");
$stmt->execute();
$states = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($states as $state) {
echo '<option value="' . $state['state_id'] . '">' . $state['state_name'] . '</option>';
}
?>
</select>

<div id="report_date_div" style="display: none;">
<label for="postponed_date">Nouvelle date de report:</label>
<input type="date" id="postponed_date" name="postponed_date">
</div>

<label for="note">Note / Justification (toutes les autres situations):</label>
<input type="text" id="note" name="note" placeholder="Ex: Client a demandé un report">

<button type="submit" class="btn btn-primary mt-3">Mettre à jour la commande</button>
</form>


<div id="result_state"></div>

</div>


</div>
</div>
</div>
<?php endif; ?>









<?php if (($loginRank == "admin")||($loginRank == "agency")): ?>

<div class='modal fade' id='modal_delivery' tabindex='-1'>
<div class='modal-dialog modal-dialog-centered'>
<div class='modal-content'>
<div class='modal-header'>
<h5 class='modal-title'>Transférer Au Livreur</h5>
<button type='button' class='btn-close' data-bs-dismiss='modal'></button>
</div>

<div class='modal-body text-center'>
<form id="delivery_form" onsubmit="return collectSelectedIds('delivery_form')">
<input type="hidden" name="order_id" value="">

<label for="state_select">Choisir livreur</label>
<select  class='js-select w-100' name="delivery_id" required>
<option value="">Sélectionner un livreur</option>
<?php
$stmt = $con->prepare("SELECT * FROM users WHERE user_unlink = '0' AND user_rank = 'delivery' ORDER BY user_name ASC");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($users as $user) {
echo '<option value="' . $user['user_id'] . '">' . $user['user_name'] . '</option>';
}
?>
</select>
<button type="submit" class="btn btn-primary mt-3">Mettre à jour</button>
</form>
<div id="result_delivery"></div>
</div>
</div>
</div>
</div>
<?php endif; ?>








<?php if (($loginRank == "admin")||($loginRank == "agency")||($loginRank == "delivery")): ?>

<div class='modal fade' id='modal_print' tabindex='-1'>
<div class='modal-dialog modal-dialog-centered'>
<div class='modal-content'>
<div class='modal-header'>
<h5 class='modal-title'>Imprimer ticket</h5>
<button type='button' class='btn-close' data-bs-dismiss='modal'></button>
</div>

<div class='modal-body text-center'>

<form id="print_form" onsubmit="return collectSelectedIds('print_form')">
<input type="hidden" name="order_id" value="">

<label for="state_select">Choisir Taille</label>

<select  class='js-select w-100' name="print_id" required>
<option value="0">Sélectionner Taille</option>
<option value="a4">A4</option>
<option value="10">10x10</option>
</select>

<button type="submit" class="btn btn-primary mt-3">Mettre à jour</button>
</form>

<div id="result_print"></div>
</div>
</div>
</div>
</div>
<?php endif; ?>



















<?php









function getStateActivity($order_id, $con) {
$stmt = $con->prepare("SELECT sa.*, 
u.user_name, 
s.state_name 
FROM state_activity sa 
LEFT JOIN users u ON u.user_id = sa.sa_user 
LEFT JOIN state s ON s.state_id = sa.sa_state 
WHERE sa.sa_order = ? 
ORDER BY sa.sa_id DESC");

$stmt->execute([$order_id]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($rows) == 0) {
echo "<div class='alert alert-info'>--</div>";
}else{

echo '<ul class="timeline">';
foreach ($rows as $row) {
echo '
<li class="timeline-item mb-5">
<h6 class="fw-bold">' . html_entity_decode($row["state_name"]) . '</h6>
<p class="text-muted mb-2"><small><i class="bi bi-calendar3"></i> ' . $row["sa_date"] . '</small></p>
<p class="mb-1">Remarque : ' . html_entity_decode($row["sa_note"]) . '</p>
<p class="text-muted"><small>Mis à jour par :  ' . html_entity_decode($row["user_name"]) . '</small></p>
</li>';
}
echo '</ul>';
}
}

include get_file("files/sql/get/fetch_orders");






} else {
echo "❌ Aucune commande trouvée avec l'ID : " . htmlspecialchars($code);
}

} catch (PDOException $e) {
echo "❌ Erreur base de données : " . $e->getMessage();
}
} else {
echo "❌ Requête invalide.";
}
?>




<script>


// عند الضغط على زر تحديد الكل
$('#selectAllBtn').on('click', function() {
  let checkboxes = $('.order-checkbox');
  let allChecked = checkboxes.length && checkboxes.filter(':checked').length === checkboxes.length;

  // إذا الكل محدد ➜ ألغِ التحديد
  if (allChecked) {
    checkboxes.prop('checked', false);
  } else {
    checkboxes.prop('checked', true);
  }

  updateHiddenField();
  updateSelectAllBtnText();
});

// عند تغيير أي Checkbox بشكل يدوي
$(document).on('change', '.order-checkbox', function() {
  updateHiddenField();
  updateSelectAllBtnText();
});


function updateSelectAllBtnText() {
let checkboxes = document.querySelectorAll('.order-checkbox');
if (checkboxes.length === 0) {
$('#selectAllBtn').text('Sélectionner tout');
return;
}
let allChecked = Array.from(checkboxes).every(cb => cb.checked);
$('#selectAllBtn').text(allChecked ? 'Désélectionner tout' : 'Sélectionner tout');
}

function updateHiddenField() {
let selectedIds = $('.order-checkbox:checked').map(function() {
return $(this).val();
}).get().join(',');
console.log("Selected IDs:", selectedIds);
$('input[name="order_id"]').val(selectedIds);
}

function onOrdersListUpdated() {
console.log("onOrdersListUpdated called");
updateSelectAllBtnText();
updateHiddenField();
// إعادة تهيئة select2 بعد تحميل المحتوى الديناميكي
$('.js-select').select2();
}




$('#state_form').on('submit', function(e) {
e.preventDefault();
$.ajax({
url: 'config_orders?do=state',
method: 'POST',
data: $(this).serialize(),
success: function(response) {
$('#result_state').html('<div style="color: green;">' + response + '</div>');
},
error: function() {
$('#result_state').html('<div style="color: red;">حدث خطأ في الفورم 1</div>');
}
});
});

$('#delivery_form').on('submit', function(e) {
e.preventDefault();
$.ajax({
url: 'config_orders?do=delivery',
method: 'POST',
data: $(this).serialize(),
success: function(response) {
$('#result_delivery').html('<div style="color: green;">' + response + '</div>');
},
error: function() {
$('#result_delivery').html('<div style="color: red;">Form Error</div>');
}
});
});

$('#print_form').on('submit', function(e) {
e.preventDefault();
$.ajax({
url: 'config_orders?do=print',
method: 'POST',
data: $(this).serialize(),
success: function(response) {
$('#result_print').html('<div style="color: green;">' + response + '</div>');
},
error: function() {
$('#result_print').html('<div style="color: red;">حدث خطأ في الفورم 3</div>');
}
});
});


$('.state_select').on('change', function() {
let stateId = $(this).val();

if (stateId == 5) { 
$('#report_date_div').show();
} else {
$('#report_date_div').hide();
}
});
</script>

