<?php 
$bootstrap = new Bootstrap();
$bootstrap->runBootstrap();

include get_file("files/sql/get/os_settings");
include get_file("files/sql/get/session");
include get_file("files/sql/get/functions");

global $con;

// إعدادات الصفحة والفلاتر
$display = isset($_POST["display"]) ? $_POST["display"] : 10;
$limit   = ($display == 10 || $display == 50 || $display == 100 || $display == 200) ? $display : 10;

$page  = isset($_POST['page']) && $_POST['page'] > 1 ? $_POST['page'] : 1;
$start = ($page - 1) * $limit;

$table  = "box";
$search = isset($_POST['search']) ? $_POST['search'] : '';

// شرط الفلترة حسب الصلاحية
$xoo = $loginRank == 'admin' ? " box_unlink = '0' " : " box_unlink = '10' ";

$query = "SELECT * FROM box WHERE $xoo ";
if ($search != '') {
  $srs = str_replace(' ', '%', $search);
  $query .= "AND (box_id LIKE '%$srs%' OR box_name LIKE '%$srs%') ";
}

$query .= "ORDER BY box_id DESC LIMIT $start, $limit";

// عدد النتائج
$countStatement = $con->prepare("SELECT COUNT(*) FROM box WHERE $xoo " . ($search != '' ? "AND (box_id LIKE '%$srs%' OR box_name LIKE '%$srs%')" : ""));
$countStatement->execute();
$total_data = $countStatement->fetchColumn();

// البيانات الفعلية
$statement = $con->prepare($query);
$statement->execute();
$result = $statement->fetchAll();

if (count($result) > 0) {

  echo "<div class='row text-center'>";
  echo "<div class='col-2'><h6><b>Code</b></h6></div>";
  echo "<div class='col-6'><h6><b>Emballage</b></h6></div>";
  echo "<div class='col-4'><h6><b>Contrôle</b></h6></div>";
  echo "</div>";

  foreach ($result as $row) {

    echo "<hr>";
    echo "<div class='row text-center'>";

    echo "<div class='col-2'>";
    echo "<h6>" . $row['box_id'] . "</h6>";
    echo "</div>";

    echo "<div class='col-6'>";
    echo "<h6>" . ($row['box_name']) . "</h6>";
    echo "<h6>Type : <b>" . ($row['box_type']) . "</b></h6>";
    echo "<h6>Prix : <b>" . ($row['box_price']) . "</b></h6>";

    if (!empty($row['box_photo'])) {
      echo "<img src='uploads/box/" . ($row['box_photo']) . "' style='width:60px; height:auto; border-radius:4px; margin-top:5px;'>";
    }

    echo "</div>";

    echo "<div class='col-4'>";
    if ($loginRank == "admin") {
      echo "<a data-bs-toggle='modal' data-bs-target='#modalUpdate" . $row['box_id'] . "' class='text-info' style='font-size: 26px; margin-right:15px;'>
              <i class='fa-regular fa-pen-to-square'></i>
            </a>";

      echo "<a data-bs-toggle='modal' data-bs-target='#modalDelete" . $row['box_id'] . "' class='text-danger' style='font-size: 26px;'>
              <i class='fa-solid fa-trash'></i>
            </a>";
    }
    echo "</div>";
    echo "</div>";

    // ✅ مودال حذف
    if ($loginRank == "admin") {
      echo "<div class='modal fade' id='modalDelete" . $row['box_id'] . "' tabindex='-1' aria-hidden='true'>";
      echo "<div class='modal-dialog modal-dialog-centered'>";
      echo "<div class='modal-content'>";
      echo "<div class='modal-header'>";
      echo "<h5 class='modal-title'>Supprimer un élément</h5>";
      echo "<button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>";
      echo "</div>";
      echo "<div class='modal-body text-center'>";
      echo "<h6>Êtes-vous sûr de vouloir supprimer cet élément ?</h6>";											
      echo "<a class='btn btn-success' href='dataUnlink?do=boxing&dataUnlinkId=" . md5($row['box_id']) . "'>Oui, je confirme</a>";
      echo "</div>";
      echo "</div>";
      echo "</div>";
      echo "</div>";
    }

    // ✅ مودال تعديل
    if ($loginRank == "admin") {

      $id     = "formId" . $row['box_id'];
      $result = "data_result" . $row['box_id'];
      $action = "editBoxing";
      $method = "post";

      formAwdStart($id, $result, $action, $method);

      echo "<input type='hidden' name='id' value='" . md5($row['box_id']) . "'/>";

      echo "<div class='modal fade' id='modalUpdate" . $row['box_id'] . "' tabindex='-1' aria-hidden='true'>";
      echo "<div class='modal-dialog modal-dialog-centered'>";
      echo "<div class='modal-content'>";
      echo "<div class='modal-header'>";
      echo "<h5 class='modal-title'>Modifier L'emballage</h5>";
      echo "<button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>";
      echo "</div>";

      echo "<div class='modal-body'>";

      print '
      <div class="col-sm-12">
        <div class="my-3">
          <div class="input">Emballage</div>
          <input name="boxing" type="text" class="form-control" value="'.($row['box_name']).'" placeholder=""/>
        </div>
      </div>';

      print '
      <div class="col-sm-12">
        <div class="my-3">
          <div class="input">Prix</div>
          <input name="price" type="number" class="form-control" value="'.($row['box_price']).'" placeholder=""/>
        </div>
      </div>';

      print '
      <div class="col-sm-12">
        <div class="my-3">
          <div class="input">Type</div>
          <input name="type" type="text" class="form-control" value="'.($row['box_type']).'" placeholder=""/>
        </div>
      </div>';

      print '
      <div class="col-sm-12">
        <div class="my-3">
          <div class="input">Changer Photo (optionnel)</div>
          <input name="photo" type="file" class="form-control" accept="image/*"/>
        </div>
      </div>';

      if (!empty($row['box_photo'])) {
        echo "<div class='text-center my-2'>
                <img src='uploads/box/" . ($row['box_photo']) . "' style='width:80px; height:auto; border-radius:4px;'>
              </div>";
      }

      echo "<div class='col-sm-12 text-center my-2'><div id='$result'></div></div>";

      echo "<div class='col-sm-12 text-center my-2'>
              <button class='btn btn-primary'>Mettre à jour</button>
            </div>";

      echo "</div>"; // modal-body
      echo "</div>"; // modal-content
      echo "</div>"; // modal-dialog
      echo "</div>"; // modal

      formAwdEnd();
    }

  }

} else {
  echo '<div class="alert alert-warning">Aucun résultat trouvé</div>';
}

// pagination
echo renderPagination($total_data, $page, $limit);
?>
