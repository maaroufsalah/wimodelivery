<?php
ob_start();
session_start();
global $con;

require_once 'vendor/autoload.php';
include get_file("files/sql/get/session");

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

try {
    // تحويل القيم المفصولة بفواصل إلى مصفوفات أرقام
    function getIntArrayFromQuery($param) {
        return isset($_GET[$param]) ? array_filter(array_map('intval', explode(',', $_GET[$param]))) : [];
    }

    $filter_city  = getIntArrayFromQuery('city');
    $filter_state = getIntArrayFromQuery('state');
    $filter_user  = getIntArrayFromQuery('user');

    $where = ["o.or_unlink = '0'"];
    $params = [];

    if (!empty($filter_city)) {
        $placeholders = implode(',', array_fill(0, count($filter_city), '?'));
        $where[] = "o.or_city IN ($placeholders)";
        $params = array_merge($params, $filter_city);
    }

    if (!empty($filter_state)) {
        $placeholders = implode(',', array_fill(0, count($filter_state), '?'));
        $where[] = "o.or_state_delivery IN ($placeholders)";
        $params = array_merge($params, $filter_state);
    }

    if (!empty($filter_user)) {
        $placeholders = implode(',', array_fill(0, count($filter_user), '?'));
        $where[] = "o.or_trade IN ($placeholders)";
        $params = array_merge($params, $filter_user);
    }

    // فلاتر حسب نوع المستخدم
    if ($loginRank == "user") {
        $where[] = "o.or_trade = ?";
        $params[] = $loginId;
    } elseif ($loginRank == "delivery") {
        $where[] = "o.or_delivery_user = ?";
        $params[] = $loginId;
    } elseif ($loginRank == "aide") {
        $where[] = "o.or_trade = ?";
        $params[] = $loginUser['user_aide'];
    }

    $sql = "SELECT o.*, u.user_name, c.city_name
            FROM orders o
            LEFT JOIN users u ON o.or_trade = u.user_id AND u.user_rank = 'user'
            LEFT JOIN city c ON o.or_city = c.city_id
            WHERE " . implode(" AND ", $where);

    $stmt = $con->prepare($sql);
    $stmt->execute($params);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Excel generation
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

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

    $sheet->freezePane('A2');
    $rowNum = 2;

    foreach ($data as $order) {
        $change = ($order['or_change'] == 0) ? "Non" : "Oui";
        $codeColis = $order['or_id'];

        $sheet->setCellValueExplicit('A' . $rowNum, $codeColis, DataType::TYPE_STRING);
        $sheet->setCellValue('B' . $rowNum, $order['or_name']);
        $sheet->setCellValueExplicit('C' . $rowNum, "" . $order['or_phone'], DataType::TYPE_STRING);
        $sheet->setCellValue('D' . $rowNum, $order['or_note']);
        $sheet->setCellValue('E' . $rowNum, $order['city_name'] ?? '');
        $sheet->setCellValue('F' . $rowNum, $order['or_address']);
        $sheet->setCellValue('G' . $rowNum, $order['or_total']);
        $sheet->setCellValue('H' . $rowNum, $change);

        if ($rowNum % 2 == 0) {
            $sheet->getStyle("A{$rowNum}:H{$rowNum}")
                ->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()
                ->setARGB('FFEFEFEF');
        }

        $rowNum++;
    }

    $sheet->getStyle('A1:H' . ($rowNum - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
    $sheet->getStyle('A1:H1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFDDDDDD');
    $sheet->getStyle('A1:H1')->getFont()->setBold(true)->setSize(12);
    $sheet->getStyle('A2:H' . ($rowNum - 1))->getFont()->setSize(10);

    foreach (range('A', 'H') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

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
