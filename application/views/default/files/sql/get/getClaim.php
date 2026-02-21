<?php
global $con;
include get_file("files/sql/get/session");

// استقبال البيانات من AJAX
$page    = isset($_POST['page']) ? (int)$_POST['page'] : 1;
$search  = $_POST['search'] ?? '';
$display = isset($_POST['display']) ? (int)$_POST['display'] : 10;
$user    = isset($_POST['user']) ? (int)$_POST['user'] : 0;
$state   = isset($_POST['state']) ? (int)$_POST['state'] : -1;

$page = max($page, 1);
$display = max($display, 10);
$offset = ($page - 1) * $display;

// قاعدة الاستعلام الرئيسية
$params = [];
$baseQuery = "FROM claim c 
              JOIN users u ON c.claim_user = u.user_id 
              WHERE c.claim_unlink = 0"; // ✅ filter here

// التحكم حسب الرتبة
if ($loginRank === "user") {
    $baseQuery .= " AND c.claim_user = :loginId";
    $params[':loginId'] = $loginId;
} elseif ($loginRank === "aide") {
    $baseQuery .= " AND c.claim_user = :aideId";
    $params[':aideId'] = $loginUser['user_aide'];
}

// فلترة البحث
if (!empty($search)) {
    $baseQuery .= " AND c.claim_id LIKE :search";
    $params[':search'] = "%$search%";
}
if ($user > 0) {
    $baseQuery .= " AND c.claim_user = :user";
    $params[':user'] = $user;
}
if (in_array($state, [0, 1, 2], true)) {
    $baseQuery .= " AND c.claim_state = :state";
    $params[':state'] = $state;
}

// ----------- استعلام البيانات -----------
$query = "SELECT c.*, u.user_name 
          $baseQuery 
          ORDER BY c.claim_id DESC 
          LIMIT :offset, :display";$stmt = $con->prepare($query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
}
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':display', $display, PDO::PARAM_INT);
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ----------- استعلام عدد الصفوف -----------
$countQuery = "SELECT COUNT(*) $baseQuery";
$stmtTotal = $con->prepare($countQuery);
foreach ($params as $key => $value) {
    $stmtTotal->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
}
$stmtTotal->execute();
$totalRows = $stmtTotal->fetchColumn();
$totalPages = ($totalRows > 0) ? ceil($totalRows / $display) : 0;

// ----------- بناء HTML -----------
ob_start();

if (count($results) > 0) {
?>
<div class="table-responsive">
<table class="table table-bordered table-hover align-middle text-center">
    <thead class="table-dark">
        <tr>
            <th>Réclamation N°</th>
            <th>Vendeur</th>
            <th>Date</th>
            <th>Colis</th>
            <th>Détails</th>
            <th>Etat</th>
            <?php if ($loginRank === "admin"): ?>
            <th>Actions</th>
            <?php endif; ?>
        </tr>
    </thead>
    <tbody>
<?php
    foreach ($results as $row) {


$con->query("
UPDATE claim SET claim_seen = 1 WHERE claim_seen = 0 AND claim_unlink = '0' AND claim_id = '".$row['claim_id']."'
");



        $stmt = $con->prepare("SELECT * FROM orders WHERE or_id = :or_id LIMIT 1");
        $stmt->execute([':or_id' => $row['claim_orders']]);
        $order = $stmt->fetch();

        switch ($row['claim_state']) {
            case 0: $stateHtml = "<span class='text-danger'><i class='fa-solid fa-spinner'></i> En Cours</span>"; break;
            case 1: $stateHtml = "<span class='text-info'><i class='fa-solid fa-microchip'></i> En traitement</span>"; break;
            case 2: $stateHtml = "<span class='text-success'><i class='fa-regular fa-circle-check'></i> Traité</span>"; break;
            default: $stateHtml = "-"; break;
        }
?>
<tr>
    <td>#<?= ($row['claim_id']) ?></td>
    <td><?= ($row['user_name']) ?></td>
    <td><?= ($row['claim_date']) ?></td>
    <td>
        <?= ($row['claim_orders']) ?>
        <?= $order ? "<br>" . date('Y-m-d H:i:s', strtotime($order['or_created'])) : '' ?>
    </td>
    <td><?= nl2br(($row['claim_note'])) ?></td>
    <td><?= $stateHtml ?></td>
    <?php if ($loginRank === "admin"): ?>
    <td style="white-space: nowrap;">
        <a href="updateClaim?id=<?= md5($row['claim_id']) ?>&state=0" class="btn btn-danger btn-sm" title="En Cours">
            <i class="fa-solid fa-spinner"></i>
        </a>
        <a href="updateClaim?id=<?= md5($row['claim_id']) ?>&state=1" class="btn btn-info btn-sm" title="En traitement">
            <i class="fa-solid fa-microchip"></i>
        </a>
        <a href="updateClaim?id=<?= md5($row['claim_id']) ?>&state=2" class="btn btn-success btn-sm" title="Traité">
            <i class="fa-regular fa-circle-check"></i>
        </a>
        <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#modalDelete<?= $row['claim_id'] ?>" title="Supprimer">
            <i class="fa-solid fa-trash"></i>
        </button>

        <!-- Modal Delete -->
        <div class="modal fade" id="modalDelete<?= $row['claim_id'] ?>" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title">Supprimer un élément</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
              </div>
              <div class="modal-body text-center">
                <p>Êtes-vous sûr de bien vouloir supprimer cet élément ?</p>
                <a class="btn btn-success" href="dataUnlink?do=claim&dataUnlinkId=<?= md5($row['claim_id']) ?>">Oui, je veux</a>
              </div>
            </div>
          </div>
        </div>
    </td>
    <?php endif; ?>
</tr>
<?php
    }
?>
    </tbody>
</table>
</div>
<?php
} else {
?>
<div class="text-center my-5">
    <i class="fa-solid fa-file-half-dashed fa-3x mb-2"></i>
    <h6>Aucun résultat trouvé</h6>
</div>
<?php
}
?>

<hr>
<div>Total : <b><?= $totalRows ?></b></div>
<hr>

<div class="pagination-wrapper text-center">
  <ul class="pagination justify-content-center">
    <?php if ($page > 1): ?>
    <li class="page-item"><a href="#" class="page-link" data-page="<?= $page - 1 ?>">«</a></li>
    <?php endif; ?>

    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
    <li class="page-item<?= ($i == $page) ? ' active' : '' ?>">
      <a href="#" class="page-link" data-page="<?= $i ?>"><?= $i ?></a>
    </li>
    <?php endfor; ?>

    <?php if ($page < $totalPages): ?>
    <li class="page-item"><a href="#" class="page-link" data-page="<?= $page + 1 ?>">»</a></li>
    <?php endif; ?>
  </ul>
</div>

<?php
echo ob_get_clean();
?>
