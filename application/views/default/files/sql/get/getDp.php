<?php
include get_file("files/sql/get/os_settings");
include get_file("files/sql/get/session");
include get_file("files/sql/get/functions");

global $con;

$page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
$perPage = isset($_POST['display']) ? (int)$_POST['display'] : 10;
$start = ($page - 1) * $perPage;

$search = isset($_POST['search']) ? trim($_POST['search']) : '';
$cityFilter = isset($_POST['city']) ? (int)$_POST['city'] : 0;
$warehouseFilter = isset($_POST['warehouse']) ? (int)$_POST['warehouse'] : 0;
$user = isset($_POST['user']) ? (int)$_POST['user'] : 0;

$where = [];
$params = [];

if ($search !== '') {
$where[] = "(p.pr_id LIKE :search 
OR c.city_name LIKE :search 
OR w.wh_name LIKE :search 
OR u.user_name LIKE :search)";
$params[':search'] = "%$search%";
}
if ($cityFilter > 0) {
$where[] = "p.pr_city = :city";
$params[':city'] = $cityFilter;
}
if ($warehouseFilter > 0) {
$where[] = "p.pr_warehouse = :warehouse";
$params[':warehouse'] = $warehouseFilter;
}
if ($user > 0) {
$where[] = "p.pr_user_delivery = :user";
$params[':user'] = $user;
}

$whereSQL = count($where) ? "WHERE " . implode(" AND ", $where) : "";

// حساب عدد النتائج
$stmtCount = $con->prepare("
SELECT COUNT(*) 
FROM pricing p 
LEFT JOIN warehouse w ON w.wh_id = p.pr_warehouse
LEFT JOIN city c ON c.city_id = p.pr_city
LEFT JOIN users u ON u.user_id = p.pr_user_delivery
$whereSQL
");
$stmtCount->execute($params);
$totalRows = $stmtCount->fetchColumn();
$totalPages = ceil($totalRows / $perPage);

// استعلام البيانات مع JOIN
$sql = "
SELECT p.*, w.wh_name, c.city_name, u.user_name
FROM pricing p
LEFT JOIN warehouse w ON w.wh_id = p.pr_warehouse
LEFT JOIN city c ON c.city_id = p.pr_city
LEFT JOIN users u ON u.user_id = p.pr_user_delivery
$whereSQL
ORDER BY p.pr_id DESC
LIMIT $start, $perPage
";
$stmt = $con->prepare($sql);
$stmt->execute($params);
$data = $stmt->fetchAll();
?>

<div class="row my-3 text-center">
<?php if (count($data) > 0): ?>
<?php foreach ($data as $row): ?>
<div class="col-md-4">

<?php
$id = "formId" . $row['pr_id'];
$result = "data_result" . $row['pr_id'];
$action = "tarifs_update?do=delivery";
$method = "post";
formAwdStart($id, $result, $action, $method);
?>


<input type="hidden" name="id" value="<?= (int)$row['pr_id'] ?>" />

<div class="card mb-3 shadow-sm">
<div class="card-body">
<div class="row">
<p class="col-12">
<h5 class="card-title">
<?= htmlspecialchars($row['wh_name']) ?> → <?= htmlspecialchars($row['city_name']) ?>
</h5>
<h6 class='text-info'>(<?= htmlspecialchars($row['user_name']) ?>)</h6>
</p>

<p class="col-4">
<strong>Livraison:</strong> 
<input name="delivery" type="number" class="form-control" value="<?= (float)$row['pr_delivery'] ?>">
</p>

<p class="col-4">
<strong>Annulation:</strong> 
<input name="cancel" type="number" class="form-control" value="<?= (float)$row['pr_cancel'] ?>">
</p>

<p class="col-4">
<strong>Retour:</strong> 
<input name="return" type="number" class="form-control" value="<?= (float)$row['pr_return'] ?>">
</p>

<p class="col-12">
<div id="<?= $result ?>" class=""></div>
</p>

<div class="d-flex justify-content-between mt-3">
<button class="btn btn-primary btn-sm" type="submit">
<i class="fas fa-edit"></i> Modifier
</button>

<button onclick="deleteTarif('<?= (int)$row['pr_id'] ?>', 'delivery')" 
class="btn btn-sm btn-danger" type="button">
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
</div>

<?php
echo renderPagination($totalRows, $page, $perPage);
?>

<script>
function deleteTarif(id, type = 'delivery') {
if (!confirm("Voulez-vous vraiment supprimer cette tarification ?")) return;

$.ajax({
url: "tarifs_delete",
type: "POST",
data: {
id: id,
type: type
},
success: function(response) {
if (response.trim() === "success") {
$('#formId' + id).closest('.col-md-4').fadeOut('slow', function () {
$(this).remove();
});
} else {
alert("Erreur: " + response);
}
},
error: function(xhr) {
alert("Erreur lors de la suppression: " + xhr.responseText);
}
});
}
</script>
