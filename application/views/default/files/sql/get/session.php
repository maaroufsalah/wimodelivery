<?php
global $con;

if (isset($_COOKIE['login_session'])) {
    // الكوكيز محفوظة بهذا الشكل: user_id:token
    $cookieValue = $_COOKIE['login_session'];
    if (strpos($cookieValue, ":") === false) {
        header("Location: logout");
        exit();
    }

    list($loginId, $token) = explode(":", $cookieValue);

    $stmt = $con->prepare("SELECT * FROM users WHERE user_id = :id AND user_token = :token LIMIT 1");
    $stmt->execute([
        ':id'    => $loginId,
        ':token' => $token
    ]);

    if ($stmt->rowCount() > 0) {
        $loginUser = $stmt->fetch();

        $loginRank       = $loginUser['user_rank'];
        $loginId         = $loginUser['user_id'];
        $loginActivation = $loginUser['user_activation'];
        $loginName       = $loginUser['user_name'];
        $loginMail       = $loginUser['user_email'];
        $loginPhone      = $loginUser['user_phone'];
        $loginOwner      = $loginUser['user_owner'];
        $loginCountry    = $loginUser['user_warehouse'];
        $loginPassword   = $loginUser['user_pass'];
        $loginAvatar     = $loginUser['user_avatar'];
        $loginLocation   = $loginUser['user_location'];
        $loginCity       = $loginUser['user_city'];
        $loginBalance    = $loginUser['user_balance'];

        // إذا كان الحساب معطل
        if ($loginUser['user_unlink'] == 1) {
            header("Location: logout");
            exit();
        }

        // إذا كانت حالة الحساب غير مفعلة
        if ($loginUser['user_state'] != 1) {
            echo "<div class='container my-3'>
                    <div class='card my-3'>
                        <div class='card-body text-center my-3'>
                            <img src='uploads/$set_logo' class='img-fluid' style='width:150px'/>";

            if ($loginUser['user_state'] == "0") {
                echo "<div class='alert alert-danger my-3'>Votre compte est en cours d'activation</div>";
            } elseif ($loginUser['user_state'] == "2") {
                echo "<div class='alert alert-danger my-3'>Votre compte a été suspendu</div>";
            }

            echo "      </div>
                    </div>
                </div>";
            exit();
        }

    } else {
        // فشل التحقق (التوكن غير صالح أو كلمة السر تغيرت)
        header("Location: logout");
        exit();
    }

} else {
    load_url("login_account", 0);
    exit();
}
?>
