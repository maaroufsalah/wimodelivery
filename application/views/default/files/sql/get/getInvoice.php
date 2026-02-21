<?php
global $con;
include get_file("files/sql/get/session");
include_once get_file("files/sql/get/functions"); // يجب أن يحتوي على الدالة

// استقبال البيانات من AJAX
$page   = isset($_POST['page']) ? (int)$_POST['page'] : 1;
$search = isset($_POST['search']) ? $_POST['search'] : '';
$display = isset($_POST['display']) ? (int)$_POST['display'] : 10;
$user   = isset($_POST['user']) ? (int)$_POST['user'] : 0;
$state  = isset($_POST['state']) ? (int)$_POST['state'] : -1; // -1 تعني عدم التصفية بالحالة

$page = ($page > 0) ? $page : 1;
$display = ($display > 0) ? $display : 10;
$offset = ($page - 1) * $display;

// بناء الاستعلام حسب صلاحية المستخدم
if ($loginRank == "admin") {
$query = "SELECT * FROM invoice WHERE 1=1";
$params = [];
} elseif ($loginRank == "aide") {
$query = "SELECT * FROM invoice WHERE in_user = :aideUser";
$params = [':aideUser' => $loginUser['user_aide']];
} else {
$query = "SELECT * FROM invoice WHERE in_user = :loginId";
$params = [':loginId' => $loginId];
}

// شروط الفلترة
if (!empty($search)) {
$query .= " AND in_id LIKE :search";
$params[':search'] = "%$search%";
}
if ($user > 0) {
$query .= " AND in_user = :user";
$params[':user'] = $user;
}
if ($state === 0 || $state === 1) {
$query .= " AND in_state = :state";
$params[':state'] = $state;
}

// إضافة LIMIT مع bindValue لاحقاً
$query .= " ORDER BY in_id DESC LIMIT :offset, :display";

$stmt = $con->prepare($query);

// ربط القيم
foreach ($params as $key => $value) {
$stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
}
// offset و display يجبان كـ PDO::PARAM_INT
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':display', $display, PDO::PARAM_INT);

$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// استعلام حساب عدد الصفوف (لتحديد عدد الصفحات)
$totalQuery = "SELECT COUNT(*) FROM invoice WHERE 1=1";
$totalParams = [];

// نفس شروط الفلترة (مع استثناء صلاحية "aide" و "user")
if ($loginRank == "admin") {
// لا تغيير على $totalQuery و $totalParams لأن الشرط 1=1
} elseif ($loginRank == "aide") {
$totalQuery .= " AND in_user = :aideUser";
$totalParams[':aideUser'] = $loginUser['user_aide'];
} else {
$totalQuery .= " AND in_user = :loginId";
$totalParams[':loginId'] = $loginId;
}

if (!empty($search)) {
$totalQuery .= " AND in_id LIKE :search";
$totalParams[':search'] = "%$search%";
}
if ($user > 0) {
$totalQuery .= " AND in_user = :user";
$totalParams[':user'] = $user;
}
if ($state === 0 || $state === 1) {
$totalQuery .= " AND in_state = :state";
$totalParams[':state'] = $state;
}

$stmtTotal = $con->prepare($totalQuery);
foreach ($totalParams as $key => $value) {
$stmtTotal->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
}
$stmtTotal->execute();
$totalRows = $stmtTotal->fetchColumn();
$totalPages = ($totalRows > 0) ? ceil($totalRows / $display) : 0;

// بناء جدول Bootstrap متجاوب لعرض النتائج
$html = '';

