<?php 
global $con;

function POST_NUM($key) {
return isset($_POST[$key]) ? intval($_POST[$key]) : 0;
}


// عدد العناصر لكل صفحة
$display = POST_NUM("display");
$allowed_limits = [6, 50, 100, 200];
$limit = in_array($display, $allowed_limits) ? $display : 10;

// الصفحة الحالية
$page = POST_NUM('page') > 1 ? POST_NUM('page') : 1;
$start = ($page - 1) * $limit;

// الفلاتر
$table = "shipping_charges";
$warehouse = POST("warehouse");
$city = POST("city");
$search = trim($_POST['search'] ?? '');

$xoo = "sc_unlink = '0'";
$where = [];
$where[] = $xoo;

// الشرط الأساسي
if ($search != '') {
$srs = str_replace(' ', '%', $search);
$where[] = "(sc_id LIKE :search OR sc_city_name LIKE :search OR sc_warehouse_name LIKE :search OR sc_delivery LIKE :search OR sc_cancel LIKE :search OR sc_return LIKE :search)";
}

if ($warehouse > 0) {
$where[] = "sc_warehouse = :warehouse";
}

if ($city > 0) {
$where[] = "sc_city = :city";
}

$where_clause = count($where) ? "WHERE " . implode(" AND ", $where) : "";

$order_by = "ORDER BY sc_delivery ASC";

$query = "SELECT * FROM $table $where_clause $order_by";
$filter_query = $query . " LIMIT $start, $limit";

// التحضير
$statement = $con->prepare($query);

// الربط
if ($search != '') {
$statement->bindValue(':search', "%$srs%");
}
if ($warehouse > 0) {
$statement->bindValue(':warehouse', $warehouse);
}
if ($city > 0) {
$statement->bindValue(':city', $city);
}

$statement->execute();
$total_data = $statement->rowCount();

$statement = $con->prepare($filter_query);

if ($search != '') {
$statement->bindValue(':search', "%$srs%");
}
if ($warehouse > 0) {
$statement->bindValue(':warehouse', $warehouse);
}
if ($city > 0) {
$statement->bindValue(':city', $city);
}

$statement->execute();
$result = $statement->fetchAll();
$total_filter_data = $statement->rowCount();

if ($total_data > 0) {
echo "<div class='table-responsive'>";

echo "
<table class='table  table-bordered  text-center'>
<thead class='table-dark'>
<tr>
<th>Ville</th>
<th>Frais De Livraison</th>
<th>Frais De Retour</th>
</tr>
</thead>
<tbody>";

foreach ($result as $row) {
$stmt = $con->prepare("SELECT * FROM warehouse WHERE wh_id = ? LIMIT 1");
$stmt->execute([$row['sc_warehouse']]);
$warehouseCity = $stmt->fetch();

$stmt = $con->prepare("SELECT * FROM city WHERE city_id = ? LIMIT 1");
$stmt->execute([$row['sc_city']]);
$cityData = $stmt->fetch();

echo "<tr>
<td>{$cityData['city_name']}</td>
<td>{$row['sc_delivery']}</td>
<td>{$row['sc_return']}</td>
</tr>";
}

echo "</tbody></table>";
echo "</div>";
} else {
echo "<div class='card my-0'><div class='card-body text-center'>
<h6>Aucun résultat trouvé</h6>
</div></div>";
}

// Pagination
$output = "
<div class='pagination-wrapper text-center'>
<div class='' style='border-radius:0rem'>
<div class='card-body text-center'>
<div>
<ul class='pagination mt-3' style='display: inline-flex;'>
";

$total_links = ceil($total_data / $limit);
$page_array = [];
if ($total_links > 4) {
if ($page < 5) {
for ($i = 1; $i <= 5; $i++) $page_array[] = $i;
$page_array[] = '...';
$page_array[] = $total_links;
} elseif ($page > $total_links - 5) {
$page_array[] = 1;
$page_array[] = '...';
for ($i = $total_links - 5; $i <= $total_links; $i++) $page_array[] = $i;
} else {
$page_array[] = 1;
$page_array[] = '...';
for ($i = $page - 1; $i <= $page + 1; $i++) $page_array[] = $i;
$page_array[] = '...';
$page_array[] = $total_links;
}
} else {
for ($i = 1; $i <= $total_links; $i++) $page_array[] = $i;
}

$previous_link = $page > 1 ? 
"<li class='page-item'><a class='page-link' href='javascript:void(0)' data-page_number='".($page-1)."'><span class='material-symbols-outlined'>chevron_left</span></a></li>" :
"<li class='page-item disabled'><a class='page-link'><span class='material-symbols-outlined'>chevron_left</span></a></li>";

$next_link = $page < $total_links ? 
"<li class='page-item'><a class='page-link' href='javascript:void(0)' data-page_number='".($page+1)."'><span class='material-symbols-outlined'>chevron_right</span></a></li>" :
"<li class='page-item disabled'><a class='page-link'><span class='material-symbols-outlined'>chevron_right</span></a></li>";

$page_links = '';
foreach ($page_array as $p) {
if ($p == '...') {
$page_links .= "<li class='page-item disabled'><a class='page-link'>...</a></li>";
} elseif ($p == $page) {
$page_links .= "<li class='page-item active'><a class='page-link'>$p</a></li>";
} else {
$page_links .= "<li class='page-item'><a class='page-link' href='javascript:void(0)' data-page_number='$p'>$p</a></li>";
}
}

$output .= $previous_link . $page_links . $next_link;
$output .= "</ul></div></div></div></div>";

echo $output;
?>
