<?php
global $con;

function alert($message, $type = 'success') {
    return "<div class='alert alert-$type alert-dismissible fade show' role='alert'>
        $message
        <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
    </div>";
}

if (function_exists('SRM') && SRM("POST")) {
    $id = $_POST['id']; // md5(user_id)
    $new_pass = $_POST['new_password'];
    $confirm_pass = $_POST['confirm_password'];

    if (empty($new_pass) || empty($confirm_pass)) {
        exit(alert("Veuillez remplir tous les champs", "danger"));
    }

    if ($new_pass !== $confirm_pass) {
        exit(alert("Les mots de passe ne correspondent pas", "danger"));
    }

    // تحقق من وجود المستخدم
    $stmt = $con->prepare("SELECT * FROM users WHERE md5(user_id) = ?");
    $stmt->execute([$id]);

    if (!$stmt->rowCount()) {
        exit(alert("Utilisateur introuvable", "danger"));
    }

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // تحديث كلمة المرور + user_token
    $hashed = password_hash($new_pass, PASSWORD_DEFAULT);
    $newToken = md5(uniqid(rand(), true)); // 🔑 توليد توكن جديد

    $update = $con->prepare("UPDATE users SET user_pass = ?, user_token = ? WHERE user_id = ?");
    if ($update->execute([$hashed, $newToken, $user['user_id']])) {
        // حذف الكوكيز الحالية حتى يجبر على تسجيل الدخول من جديد
        if (isset($_COOKIE['login_session'])) {
            setcookie("login_session", "", time() - 3600, "/");
        }
        echo alert("✔️ Mot de passe mis à jour avec succès. Veuillez vous reconnecter.", "success");
    } else {
        echo alert("❌ Erreur lors de la mise à jour", "danger");
    }
}
?>
