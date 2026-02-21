<?php
// ✅ التهيئة
include get_file("files/sql/get/os_settings");
include get_file("files/sql/get/session");
include get_file("files/sql/get/functions");

global $con;

// ✅ pagination
$display = isset($_POST["display"]) ? (int)$_POST["display"] : 10;
$limit   = in_array($display, [10, 50, 100, 200]) ? $display : 10;

$page  = isset($_POST['page']) && (int)$_POST['page'] > 1 ? (int)$_POST['page'] : 1;
$start = ($page - 1) * $limit;

// ✅ جلب sections & sliders
$sections = $con->query("SELECT sec_id, sec_name FROM sections WHERE sec_unlink = '0'")->fetchAll(PDO::FETCH_ASSOC);
$sliders  = $con->query("SELECT sli_id, sli_name FROM slider WHERE sli_unlink = '0'")->fetchAll(PDO::FETCH_ASSOC);

// ✅ الفئات
$categories = $con->query("SELECT * FROM classes WHERE c_unlink = '0' ORDER BY c_name ASC")->fetchAll(PDO::FETCH_ASSOC);
$subcategoriesByCategory = [];
if ($categories) {
$catIds = implode(',', array_map('intval', array_column($categories, 'c_id')));
$subs = $con->query("SELECT * FROM s_classes WHERE sub_category IN ($catIds)")->fetchAll(PDO::FETCH_ASSOC);
foreach ($subs as $sub) {
$subcategoriesByCategory[$sub['sub_category']][] = $sub;
}
}

// ✅ filters
$params = [];
$filters = " WHERE p.p_unlink = '0'";

if ($loginRank === "admin") {
// لا شرط إضافي
} elseif ($loginRank === "user") {
$filters .= " AND p.p_user = ?";
$params[] = $loginId;
} elseif ($loginRank === "aide") {
$filters .= " AND p.p_user = ?";
$params[] = $loginUser['user_aide'] ?? 0;
} else {
$filters .= " AND 1=0";
}

if (!empty($_POST['search'])) {
$search = trim($_POST['search']);
$filters .= " AND (p.p_name LIKE ? OR p.p_id LIKE ?)";
$params[] = "%$search%";
$params[] = "%$search%";
}

if (!empty($_POST['category'])) {
$cats = is_array($_POST['category']) ? $_POST['category'] : explode(',', $_POST['category']);
$in = implode(',', array_fill(0, count($cats), '?'));
$filters .= " AND p.p_category IN ($in)";
$params = array_merge($params, $cats);
}

if (!empty($_POST['sub_category'])) {
$subs = is_array($_POST['sub_category']) ? $_POST['sub_category'] : explode(',', $_POST['sub_category']);
$in = implode(',', array_fill(0, count($subs), '?'));
$filters .= " AND p.p_sub_category IN ($in)";
$params = array_merge($params, $subs);
}

if (!empty($_POST['user'])) {
$filters .= " AND p.p_user = ?";
$params[] = $_POST['user'];
}

// ✅ data
$sql = "SELECT p.*, c.c_name, sc.sub_name, u.user_name, pi.image_url
FROM products p
LEFT JOIN classes c ON p.p_category = c.c_id
LEFT JOIN s_classes sc ON p.p_sub_category = sc.sub_id
LEFT JOIN users u ON p.p_user = u.user_id
LEFT JOIN (SELECT product_id, image_url FROM product_images WHERE is_main = 1) pi ON pi.product_id = p.p_id
$filters
ORDER BY p.p_id DESC LIMIT $start, $limit";

$stmt = $con->prepare($sql);
$stmt->execute($params);
$result = $stmt->fetchAll();

// ✅ count
$countSql = "SELECT COUNT(*) 
FROM products p
LEFT JOIN classes c ON p.p_category = c.c_id
LEFT JOIN s_classes sc ON p.p_sub_category = sc.sub_id
LEFT JOIN users u ON p.p_user = u.user_id
$filters";
$stmtCount = $con->prepare($countSql);
$stmtCount->execute($params);
$total_data = $stmtCount->fetchColumn();
?>

<?php if ($result) : ?>
<div class='row gx-4 gx-lg-5 row-cols-2 row-cols-md-3 row-cols-xl-4 justify-content-center'>
<?php foreach ($result as $row) :
$imageUrl = $row['image_url'] ? "uploads/products/{$row['image_url']}" : "uploads/app/default.jpg";
$productId = $row['p_id'];
$productIdHash = md5($productId);
?>
<div class='col my-3'>
<div class='card h-100'>
<div class='badge bg-info text-white position-absolute' style='top: 10px; right: 0.5rem'>
<?= ($row['c_name']) ?>
</div>
<?php if ($row['sub_name']) : ?>
<div class='badge bg-dark text-white position-absolute' style='top: 35px; right: 0.5rem'>
<?= ($row['sub_name']) ?>
</div>
<?php endif; ?>
<img class='card-img-top' style='height: 250px;' src='<?= $imageUrl ?>' alt=''>
<div class='card-body p-4 text-center'>

