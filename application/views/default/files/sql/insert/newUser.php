<?php
global $con;

// دالة التنبيهات
function alert($message, $type = 'success') {
    return "<div class='alert alert-$type alert-dismissible fade show' role='alert'>
        $message
        <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
    </div>";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $required = ['owner', 'name', 'phone', 'email', 'city', 'location', 'cin', 'bank_number', 'state', 'new_password', 'confirm_password'];

    foreach ($required as $field) {
        if (empty(trim($_POST[$field] ?? ''))) {
            echo alert("Tous les champs sont obligatoires.", "danger");
            exit;
        }
    }

    if ($_POST['new_password'] !== $_POST['confirm_password']) {
        echo alert("Les mots de passe ne correspondent pas.", "danger");
        exit;
    }

    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        echo alert("Adresse e-mail invalide.", "danger");
        exit;
    }

    if (!preg_match("/^[0-9]{10}$/", $_POST['phone'])) {
        echo alert("Numéro de téléphone invalide.", "danger");
        exit;
    }

    // ✅ التحقق إذا كان البريد الإلكتروني موجود مسبقًا
    $checkEmail = $con->prepare("SELECT COUNT(*) FROM users WHERE user_email = :email");
    $checkEmail->execute([':email' => $_POST['email']]);
    if ($checkEmail->fetchColumn() > 0) {
        echo alert("Cette adresse e-mail est déjà utilisée.", "danger");
        exit;
    }

    // ✅ التحقق إذا كان CIN موجود مسبقًا
    $checkCIN = $con->prepare("SELECT COUNT(*) FROM users WHERE user_cin = :cin");
    $checkCIN->execute([':cin' => $_POST['cin']]);
    if ($checkCIN->fetchColumn() > 0) {
        echo alert("Ce numéro CIN est déjà utilisé.", "danger");
        exit;
    }

    $hashed_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);

    $image_name = null;
    if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] == 0) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $image_name = uniqid('profile_', true) . '.' . $ext;
        $image_tmp = $_FILES['image']['tmp_name'];
        $image_path = 'uploads/profile/' . $image_name;

        if (!move_uploaded_file($image_tmp, $image_path)) {
            echo alert("Erreur lors du téléchargement de l'image.", "danger");
            exit;
        }
    }

    $stmt = $con->prepare("
        INSERT INTO users 
        (user_owner, user_name, user_phone, user_email, user_phone_store, user_city, user_location, user_cin, user_bank_number, user_state, user_avatar, user_pass, user_rank) 
        VALUES 
        (:owner, :name, :phone, :email, :phone_store, :city, :location, :cin, :bank_number, :state, :image, :password, :rank)
    ");

    $stmt->bindValue(':owner', $_POST['owner']);
    $stmt->bindValue(':name', $_POST['name']);
    $stmt->bindValue(':phone', $_POST['phone']);
    $stmt->bindValue(':email', $_POST['email']);
    $stmt->bindValue(':phone_store', $_POST['phone_store'] ?? null);
    $stmt->bindValue(':city', $_POST['city']);
    $stmt->bindValue(':location', $_POST['location']);
    $stmt->bindValue(':cin', $_POST['cin']);
    $stmt->bindValue(':bank_number', $_POST['bank_number']);
    $stmt->bindValue(':state', $_POST['state']);
    $stmt->bindValue(':image', $image_name);
    $stmt->bindValue(':password', $hashed_password);
    $stmt->bindValue(':rank', $_POST['rank'] ?? 'user');

    if ($stmt->execute()) {
        echo alert("L'utilisateur a été ajouté avec succès!", "success");
    } else {
        echo alert("Erreur lors de l'ajout de l'utilisateur.", "danger");
    }
}
?>
