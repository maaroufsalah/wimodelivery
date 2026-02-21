<?php 

global $con;

// تضمين الملفات اللازمة
include get_file("files/sql/get/os_settings");
include get_file("files/sql/get/session");
include get_file("files/sql/get/functions");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $via  = isset($_POST['via']) ? $_POST['via'] : [];
    $user = isset($_POST['uid']) ? $_POST['uid'] : '';

    if (empty($user)) {
        echo "<div class='alert alert-danger'>Veuillez remplir tous les champs</div>";
        exit();
    }

    // تحقق من وجود المستخدم (نفترض أن $user هو md5(user_id))
    $stmt = $con->prepare("SELECT * FROM users WHERE md5(user_id) = ?");
    $stmt->execute([$user]);
    $userResult = $stmt->fetch();

    if (!$userResult) {
        echo "<div class='alert alert-danger'>Utilisateur non trouvé</div>";
        exit();
    }

    if (!empty($via) && is_array($via)) {
        // إنشاء placeholders لمصفوفة $via
        $placeholders = implode(',', array_fill(0, count($via), '?'));

        // جلب الصلاحيات المطلوبة فقط
        $stmt = $con->prepare("SELECT * FROM permission WHERE per_id IN ($placeholders)");
        $stmt->execute($via);
        $checkResult = $stmt->fetchAll();

        if (count($checkResult) > 0) {
            // حذف الصلاحيات القديمة للمستخدم
            $delStmt = $con->prepare("DELETE FROM permission_checker WHERE pc_user = ?");
            $delStmt->execute([$userResult['user_id']]);

            // إدخال الصلاحيات الجديدة
            $insertStmt = $con->prepare("INSERT INTO permission_checker (pc_user, pc_via) VALUES (?, ?)");

            foreach ($checkResult as $row) {
                $perId = $row['per_id'];

                // يمكن حذف التحقق من التكرار لأنك حذفت كل الصلاحيات سابقاً
                $insertStmt->execute([$userResult['user_id'], $perId]);
            }
        }
    }

    // إنهاء الاتصال بقاعدة البيانات
    $con = null;

    // إعادة التوجيه بعد ثانيتين (إذا الدالة موجودة)
    if (function_exists('load_url')) {
        load_url("", 2);
    }
}
?>