if (count($results) > 0) {
$html .= '<div class="table-responsive">';
$html .= '<table class="table table-bordered table-hover align-middle text-center">';
$html .= '<thead class="table-dark text-center">';
$html .= '<tr>';
$html .= '<th>Facture N°</th>';
$html .= '<th>Vendeur</th>';
$html .= '<th>Date</th>';
$html .= '<th>Total (DH)</th>';
$html .= '<th>Frais (DH)</th>';
$html .= '<th>Net (DH)</th>';
$html .= '<th>État</th>';
$html .= '<th>Actions</th>';
$html .= '</tr>';
$html .= '</thead><tbody>';

foreach ($results as $row) {
$stmtUser = $con->prepare("SELECT * FROM users WHERE user_id = :uid LIMIT 1");
$stmtUser->execute([':uid' => $row['in_user']]);
$user = $stmtUser->fetch();

$html .= '<tr class="text-center">';
$html .= '<td><strong>#' . ($row['in_id']) . '</strong></td>';
$html .= '<td>' . ($user['user_name'] ?? '—') .'<br>'. ($user['user_owner'] ?? '—') .'</td>';
$html .= '<td>' . ($row['in_date']) . '</td>';
$html .= '<td>' . number_format($row['in_total'], 2) . '</td>';
$html .= '<td>' . number_format($row['in_fee'], 2) . '</td>';
$html .= '<td>' . number_format($row['in_net'], 2) . '</td>';

// حالة الفاتورة
$etatHtml = '';
if ($loginRank == "admin") {
if ($row['in_state'] == 0) {
$etatHtml = "<span class='text-danger'>Non Payé</span> 
<a href='dataUpdate?do=invoice&id=" . md5($row['in_id']) . "' class='btn btn-info btn-sm ms-2'>Payer</a>";
} else {
$etatHtml = "<span class='text-success'>Payé</span>";
}
} else {
$etatHtml = ($row['in_state'] == 1) ? "<span class='text-success'>Payé</span>" : "<span class='text-danger'>Non Payé</span>";
}
$html .= '<td>' . $etatHtml . '</td>';

// أزرار الطباعة والحذف
$actions = '<a target="_blank" href="print_invoice?id=' . md5($row['in_id']) . '" class="text-dark me-2" title="Imprimer">
<i class="fa-solid fa-print fa-lg"></i>
</a>';

if (hasUserPermission($con, $loginId, 17 ,'admin')) {
$actions .= '<a href="#" data-bs-toggle="modal" data-bs-target="#modalDelete' . $row['in_id'] . '" class="btn btn-danger btn-sm" title="Supprimer">
<i class="fa-solid fa-trash"></i>
</a>';
}

$html .= '<td>' . $actions . '</td>';
$html .= '</tr>';

// مودال الحذف
if (hasUserPermission($con, $loginId, 17 ,'admin')) {
$html .= '<div class="modal fade" id="modalDelete' . $row['in_id'] . '" tabindex="-1" aria-hidden="true">';
$html .= '<div class="modal-dialog modal-dialog-centered">';
$html .= '<div class="modal-content">';
$html .= '<div class="modal-header">';
$html .= '<h5 class="modal-title">Supprimer un élément</h5>';
$html .= '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>';
$html .= '</div>';
$html .= '<div class="modal-body text-center">';
$html .= '<h6>Êtes-vous sûr de bien vouloir supprimer cet élément ?</h6>';
$html .= '<a class="btn btn-success mt-3" href="dataUnlink?do=invoice&dataUnlinkId=' . md5($row['in_id']) . '">Oui, je veux</a>';
$html .= '</div></div></div></div>';
}
}

$html .= '</tbody></table></div>';
} else {
$html .= '<div class="text-center my-5">';
$html .= '<i class="fa-solid fa-file-half-dashed fa-3x my-2"></i>';
$html .= '<h6>Aucun résultat trouvé</h6>';
$html .= '</div>';
}

// روابط التنقل بين الصفحات
$html .= "<hr><div>Total : <b>$totalRows</b></div><hr>";
$html .= "<div class='pagination-wrapper text-center'>
<ul class='pagination mt-3' style='display: inline-flex;'>";

if ($page > 1) {
$html .= "<li class='page-item'><a class='page-link' href='#' data-page='" . ($page - 1) . "'>«</a></li>";
}

$range = 2; // عدد الصفحات يمين ويسار الصفحة الحالية
$start = max(1, $page - $range);
$end   = min($totalPages, $page + $range);

// صفحة أولى
if ($start > 1) {
    $html .= "<li class='page-item'><a class='page-link' href='#' data-page='1'>1</a></li>";
    if ($start > 2) $html .= "<li class='page-item disabled'><span class='page-link'>...</span></li>";
}

// الصفحات القريبة
for ($i = $start; $i <= $end; $i++) {
    $active = ($i == $page) ? " active" : "";
    $html .= "<li class='page-item$active'><a class='page-link' href='#' data-page='$i'>$i</a></li>";
}

// صفحة أخيرة
if ($end < $totalPages) {
    if ($end < $totalPages - 1) $html .= "<li class='page-item disabled'><span class='page-link'>...</span></li>";
    $html .= "<li class='page-item'><a class='page-link' href='#' data-page='$totalPages'>$totalPages</a></li>";
}



if ($page < $totalPages) {
$html .= "<li class='page-item'><a class='page-link' href='#' data-page='" . ($page + 1) . "'>»</a></li>";
}

$html .= "</ul></div>";

// إخراج المحتوى
echo $html;
?>
