<?php 
$bootstrap = new Bootstrap();
$bootstrap->runBootstrap();


include get_file("files/sql/get/os_settings");
include get_file("files/sql/get/session");
include get_file("files/sql/get/functions");


global $con;


// تأكد من أن الطلب عبر AJAX
$display = isset($_POST["display"]) ? $_POST["display"] : 10;
$limit = ($display == 10 || $display == 50 || $display == 100 || $display == 200) ? $display : 10;

$page = isset($_POST['page']) && $_POST['page'] > 1 ? $_POST['page'] : 1;
$start = ($page - 1) * $limit;

$table  = "slider";
$search = isset($_POST['search']) ? $_POST['search'] : '';

$xoo = $loginRank == 'admin' ? " sli_unlink = '0' " : " sli_unlink = '10' ";

$query = "SELECT * FROM $table WHERE $xoo ";
if ($search != '') {
$srs = str_replace(' ', '%', $search);
$query .= "AND (sli_id LIKE '%$srs%' OR sli_name LIKE '%$srs%') ";
}

$query .= "ORDER BY sli_id DESC LIMIT $start, $limit";

// استعلام لحساب العدد الإجمالي للبيانات
$statement = $con->prepare("SELECT COUNT(*) FROM $table WHERE $xoo");
$statement->execute();
$total_data = $statement->fetchColumn();

// استعلام لعرض البيانات المطلوبة
$statement = $con->prepare($query);
$statement->execute();
$result = $statement->fetchAll();

// التحقق من النتائج
if (count($result) > 0) {


echo "<div class='row align-items-center text-center'>";

echo "<div class='col-4'>";
echo "<h6><b>Codes</b></h6>";
echo "</div>";

echo "<div class='col-4'>";
echo "<h6><b>Promotions</b></h6>";
echo "</div>";

echo "<div class='col-4'>";
echo "<h6><b>Contrôles</b></h6>";
echo "</div>";


echo "</div>";

foreach ($result as $row) {



echo "<hr>";
echo "<div class='row align-items-center text-center'>";

echo "<div class='col-4'>";
echo "<h6>" . $row['sli_id'] . "</h6>";
echo "</div>";

echo "<div class='col-4'>";
if (empty($row['sli_image'])){
print "<i class='fa-solid fa-camera-retro fa-2x'></i>";
}else{
print "<img src='uploads/sliders/{$row['sli_image']}' class='m-3' style='width:100px;height:70px;'/>";
}
echo "<h6>" . $row['sli_name'] . "</h6>";

echo "</div>";

echo "<div class='col-4 text-left'>";

if ($loginRank == "admin") {
echo "
<a data-bs-toggle='modal' data-bs-target='#modalDelete" . $row['sli_id'] . "' class='text-danger' style='font-size: 26px;'>
<i class='fa-solid fa-trash'></i>
</a>
";
}


if ($loginRank == "admin") {
echo "
<a href='?do=edit&id=".md5($row['sli_id'])."' class='text-info' style='font-size: 26px;'>
<i class='fa-solid fa-pen-to-square'></i>
</a>
";
}



echo "</div>";

echo "</div>";

if ($loginRank == "admin") {
echo "<div class='modal fade' id='modalDelete" . $row['sli_id'] . "' tabindex='-1' aria-hidden='true'>";
echo "<div class='modal-dialog modal-dialog-centered'>";
echo "<div class='modal-content'>";
echo "<div class='modal-header'>";
echo "<h5 class='modal-title'>Supprimer un élément</h5>";
echo "<button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>";
echo "</div>";
echo "<div class='modal-body'>";
echo "<div class='col-sm-12 text-center my-2'>";
echo "<h6>Êtes-vous sûr de bien vouloir supprimer cet élément ?</h6>";												
echo "<a class='btn btn-success' href='dataUnlink?do=slider&dataUnlinkId=" . md5($row['sli_id']) . "'>Oui, je veux</a>";
echo "</div>";
echo "</div>";
echo "</div>";
echo "</div>";
echo "</div>";
}
























echo "</div>";
}





// التصفح الصفحي
$total_pages = ceil($total_data / $limit);

echo "<div>Total : <b>$total_data</b></div>";
echo "<hr>";

echo "<div class='pagination-wrapper text-center'>
<ul class='pagination mt-3' style='display: inline-flex;'>";

// الروابط السابقة
if ($page > 1) {
echo "<li class='page-item'><a class='page-link' href='#' data-page='" . ($page - 1) . "'>«</a></li>";
}

// الروابط الخاصة بكل صفحة
for ($i = 1; $i <= $total_pages; $i++) {
$active = ($i == $page) ? " active" : "";
echo "<li class='page-item$active'><a class='page-link' href='#' data-page='$i'>$i</a></li>";
}

// الروابط التالية
if ($page < $total_pages) {
echo "<li class='page-item'><a class='page-link' href='#' data-page='" . ($page + 1) . "'>»</a></li>";
}

echo "</ul>
</div>";

} else {
echo "<div class='no-data'>Aucun résultat trouvé</div>";
}

?>
