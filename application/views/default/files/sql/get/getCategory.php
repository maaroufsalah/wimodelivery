<?php 
// بداية الكود الأصلي بدون تغيير
$bootstrap = new Bootstrap();
$bootstrap->runBootstrap();

include get_file("files/sql/get/os_settings");
include get_file("files/sql/get/session");
include get_file("files/sql/get/functions");

global $con;

// الحصول على متغيرات الفلاتر والصفحة
$display = isset($_POST["display"]) ? $_POST["display"] : 10;
$limit = ($display == 10 || $display == 50 || $display == 100 || $display == 200) ? $display : 10;

$page = isset($_POST['page']) && $_POST['page'] > 1 ? $_POST['page'] : 1;
$start = ($page - 1) * $limit;

$table  = "classes";
$search = isset($_POST['search']) ? $_POST['search'] : '';

// هنا تضبط شرط الفلترة (مثال للـ admin وغير admin)
$xoo = $loginRank == 'admin' ? " c_unlink = '0' " : " c_unlink = '10' ";

$query = "SELECT * FROM $table WHERE $xoo ";
if ($search != '') {
$srs = str_replace(' ', '%', $search);
$query .= "AND (c_id LIKE '%$srs%' OR c_name LIKE '%$srs%') ";
}

$query .= "ORDER BY c_id DESC LIMIT $start, $limit";

// استعلام عدد النتائج
$countStatement = $con->prepare("SELECT COUNT(*) FROM $table WHERE $xoo " . ($search != '' ? "AND (c_id LIKE '%$srs%' OR c_name LIKE '%$srs%')" : ""));
$countStatement->execute();
$total_data = $countStatement->fetchColumn();

// استعلام البيانات الفعلية
$statement = $con->prepare($query);
$statement->execute();
$result = $statement->fetchAll();

// التحقق من وجود نتائج
if (count($result) > 0) {

echo "<div class='row text-center'>";
echo "<div class='col-4'><h6><b>Codes</b></h6></div>";
echo "<div class='col-4'><h6><b>catégories</b></h6></div>";
echo "<div class='col-4'><h6><b>Contrôles</b></h6></div>";
echo "</div>";

foreach ($result as $row) {



$stmt = $con->prepare("SELECT * FROM type WHERE type_unlink = '0' ORDER BY type_name");
$stmt->execute();
$typeCount = $stmt->rowCount();
$type = $stmt->fetchAll();


$stmt = $con->prepare("SELECT * FROM type WHERE type_unlink = '0' AND type_id = '{$row['c_type']}' LIMIT 1");
$stmt->execute();
$typeCount = $stmt->rowCount();
$type_via = $stmt->fetch();



echo "<hr>";
echo "<div class='row align-items-center text-center'>";

echo "<div class='col-4'>";
echo "<h6>" . $row['c_id'] . "</h6>";
echo "</div>";

echo "<div class='col-4'>";
if (empty($row['c_image'])){
print "<i class='fa-solid fa-camera-retro fa-2x'></i>";
}else{
print "<img src='uploads/category/{$row['c_image']}' class='m-3' style='width:70px;height:70px;'/>";
}
echo "<h6>" . $row['c_name'] . "</h6>";
echo "<h6 class='text-danger'>(Type :<b>" . $type_via['type_name'] . "</b>)</h6>";

echo "</div>";

echo "<div class='col-4 text-left'>";
if ($loginRank == "admin") {
echo "
<a   data-bs-toggle='modal' data-bs-target='#modalUpdate" . $row['c_id'] . "'  class='text-info' style='font-size: 26px;'>
<i class='fa-regular fa-pen-to-square'></i>
</a>
";
echo "
<a data-bs-toggle='modal' data-bs-target='#modalDelete" . $row['c_id'] . "' class='text-danger' style='font-size: 26px;'>
<i class='fa-solid fa-trash'></i>
</a>
";
}
echo "</div>";

echo "</div>";

if ($loginRank == "admin") {
echo "<div class='modal fade' id='modalDelete" . $row['c_id'] . "' tabindex='-1' aria-hidden='true'>";
echo "<div class='modal-dialog modal-dialog-centered'>";
echo "<div class='modal-content'>";
echo "<div class='modal-header'>";
echo "<h5 class='modal-title'>Supprimer un élément</h5>";
echo "<button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>";
echo "</div>";
echo "<div class='modal-body'>";
echo "<div class='col-sm-12 text-center my-2'>";
echo "<h6>Êtes-vous sûr de bien vouloir supprimer cet élément ?</h6>";												
echo "<a class='btn btn-success' href='dataUnlink?do=category&dataUnlinkId=" . md5($row['c_id']) . "'>Oui, je veux</a>";
echo "</div>";
echo "</div>";
echo "</div>";
echo "</div>";
echo "</div>";
}














// edit data
if ($loginRank == "admin") {

$id = "formId".$row['c_id'];
$result = "data_result".$row['c_id'];
$action = "editCategory";
$method = "post";
formAwdStart ($id,$result,$action,$method); 

print "<input type='hidden' name='id' value='" . md5($row['c_id']) . "'/>";

echo "<div class='modal fade' id='modalUpdate" . $row['c_id'] . "' tabindex='-1' aria-hidden='true'>";
echo "<div class='modal-dialog modal-dialog-centered'>";
echo "<div class='modal-content'>";
echo "<div class='modal-header'>";
echo "<h5 class='modal-title'>Modifier catégorie</h5>";
echo "<button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>";
echo "</div>";

echo "<div class='modal-body'>";



print '
<div class="col-sm-12">
<div class="my-3">
<div class="input">Catégorie</div>
<input name="category" type="text" class="form-control" value="'.$row['c_name'].'" placeholder=""/>
</div>
</div>
';

?>
<div class="col-sm-12">
<div class="my-3">
<div class="input">Type de vente</div>
<select name='type' class=' w-100'>
<option value='0' disabled selected>Choisir type</option>
<?php
foreach ($type as $row_type){
$selected = ($row_type['type_id'] == $row['c_type']) ? 'selected' : '';

print "<option value='{$row_type['type_id']}' {$selected}>{$row_type['type_name']}</option>";
}
?>
</select>
</div>
</div>
<?php



print '
<div class="col-sm-12 my-3">
<label for="formFile" class="form-label">Images</label>
<input class="form-control my-3" name="image" type="file" id="image">
</div>
';



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

} else {
echo '<div class="alert alert-warning">Aucun résultat trouvé</div>';
}

// هنا تضع دالة الصفحة التي تستخدمها
echo renderPagination($total_data, $page, $limit);

?>
