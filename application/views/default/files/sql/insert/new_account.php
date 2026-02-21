<?php
global $con;

// دالة التنبيهات بـ SweetAlert
function alert($message, $type = 'success') {
$icon = $type === 'success' ? 'success' : 'error';
$title = $type === 'success' ? 'Succès' : 'Erreur';
return "<script>
Swal.fire({
icon: '$icon',
title: '$title',
html: `$message`
});
</script>";
}

// التحقق من إرسال البيانات
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

$owner        = $_POST['owner'];
$name         = $_POST['name'];
$phone        = $_POST['phone'];
$email        = $_POST['email'];
$phone_store  = $_POST['phone_store'] ?? '';
$city         = $_POST['city'];
$location     = $_POST['location'];
$cin          = $_POST['cin'];
$bank_number  = $_POST['bank_number'];
$state        = 0;
$rank         = $_POST['rank'] ?? 'user';
$password     = password_hash($_POST['new_password'], PASSWORD_DEFAULT);



// التحقق من أن الحقول الإلزامية غير فارغة
if (
empty($_POST['owner']) || empty($_POST['name']) || empty($_POST['phone']) ||
empty($_POST['email']) || empty($_POST['city']) || empty($_POST['location']) ||
empty($_POST['cin']) || empty($_POST['bank_number']) || empty($_POST['new_password']) ||
empty($_POST['confirm_password'])
) {
echo alert("Tous les champs sont obligatoires.", "danger");
exit;
}

// التحقق من تطابق كلمات المرور
if ($_POST['new_password'] !== $_POST['confirm_password']) {
echo alert("Les mots de passe ne correspondent pas.", "danger");
exit;
}

if (!preg_match('/^[0-9]{24}$/', $bank_number)) {
    echo alert("Le numéro de la banque doit contenir exactement 24 chiffres.", "danger");
    exit;
}


// فحص البريد الإلكتروني
if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
echo alert("Adresse e-mail invalide.", "danger");
exit;
}

// فحص رقم الهاتف
if (!preg_match("/^[0-9]{10}$/", $_POST['phone'])) {
echo alert("Numéro de téléphone invalide.", "danger");
exit;
}


// التحقق من أن رقم الهاتف أو البريد الإلكتروني أو CIN غير مستخدمين
$check = $con->prepare("SELECT * FROM users WHERE user_phone = :phone OR user_email = :email OR user_cin = :cin");
$check->bindParam(':phone', $phone);
$check->bindParam(':email', $email);
$check->bindParam(':cin', $cin);
$check->execute();

if ($check->rowCount() > 0) {
$existing = $check->fetch(PDO::FETCH_ASSOC);

if ($existing['user_phone'] === $phone) {
echo alert("Ce numéro de téléphone est déjà utilisé.", "danger");
} elseif ($existing['user_email'] === $email) {
echo alert("Cette adresse e-mail est déjà utilisée.", "danger");
} elseif ($existing['user_cin'] === $cin) {
echo alert("Ce numéro CIN est déjà utilisé.", "danger");
} else {
echo alert("L'utilisateur existe déjà.", "danger");
}
exit;
}

// استعلام الإدخال
$stmt = $con->prepare("INSERT INTO users (
user_owner, user_name, user_phone, user_email, user_phone_store, user_city,
user_location, user_cin, user_bank_number, user_state, user_pass, user_rank
) VALUES (
:owner, :name, :phone, :email, :phone_store, :city,
:location, :cin, :bank_number, :state, :password, :rank
)");

// ربط المتغيرات
$stmt->bindParam(':owner', $owner);
$stmt->bindParam(':name', $name);
$stmt->bindParam(':phone', $phone);
$stmt->bindParam(':email', $email);
$stmt->bindParam(':phone_store', $phone_store);
$stmt->bindParam(':city', $city);
$stmt->bindParam(':location', $location);
$stmt->bindParam(':cin', $cin);
$stmt->bindParam(':bank_number', $bank_number);
$stmt->bindParam(':state', $state);
$stmt->bindParam(':password', $password);
$stmt->bindParam(':rank', $rank);

// تنفيذ الاستعلام
if ($stmt->execute()) {
echo alert("Votre compte a été ajouté avec succès!<br><a class='btn btn-dark btn-lg mt-3' href='login_account' style='border-radius:0rem'>Se connecter</a>", "success");
} else {
echo alert("Erreur lors de l'ajout de l'utilisateur.", "danger");
}
}
?>
