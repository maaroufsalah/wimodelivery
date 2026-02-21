<?php 

global $con;

if ($loginRank == "admin") {
print "<div class='my-3 text-end'>";
print "<a data-bs-toggle='modal' data-bs-target='#new_dp' class='btn btn-primary'>Ajouter</a>";
print "</div>";
}










echo "<div class='modal fade' id='new_dp' tabindex='-1' aria-hidden='true'>";
echo "<div class='modal-dialog modal-dialog-centered'>";
echo "<div class='modal-content'>";
echo "<div class='modal-header'>";
echo "<h5 class='modal-title'>Ajouter</h5>";
echo "<button type='button' class='btn-close updatedata' data-bs-dismiss='modal' aria-label='Close'></button>";
echo "</div>";


$id = "formId";
$result = "data_result";
$action = "new_tarif_delivery";
$method = "post";
formAwdStart ($id,$result,$action,$method); 



echo "<div class='modal-body'>";
echo "<div class='row'>";


$stmt = $con->prepare("SELECT * FROM users WHERE user_unlink = '0' AND user_rank = 'delivery' ORDER BY user_name ASC");
$stmt->execute();
$users = $stmt->fetchAll();
if (count($users) > 0) {
print "<div class='col-md-12'>";
print "<label class='form-label'>Livreur</label>";
print "<select class='form-select' name='user'>";
print "<option value='0' selected>Sélectionner</option>";
foreach ($users as $row) {
print "<option value='" . $row['user_id'] . "'>" . $row['user_name'] . "</option>";
}
print "</select>";
print "</div>";
}




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
print "<select class='form-select  js-select display filter' id=''>";
print "<option value='10' selected>10</option>";
print "<option value='50'>50</option>";
print "<option value='100'>100</option>";
print "<option value='200'>200</option>";
print "</select>";
print "</div>";

print "<div class='col-md-9'>";
print "<label class='form-label'>Chercher</label>";
print "<input type='text' class='form-control search filter' id='' placeholder='cherche ici'/>";
print "</div>";



$stmt = $con->prepare("SELECT * FROM users WHERE user_unlink = '0' AND user_rank = 'delivery' ORDER BY user_name ASC");
$stmt->execute();
$users = $stmt->fetchAll();
if (count($users) > 0) {
print "<div class='col-md-4'>";
print "<label class='form-label'>Livreur</label>";
print "<select class='form-select  js-select user filter' id=''>";
print "<option value='0' selected>Sélectionner</option>";
foreach ($users as $row) {
print "<option value='" . $row['user_id'] . "'>" . $row['user_name'] . "</option>";
}
print "</select>";
print "</div>";
}





$stmt = $con->prepare("SELECT * FROM warehouse WHERE wh_unlink = '0' ORDER BY wh_name ASC");
$stmt->execute();
$warehouses = $stmt->fetchAll();
if (count($warehouses) > 0) {
print "<div class='col-md-4'>";
print "<label class='form-label'>Entrepôt</label>";
print "<select class='form-select  js-select warehouse filter' id=''>";
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
print "<div class='col-md-4'>";
print "<label class='form-label'>Ville</label>";
print "<select class='form-select  js-select city filter' id=''>";
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
print "<div id='dynamic_content_o'></div>";

// HTML تحميل أثناء انتظار البيانات
$load = '<div class="progress" style="height:10px;"><div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 100%"></div></div>';

?>

<script>
$(document).ready(function() {
  let ajaxRequest = null;

  function loadData(page = 1) {
    const data = {
      page: page,
      search: $('.search').val(),
      display: $('.display').val(),
      user: $('.user').val(),
      warehouse: $('.warehouse').val(),
      city: $('.city').val()
    };

    // ألغِ الطلب السابق
    if (ajaxRequest !== null) {
      ajaxRequest.abort();
    }

    ajaxRequest = $.ajax({
      url: 'getDp',
      method: 'POST',
      data: data,
      cache: false,
      beforeSend: function() {
        $('.loader').html('<?= $load ?>');
      },
      success: function(response) {
        $('#dynamic_content_o').html(response);
        $('.loader').html('');
      },
      complete: function() {
        ajaxRequest = null; // إعادة التهيئة بعد الانتهاء
      },
      error: function(xhr) {
        if (xhr.statusText !== 'abort') {
          console.error("Erreur: " + xhr.statusText);
        }
      }
    });
  }

  loadData();

  $('.filter').on('change keyup', function() {
    loadData(1);
  });

$(document).on('click', '.page-link', function (e) {
e.preventDefault();
const page = $(this).data('page');
if (page) {
loadData(page);
}
});

  $(document).on('click', '.updatedata', function() {
    loadData();
  });

});
</script>
