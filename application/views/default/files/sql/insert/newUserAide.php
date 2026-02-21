<?php
global $con;

include get_file("files/sql/get/os_settings");
include get_file("files/sql/get/session");
include get_file("files/sql/get/functions");

// دالة الطباعة
function alert($message, $type = 'success') {
    return "<div class='alert alert-$type alert-dismissible fade show' role='alert'>
    $message
    <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
    </div>";
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // الحقول المطلوبة
    $required = ['owner', 'name', 'phone', 'email', 'phone_store', 'city', 'location', 'state', 'new_password', 'confirm_password'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            echo alert("Tous les champs sont obligatoires.", "danger");
            exit;
        }
    }

    // كلمات المرور
    if ($_POST['new_password'] !== $_POST['confirm_password']) {
        echo alert("Les mots de passe ne correspondent pas.", "danger");
        exit;
    }

    // البريد
    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        echo alert("Adresse e-mail invalide.", "danger");
        exit;
    }

    // الهاتف
    if (!preg_match("/^[0-9]{10}$/", $_POST['phone'])) {
        echo alert("Numéro de téléphone invalide.", "danger");
        exit;
    }

    // تحقق من تكرار البريد
    $check = $con->prepare("SELECT COUNT(*) FROM users WHERE user_email = :email");
    $check->execute([':email' => $_POST['email']]);
    if ($check->fetchColumn() > 0) {
        echo alert("Cette adresse e-mail est déjà utilisée.", "danger");
        exit;
    }

    // كلمة المرور
    $hashed_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);

    // الصورة
    $image_name = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($ext, $allowed)) {
            echo alert("Format d'image non autorisé.", "danger");
            exit;
        }
        if ($_FILES['image']['size'] > 2 * 1024 * 1024) {
            echo alert("L'image est trop volumineuse.", "danger");
            exit;
        }
        $image_name = uniqid('profile_', true) . '.' . $ext;
        $image_tmp = $_FILES['image']['tmp_name'];
        $image_path = 'uploads/profile/' . $image_name;
        if (!move_uploaded_file($image_tmp, $image_path)) {
            echo alert("Erreur lors du téléchargement de l'image.", "danger");
            exit;
        }
    }

    // إضافة المستخدم
    $stmt = $con->prepare("
        INSERT INTO users 
        (user_aide, user_owner, user_name, user_phone, user_email, user_phone_store, user_city, user_location, user_cin, user_bank_number, user_state, user_avatar, user_pass, user_rank) 
        VALUES 
        (:aide, :owner, :name, :phone, :email, :phone_store, :city, :location, :cin, :bank_number, :state, :image, :password, 'aide')
    ");
    $stmt->bindParam(':aide', $loginId);
    $stmt->bindParam(':owner', $_POST['owner']);
    $stmt->bindParam(':name', $_POST['name']);
    $stmt->bindParam(':phone', $_POST['phone']);
    $stmt->bindParam(':email', $_POST['email']);
    $stmt->bindParam(':phone_store', $_POST['phone_store']);
    $stmt->bindParam(':city', $_POST['city']);
    $stmt->bindParam(':location', $_POST['location']);
    $stmt->bindParam(':cin', $_POST['cin']);
    $stmt->bindParam(':bank_number', $_POST['bank_number']);
    $stmt->bindParam(':state', $_POST['state']);
    $stmt->bindParam(':image', $image_name);
    $stmt->bindParam(':password', $hashed_password);

    if ($stmt->execute()) {

        // الحصول على ID المستخدم الجديد
        $user_id = $con->lastInsertId();

        // إضافة الصلاحيات
        $via = isset($_POST['via']) ? $_POST['via'] : [];
        if (!empty($via) && is_array($via)) {

            $placeholders = implode(',', array_fill(0, count($via), '?'));
            $permStmt = $con->prepare("SELECT per_id FROM permission WHERE per_id IN ($placeholders)");
            $permStmt->execute($via);
            $permissions = $permStmt->fetchAll();

            if (count($permissions) > 0) {
                // حذف قديم - غير ضروري لأنه مستخدم جديد، لكن احتياطاً:
                $delStmt = $con->prepare("DELETE FROM permission_checker WHERE pc_user = ?");
                $delStmt->execute([$user_id]);

                $insertPerm = $con->prepare("INSERT INTO permission_checker (pc_user, pc_via) VALUES (?, ?)");
                foreach ($permissions as $perm) {
                    $insertPerm->execute([$user_id, $perm['per_id']]);
                }
            }
        }

        echo alert("L'utilisateur a été ajouté avec succès avec ses permissions!", "success");
        if (function_exists('load_url')) {
            load_url("staffs", 2);
        }

    } else {
        echo alert("Erreur lors de l'ajout de l'utilisateur.", "danger");
    }
}
?>
