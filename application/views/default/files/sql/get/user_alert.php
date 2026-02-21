
<?php 
// بداية الكود الأصلي بدون تغيير
$bootstrap = new Bootstrap();
$bootstrap->runBootstrap();

include get_file("files/sql/get/os_settings");
include get_file("files/sql/get/session");
include get_file("files/sql/get/functions");

global $con;



// جلب الأخبار مع شرط فلترة منطقية داخل SQL لتسريع الأداء
$stmt = $con->prepare("
    SELECT * FROM news
    WHERE n_unlink = '0'
      AND n_rank = 'user'
      AND (n_user = :loginId OR n_user IS NULL OR n_user = '')
    ORDER BY n_id DESC
");

$stmt->execute(['loginId' => $loginId]);
$alerts = $stmt->fetchAll();

if (count($alerts) > 0) {
    foreach ($alerts as $a_row) {

        // لا حاجة لفحص $a_row['n_user'] هنا لأن الفلترة تمت في SQL

        if ($a_row['n_type'] == "alert") {
            echo "
            <div class='alert my-2' style='background:{$a_row['n_bg']};color:{$a_row['n_color']}'>
                " . html_entity_decode($a_row['n_note']) . "
            </div>
            ";
        } elseif ($a_row['n_type'] == "pop") {
            ?>
            <!-- Modal Bootstrap -->
            <div class="modal fade" id="autoOpenModal<?=$a_row['n_id'];?>" tabindex="-1" aria-labelledby="autoOpenModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">

                        <div class="modal-header">
                            <h5 class="modal-title" id="autoOpenModalLabel">Actualités</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body">
                            <?php if (!empty($a_row['n_image'])): ?>
                                <img src='uploads/news/<?=$a_row['n_image'];?>' class='img-fluid' style='width:100%'/>
                            <?php endif; ?>
                            <div class="mt-3">
                                <?= html_entity_decode($a_row['n_note']); ?>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Script to auto open modal -->
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    var myModal<?=$a_row['n_id'];?> = new bootstrap.Modal(document.getElementById('autoOpenModal<?=$a_row['n_id'];?>'));
                    myModal<?=$a_row['n_id'];?>.show();
                });
            </script>
            <?php
        }
    }
}
?>





