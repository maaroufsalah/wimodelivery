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

$table  = "news";
$search = isset($_POST['search']) ? $_POST['search'] : '';

// هنا تضبط شرط الفلترة (مثال للـ admin وغير admin)
$xoo = $loginRank == 'admin' ? " n_unlink = '0' " : " n_unlink = '10' ";

$query = "SELECT * FROM $table WHERE $xoo ";
if ($search != '') {
$srs = str_replace(' ', '%', $search);
$query .= "AND (n_id LIKE '%$srs%' OR n_note LIKE '%$srs%') ";
}

$query .= "ORDER BY n_id DESC LIMIT $start, $limit";

// استعلام عدد النتائج
$countStatement = $con->prepare("SELECT COUNT(*) FROM $table WHERE $xoo " . ($search != '' ? "AND (n_id LIKE '%$srs%' OR n_name LIKE '%$srs%')" : ""));
$countStatement->execute();
$total_data = $countStatement->fetchColumn();

// استعلام البيانات الفعلية
$statement = $con->prepare($query);
$statement->execute();
$result = $statement->fetchAll();

// التحقق من وجود نتائج
if (count($result) > 0) {

echo "<div class='row text-center'>";
echo "<div class='col-9'><h6><b>Détails</b></h6></div>";
echo "<div class='col-3'><h6><b>Contrôles</b></h6></div>";
echo "</div>";

foreach ($result as $row) {


echo "<hr>";
echo "<div class='row align-items-center'>";



echo "<div class='col-9'>";
if (empty($row['n_image'])){
}else{
print "<img src='uploads/news/{$row['n_image']}' class='m-3' style='width:70px;height:70px;'/>";
}


echo "
<div class='alert' style='background:" . $row['n_bg'] . ";color:" . $row['n_color'] . "'>
" . html_entity_decode($row['n_note']) . "
</div>
";


if ($row['n_rank'] == "user"){
print "<span class='badge bg-danger'>Pour : Clients</span>";
}


if ($row['n_rank'] == "delivery"){
print "<span class='badge bg-info'>Pour : Livreurs</span>";
}

if (!empty($row['n_user'])){
$stmt = $con->prepare("SELECT * FROM users WHERE user_id = '".$row['n_user']."' LIMIT 1");
$stmt->execute();
$user_nrank = $stmt->fetch();

print "<span class='badge bg-dark'>Compte : ".$user_nrank['user_name']."</span>";
}


echo "</div>";

echo "<div class='col-3 text-center'>";
if ($loginRank == "admin") {
echo "
<a   data-bs-toggle='modal' data-bs-target='#modalUpdate" . $row['n_id'] . "'  class='text-info' style='font-size: 26px;'>
<i class='fa-regular fa-pen-to-square'></i>
</a>
";
echo "
<a data-bs-toggle='modal' data-bs-target='#modalDelete" . $row['n_id'] . "' class='text-danger' style='font-size: 26px;'>
<i class='fa-solid fa-trash'></i>
</a>
";
}



echo "</div>";

echo "</div>";

if ($loginRank == "admin") {
echo "<div class='modal fade' id='modalDelete" . $row['n_id'] . "' tabindex='-1' aria-hidden='true'>";
echo "<div class='modal-dialog modal-dialog-centered'>";
echo "<div class='modal-content'>";
echo "<div class='modal-header'>";
echo "<h5 class='modal-title'>Supprimer un élément</h5>";
echo "<button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>";
echo "</div>";
echo "<div class='modal-body'>";
echo "<div class='col-sm-12 text-center my-2'>";
echo "<h6>Êtes-vous sûr de bien vouloir supprimer cet élément ?</h6>";												
echo "<a class='btn btn-success' href='dataUnlink?do=news&dataUnlinkId=" . md5($row['n_id']) . "'>Oui, je veux</a>";
echo "</div>";
echo "</div>";
echo "</div>";
echo "</div>";
echo "</div>";
}














// edit data
if ($loginRank == "admin") {

$id = "formId".$row['n_id'];
$result = "data_result".$row['n_id'];
$action = "editNews";
$method = "post";
formAwdStart ($id,$result,$action,$method); 


if ($row['n_rank'] == "user"){
$stmt = $con->prepare("SELECT * FROM users WHERE user_unlink = '0' AND user_rank = 'user' ORDER BY user_name");
$stmt->execute();
$type_user = $stmt->fetchAll();
}else{
$stmt = $con->prepare("SELECT * FROM users WHERE user_unlink = '0' AND user_rank = 'delivery' ORDER BY user_name");
$stmt->execute();
$type_user = $stmt->fetchAll();
}



?>
<input type="hidden" name="news_id" value="<?= $row['n_id'] ?>">
<input type="hidden" name="old_image" value="<?= $row['n_image'] ?>">
<?php

echo "<div class='modal fade' id='modalUpdate" . $row['n_id'] . "' tabindex='-1' aria-hidden='true'>";
echo "<div class='modal-dialog modal-fullscreen modal-dialog-centered'>";
echo "<div class='modal-content'>";
echo "<div class='modal-header'>";
echo "<h5 class='modal-title'>Modifier</h5>";
echo "<button type='button' class='btn-close updatedata' data-bs-dismiss='modal' aria-label='Close'></button>";
echo "</div>";

echo "<div class='modal-body'>";

print '
<div class="col-sm-12">
<div class="my-3">
<div class="input">Couleur</div>
<input name="color" type="color" class="form-control" value="'.$row['n_color'].'" placeholder=""/>
</div>
</div>
';


print '
<div class="col-sm-12">
<div class="my-3">
<div class="input">Background</div>
<input name="background" type="color" class="form-control" value="'.$row['n_bg'].'" placeholder=""/>
</div>
</div>
';


?>


<!--
<div class="col-sm-12">
<div class="my-3">
<label>Pour</label>
<select name="rank" class="form-select">
<option value="0" disabled>Choisir rang</option>
<option value="user" <?php if ($row['n_rank'] == "user"){ echo "selected"; }?>>Clients</option>
<option value="delivery" <?php if ($row['n_rank'] == "delivery"){ echo "selected"; }?>>Livreurs</option>
</select>
</div>
</div>
-->



<div class="col-sm-12">
<div class="my-3">
<div class="input">Comptes</div>
<select name='user' class='js-select w-100'>
<option value='0' disabled selected>Choisir Comptes</option>
<?php foreach ($type_user as $usr): ?>
<option value='<?= $usr['user_id'] ?>' <?php if ($row['n_user'] == $usr['user_id']){ echo "selected"; }?>><?= $usr['user_name'] ?></option>
<?php endforeach; ?>
</select>
</div>
</div>



<div class="col-sm-12">
<div class="my-3">
<label>Type</label>
<select name="type" class="form-select">
<option value="0" disabled>Choisir Type</option>
<option value="pop" <?php if ($row['n_type'] == "pop"){ echo "selected"; }?>>POP</option>
<option value="alert" <?php if ($row['n_type'] == "alert"){ echo "selected"; }?>>Alert</option>
</select>
</div>
</div>

<?php



// for pop
print '
<div class="col-sm-12 my-3">
<label for="formFile" class="form-label">Images POP</label>
<input class="form-control my-3" name="image" type="file" id="image">
</div>
';


// alert
print '
<div class="col-sm-12">
<div class="input">Détails de Alert</div>
<textarea class="editor' . $row['n_id'] . ' w-100" name="details">' . $row['n_note'] . '</textarea>
</div>
';

echo "<div class='col-sm-12 text-center my-2'>";
echo "<div id='".$result."'></div>";
echo "</div>";


echo "<div class='col-sm-12 text-center my-2'>";
echo "<button class='btn btn-primary' type='submit'>Modifier</button>";
echo "</div>";


echo "</div>";




echo "</div>";
echo "</div>";
echo "</div>";

formAwdEnd ();
}













echo "</div>";

print "
<script>
    var editor" . $row['n_id'] . " = new RichTextEditor('.editor" . $row['n_id'] . "');
</script>
";
}

} else {
echo '<div class="alert alert-warning">Aucun résultat trouvé</div>';
}

// هنا تضع دالة الصفحة التي تستخدمها
echo renderPagination($total_data, $page, $limit);

?>
