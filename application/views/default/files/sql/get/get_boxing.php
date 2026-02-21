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

$xoo = " box_unlink = '0' "; // للعرض فقط لا نستخدم صلاحيات خاصة

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

  echo '<div class="table-responsive">';
  echo '<table class="table table-bordered table-striped table-hover text-center">';
  echo '<thead class="table-dark">';
  echo '<tr>';
  echo '<th scope="col">Code</th>';
  echo '<th scope="col">Emballage</th>';
  echo '<th scope="col">Type</th>';
  echo '<th scope="col">Prix</th>';
  echo '<th scope="col">Image</th>';
  echo '</tr>';
  echo '</thead>';
  echo '<tbody>';

  foreach ($result as $row) {
    echo '<tr>';
    echo '<td>' . $row['box_id'] . '</td>';
    echo '<td>' . htmlspecialchars($row['box_name']) . '</td>';
    echo '<td>' . htmlspecialchars($row['box_type']) . '</td>';
    echo '<td>' . htmlspecialchars($row['box_price']) . '</td>';

    echo '<td>';
    if (!empty($row['box_photo'])) {
      $photoId = 'photoModal' . $row['box_id'];
      echo '
        <img src="uploads/box/' . htmlspecialchars($row['box_photo']) . '" 
             alt="photo" 
             style="width:60px; height:auto; cursor:pointer; border-radius:4px;"
             data-bs-toggle="modal" 
             data-bs-target="#' . $photoId . '"/>

        <div class="modal fade" id="' . $photoId . '" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title">Photo complète</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body text-center">
                <img src="uploads/box/' . htmlspecialchars($row['box_photo']) . '" 
                     alt="photo" 
                     style="max-width:100%; height:auto; border-radius:6px;">
              </div>
            </div>
          </div>
        </div>
      ';
    } else {
      echo '<span class="text-muted">Aucune</span>';
    }
    echo '</td>';

    echo '</tr>';
  }

  echo '</tbody>';
  echo '</table>';
  echo '</div>';

} else {
  echo '<div class="alert alert-warning">Aucun résultat trouvé</div>';
}

echo renderPagination($total_data, $page, $limit);
?>
