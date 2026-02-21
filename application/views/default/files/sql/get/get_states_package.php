<?php

global $con;

try {
// جلب جميع الحالات من جدول state
$stmt = $con->prepare("SELECT * FROM state WHERE state_unlink = 0");
$stmt->execute();

$states = $stmt->fetchAll(PDO::FETCH_ASSOC);

// طباعة HTML مباشرة بدلاً من JSON
foreach ($states as $state) {
echo '<option value="' . $state['state_id'] . '">' . $state['state_name'] . '</option>';
}

} catch (Exception $e) {
echo 'Erreur: ' . $e->getMessage();
}

// إغلاق الاتصال
$con = null;
?>