<h6 class='fw-bolder' style='font-size: 15px;'><?= ($row['p_name']) ?></h6>

<h6>SKU : <b><?= $productId ?></b></h6>
<h6>Qté : <b><?= $row['p_qty'] ?></b></h6>
<h6>Vendeur : <b><?= ($row['user_name']) ?></b></h6>

<?php if (!isset($row['p_discount'])): ?>
<span class="text-muted text-decoration-line-through">
<?= ($row['p_discount']); ?> Dhs
</span>
<?php endif; ?>

<?php if (!isset($row['p_sell'])): ?>
<span>
<?= ($row['p_sell']); ?> Dhs
</span>
<?php endif; ?>


<?php
$state = isset($row['p_state']) ? (int)$row['p_state'] : 0;
if ($loginRank == "admin"){
?>

<div class="form-check form-switch my-2">
<label class="form-check-label" for="switch<?= $row['p_id'] ?>" style="color: <?= $state === 0 ? 'red' : 'green'; ?>;">
<?= $state === 0 ? 'Produit inactif' : 'Produit actif'; ?>
</label>
<input 
class="form-check-input toggle-state" 
type="checkbox" 
role="switch"
data-id="<?= $row['p_id'] ?>"
id="switch<?= $row['p_id'] ?>"
<?= $row['p_state'] == 1 ? 'checked' : '' ?>>
</div>

<?php
}else{
?>
<div  style="color: <?= $state === 0 ? 'red' : 'green'; ?>;"><?= $state === 0 ? 'Produit inactif' : 'Produit actif'; ?></div>

<?php
}
?>
</div>



<div class='card-footer p-4 pt-0 border-top-0 bg-transparent text-center'>
<?php if ($loginRank === 'admin') : ?>
<a class='btn btn-sm btn-outline-dark mt-2' data-bs-toggle='modal' data-bs-target='#add_to_section<?= $productId ?>'>Ajouter Au Section</a>
<a class='btn btn-sm btn-outline-info mt-2' data-bs-toggle='modal' data-bs-target='#add_to_slider<?= $productId ?>'>Ajouter Au Slider</a>
<a class='btn btn-sm btn-outline-danger mt-2' data-bs-toggle='modal' data-bs-target='#modalDelete<?= $productId ?>'>Supprimer</a>
<a class='btn btn-sm btn-outline-success mt-2' href='?do=edit&id=<?= $productIdHash ?>'>Modifier</a>
<?php endif; ?>



<?php if ($loginRank === 'user' && $state === 0) : ?>
<a class='btn btn-sm btn-outline-success mt-2' href='?do=edit&id=<?= $productIdHash ?>'>Modifier</a>
<?php endif; ?>

<a class='btn btn-sm btn-outline-dark mt-2' data-bs-toggle='modal' data-bs-target='#qty<?= $productId ?>'>
Historique du Stock
</a>



</div>
</div>
</div>
<?php endforeach; ?>
</div>

<!-- ✅ modals -->
<?php foreach ($result as $row) :
if ($loginRank !== 'admin') continue;
$productId = $row['p_id'];
$productIdHash = md5($productId);
?>

<!-- Modal Delete -->
<div class='modal fade' id='modalDelete<?= $productId ?>' tabindex='-1'>
<div class='modal-dialog modal-dialog-centered'>
<div class='modal-content'>
<div class='modal-header'><h5>Supprimer</h5></div>
<div class='modal-body text-center'>
<h6>Êtes-vous sûr ?</h6>
<a class='btn btn-success' href='dataUnlink?do=stock&dataUnlinkId=<?= $productIdHash ?>'>Oui, je veux</a>
</div>
</div>
</div>
</div>

<!-- Modal Section -->
<?php formAwdStart("formId{$productId}", "result{$productId}", "add_to_section", "post"); ?>
<div class='modal fade' id='add_to_section<?= $productId ?>'>
<div class='modal-dialog modal-dialog-centered'>
<div class='modal-content'>
<div class='modal-header'><h5>Ajouter au Section</h5></div>
<div class='modal-body'>
<input type='hidden' name='product_id' value='<?= $productIdHash ?>'>
<select name='section_id' class='form-select' required>
<option value='' disabled selected>Choisir section</option>
<?php foreach ($sections as $sec) : ?>
<option value='<?= $sec['sec_id'] ?>'><?= ($sec['sec_name']) ?></option>
<?php endforeach; ?>
</select>
<div id='result<?= $productId ?>'></div>
<button class='btn btn-primary mt-2'>Mise à jour</button>
</div>
</div>
</div>
</div>
<?php formAwdEnd(); ?>

