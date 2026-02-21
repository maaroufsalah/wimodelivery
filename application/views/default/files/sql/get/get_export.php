<?php
ob_start();
global $con;
require_once 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Cell\DataType; // تصحيح هنا

try {
$ids = isset($_GET['ids']) ? $_GET['ids'] : '';

$where = ["o.or_unlink = '0'"];
$params = [];

if (!empty($ids)) {
$idList = array_filter(explode(',', $ids), function ($v) {
return is_numeric(trim($v));
});

if (count($idList) > 0) {
$placeholders = implode(',', array_fill(0, count($idList), '?'));
$where[] = "o.or_id IN ($placeholders)";
$params = array_merge($params, $idList);
}
}

$sql = "SELECT o.*, u.user_name, c.city_name
FROM orders o
LEFT JOIN users u ON o.or_trade = u.user_id AND u.user_rank = 'user'
LEFT JOIN city c ON o.or_city = c.city_id
WHERE " . implode(" AND ", $where);

$stmt = $con->prepare($sql);
$stmt->execute($params);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// عناوين الأعمدة
$columns = [
'A1' => 'Code Colis',
'B1' => 'Name',
'C1' => 'Phone',
'D1' => 'Note',
'E1' => 'Ville',
'F1' => 'Adresse',
'G1' => 'Prix',
'H1' => 'Change'
];
foreach ($columns as $cell => $title) {
$sheet->setCellValue($cell, $title);
}

// تجميد الصف الأول
$sheet->freezePane('A2');

// تعبئة البيانات
$rowNum = 2;
foreach ($data as $order) {
$change = ($order['or_change'] == 0) ? "Non" : "Oui";


$codeColis = $order['or_id'];
$sheet->setCellValueExplicit('A' . $rowNum, $codeColis, DataType::TYPE_STRING);


$sheet->setCellValue('B' . $rowNum, $order['or_name']);
// هنا نستخدم DataType::TYPE_STRING لتفادي الخطأ
$sheet->setCellValueExplicit('C' . $rowNum, "" . $order['or_phone'], DataType::TYPE_STRING);
$sheet->setCellValue('D' . $rowNum, $order['or_note']);
$sheet->setCellValue('E' . $rowNum, $order['city_name'] ?? '');
$sheet->setCellValue('F' . $rowNum, $order['or_address']);
$sheet->setCellValue('G' . $rowNum, $order['or_total']);
$sheet->setCellValue('H' . $rowNum, $change);

// تلوين الصفوف بالتناوب
if ($rowNum % 2 == 0) {
$sheet->getStyle("A{$rowNum}:H{$rowNum}")->getFill()->setFillType(Fill::FILL_SOLID)
->getStartColor()->setARGB('FFEFEFEF');
}

$rowNum++;
}

// محاذاة الأعمدة إلى اليسار
$sheet->getStyle('A1:H' . ($rowNum - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

// تظليل صف العناوين
$sheet->getStyle('A1:H1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFDDDDDD');
$sheet->getStyle('A1:H1')->getFont()->setBold(true)->setSize(12);

// ضبط حجم الخط لباقي الصفوف
$sheet->getStyle('A2:H' . ($rowNum - 1))->getFont()->setSize(10);

// توسيع الأعمدة تلقائيًا
foreach (range('A', 'H') as $col) {
$sheet->getColumnDimension($col)->setAutoSize(true);
}

// إعداد رؤوس HTTP لتنزيل الملف
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="orders.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');

ob_end_flush();
exit;

} catch (PDOException $e) {
echo "Connection failed: " . $e->getMessage();
}
