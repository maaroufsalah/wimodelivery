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

$table  = "warehouse";
$search = isset($_POST['search']) ? $_POST['search'] : '';

$xoo = $loginRank == 'admin' ? " wh_unlink = '0' " : " wh_unlink = '10' ";

$query = "SELECT * FROM warehouse WHERE $xoo ";
if ($search != '') {
$srs = str_replace(' ', '%', $search);
$query .= "AND (wh_id LIKE '%$srs%' OR wh_name LIKE '%$srs%') ";
}

$query .= "ORDER BY wh_id DESC LIMIT $start, $limit";

// استعلام لحساب العدد الإجمالي للبيانات
$statement = $con->prepare("SELECT COUNT(*) FROM warehouse WHERE $xoo");
$statement->execute();
$total_data = $statement->fetchColumn();

// استعلام لعرض البيانات المطلوبة
$statement = $con->prepare($query);
$statement->execute();
$result = $statement->fetchAll();

// التحقق من النتائج
if (count($result) > 0) {


echo "<div class='row text-center'>";

echo "<div class='col-4'>";
echo "<h6><b>Codes</b></h6>";
echo "</div>";

echo "<div class='col-4'>";
echo "<h6><b>Agences</b></h6>";
echo "</div>";

echo "<div class='col-4'>";
echo "<h6><b>Contrôles</b></h6>";
echo "</div>";


echo "</div>";

foreach ($result as $row) {
echo "<hr>";
echo "<div class='row text-center'>";

echo "<div class='col-4'>";
echo "<h6>" . $row['wh_id'] . "</h6>";
echo "</div>";

echo "<div class='col-4'>";
echo "<h6>" . $row['wh_name'] . "</h6>";
echo "</div>";

echo "<div class='col-4 text-left'>";
if ($loginRank == "admin") {
echo "
<a   data-bs-toggle='modal' data-bs-target='#modalUpdate" . $row['wh_id'] . "'  class='text-info' style='font-size: 26px;'>
<i class='fa-regular fa-pen-to-square'></i>
</a>
";
echo "
<a data-bs-toggle='modal' data-bs-target='#modalDelete" . $row['wh_id'] . "' class='text-danger' style='font-size: 26px;'>
<i class='fa-solid fa-trash'></i>
</a>
";
}
echo "</div>";

echo "</div>";

if ($loginRank == "admin") {
echo "<div class='modal fade' id='modalDelete" . $row['wh_id'] . "' tabindex='-1' aria-hidden='true'>";
echo "<div class='modal-dialog modal-dialog-centered'>";
echo "<div class='modal-content'>";
echo "<div class='modal-header'>";
echo "<h5 class='modal-title'>Supprimer un élément</h5>";
echo "<button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>";
echo "</div>";
echo "<div class='modal-body'>";
echo "<div class='col-sm-12 text-center my-2'>";
echo "<h6>Êtes-vous sûr de bien vouloir supprimer cet élément ?</h6>";												
echo "<a class='btn btn-success' href='dataUnlink?do=warehouse&dataUnlinkId=" . md5($row['wh_id']) . "'>Oui, je veux</a>";
echo "</div>";
echo "</div>";
echo "</div>";
echo "</div>";
echo "</div>";
}














// edit data
if ($loginRank == "admin") {



$stmt = $con->prepare("SELECT * FROM city WHERE city_unlink = '0' ORDER BY city_name");
$stmt->execute();
$city = $stmt->fetchAll();


$id = "formId".$row['wh_id'];
$result = "data_result".$row['wh_id'];
$action = "edit_agency";
$method = "post";
formAwdStart ($id,$result,$action,$method); 

print "<input type='hidden' name='id' value='" . md5($row['wh_id']) . "'/>";

echo "<div class='modal fade' id='modalUpdate" . $row['wh_id'] . "' tabindex='-1' aria-hidden='true'>";
echo "<div class='modal-dialog modal-dialog-centered'>";
echo "<div class='modal-content'>";
echo "<div class='modal-header'>";
echo "<h5 class='modal-title'>Modifier L'agence</h5>";
echo "<button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>";
echo "</div>";

echo "<div class='modal-body'>";



print '
<div class="col-sm-12">
<div class="my-3">
<div class="input">Agence</div>
<input name="name" type="text" class="form-control" value="'.$row['wh_name'].'" placeholder=""/>
</div>
</div>
';

?>

<?php
$selected_cities = explode(',', $row['wh_city']);
?>


<div class="col-sm-12">
<div class="my-3">
<div class="input">Ville(s)</div>
<select name="city[]" class="js-select w-100 city" multiple>
<?php foreach ($city as $rowc): ?>
<option value='<?= $rowc['city_id'] ?>' <?= in_array($rowc['city_id'], $selected_cities) ? 'selected' : '' ?>><?= $rowc['city_name'] ?></option>
<?php endforeach; ?>
</select>
<small class="text-muted">Maintenez Ctrl (ou Cmd) pour sélectionner plusieurs villes</small>
</div>
</div>


<?php
echo "<div class='col-sm-12 text-center my-2'>";
echo "<div id='$result'></div>";
echo "</div>";

echo "<div class='col-sm-12 text-center my-2'>";
echo "<button class='btn btn-primary'>Mise a jour</button>";
echo "</div>";


echo "</div>";

echo "</div>";
echo "</div>";
echo "</div>";

formAwdEnd ();
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