<!-- Modal Slider -->
<?php formAwdStart("formId_slider_{$productId}", "result_slider_{$productId}", "add_to_slider", "post"); ?>
<div class='modal fade' id='add_to_slider<?= $productId ?>'>
<div class='modal-dialog modal-dialog-centered'>
<div class='modal-content'>
<div class='modal-header'><h5>Ajouter au Slider</h5></div>
<div class='modal-body'>
<input type='hidden' name='product_id' value='<?= $productIdHash ?>'>
<select name='slider_id' class='form-select' required>
<option value='' disabled selected>Choisir slider</option>
<?php foreach ($sliders as $sli) : ?>
<option value='<?= $sli['sli_id'] ?>'><?= ($sli['sli_name']) ?></option>
<?php endforeach; ?>
</select>
<div id='result_slider_<?= $productId ?>'></div>
<button class='btn btn-primary mt-2'>Mise à jour</button>
</div>
</div>
</div>
</div>
<?php formAwdEnd(); ?>












































<?php endforeach; ?>


















<?php foreach ($result as $row): 
$productId = $row['p_id']; // تأكد من تعريف $productId
?>

<div class='modal fade' id='qty<?= $productId ?>'>
<div class='modal-dialog modal-dialog-centered'>
<div class='modal-content'>
<div class='modal-header'><h5>Historique du Stock</h5></div>
<div class='modal-body'>

<?php
if (!function_exists('displayStockLog')) {
function displayStockLog($con, $productId, $rankFilter, $title) {
$sql = "SELECT sl.*, u.user_name, u.user_id FROM stock_log sl
LEFT JOIN users u ON u.user_id = sl.user_id
WHERE sl.p_id = :pid AND ";
if ($rankFilter === null) {
$sql .= "sl.rank IS NULL";
} else {
$sql .= "sl.rank = :rank";
}
$sql .= " ORDER BY sl.change_date DESC";

$stmt = $con->prepare($sql);
$stmt->bindValue(':pid', $productId, PDO::PARAM_INT);
if ($rankFilter !== null) {
$stmt->bindValue(':rank', $rankFilter, PDO::PARAM_STR);
}
$stmt->execute();
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (count($logs)>0){
?>
<h6><?= ($title) ?></h6>
<div class="table-responsive">
<table class="table table-bordered table-striped">
<thead class="table-dark">
<tr>
<th>#</th>
<th>Produit ID</th>
<th>Utilisateur ID</th>
<th>Changement</th>
<th>Ancienne QTE</th>
<th>Nouvelle QTE</th>
<th>Type d'opération</th>
<th>Date</th>
</tr>
</thead>
<tbody>
<?php foreach ($logs as $log): ?>
<tr>
<td><?= ($log['log_id']) ?></td>
<td><?= ($log['p_id']) ?></td>
<td><?= ($log['user_id']) ?> - <?= ($log['user_name']) ?></td>
<td><?= ($log['change_qty']) ?></td>
<td><?= ($log['old_qty']) ?></td>
<td><?= ($log['new_qty']) ?></td>
<td><?= ($log['operation_type']) ?></td>
<td><?= ($log['change_date']) ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
<?php
}
}
}
?>

<?php displayStockLog($con, $productId, null, 'Historique du Stock (Modification Qté)'); ?>
<?php displayStockLog($con, $productId, 'user', 'Historique du Stock (Colis Ajouté par Stock)'); ?>

</div>
</div>
</div>
</div>

<?php endforeach; ?>










<?= renderPagination($total_data, $page, $limit); ?>

<?php else : ?>
<div class='no-data'>Aucun résultat trouvé</div>
<?php endif; ?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script>
$(document).on("change", ".toggle-state", function() {
    let productId = $(this).data("id");
    let state = $(this).is(":checked") ? 1 : 0;

    $.ajax({
        url: "update_state_stock", // تأكد من الاسم الصحيح
        type: "POST",
        data: { id: productId, state: state },
        success: function(resp) {
            resp = resp.trim(); // إزالة أي فراغات
            if(resp === "success") {
                console.log("État mis à jour ✔");

                // إعادة توجيه المستخدم بعد نجاح التحديث
                window.location.href = ""; // ضع هنا رابط الصفحة الجديدة

            } else {
                alert("Erreur: " + resp);
            }
        },
        error: function(xhr, status, error) {
            alert("Erreur réseau ou serveur ❌\n" + error);
        }
    });
});
</script>
