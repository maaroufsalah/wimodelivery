<?php
$bootstrap = new Bootstrap();
$bootstrap->runBootstrap();

include get_file("files/sql/get/os_settings");
include get_file("files/sql/get/session");
include get_file("files/sql/get/functions");

global $con;

$table = "products";
$primaryKey = "p_id";

// إعدادات الفلاتر والصفحات
$user = isset($_POST["user"]) ? (int) $_POST["user"] : 10;
$display = isset($_POST["display"]) ? (int) $_POST["display"] : 10;
$limit = in_array($display, [10, 50, 100, 200]) ? $display : 10;

$page = isset($_POST['page']) && (int) $_POST['page'] > 1 ? (int) $_POST['page'] : 1;
$start = ($page - 1) * $limit;

// البحث والفلاتر
$search = isset($_POST['search']) ? $_POST['search'] : '';
$filter_trade = isset($_POST['user']) ? intval($_POST['user']) : 0;

// بناء شرط الاستعلام حسب نوع المستخدم
if ($loginRank == "admin") {

$xoo = " p_unlink = '0' and p_user = '$user' AND p_state = '1' ";

} elseif ($loginRank == "user") {

$xoo = " p_unlink = '0' and p_user = '$user' AND p_state = '1' ";

} elseif ($loginRank == "aide") {

$xoo = " p_unlink = '0' and p_user = '".$loginUser['user_aide']."' AND p_state = '1' ";

} else {
$xoo = " p_unlink = '10' ";
}

// استعلام المنتجات
$query = "SELECT * FROM $table WHERE $xoo ";
$params = [];

if ($search != '') {
$srs = str_replace(' ', '%', $search);
$query .= "AND (p_id LIKE ? OR p_name LIKE ?) ";
$params[] = "%$srs%";
$params[] = "%$srs%";
}

if ($filter_trade > 0) {
$query .= "AND p_user = ? ";
$params[] = $filter_trade;
}

$query .= "ORDER BY p_id DESC LIMIT $start, $limit";
$statement = $con->prepare($query);
$statement->execute($params);
$data = $statement->fetchAll(PDO::FETCH_ASSOC);

// حساب عدد النتائج الكلية
$countQuery = "SELECT COUNT(*) FROM $table WHERE $xoo ";
$countParams = [];

if ($search != '') {
$countQuery .= "AND (p_id LIKE ? OR p_name LIKE ?) ";
$countParams[] = "%$srs%";
$countParams[] = "%$srs%";
}

if ($filter_trade > 0) {
$countQuery .= "AND p_user = ? ";
$countParams[] = $filter_trade;
}

$statement = $con->prepare($countQuery);
$statement->execute($countParams);
$total_data = $statement->fetchColumn();
?>

<?php if (count($data) > 0): ?>
<?php foreach ($data as $product): ?>
<?php
// الصورة الرئيسية
$stmtImage = $con->prepare("SELECT image_url FROM product_images WHERE product_id = ? AND is_main = 1 LIMIT 1");
$stmtImage->execute([$product['p_id']]);
$mainImage = $stmtImage->fetchColumn();
$imageUrl = $mainImage ? "uploads/products/" . htmlspecialchars($mainImage) : "uploads/app/default.jpg";

// استعلام المجموعات
$stmt = $con->prepare("SELECT * FROM product_option_groups WHERE product_id = ?");
$stmt->execute([$product['p_id']]);
$option_groups = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($option_groups as $group): 
?>
<input type="hidden" name="group_ids[]" value="<?= $group['id']; ?>">
<?php
endforeach; 




?>
<div class='row my-3'>
<div class='col-2'>
<img src='<?= $imageUrl ?>' class='img-fluid'>
</div>
<div class='col-10'>
<div class='card p-3'>
<h5><?= htmlspecialchars($product['p_name']) ?></h5>
<div class='tf-product-info-variant-picker'>
<?php foreach ($option_groups as $group): ?>
<div class='variant-picker-item'>
<label><strong><?= htmlspecialchars($group['group_name']) ?>:</strong></label>
<div class='variant-picker-values'>
<?php
$stmt = $con->prepare("SELECT * FROM product_option_values WHERE group_id = ?");
$stmt->execute([$group['id']]);
$option_values = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($option_values as $value):
?>
<input type="radio" name="option_<?= $group['id']; ?>" id="opt<?= $value['id']; ?>" value="<?= htmlspecialchars($value['value_name']); ?>">
<label for="opt<?= $value['id']; ?>"><?= htmlspecialchars($value['value_name']); ?> (+<?= number_format($value['value_price'], 2); ?> Dhs)</label>
<?php endforeach; ?>
</div>
</div>
<?php endforeach; ?>
</div>
<div class='tf-product-info-quantity my-2'>
<label>Qté:</label>
<input type='number' name='quantity' value='1' min='1' max='10' class='form-control w-auto d-inline'>
</div>
<input type='hidden' name='price' value='<?= $product['p_sell']; ?>'>
<div class='my-2'>
<span class='text-muted text-decoration-line-through'><?= $product['p_discount']; ?> Dhs</span>
<strong class='ms-2'><?= $product['p_sell']; ?> Dhs</strong>
</div>
<button class='btn btn-dark btn-sm btn-add-to-colis' data-product-id='<?= $product['p_id']; ?>'>Ajouter Au Colis</button>
</div>
</div>
</div>
<?php endforeach; ?>

<!-- التصفح الصفحي -->
<?php
$total_pages = ceil($total_data / $limit);
?>
<div>Total : <b><?= $total_data ?></b></div>
<div class='pagination-wrapper text-center'>
<ul class='pagination justify-content-center mt-3'>
<?php if ($page > 1): ?>
<li class='page-item'><a class='page-link' href='#' data-page='<?= $page - 1 ?>'>«</a></li>
<?php endif; ?>
<?php for ($i = 1; $i <= $total_pages; $i++): ?>
<li class='page-item<?= $i == $page ? " active" : "" ?>'><a class='page-link' href='#' data-page='<?= $i ?>'><?= $i ?></a></li>
<?php endfor; ?>
<?php if ($page < $total_pages): ?>
<li class='page-item'><a class='page-link' href='#' data-page='<?= $page + 1 ?>'>»</a></li>
<?php endif; ?>
</ul>
</div>
<?php else: ?>
<div class='no-data'>Aucun résultat trouvé</div>
<?php endif; ?>

<script>
$(document).on('click', '.btn-add-to-colis', function(e) {
e.preventDefault();
var button = $(this);
var productId = button.data('product-id');
var parent = button.closest('.card');

var quantity = parent.find('input[name="quantity"]').val();
var price = parent.find('input[name="price"]').val();

var options = {};
parent.find('.variant-picker-values input[type="radio"]:checked').each(function () {
var groupId = $(this).attr('name').replace('option_', '');
var value = $(this).val();
options[groupId] = value;
});

$.ajax({
url: 'add_to_package',
method: 'POST',
data: {
product_id: productId,
quantity: quantity,
price: price,
options: options
},
success: function(response) {
location.reload(); // تحديث الصفحة

},
error: function() {
alert("Erreur lors de l'ajout au colis.");
}
});
});
</script>
