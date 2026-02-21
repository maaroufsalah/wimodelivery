<?php
include get_file("files/sql/get/os_settings");
include get_file("files/sql/get/session");
include get_file("files/sql/get/functions");
global $con;

if ($loginRank != "user") {
exit("<div class='alert alert-danger'>Non autorisé</div>");
}

$page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
$perPage = isset($_POST['display']) ? (int)$_POST['display'] : 50;
$start = ($page - 1) * $perPage;

$search     = trim($_POST['search'] ?? '');
$city       = (int)($_POST['city'] ?? 0);
$activation = (int)($_POST['activation'] ?? -1);
$admin      = trim($_POST['admin'] ?? '');
$rank       = trim($_POST['rank'] ?? '');

$where  = [];
$params = [];

if ($search !== '') {
$where[] = "(user_name LIKE :search OR user_email LIKE :search OR user_phone LIKE :search)";
$params[':search'] = "%$search%";
}
if ($city > 0) {
$where[] = "user_city = :city";
$params[':city'] = $city;
}
if ($activation !== -1) {
$where[] = "user_state = :activation";
$params[':activation'] = $activation;
}

if ($admin !== '') {
$where[] = "user_admin = :admin";
$params[':admin'] = $admin;
}


if ($rank !== '') {
$where[] = "user_rank = :rank";
$params[':rank'] = $rank;
}


if ($loginId !== '') {
$where[] = "user_aide = :aide AND user_unlink = '0' ";
$params[':aide'] = $loginId;
}




$whereSQL = count($where) ? " WHERE " . implode(" AND ", $where) : " ";

// عدد المستخدمين
$stmtCount = $con->prepare("SELECT COUNT(*) FROM users $whereSQL");
$stmtCount->execute($params);
$totalUsers = $stmtCount->fetchColumn();
$totalPages = ceil($totalUsers / $perPage);

// جلب المستخدمين
$sql = "SELECT * FROM users $whereSQL ORDER BY user_id DESC LIMIT $start, $perPage";
$stmt = $con->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();

// المدن كلها دفعة واحدة لتحسين الأداء
$cities = $con->query("SELECT city_id, city_name FROM city")->fetchAll(PDO::FETCH_KEY_PAIR);
?>

<div class="row text-center">
<?php if ($users): foreach ($users as $user): ?>
<div class="col-12 col-sm-6 col-md-4 col-lg-3 mb-4">
<div class="card shadow-sm h-100 profile-card">
<div class="card-body text-center">
<?php if ($user['user_avatar']): ?>
<img src="uploads/profile/<?= htmlspecialchars($user['user_avatar']) ?>" class="rounded-circle mb-3 border" style="width: 90px; height: 90px; object-fit: cover;">
<?php else: ?>
<div class="rounded-circle bg-light border mb-3 d-flex align-items-center justify-content-center" style="width: 90px; height: 90px;">
<i class="bi bi-person fs-1 text-muted"></i>
</div>
<?php endif; ?>

<h5 class="fw-bold mb-1"><?= htmlspecialchars($user['user_owner']) ?></h5>
<h6 class="fw-bold mb-1">(<?= htmlspecialchars($user['user_name']) ?>)</h6>
<p class="text-muted mb-1"><i class="bi bi-envelope me-1"></i> <b><?= htmlspecialchars($user['user_email']) ?></b></p>
<p class="text-muted mb-2"><i class="bi bi-telephone me-1"></i> <b><?= htmlspecialchars($user['user_phone']) ?></b></p>
<p><span class="badge bg-secondary"><?= htmlspecialchars($cities[$user['user_city']] ?? '---') ?></span></p>

<p>
<?php
$ranks = [
'admin' => 'bg-success',
'user' => 'bg-primary',
'agency' => 'bg-info text-dark',
'delivery' => 'bg-warning text-dark'
];
$rankClass = $ranks[$user['user_rank']] ?? 'bg-dark';
echo "<span class='badge $rankClass'>" . ucfirst($user['user_rank']) . "</span>";
?>
</p>
<p>
<?= $user['user_state'] ? '<span class="badge bg-success">Activé</span>' : '<span class="badge bg-danger">Non Activé</span>' ?>
</p>



<?php if ($loginRank == "user"):?>
<a href="?do=edit&id=<?= md5($user['user_id']) ?>" class="text-info fs-5 me-2"><i class="fa-solid fa-pen-to-square"></i></a>
<?php endif; ?>

<?php if ($loginRank == "user"):?>
<a data-bs-toggle="modal" data-bs-target="#modalDelete<?= $user['user_id'] ?>" class="text-danger fs-5"><i class="fa-solid fa-trash"></i></a>
<?php endif; ?>



</div>
</div>
</div>

<?php if ($loginRank == "user"):?>
<!-- Modal حذف -->
<div class="modal fade" id="modalDelete<?= $user['user_id'] ?>" tabindex="-1">
<div class="modal-dialog modal-dialog-centered">
<div class="modal-content">
<div class="modal-header">
<h5 class="modal-title">Supprimer</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body text-center">
<p>Êtes-vous sûr ?</p>
<a class="btn btn-success" href="dataUnlink?do=user&dataUnlinkId=<?= md5($user['user_id']) ?>">Oui</a>
</div>
</div>
</div>
</div>
<?php endif; ?>


<?php endforeach; else: ?>
<div class="col-12">
<div class="alert alert-warning">Aucun utilisateur trouvé.</div>
</div>
<?php endif; ?>
</div>

<!-- Pagination -->
<!-- Pagination -->
<?php if ($totalPages > 1): ?>
<div class="col-12">
<div class="alert alert-warning">Total : <?= $totalUsers; ?></div>
</div>
<div class="text-center mt-3">
<ul class="pagination justify-content-center">
<!-- زر السابق -->
<?php if ($page > 1): ?>
<li class="page-item">
<a href="#" class="page-link" data-page="<?= $page - 1 ?>">«</a>
</li>
<?php endif; ?>

<?php
$adjacents = 2;
$startPage = max(1, $page - $adjacents);
$endPage = min($totalPages, $page + $adjacents);

// أول صفحة
if ($startPage > 1) {
echo '<li class="page-item"><a href="#" class="page-link" data-page="1">1</a></li>';
if ($startPage > 2) {
echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
}
}

// الصفحات الوسطى
for ($i = $startPage; $i <= $endPage; $i++) {
$active = ($i == $page) ? 'active' : '';
echo "<li class='page-item $active'><a href='#' class='page-link' data-page='$i'>$i</a></li>";
}

// آخر صفحة
if ($endPage < $totalPages) {
if ($endPage < $totalPages - 1) {
echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
}
echo "<li class='page-item'><a href='#' class='page-link' data-page='$totalPages'>$totalPages</a></li>";
}
?>

<!-- زر التالي -->
<?php if ($page < $totalPages): ?>
<li class="page-item">
<a href="#" class="page-link" data-page="<?= $page + 1 ?>">»</a>
</li>
<?php endif; ?>
</ul>
</div>
<?php endif; ?>
