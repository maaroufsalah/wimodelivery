<?php 

global $con;

if ($loginRank == "admin") {
print "<div class='my-3 text-end'>";
print "<a data-bs-toggle='modal' data-bs-target='#new_gb' class='btn btn-primary'>Ajouter</a>";
print "</div>";
}










echo "<div class='modal fade' id='new_gb' tabindex='-1' aria-hidden='true'>";
echo "<div class='modal-dialog modal-dialog-centered'>";
echo "<div class='modal-content'>";
echo "<div class='modal-header'>";
echo "<h5 class='modal-title'>Ajouter</h5>";
echo "<button type='button' class='btn-close updatedata' data-bs-dismiss='modal' aria-label='Close'></button>";
echo "</div>";


$id = "formId";
$result = "data_result";
$action = "new_tarif";
$method = "post";
formAwdStart ($id,$result,$action,$method); 



echo "<div class='modal-body'>";
echo "<div class='row'>";



$stmt = $con->prepare("SELECT * FROM warehouse WHERE wh_unlink = '0' ORDER BY wh_name ASC");
$stmt->execute();
$warehouses = $stmt->fetchAll();
if (count($warehouses) > 0) {
print "<div class='col-md-6'>";
print "<label class='form-label'>Entrepôt</label>";
print "<select class='form-select' name='warehouse'>";
print "<option value='0' selected>Sélectionner</option>";
foreach ($warehouses as $row) {
print "<option value='" . $row['wh_id'] . "'>" . $row['wh_name'] . "</option>";
}
print "</select>";
print "</div>";
}

$stmt = $con->prepare("SELECT * FROM city WHERE city_unlink = '0' ORDER BY city_name ASC");
$stmt->execute();
$cities = $stmt->fetchAll();
if (count($cities) > 0) {
print "<div class='col-md-6'>";
print "<label class='form-label'>Ville</label>";
print "<select class='form-select' name='city'>";
print "<option value='0' selected>Sélectionner</option>";
foreach ($cities as $row) {
print "<option value='" . $row['city_id'] . "'>" . $row['city_name'] . "</option>";
}
print "</select>";
print "</div>";
}



print '
<p class="col-4">
<strong>Livraison:</strong> 
<input name="delivery" type="number" class="form-control" value="">
</p>
';
print '
<p class="col-4">
<strong>Annulation:</strong> 
<input name="cancel" type="number" class="form-control" value=""> 
</p>
';
print '
<p class="col-4">
<strong>Retour:</strong> 
<input name="return" type="number" class="form-control" value="">
</p>
';






echo "<div class='col-sm-12 text-center my-2'>";
echo "<div id='".$result."'></div>";
echo "</div>";


echo "<div class='col-sm-12 text-center my-2'>";
echo "<button class='btn btn-primary' type='submit'>Ajouter</button>";
echo "</div>";


echo "</div>";
echo "</div>";



formAwdEnd ();


echo "</div>";
echo "</div>";
echo "</div>";






































print "<div class='' style='border-radius: 0rem;'>";
print "<div class=''>";
print "<div class='row my-2 align-items-center'>";

print "<div class='col-md-3'>";
print "<label class='form-label'>Afficher</label>";
print "<select class='form-select filter' id='display'>";
print "<option value='10' selected>10</option>";
print "<option value='50'>50</option>";
print "<option value='100'>100</option>";
print "<option value='200'>200</option>";
print "</select>";
print "</div>";

print "<div class='col-md-3'>";
print "<label class='form-label'>Chercher</label>";
print "<input type='text' class='form-control filter' id='search' placeholder='cherche ici'/>";
print "</div>";

$stmt = $con->prepare("SELECT * FROM warehouse WHERE wh_unlink = '0' ORDER BY wh_name ASC");
$stmt->execute();
$warehouses = $stmt->fetchAll();
if (count($warehouses) > 0) {
print "<div class='col-md-3'>";
print "<label class='form-label'>Entrepôt</label>";
print "<select class='form-select filter' id='warehouse'>";
print "<option value='0' selected>Sélectionner</option>";
foreach ($warehouses as $row) {
print "<option value='" . $row['wh_id'] . "'>" . $row['wh_name'] . "</option>";
}
print "</select>";
print "</div>";
}

$stmt = $con->prepare("SELECT * FROM city WHERE city_unlink = '0' ORDER BY city_name ASC");
$stmt->execute();
$cities = $stmt->fetchAll();
if (count($cities) > 0) {
print "<div class='col-md-3'>";
print "<label class='form-label'>Ville</label>";
print "<select class='form-select filter' id='city'>";
print "<option value='0' selected>Sélectionner</option>";
foreach ($cities as $row) {
print "<option value='" . $row['city_id'] . "'>" . $row['city_name'] . "</option>";
}
print "</select>";
print "</div>";
}

print "</div>"; // row


print "<hr class='my-4'>";
print "<div class='loader my-2'></div>";
print "<div id='dynamic_content'></div>";

// HTML تحميل أثناء انتظار البيانات
$load = '<div class="progress" style="height:10px;"><div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 100%"></div></div>';

?>

<script>
$(document).ready(function() {

// تحميل البيانات أول مرة
function loadData(page = 1) {
const data = {
page: page,
search: $('#search').val(),
display: $('#display').val(),
warehouse: $('#warehouse').val(),
city: $('#city').val()
};

$.ajax({
url: 'getGb', // مسار PHP الذي يعيد المحتوى
method: 'POST',
data: data,
cache: false,
beforeSend: function() {
$('.loader').html('<div class="spinner-border spinner-border-sm"></div> Chargement...');
},
success: function(response) {
$('#dynamic_content').html(response);
$('.loader').html('');
},
error: function(xhr) {
console.error(xhr.responseText);
$('.loader').html('<div class="text-danger">Erreur lors du chargement</div>');
}
});
}

// تحميل أولي
loadData();

// تشغيل الفلاتر عند التغيير
$('.filter').on('change keyup', function() {
loadData(1); // ارجع للصفحة 1 عند التصفية
});

$(document).on('click', '.page-link', function (e) {
e.preventDefault();
const page = $(this).data('page');
if (page) {
loadData(page);
}
});

// إعادة التحميل عند التحديث اليدوي (اختياري)
$(document).on('click', '.updatedata', function() {
loadData();
});

});
</script>
