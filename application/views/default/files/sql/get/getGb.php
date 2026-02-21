<?php
include get_file("files/sql/get/os_settings");
include get_file("files/sql/get/session");
include get_file("files/sql/get/functions");

global $con;

$page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
$display = isset($_POST['display']) ? (int)$_POST['display'] : 10;
$perPage = in_array($display, [10, 50, 100, 200]) ? $display : 10;

$start = ($page - 1) * $perPage;

$search = isset($_POST['search']) ? trim($_POST['search']) : '';
$city = isset($_POST['city']) ? (int)$_POST['city'] : 0;
$warehouse = isset($_POST['warehouse']) ? (int)$_POST['warehouse'] : 0;

$where = [];
$params = [];

if ($search !== '') {
$where[] = "(sc_id LIKE :search OR sc_city_name LIKE :search OR sc_warehouse_name LIKE :search)";
$params[':search'] = "%$search%";
}
if ($city > 0) {
$where[] = "sc_city = :city";
$params[':city'] = $city;
}
if ($warehouse > 0) {
$where[] = "sc_warehouse = :warehouse";
$params[':warehouse'] = $warehouse;
}

$whereSQL = count($where) ? "WHERE " . implode(" AND ", $where) : "";

// حساب عدد النتائج
$stmtCount = $con->prepare("SELECT COUNT(*) FROM shipping_charges $whereSQL");
$stmtCount->execute($params);
$totalRows = $stmtCount->fetchColumn();
$totalPages = ceil($totalRows / $perPage);

// استعلام البيانات
$sql = "SELECT * FROM shipping_charges $whereSQL ORDER BY sc_id DESC LIMIT $start, $perPage";
$stmt = $con->prepare($sql);
$stmt->execute($params);
$data = $stmt->fetchAll();
?>

<div class="row my-3 text-center">
<?php if (count($data) > 0): ?>
<?php foreach ($data as $row): ?>
<div class="col-md-4">
<?php
$stmt = $con->prepare("SELECT * FROM warehouse WHERE wh_id = ? LIMIT 1");
$stmt->execute([$row['sc_warehouse']]);
$warehouse = $stmt->fetch();

$stmt = $con->prepare("SELECT * FROM city WHERE city_id = ? LIMIT 1");
$stmt->execute([$row['sc_city']]);
$city = $stmt->fetch();

$id = "formId" . $row['sc_id'];
$result = "data_result" . $row['sc_id'];
$action = "tarifs_update";
$method = "post";
formAwdStart($id, $result, $action, $method);
?>

<input type="hidden" name="id" value="<?= $row['sc_id'] ?>" />

<div class="card mb-3 shadow-sm">
<div class="card-body">
<div class="row">
<p class="col-12">
<h5 class="card-title"><?= htmlspecialchars($warehouse['wh_name']) ?> → <?= htmlspecialchars($city['city_name']) ?></h5>
</p>

<p class="col-4">
<strong>Livraison:</strong> 
<input name="delivery" type="number" class="form-control" value="<?= htmlspecialchars($row['sc_delivery']) ?>">
</p>

<p class="col-4">
<strong>Annulation:</strong> 
<input name="cancel" type="number" class="form-control" value="<?= htmlspecialchars($row['sc_cancel']) ?>"> 
</p>

<p class="col-4">
<strong>Retour:</strong> 
<input name="return" type="number" class="form-control" value="<?= htmlspecialchars($row['sc_return']) ?>">
</p>

<p class="col-12">
<div id="<?= $result ?>" class=""></div>
</p>

<div class="d-flex justify-content-between mt-3">
<button class="btn btn-primary btn-sm" type="submit">
<i class="fas fa-edit"></i> Modifier
</button>

<button onclick="deleteTarif('<?= $row['sc_id'] ?>', 'globale')" class="btn btn-sm btn-danger" type="button">
<i class="fas fa-trash-alt"></i> Supprimer
</button>
</div>
</div>
</div>
</div>

<?php formAwdEnd(); ?>
</div>
<?php endforeach; ?>
<?php else: ?>
<div class="col-12">
<div class="alert alert-warning text-center">Aucune tarification trouvée.</div>
</div>
<?php endif; ?>

<?php
echo renderPagination($totalRows, $page, $perPage);
?>
</div>

<script>
function deleteTarif(id, type = 'globale') {
if (!confirm("Voulez-vous vraiment supprimer cette tarification ?")) return;

$.ajax({
url: "tarifs_delete",
type: "POST",
data: { id: id, type: type },
success: function(response) {
$('#formId' + id).closest('.col-md-4').fadeOut('slow', function () {
$(this).remove();
});
},
error: function(xhr) {
alert("Erreur lors de la suppression: " + xhr.responseText);
}
});
}
</script>
