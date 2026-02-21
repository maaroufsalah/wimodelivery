<?php

use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;

global $con;

include get_file("files/sql/get/os_settings");
include get_file("files/sql/get/session");

// جلب المدن
$stmt = $con->prepare("SELECT city_id, city_name FROM city WHERE city_unlink = 0 ORDER BY city_name ASC");
$stmt->execute();
$cities = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $con->prepare("SELECT * FROM users WHERE user_unlink = 0 AND user_rank = 'user' ORDER BY user_name ASC");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $con->prepare("SELECT * FROM warehouse WHERE wh_unlink = 0 ORDER BY wh_name ASC");
$stmt->execute();
$warehouse = $stmt->fetchAll(PDO::FETCH_ASSOC);

// خيارات select الخاصة
$selectOptions = [
8 => ['oui', 'non'],
9 => ['oui', 'non'],
10 => ['oui', 'non'],
];

?>

<style>
form { margin: 0; }
table { width: 100%; text-transform: capitalize; display: block; }
td, th { vertical-align: middle; }
input, select { width: 100%; box-sizing: border-box; }
</style>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file_upload']) && $_FILES['file_upload']['error'] === UPLOAD_ERR_OK) {

$tmpName = $_FILES['file_upload']['tmp_name'];
$user = POST("user",0,'int');
$warehouse = POST("warehouse",0,'int');

