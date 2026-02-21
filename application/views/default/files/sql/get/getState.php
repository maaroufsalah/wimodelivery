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

$table  = "state";
$search = isset($_POST['search']) ? $_POST['search'] : '';

// هنا تضبط شرط الفلترة (مثال للـ admin وغير admin)
$xoo = $loginRank == 'admin' ? " state_unlink = '0' " : " state_unlink = '10' ";

$query = "SELECT * FROM state WHERE $xoo ";
if ($search != '') {
$srs = str_replace(' ', '%', $search);
$query .= "AND (state_id LIKE '%$srs%' OR state_name LIKE '%$srs%') ";
}

$query .= "ORDER BY state_id DESC LIMIT $start, $limit";

// استعلام عدد النتائج
$countStatement = $con->prepare("SELECT COUNT(*) FROM state WHERE $xoo " . ($search != '' ? "AND (state_id LIKE '%$srs%' OR state_name LIKE '%$srs%')" : ""));
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
echo "<div class='col-4'><h6><b>États</b></h6></div>";
echo "<div class='col-4'><h6><b>Contrôles</b></h6></div>";
echo "</div>";

foreach ($result as $row) {
echo "<hr>";
echo "<div class='row text-center'>";

echo "<div class='col-4'>";
echo "<h6>" . $row['state_id'] . "</h6>";
echo "</div>";

echo "<div class='col-4'>";
echo "
<h6 class='' style='background:" . $row['state_background'] . ";color :" . $row['state_color'] . "'>
" . $row['state_name'] . "
</h6>
";

if (!empty($row['state_rank'])){
echo "<h6>(Rang : <b>".$row['state_rank']."</b>)</h6>";
}
echo "</div>";


echo "<div class='col-4 text-left'>";

// تأكد أن المتغيرات موجودة
if ($loginRank == "admin") {

// قائمة الأرقام الممنوعة
$protectedStates = [1,2,3,4,5,6,51,52,53,57,58,60];

// تحقق إن كانت state_id غير محمية
if (!in_array($row['state_id'], $protectedStates)) {

echo "
<a data-bs-toggle='modal' data-bs-target='#modalUpdate" . $row['state_id'] . "' class='text-info' style='font-size: 26px;'>
<i class='fa-regular fa-pen-to-square'></i>
</a>
";

echo "
<a data-bs-toggle='modal' data-bs-target='#modalDelete" . $row['state_id'] . "' class='text-danger' style='font-size: 26px;'>
<i class='fa-solid fa-trash'></i>
</a>
";

}else{


echo "
<a class='text-danger' style='font-size: 26px;'>
<i class='fa-solid fa-ban'></i>
</a>
";


}


echo "
<a data-bs-toggle='modal' data-bs-target='#modalUpdate2" . $row['state_id'] . "' class='text-dark' style='font-size: 26px;'>
<i class='fa-solid fa-shuffle'></i>
</a>
";




}


echo "</div>";



echo "</div>";


// data unlink
if ($loginRank == "admin") {
echo "<div class='modal fade' id='modalDelete" . $row['state_id'] . "' tabindex='-1' aria-hidden='true'>";
echo "<div class='modal-dialog modal-dialog-centered'>";
echo "<div class='modal-content'>";
echo "<div class='modal-header'>";
echo "<h5 class='modal-title'>Supprimer un élément</h5>";
echo "<button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>";
echo "</div>";
echo "<div class='modal-body'>";
echo "<div class='col-sm-12 text-center my-2'>";
echo "<h6>Êtes-vous sûr de bien vouloir supprimer cet élément ?</h6>";												
echo "<a class='btn btn-success' href='dataUnlink?do=state&dataUnlinkId=" . md5($row['state_id']) . "'>Oui, je veux</a>";
echo "</div>";
echo "</div>";
echo "</div>";
echo "</div>";
echo "</div>";
}










// edit data
if ($loginRank == "admin") {

$id = "formId".$row['state_id'];
$result = "data_result".$row['state_id'];
$action = "editState";
$method = "post";
formAwdStart ($id,$result,$action,$method); 

print "<input type='hidden' name='id' value='" . md5($row['state_id']) . "'/>";

echo "<div class='modal fade' id='modalUpdate" . $row['state_id'] . "' tabindex='-1' aria-hidden='true'>";
echo "<div class='modal-dialog modal-dialog-centered'>";
echo "<div class='modal-content'>";
echo "<div class='modal-header'>";
echo "<h5 class='modal-title'>Modifier État</h5>";
echo "<button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>";
echo "</div>";

echo "<div class='modal-body'>";



print '
<div class="col-sm-12">
<div class="my-3">
<div class="input">État</div>
<input name="state" type="text" class="form-control" value="'.$row['state_name'].'" placeholder=""/>
</div>
</div>
';

print '
<div class="col-sm-12">
<div class="my-3">
<div class="input">Couleur</div>
<input name="color" type="color" class="form-control" value="'.$row['state_color'].'" placeholder=""/>
</div>
</div>
';

print '
<div class="col-sm-12">
<div class="my-3">
<div class="input">Background</div>
<input name="background" type="color" class="form-control" value="'.$row['state_background'].'" placeholder=""/>
</div>
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









// edit data
if ($loginRank == "admin") {

$id = "formId2".$row['state_id'];
$result = "data_result2".$row['state_id'];
$action = "edit_state_rank";
$method = "post";
formAwdStart ($id,$result,$action,$method); 

print "<input type='hidden' name='id' value='" . md5($row['state_id']) . "'/>";

echo "<div class='modal fade' id='modalUpdate2" . $row['state_id'] . "' tabindex='-1' aria-hidden='true'>";
echo "<div class='modal-dialog modal-dialog-centered'>";
echo "<div class='modal-content'>";
echo "<div class='modal-header'>";
echo "<h5 class='modal-title'>Modifier État</h5>";
echo "<button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>";
echo "</div>";

echo "<div class='modal-body'>";


?>


<div class="col-sm-12">
<div class="my-3">
<label>Rang</label>
<select name="rank" class="form-select">
<option value="0" disabled>Choisir rang</option>
<option value="admin" <?php if ($row['state_rank'] == "admin"){echo "selected";}?>>Admin</option>
<option value="delivery" <?php if ($row['state_rank'] == "delivery"){echo "selected";}?>>Livreur</option>
</select>
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

} else {
echo '<div class="alert alert-warning">Aucun résultat trouvé</div>';
}

// هنا تضع دالة الصفحة التي تستخدمها
echo renderPagination($total_data, $page, $limit);

?>
