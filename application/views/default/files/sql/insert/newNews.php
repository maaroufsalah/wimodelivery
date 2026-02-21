<?php
global $con;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

$color      = $_POST['color'] ?? '';
$background = $_POST['background'] ?? '';
$rank       = $_POST['rank'] ?? 0;
$type       = $_POST['type'] ?? '';
$details    = trim($_POST['details'] ?? '');
$user_id    = 0;

// التحقق العام
if (empty($type)) {
echo '
<div class="alert alert-danger alert-dismissible fade show" role="alert">
⚠️ Veuillez choisir un type.
<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
';
exit();
}

// تحقق خاص لكل نوع
if ($type == 'pop') {
if (empty($_FILES['image']['name'])) {
echo '
<div class="alert alert-danger alert-dismissible fade show" role="alert">
⚠️ Pour le type POP, veuillez choisir une image .
<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
';
exit();
}
}

if ($type == 'alert') {
if (empty($details) || empty($color) || empty($background)) {
echo '
<div class="alert alert-danger alert-dismissible fade show" role="alert">
⚠️ Pour le type Alert, veuillez remplir les détails, la couleur et le background.
<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
';
exit();
}
}

// رفع الصورة إذا موجودة
$imageName = '';
if (!empty($_FILES['image']['name'])) {
$targetDir = "uploads/news/";
if (!file_exists($targetDir)) {
mkdir($targetDir, 0777, true);
}
$imageName = time() . "_" . basename($_FILES["image"]["name"]);
$targetFile = $targetDir . $imageName;
move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile);
}

// إدخال البيانات
$stmt = $con->prepare("
INSERT INTO news 
(n_color, n_bg, n_note, n_rank, n_type, n_user, n_image, n_unlink)
VALUES (:color, :background, :details, :rank, :type, :user, :image, 0)
");
$stmt->execute([
':color'      => $color,
':background' => $background,
':details'    => htmlspecialchars($details, ENT_QUOTES, 'UTF-8'),
':rank'       => $rank,
':type'       => $type,
':user'       => $user_id,
':image'      => $imageName
]);

echo '
<div class="alert alert-success alert-dismissible fade show" role="alert">
✅ News ajouté avec succès !
<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
';

echo '
<script>
document.getElementById("formId").reset();
</script>
';

$con = null;
}
?>
