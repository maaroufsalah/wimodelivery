<?php
global $con;

// دالة التنبيهات
function alert($message, $type = 'success') {
return "<div class='alert alert-$type alert-dismissible fade show' role='alert'>
$message
<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
</div>";
}

// التأكد من أن الطلب من نوع POST
if (function_exists('SRM') && SRM("POST")) {

// استقبال البيانات
$id = $_POST['id'];
$owner = trim($_POST['owner']);
$name = trim($_POST['name']);
$email = trim($_POST['email']);
$phone = trim($_POST['phone']);
$phone_store = trim($_POST['phone_store']);
$location = trim($_POST['location']);
$city = intval($_POST['city']);
$cin = trim($_POST['cin']);
$bank = trim($_POST['bank_number']);
$state = intval($_POST['state']);
$identity = intval($_POST['identity']);

// التحقق من وجود المستخدم - يمكنك استخدام user_id مباشرة أو md5(user_id)
$check = $con->prepare("SELECT * FROM users WHERE md5(user_id) = ?"); // <-- استخدم هذا إذا كنت ترسل md5
// $check = $con->prepare("SELECT * FROM users WHERE user_id = ?");  // <-- استخدم هذا إذا كنت ترسل ID عادي
$check->execute([$id]);

if (!$check->rowCount()) {
exit(alert("Utilisateur introuvable", "danger"));
}

$old = $check->fetch(PDO::FETCH_ASSOC);

// معالجة الصورة إذا تم رفعها
$image_name = null;
if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
$allowed = ['jpg', 'jpeg', 'png', 'webp'];
$ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

if (!in_array($ext, $allowed)) {
exit(alert("Type de fichier non autorisé", "danger"));
}

// تحديد مسار حفظ الصورة
$image_name = uniqid("user_") . "." . $ext;
$upload_dir = $_SERVER['DOCUMENT_ROOT'] . "/uploads/profile/";
$upload_path = $upload_dir . $image_name;

// إنشاء المجلد إذا لم يكن موجودًا
if (!is_dir($upload_dir)) {
mkdir($upload_dir, 0775, true);
}

// نقل الصورة
if (!move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
exit(alert("Échec du téléchargement de l'image", "danger"));
}

// حذف الصورة القديمة
if (!empty($old['user_avatar'])) {
$old_path = $upload_dir . $old['user_avatar'];
if (file_exists($old_path)) {
unlink($old_path);
}
}
}

// تحديث البيانات في قاعدة البيانات
$sql = "UPDATE users SET 
user_owner = :owner,
user_name = :name,
user_email = :email,
user_phone = :phone,
user_phone_store = :phone_store,
user_location = :location,
user_city = :city,
user_cin = :cin,
user_bank_number = :bank,
user_identity = :identity,
user_state = :state";

if ($image_name) {
$sql .= ", user_avatar = :image";
}

$sql .= " WHERE user_id = :id";

$stmt = $con->prepare($sql);

$params = [
"owner" => $owner,
"name" => $name,
"email" => $email,
"phone" => $phone,
"phone_store" => $phone_store,
"location" => $location,
"city" => $city,
"cin" => $cin,
"bank" => $bank,
"identity" => $identity,
"state" => $state,
"id" => $old['user_id'], // نستخدم user_id الحقيقي من قاعدة البيانات
];

if ($image_name) {
$params['image'] = $image_name;
}

// تنفيذ التحديث
if ($stmt->execute($params)) {
echo alert("✔️ Modifications enregistrées avec succès", "success");
} else {
echo alert("❌ Erreur lors de la mise à jour", "danger");
}
}
?>
