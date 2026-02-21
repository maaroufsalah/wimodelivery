<?php 

function fd($date, $format = "d/m/Y H:i") {
    if (empty($date)) return "";
    $dt = new DateTime($date);
    return $dt->format($format);
}


function formAwdStart($id, $result, $action, $method = 'POST') {
    $progressBar = '<div class="progress" style="height:10px;">
        <div class="progress-bar progress-bar-striped progress-bar-animated" 
             role="progressbar" 
             aria-valuenow="75" 
             aria-valuemin="0" 
             aria-valuemax="100" 
             style="width: 100%">
        </div>
    </div>';

    echo "
    <script>
    $(document).ready(function () {
        $('#$id').on('submit', function (e) {
            e.preventDefault();
            $.ajax({
                url: '$action',
                type: '$method',
                data: new FormData(this),
                contentType: false,
                cache: false,
                processData: false,
                beforeSend: function () {
                    $('#$result').html(`$progressBar`);
                },
                success: function (response) {
                    $('#$result').html(response);
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    $('#$result').html('<div class=\"alert alert-danger\">An error occurred: ' + textStatus + '</div>');
                }
            });
        });
    });
    </script>
    ";
    
    echo "<form id='$id' action='$action' method='$method' enctype='multipart/form-data'>";
}




function formAwdEnd (){

print "</form>";

}


function slugify($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);      // استبدال الفراغات والعلامات بـ "-"
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);    // تحويل الأحرف إلى ascii
    $text = preg_replace('~[^-\w]+~', '', $text);           // إزالة أي شيء غير مسموح به
    $text = trim($text, '-');                               // حذف الـ "-" من البداية والنهاية
    $text = preg_replace('~-+~', '-', $text);               // دمج الشرطات المتكررة
    $text = strtolower($text);                              // تحويل إلى أحرف صغيرة

    return empty($text) ? 'produit' : $text;
}








// دالة لحذف صفوف من جدول معين بشرط
function SQLUNLINK($table, $where) {
    global $con;

    try {
        $con->beginTransaction();
        $stmt = $con->prepare("DELETE FROM $table WHERE $where");
        $result = $stmt->execute();
        $con->commit();
        return $result;
    } catch (PDOException $e) {
        $con->rollBack();
        return false;
    }
}

// دالة للتحقق من وجود قيمة في جدول معين
function checkItem($field, $table, $value) {
    global $con;

    try {
        $con->beginTransaction();
        $stmt = $con->prepare("SELECT COUNT(*) FROM $table WHERE $field = ?");
        $stmt->execute([$value]);
        $count = $stmt->fetchColumn();
        $con->commit();
        return $count > 0;
    } catch (PDOException $e) {
        $con->rollBack();
        return false;
    }
}





function escape($html) {
    return htmlspecialchars($html, ENT_QUOTES, 'UTF-8');
}