try {
$reader = new XlsxReader();
$reader->setReadDataOnly(true);
$spreadsheet = $reader->load($tmpName);
$worksheet = $spreadsheet->getActiveSheet();

$rows = [];
foreach ($worksheet->getRowIterator() as $rowIndex => $row) {
$cellIterator = $row->getCellIterator();
$cellIterator->setIterateOnlyExistingCells(false);
$cells = [];
foreach ($cellIterator as $cell) {
$cells[] = $cell->getValue();
}
if (array_filter($cells)) {
$rows[$rowIndex] = $cells;
}
}

$headers = $rows[1];
unset($rows[1]);

echo '
<div class="text-center">
<button id="show-all-buttons" class="btn btn-primary mb-3">Valider Importation</button>
<table class="table table-bordered table-responsive align-middle">
</div>
';

echo '<thead class="table-light"><tr>';
for ($colIndex = 0; $colIndex < 11; $colIndex++) {
$headerTitle = isset($headers[$colIndex]) ? $headers[$colIndex] : 'عمود ' . ($colIndex + 1);
echo '<th>' . htmlspecialchars($headerTitle) . '</th>';
}
echo '<th>---</th>';
echo '</tr></thead><tbody>';

foreach ($rows as $rowIndex => $rowData) {
$id = "formId" . $rowIndex;
$result = "data_result" . $rowIndex;
$action = "save_data";
$method = "post";

formAwdStart($id, $result, $action, $method);

echo '<tr class="f_'.$rowIndex.'">';

for ($colIndex = 0; $colIndex < 11; $colIndex++) {
$value = isset($rowData[$colIndex]) ? $rowData[$colIndex] : '';
$fieldName = "data[$rowIndex][$colIndex]";
echo '<td>';

if ($colIndex == 1) {
echo '<select name="' . $fieldName . '" class="form-select form-select-sm" required>';
echo '<option value="">-- choisir ville --</option>';
foreach ($cities as $city) {
$selected = (strcasecmp($city['city_name'], $value) === 0) ? 'selected' : '';
echo '<option value="' . htmlspecialchars($city['city_id']) . '" ' . $selected . '>' . htmlspecialchars($city['city_name']) . '</option>';
}
echo '</select>';
} elseif ($colIndex == 8) {
echo '<select name="' . $fieldName . '" class="form-select form-select-sm colis-select" data-row="'.$rowIndex.'">';
foreach ($selectOptions[$colIndex] as $opt) {
$selected = (strcasecmp($opt, $value) === 0) ? 'selected' : '';
echo '<option value="' . htmlspecialchars($opt) . '" ' . $selected . '>' . htmlspecialchars(ucfirst($opt)) . '</option>';
}
echo '</select>';

$codeColisValue = isset($rowData[11]) ? $rowData[11] : '';
$displayInput = (strcasecmp($value, 'oui') === 0) ? '' : 'style="display:none;"';
echo '<input type="text" name="code_colis[' . $rowIndex . ']" class="form-control form-control-sm mt-1 colis-code" placeholder="Code Colis" value="'.htmlspecialchars($codeColisValue).'" ' . $displayInput . '>';
} elseif (isset($selectOptions[$colIndex])) {
echo '<select name="' . $fieldName . '" class="form-select form-select-sm">';
foreach ($selectOptions[$colIndex] as $opt) {
$selected = (strcasecmp($opt, $value) === 0) ? 'selected' : '';
echo '<option value="' . htmlspecialchars($opt) . '" ' . $selected . '>' . htmlspecialchars(ucfirst($opt)) . '</option>';
}
echo '</select>';
} else {
echo '<input type="text" name="' . $fieldName . '" value="' . htmlspecialchars($value) . '" class="form-control form-control-sm">';
}

echo '</td>';
}

echo '<td>';
echo '<input type="hidden" name="rowIndex" value="' . $rowIndex . '">';
echo '<input type="hidden" name="warehouse" value="' . $warehouse . '">';
echo '<input type="hidden" name="user" value="' . $user . '">';
echo '<button type="submit" name="save_data" value="1" class="btn btn-success validate-btn btn-sm">Valider</button>';
echo '</td>';

echo '</tr>';
echo "<tr><td colspan='12' id='$result'></td></tr>";

formAwdEnd();
}

echo '
</tbody></table>
';
?>
<script>
// زر لإرسال كل النماذج دفعة واحدة
document.getElementById("show-all-buttons").addEventListener("click", function(e){
e.preventDefault();

// اختر كل أزرار Valider لكل صف لم تُسجَّل بعد
const allValidateBtns = document.querySelectorAll(".validate-btn:not([data-saved='1'])");
allValidateBtns.forEach(function(btn){
btn.click();
});
});

// بعد حفظ كل صف في save_data.php، أضف الكود التالي لإضافة العلامة:
// $('.f_ROWID #data_resultROWID').html(response).closest('tr').find('.validate-btn').attr('data-saved','1');


// تعديل حقل Code Colis حسب اختيار oui/non
document.querySelectorAll(".colis-select").forEach(function(select) {
function toggleInput() {
const row = this.getAttribute("data-row");
const input = document.querySelector('input[name="code_colis[' + row + ']"]');
if (this.value.toLowerCase() === "oui") {
input.style.display = "";
input.required = true;
} else {
input.style.display = "none";
input.required = false;
input.value = "";
}
}
select.addEventListener("change", toggleInput);
toggleInput.call(select);
});
</script>

<?php

} catch (Exception $e) {
echo '<div class="alert alert-danger">Excel Erreur : ' . htmlspecialchars($e->getMessage()) . '</div>';
}

} else {
?>

<form method="POST" enctype="multipart/form-data" class="mb-4 text-center">

<div class="input-group mb-3">
<div class="">Agence de Ramassage</div>
<select name='warehouse' required>
<option value='0'>-- Choisir agence --</option>
<?php foreach($warehouse as $row) :?>
<option value='<?=$row['wh_id'];?>'><?=$row['wh_name'];?></option>
<?php endforeach; ?>
</select>
</div>

<?php if ($loginRank == "admin"): ?>
<div class="input-group mb-3">
<div class="">Vendeur</div>
<select name='user' required>
<option value='0'>-- Choisir Vendeur --</option>
<?php foreach($users as $row) : ?>
<option value='<?=$row['user_id'];?>'><?=$row['user_name'];?></option>
<?php endforeach; ?>
</select>
</div>
<?php elseif ($loginRank == "user"): ?>
<input type='hidden' name='user' value='<?=$loginId;?>'/>
<?php endif; ?>

<div class="input-group mb-3">
<label class="input-group-text" for="inputGroupFile01">Fichier</label>
<input name="file_upload" accept=".xlsx" required type="file" class="form-control" id="inputGroupFile01">
</div>

<button type="submit" class="btn btn-primary">Importer</button>
</form>

<?php
}
?>
</body>
