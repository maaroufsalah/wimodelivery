<?php 
$bootstrap = new Bootstrap();
$bootstrap->runBootstrap();

include get_file("files/sql/get/os_settings");
include get_file("files/sql/get/session");
include get_file("files/sql/get/functions");

global $con;

$display = isset($_POST["display"]) ? $_POST["display"] : 10;
$limit = in_array($display, [10, 50, 100, 200]) ? $display : 10;

$page = isset($_POST['page']) && $_POST['page'] > 1 ? $_POST['page'] : 1;
$start = ($page - 1) * $limit;

$table  = "city";
$search = $_POST['search'] ?? '';

$xoo = $loginRank == 'admin' ? " city_unlink = '0' " : " city_unlink = '10' ";

// -------- استعلام العــدد --------
$countQuery = "SELECT COUNT(*) FROM city WHERE $xoo";
if ($search != '') {
    $srs = str_replace(' ', '%', $search);
    $countQuery .= " AND (city_id LIKE '%$srs%' OR city_name LIKE '%$srs%') ";
}
$countStmt = $con->prepare($countQuery);
$countStmt->execute();
$total_data = $countStmt->fetchColumn();

// -------- استعلام البـيانات --------
$query = "SELECT * FROM city WHERE $xoo ";
if ($search != '') {
    $query .= "AND (city_id LIKE '%$srs%' OR city_name LIKE '%$srs%') ";
}
$query .= "ORDER BY city_id DESC LIMIT $start, $limit";

$statement = $con->prepare($query);
$statement->execute();
$result = $statement->fetchAll();

// -------- عرض النــتائج --------
if (count($result) > 0) {
    echo "<div class='row text-center'>
            <div class='col-4'><h6><b>Codes</b></h6></div>
            <div class='col-4'><h6><b>Villes</b></h6></div>
            <div class='col-4'><h6><b>Contrôles</b></h6></div>
          </div>";

    foreach ($result as $row) {
        echo "<hr><div class='row text-center'>
                <div class='col-sm-4'><h6>{$row['city_id']}</h6></div>
                <div class='col-4'><h6>{$row['city_name']}</h6></div>
                <div class='col-4 text-left'>";
        if ($loginRank == "admin") {
            echo "<a data-bs-toggle='modal' data-bs-target='#modalUpdate{$row['city_id']}' class='text-info' style='font-size: 26px;'>
                    <i class='fa-regular fa-pen-to-square'></i>
                  </a>
                  <a data-bs-toggle='modal' data-bs-target='#modalDelete{$row['city_id']}' class='text-danger' style='font-size: 26px;'>
                    <i class='fa-solid fa-trash'></i>
                  </a>";
        }
        echo "</div></div>";

        // مودال الحذف
        if ($loginRank == "admin") {
            echo "<div class='modal fade' id='modalDelete{$row['city_id']}' tabindex='-1' aria-hidden='true'>
                    <div class='modal-dialog modal-dialog-centered'>
                      <div class='modal-content'>
                        <div class='modal-header'>
                          <h5 class='modal-title'>Supprimer un élément</h5>
                          <button type='button' class='btn-close' data-bs-dismiss='modal'></button>
                        </div>
                        <div class='modal-body text-center'>
                          <h6>Êtes-vous sûr de bien vouloir supprimer cet élément ?</h6>
                          <a class='btn btn-success' href='dataUnlink?do=city&dataUnlinkId=" . md5($row['city_id']) . "'>Oui, je veux</a>
                        </div>
                      </div>
                    </div>
                  </div>";
        }

        // مودال التعديل
        if ($loginRank == "admin") {
            $id = "formId" . $row['city_id'];
            $resultId = "data_result" . $row['city_id'];
            $action = "editCity";
            $method = "post";
            formAwdStart($id, $resultId, $action, $method);
            echo "<input type='hidden' name='id' value='" . md5($row['city_id']) . "'/>";
            echo "<div class='modal fade' id='modalUpdate{$row['city_id']}' tabindex='-1' aria-hidden='true'>
                    <div class='modal-dialog modal-dialog-centered'>
                      <div class='modal-content'>
                        <div class='modal-header'>
                          <h5 class='modal-title'>Modifier Ville</h5>
                          <button type='button' class='btn-close' data-bs-dismiss='modal'></button>
                        </div>
                        <div class='modal-body'>
                          <div class='my-3'>
                            <label class='input'>Le nom de ville</label>
                            <input name='city' type='text' class='form-control' value='{$row['city_name']}' placeholder=''/>
                          </div>
                          <div id='$resultId' class='text-center my-2'></div>
                          <div class='text-center my-2'>
                            <button class='btn btn-primary'>Mise à jour</button>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>";
            formAwdEnd();
        }
    }

    echo "</div>";
    echo renderPagination($total_data, $page, $limit);
} else {
    echo "<div class='alert alert-warning'>Aucun résultat trouvé</div>";
}
?>
