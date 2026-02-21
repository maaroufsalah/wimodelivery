<?php
$bootstrap = new Bootstrap();
$bootstrap->runBootstrap();

include get_file("files/sql/get/os_settings");
include get_file("files/sql/get/session");
include get_file("files/sql/get/functions");

global $con;

// استقبال النوع من GET
$type = $_GET['do'] ?? '';
$typeName = '';
switch ($type) {
case 'pickup': $typeName = 'Bon De Ramassage'; break;
case 'outlog_user': $typeName = 'Bon De Retour Client'; break;
case 'outlog_delivery': $typeName = 'Bon De Retour Livreur'; break;
default: $type = ''; break;
}

// الفلاتر
$display = isset($_POST["display"]) ? $_POST["display"] : 10;
$limit = in_array($display, [10, 50, 100, 200]) ? $display : 10;

$page = isset($_POST['page']) && $_POST['page'] > 1 ? (int)$_POST['page'] : 1;
$start = ($page - 1) * $limit;

$search = trim($_POST['search'] ?? '');
$userId = isset($_POST['user']) ? (int)$_POST['user'] : 0;

// بناء شرط where
$where = "1";
$params = [];

if ($loginRank !== 'admin') {
$where .= " AND lp_user = :loginUser";
$params[':loginUser'] = $loginId;
}

if ($loginRank == 'aide') {
$where .= " AND lp_user = :loginUser";
$params[':loginUser'] = $loginUser['user_aide'];
}

if ($type !== '') {
$where .= " AND lp_type = :type";
$params[':type'] = $type;
}
if ($search !== '') {
$where .= " AND lp_id LIKE :search";
$params[':search'] = "%$search%";
}
if ($userId > 0) {
$where .= " AND lp_user = :userId";
$params[':userId'] = $userId;
}

// عدد النتائج
$countSQL = "SELECT COUNT(*) FROM log_print WHERE $where";
$countStmt = $con->prepare($countSQL);
foreach ($params as $key => $value) {
$countStmt->bindValue($key, $value);
}
$countStmt->execute();
$total_data = $countStmt->fetchColumn();

// استعلام البيانات
$dataSQL = "SELECT * FROM log_print WHERE $where ORDER BY lp_id DESC LIMIT $start, $limit";
$dataStmt = $con->prepare($dataSQL);
foreach ($params as $key => $value) {
$dataStmt->bindValue($key, $value);
}
$dataStmt->execute();
$rows = $dataStmt->fetchAll();

if (count($rows) > 0) {

echo "<div class='table-responsive'>";
echo "<table class='table table-bordered table-hover align-middle text-center'>";
echo "<thead class='table-light'>";
echo "<tr>";
echo "<th>Bon</th>";
echo "<th>Compte</th>";
echo "<th>Créé par</th>";
echo "<th>Nombre des colis</th>";
echo "<th>Actions</th>";
echo "</tr>";
echo "</thead>";
echo "<tbody>";

foreach ($rows as $row) {
// بيانات إضافية
$userName = get_value("users", "user_name", "user_id = ?", [$row['lp_user']]);
$viaName  = get_value("users", "user_name", "user_id = ?", [$row['lp_via']]);
$orderCount = get_value_sql("SELECT COUNT(*) FROM orders WHERE FIND_IN_SET(or_id, ?) > 0", [$row['lp_gid']]);

echo "<tr>";
echo "<td>#{$row['lp_id']}<br><small>{$row['lp_date']}</small></td>";

echo "<td>";
echo "$userName<br>";
echo "</td>";


echo "<td>";
echo "$viaName";
echo "</td>";

echo "<td>";
echo "$orderCount";
echo "</td>";




echo "<td>";

echo "
<a target='_blank' href='print_log?id=" . md5($row['lp_id']) . "' class='btn btn-sm btn-outline-dark mx-2'>
<i class='fa fa-print'></i>
</a>
";

if ($type == "pickup") {
echo "<a target='_blank' href='print_sticker?do=10&orders_ids={$row['lp_gid']}' class='btn btn-sm btn-outline-primary mb-1'>10 mm</a> ";
echo "<a target='_blank' href='print_sticker?do=a4&orders_ids={$row['lp_gid']}' class='btn btn-sm btn-outline-secondary mb-1'>A4</a>";
}


if ($loginRank == "admin"){
print "
<a data-bs-toggle='modal' data-bs-target='#modalDelete".$row['lp_id']."' class='btn btn-sm btn-danger mx-2'>
<i class='fa fa-trash'></i>
</a>
";
}


echo "</td>";
echo "</tr>";

?>


<?php if ($loginRank == "admin"): ?>
<div class='modal fade' id='modalDelete<?= $row['lp_id']; ?>' tabindex='-1'>
<div class='modal-dialog modal-dialog-centered'>
<div class='modal-content'>
<div class='modal-header'>
<h5 class='modal-title'>Supprimer un élément</h5>
<button type='button' class='btn-close' data-bs-dismiss='modal'></button>
</div>
<div class='modal-body text-center'>
<h6>Êtes-vous sûr de vouloir supprimer cet élément ?</h6>
<a class='btn btn-success' href='dataUnlink?do=logs&id=<?= md5($row['lp_id']); ?>'>Oui, je veux</a>
</div>
</div>
</div>
</div>
<?php endif; ?>

<?php

}

echo "</tbody>";
echo "</table>";
echo "</div>"; // نهاية div table-responsive

} else {
echo '<div class="alert alert-warning">Aucun résultat trouvé</div>';
}

echo renderPagination($total_data, $page, $limit);
?>