function hasUserPermission(PDO $db, $userId, $permissionVia, $loginRank) {
    // نتحقق أولًا إن لم يكن المستخدم Admin، فلا داعي للتحقق من الصلاحيات
    if ($loginRank !== "admin") {
        return true; // نعطي صلاحية افتراضيًا
    }

    try {
        $stmt = $db->prepare("
            SELECT 1 
            FROM permission_checker 
            WHERE pc_user = :userId AND pc_via = :permissionVia 
            LIMIT 1
        ");
        $stmt->bindParam(':userId', $userId);
        $stmt->bindParam(':permissionVia', $permissionVia);
        $stmt->execute();
        return $stmt->fetchColumn() !== false;
    } catch (PDOException $e) {
        return false;
    }
}

function hasUserPermissionAide(PDO $db, $userId, $permissionVia, $loginRank) {
    // نتحقق أولًا إن لم يكن المستخدم Admin، فلا داعي للتحقق من الصلاحيات
    if ($loginRank !== "aide") {
        return true; // نعطي صلاحية افتراضيًا
    }

    try {
        $stmt = $db->prepare("
            SELECT 1 
            FROM permission_checker 
            WHERE pc_user = :userId AND pc_via = :permissionVia 
            LIMIT 1
        ");
        $stmt->bindParam(':userId', $userId);
        $stmt->bindParam(':permissionVia', $permissionVia);
        $stmt->execute();
        return $stmt->fetchColumn() !== false;
    } catch (PDOException $e) {
        return false;
    }
}


function renderPagination($total_data, $page, $limit) {
    $output  = '<div class="alert alert-info my-2">Total : <b>' . $total_data . '</b></div>';
    $output .= '<div class="pagination-wrapper text-center"><ul class="pagination mt-3" style="display: inline-flex;">';

    $total_links = ceil($total_data / $limit);
    $previous_link = '';
    $next_link = '';
    $page_link = '';
    $page_array = [];

    if ($total_links > 4) {
        if ($page < 5) {
            for ($count = 1; $count <= 5; $count++) {
                $page_array[] = $count;
            }
            $page_array[] = '...';
            $page_array[] = $total_links;
        } else {
            $end_limit = $total_links - 5;
            if ($page > $end_limit) {
                $page_array[] = 1;
                $page_array[] = '...';
                for ($count = $end_limit; $count <= $total_links; $count++) {
                    $page_array[] = $count;
                }
            } else {
                $page_array[] = 1;
                $page_array[] = '...';
                for ($count = $page - 1; $count <= $page + 1; $count++) {
                    $page_array[] = $count;
                }
                $page_array[] = '...';
                $page_array[] = $total_links;
            }
        }
    } else {
        for ($count = 1; $count <= $total_links; $count++) {
            $page_array[] = $count;
        }
    }

    if (is_array($page_array) && count($page_array)) {
        for ($count = 0; $count < count($page_array); $count++) {
            if ($page == $page_array[$count]) {
                $page_link .= '
                <li class="page-item active">
                  <a class="page-link" href="#" style="font-size:19px" data-page="' . $page_array[$count] . '">' . $page_array[$count] . '</a>
                </li>
                ';

                $previous_id = $page_array[$count] - 1;
                if ($previous_id > 0) {
                    $previous_link = '
                    <li class="page-item">
                      <a class="page-link" href="javascript:void(0)" data-page="' . $previous_id . '">
                        <span class="material-symbols-outlined">chevron_left</span>
                      </a>
                    </li>
                    ';
                } else {
                    $previous_link = '
                    <li class="page-item disabled">
                      <a class="page-link" href="#"><span class="material-symbols-outlined">chevron_left</span></a>
                    </li>
                    ';
                }

                $next_id = $page_array[$count] + 1;
                if ($next_id > $total_links) {
                    $next_link = '
                    <li class="page-item disabled">
                      <a class="page-link" href="#"><span class="material-symbols-outlined">chevron_right</span></a>
                    </li>
                    ';
                } else {
                    $next_link = '
                    <li class="page-item">
                      <a class="page-link" href="javascript:void(0)" data-page="' . $next_id . '">
                        <span class="material-symbols-outlined">chevron_right</span>
                      </a>
                    </li>
                    ';
                }
            } else {
                if ($page_array[$count] == '...') {
                    $page_link .= '
                    <li class="page-item disabled">
                      <a class="page-link" href="#">...</a>
                    </li>
                    ';
                } else {
                    $page_link .= '
                    <li class="page-item">
                      <a class="page-link" href="javascript:void(0)" data-page="' . $page_array[$count] . '">' . $page_array[$count] . '</a>
                    </li>
                    ';
                }
            }
        }
    }

    $output .= $previous_link . $page_link . $next_link;
    $output .= '</ul></div>';

    echo $output;
}



function get_value_sql($sql, $params = []) {
    global $con;
    $stmt = $con->prepare($sql);
    $stmt->execute($params);
    $result = $stmt->fetchColumn();
    return $result !== false ? $result : '';
}


function get_value($table, $column, $where, $params = []) {
    global $con;
    $sql = "SELECT $column FROM $table WHERE $where LIMIT 1";
    $stmt = $con->prepare($sql);
    $stmt->execute($params);
    $result = $stmt->fetchColumn();
    return $result !== false ? $result : '';
}

?>


