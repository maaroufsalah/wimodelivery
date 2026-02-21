<?php
include get_file("files/sql/get/os_settings");
require_once('vendor/tcpdf/tcpdf.php');
global $con;

// قراءة المعرفات من URL (مثلاً: ?order_ids=1,2,3)
$order_ids_raw = $_GET['order_ids'] ?? '';
$order_ids = array_filter(array_map('intval', explode(',', $order_ids_raw)));

if (empty($order_ids)) die("No orders selected.");

// جلب كل الطلبات دفعة واحدة
$placeholders = implode(',', array_fill(0, count($order_ids), '?'));
$sql = "SELECT o.*, c.city_name, u.user_name AS trade_name 
FROM orders o
LEFT JOIN city c ON o.or_city = c.city_id
LEFT JOIN users u ON o.or_trade = u.user_id
WHERE o.or_id IN ($placeholders)";
$stmt = $con->prepare($sql);
$stmt->execute($order_ids);
$orders = $stmt->fetchAll();

if (!$orders) die("Orders not found.");

// إعداد المستند
$pdf = new TCPDF('P', 'mm', [100, 100], true, 'UTF-8', false);
$pdf->SetMargins(5, 5, 5);
$pdf->SetAutoPageBreak(false, 0);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

foreach ($orders as $order) {
$pdf->AddPage();

// خلفية
$pdf->Image('uploads/label.png', 0, 0, 100, 100, '', '', '', false, 300, '', false, false, 0);

// حدود
$pdf->SetLineWidth(0.5);
$pdf->Rect(2, 2, 96, 96);

// المدينة والتاريخ
$pdf->SetFont('helvetica', 'B', 8.40);
$pdf->Cell(90, 5, strtoupper($order['city_name']), 0, 1, 'C');
$pdf->Cell(90, 5, $order['or_id'] . ' - ' . date('d/m/Y', strtotime($order['or_created'])), 0, 1, 'C');
$pdf->Ln(2);

// Expéditeur
$pdf->SetFont('helvetica', 'B', 6);
$pdf->Cell(90, 4, 'Expéditeur', 0, 1, 'L');
$pdf->SetFont('helvetica', '', 8.40);
$pdf->Cell(90, 5, $order['trade_name'], 0, 1, 'L');
$pdf->Ln(1);

// Client + Téléphone
$pdf->SetFont('helvetica', '', 6);
$pdf->Cell(45, 4, 'Client', 0, 0);
$pdf->Cell(45, 4, 'Téléphone client', 0, 1);
$pdf->SetFont('helvetica', 'B', 8.40);
$pdf->Cell(45, 5, $order['or_name'], 0, 0);
$pdf->Cell(45, 5, $order['or_phone'], 0, 1);
$pdf->Ln(1);

// Adresse
$pdf->SetFont('helvetica', '', 6);
$pdf->Cell(90, 4, 'Adresse', 0, 1);
$pdf->SetFont('helvetica', 'B', 8.40);
$pdf->MultiCell(90, 5, $order['or_address'], 0, 'L');
$pdf->Ln(1);

// Montant + Produit
$pdf->SetFont('helvetica', '', 6);
$pdf->Cell(30, 4, 'Montant', 0, 0);
$pdf->Cell(60, 4, 'Produit', 0, 1);
$pdf->SetFont('helvetica', 'B', 8.40);
$pdf->Cell(30, 5, $order['or_total'] . ' DH', 0, 0);
$pdf->Cell(60, 5, $order['or_item'], 0, 1);
$pdf->Ln(1);

// Note + QR
$pdf->SetFont('helvetica', '', 6);
$pdf->Cell(90, 4, 'Note', 0, 1);
$pdf->SetFont('helvetica', 'B', 8.40);
$qrSize = 13.5;
$noteWidth = 90 - $qrSize - 5;
$pdf->MultiCell($noteWidth, 5, $order['or_note'], 0, 'L', false, 0);
$pdf->write2DBarcode($order['or_id'], 'QRCODE,H', 90 - $qrSize, $pdf->GetY(), $qrSize, $qrSize, [
'border' => false,
'padding' => 0
], 'N');
$pdf->Ln(6);

// Ouverture + Essayage
$pdf->SetFont('dejavusans', '', 6);
$pdf->Cell(15, 4, 'Ouverture:', 0, 0);
$pdf->Cell(11, 4, ($order['or_open_package'] ? '[✔] Oui' : '[☐] Oui'), 0, 0);
$pdf->Cell(11, 4, (!$order['or_open_package'] ? '[✔] Non' : '[☐] Non'), 0, 0);
$pdf->Cell(15, 4, 'Essayage:', 0, 0);
$pdf->Cell(15, 4, ($order['or_try'] ? '[✔] Oui' : '[☐] Oui'), 0, 0);
$pdf->Cell(15, 4, (!$order['or_try'] ? '[✔] Non' : '[☐] Non'), 0, 1);

// Échange
$pdf->Cell(15, 4, 'Échange:', 0, 0);
$pdf->Cell(11, 4, ($order['or_change'] ? '[✔] Oui' : '[☐] Oui'), 0, 0);
$pdf->Cell(11, 4, (!$order['or_change'] ? '[✔] Non' : '[☐] Non'), 0, 0);
$pdf->Cell(35, 4, 'B-4-2', 0, 1);
$pdf->Ln(2);

// التذييل
$pdf->SetFont('helvetica', 'B', 6);
$pdf->Cell(90, 3, $set_name . ' | Livraison e-commerce', 0, 1, 'C');
$pdf->SetFont('helvetica', '', 6);
$pdf->Cell(90, 3, $set_name . ' SARL n\'est pas responsable de vos achats', 0, 1, 'C');
}

// إخراج الملف
$pdf->Output("delivery_labels.pdf", 'I');
