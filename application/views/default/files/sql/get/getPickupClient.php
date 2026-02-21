<?php
global $con;

include get_file("files/sql/get/os_settings");
include get_file("files/sql/get/session");
include get_file("files/sql/get/functions");

$loginRank = $loginUser['user_rank'] ?? 'guest';
$loginId   = $loginUser['user_id'] ?? 0;

/* ======================================================
   ✅ دوال مساعدة
====================================================== */

function getUserData(PDO $con, $userId) {
    if (!$userId) return null;
    $stmt = $con->prepare("SELECT * FROM users WHERE user_id = ? LIMIT 1");
    $stmt->execute([$userId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getPageArray(int $total_links, int $current_page): array {
    $page_array = [];
    if ($total_links > 4) {
        if ($current_page < 5) {
            for ($count = 1; $count <= 5; $count++) {
                $page_array[] = $count;
            }
            $page_array[] = '...';
            $page_array[] = $total_links;
        } elseif ($current_page > $total_links - 4) {
            $page_array[] = 1;
            $page_array[] = '...';
            for ($count = $total_links - 4; $count <= $total_links; $count++) {
                $page_array[] = $count;
            }
        } else {
            $page_array[] = 1;
            $page_array[] = '...';
            for ($count = $current_page - 1; $count <= $current_page + 1; $count++) {
                $page_array[] = $count;
            }
            $page_array[] = '...';
            $page_array[] = $total_links;
        }
    } else {
        for ($count = 1; $count <= $total_links; $count++) {
            $page_array[] = $count;
        }
    }
    return $page_array;
}

function getStateData(PDO $con, $stateId, $rank = null) {
    if ($rank) {
        $stmt = $con->prepare("SELECT * FROM state WHERE state_id = ? AND state_rank = ? LIMIT 1");
        $stmt->execute([$stateId, $rank]);
    } else {
        $stmt = $con->prepare("SELECT * FROM state WHERE state_id = ? LIMIT 1");
        $stmt->execute([$stateId]);
    }
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function printPagination(int $total_data, int $limit, int $current_page) {
    $total_links = ceil($total_data / $limit);
    echo '<div class="card" style="border-radius:0rem">';
    echo '<div class="card-body text-center">';
    echo '<div><ul class="pagination text-center my-0" style="display: inline-flex;">';

    if ($current_page > 1) {
        echo '<li class="page-item">
                <a class="page-link" href="javascript:void(0)" data-page_number="' . ($current_page - 1) . '">
                    <span class="material-symbols-outlined">chevron_left</span>
                </a>
              </li>';
    } else {
        echo '<li class="page-item disabled">
                <a class="page-link" href="#">
                    <span class="material-symbols-outlined">chevron_left</span>
                </a>
              </li>';
    }

    $page_array = getPageArray($total_links, $current_page);
    foreach ($page_array as $page) {
        if ($page == '...') {
            echo '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
        } elseif ($page == $current_page) {
            echo '<li class="page-item active">
                    <a class="page-link" href="#" style="font-size:19px">' . $page . '</a>
                  </li>';
        } else {
            echo '<li class="page-item">
                    <a class="page-link" href="javascript:void(0)" data-page_number="' . $page . '">' . $page . '</a>
                  </li>';
        }
    }

    if ($current_page < $total_links) {
        echo '<li class="page-item">
                <a class="page-link" href="javascript:void(0)" data-page_number="' . ($current_page + 1) . '">
                    <span class="material-symbols-outlined">chevron_right</span>
                </a>
              </li>';
    } else {
        echo '<li class="page-item disabled">
                <a class="page-link" href="#">
                    <span class="material-symbols-outlined">chevron_right</span>
                </a>
              </li>';
    }

    echo '</ul></div></div></div>';
}

function printNoResults() {
    echo "<div class='text-center my-5'>";
    echo "<i class='fa-solid fa-file-half-dashed fa-3x my-2'></i>";
    echo "<h6>Aucun résultat trouvé</h6>";
    echo "</div>";
}

/* ======================================================
   ✅ الكود التنفيذي
====================================================== */

$display = POST("display");
$limit   = in_array($display, ['50', '100', '200']) ? (int)$display : 10;

$page  = (POST('page') && POST('page') > 1) ? (int)POST('page') : 1;
$start = ($page - 1) * $limit;

$table   = "pickup";
$user    = POST("user");
$state   = POST("state");
$delivery = POST("delivery");

switch ($loginRank) {
    case 'admin':    $xoo = " pi_unlink = '0' "; break;
    case 'user':     $xoo = " pi_user = :loginId "; break;
    case 'delivery': $xoo = " pi_delivery_user = :loginId "; break;
    case 'aide':     $xoo = " pi_user = :loginUser "; break;
    default:         $xoo = " pi_unlink = '10' "; break;
}

$query  = "SELECT * FROM $table WHERE $xoo";
$params = [];

if (in_array($loginRank, ['user', 'delivery'])) {
    $params[':loginId'] = $loginId;
} elseif ($loginRank === 'aide') {
    $params[':loginUser'] = $loginUser['user_aide'];
}

if (!empty($_POST['search'])) {
    $srs = '%' . str_replace(' ', '%', trim(strip_tags($_POST['search']))) . '%';
    $query .= " AND (pi_id LIKE :search OR pi_phone LIKE :search OR pi_note LIKE :search)";
    $params[':search'] = $srs;
}

if (!empty($user) && is_numeric($user)) {
    $query .= " AND pi_user = :user";
    $params[':user'] = $user;
}
if (!empty($state) && is_numeric($state)) {
    $query .= " AND pi_state = :state";
    $params[':state'] = $state;
}
if (!empty($delivery) && is_numeric($delivery)) {
    $query .= " AND pi_delivery_user = :delivery";
    $params[':delivery'] = $delivery;
}

$countQuery  = preg_replace('/SELECT \* FROM/i', 'SELECT COUNT(*) FROM', $query, 1);
$countStmt   = $con->prepare($countQuery);
$countStmt->execute($params);
$total_data  = (int)$countStmt->fetchColumn();

if ($total_data > 0) {
    $filter_query = $query . " ORDER BY pi_id DESC LIMIT :start, :limit";
    $stmt = $con->prepare($filter_query);

    foreach ($params as $k => $v) {
        $stmt->bindValue($k, $v);
    }

    $stmt->bindValue(':start', (int)$start, PDO::PARAM_INT);
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<form method='POST' id='pickup_form'>";
    echo '<div class="table-responsive">';
    echo '<table class="table table-hover table-striped table-bordered align-middle text-center">';
    echo '<thead class="table-dark">';
    echo '<tr>';
    echo '<th>Code</th>';
    echo '<th>Date</th>';
    echo '<th>Type</th>';
    if ($loginRank == "admin") { echo '<th>Client</th>'; }
    if ($loginRank == "admin") { echo '<th>Livreur</th>'; }
    echo '<th>Téléphone</th>';
    echo '<th>Remarque</th>';
    echo '<th>Adresse</th>';
    echo '<th>État</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';

    $idsToUpdate = [];

    foreach ($result as $row) {
        if ($loginRank === "admin") {
            $idsToUpdate[] = $row['pi_id'];
        }

        $stateRow     = getStateData($con, $row['pi_state']);
        $deliveryUser = getUserData($con, $row['pi_delivery_user']);
        $seller       = getUserData($con, $row['pi_user']);

        echo "<tr>";
        echo "<td>";
        if ($row['pi_state'] !== 52) {
            echo "<div class='form-check'>
                <input class='form-check-input order-checkbox' type='checkbox' name='id[]' id='chk_{$row['pi_id']}' value='{$row['pi_id']}'>
                <label class='form-check-label' for='chk_{$row['pi_id']}'><b>{$row['pi_id']}</b></label>
            </div>";
        } else {
            echo "<b>{$row['pi_id']}</b>";
        }
        echo "</td>";

        echo "<td>" . date('d/m/Y H:i', strtotime($row['pi_date'])) . "</td>";
        echo "<td>" . ($row['pi_type']) . "</td>";

        if ($loginRank == "admin") {
            echo "<td>" . ($seller['user_name'] ?? '--') . "<br><small>" . ($seller['user_phone'] ?? '--') . "</small></td>";
        }
        if ($loginRank == "admin") {
            echo "<td>" . ($deliveryUser['user_name'] ?? '--') . "<br><small>" . ($deliveryUser['user_phone'] ?? '--') . "</small></td>";
        }

        echo "<td>
            <a href='tel:{$row['pi_phone']}' class='btn btn-sm btn-outline-success' rel='noopener noreferrer'>
                <i class='fa-solid fa-phone'></i> {$row['pi_phone']}
            </a>
            </td>";

        echo "<td>" . ($row['pi_note']) . "</td>";
        echo "<td>" . ($row['pi_location']) . "</td>";

        echo "<td>";
        if (!empty($stateRow)) {
            $state_name = $stateRow['state_name'];
            $state_bg   = ($stateRow['state_background'] ?? '#ddd');
            $state_color= ($stateRow['state_color'] ?? '#000');
            $modal_id   = "modal_state{$row['pi_id']}";
            echo "<a data-bs-toggle='modal' data-bs-target='#{$modal_id}' class='btn btn-sm' style='background:{$state_bg};color:{$state_color};'><b>{$state_name}</b></a>";
        } else {
            echo "<br><h6 class='text-danger'><i class='fa-solid fa-spinner'></i> En Attente</h6>";
        }
        echo "</td>";

        echo "</tr>";
    }

    echo "</tbody></table></div>";

    if (!empty($idsToUpdate)) {
        $in = implode(',', array_fill(0, count($idsToUpdate), '?'));
        $updateStmt = $con->prepare("UPDATE pickup SET pi_seen = 1 WHERE pi_id IN ($in)");
        $updateStmt->execute($idsToUpdate);
    }

    printPagination($total_data, $limit, $page);
    echo "</form>";

} else {
    printNoResults();
}

?>
