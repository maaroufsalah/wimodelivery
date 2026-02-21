<?php
include get_file("files/sql/get/os_settings");
require_once('vendor/tcpdf/tcpdf.php');
global $con;

// دالة لاختصار النصوص الطويلة
function truncateText($text, $maxLength = 90) {
    $text = strip_tags($text);
    return mb_strlen($text, 'UTF-8') > $maxLength
        ? mb_substr($text, 0, $maxLength - 1, 'UTF-8') . '…'
        : $text;
}

// استقبال الطلبات
$order_ids_raw = $_GET['order_ids'] ?? '';
$order_ids = array_filter(array_map('intval', explode(',', $order_ids_raw)));
if (empty($order_ids)) die("No orders selected.");

// جلب الطلبات من قاعدة البيانات
$placeholders = implode(',', array_fill(0, count($order_ids), '?'));
$sql = "SELECT o.*, c.city_name, 
        u.user_name AS trade_name, 
        u.user_phone AS trade_phone
        FROM orders o
        LEFT JOIN city c ON o.or_city = c.city_id
        LEFT JOIN users u ON o.or_trade = u.user_id
        WHERE o.or_id IN ($placeholders)";
$stmt = $con->prepare($sql);
$stmt->execute($order_ids);
$orders = $stmt->fetchAll();

if (!$orders) die("Orders not found.");

// 🔧 فك ترميز HTML entities لجميع النصوص
foreach ($orders as &$order) {
    $order['or_name']    = html_entity_decode($order['or_name'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $order['or_address'] = html_entity_decode($order['or_address'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $order['or_note']    = html_entity_decode($order['or_note'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $order['city_name']  = html_entity_decode($order['city_name'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $order['trade_name'] = html_entity_decode($order['trade_name'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
}
unset($order);

// إعداد TCPDF
$pdf = new TCPDF('P', 'mm', [100, 100], true, 'UTF-8', false);
$pdf->SetAutoPageBreak(false, 0);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetMargins(5, 5, 5);
$pdf->setCellHeightRatio(1.1);

$pageWidth  = $pdf->getPageWidth();
$pageHeight = $pdf->getPageHeight();
$usableWidth = $pageWidth - 10;

$fontSmall = 6.6;
$fontBig   = 8.6;

foreach ($orders as $order) {
    // جلب المنتجات لكل طلب
    $stmt_items = $con->prepare("SELECT * FROM order_items WHERE order_id = ?");
    $stmt_items->execute([$order['or_id']]);
    $items = $stmt_items->fetchAll();

    if ($items && count($items) > 0) {
        $productText = '';
        foreach ($items as $item) {
            $name = html_entity_decode($item['product_name'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $shortName = mb_substr($name, 0, 30, 'UTF-8');
            if (mb_strlen($name, 'UTF-8') > 30) $shortName .= '...';
            $productText .= 'x' . $item['quantity'] . ' ' . $shortName . "\n";
        }
    } else {
        $productText = 'x' . $order['or_qty'] . ' ' . html_entity_decode($order['or_item'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    $pdf->AddPage();

    // الخلفية
    if (file_exists('uploads/label.png')) {
        $pdf->Image('uploads/label.png', 0, 0, $pageWidth, $pageHeight, '', '', '', false, 300);
    }

    // الشعار
    if (!empty($set_logo) && file_exists("uploads/$set_logo")) {
        $pdf->Image("uploads/$set_logo", 2, 2, 10);
    }

    // المدينة والتاريخ
    $pdf->SetFont('dejavusans', 'B', $fontBig);
    $pdf->Cell($usableWidth, 5, strtoupper(substr($order['city_name'], 0, 23)), 0, 1, 'C');

    $halfWidth = $usableWidth / 2;
    $pdf->Cell($halfWidth, 5, "Code d'envoi : " . $order['or_id'], 0, 0, 'L');
    $pdf->Cell($halfWidth, 5, date('d/m/Y', strtotime($order['or_created'])), 0, 1, 'R');
    $pdf->Ln(2);

    // المرسل
    $pdf->SetFont('dejavusans', '', $fontSmall);
    $pdf->Cell($usableWidth, 4, 'Expéditeur', 0, 1, 'L');
    $pdf->SetFont('dejavusans', 'B', $fontBig);
    $pdf->MultiCell($usableWidth, 5, truncateText($order['trade_name']." - ".$order['trade_phone'], 60), 0, 'L');
    $pdf->Ln(1);

    // العميل والهاتف
    $halfWidth = $usableWidth / 2;
    $pdf->SetFont('dejavusans', '', $fontSmall);
    $pdf->Cell($halfWidth, 4, 'Client', 0, 0);
    $pdf->Cell($halfWidth, 4, 'Téléphone client', 0, 1);
    $pdf->SetFont('dejavusans', 'B', $fontBig);
    $pdf->MultiCell($halfWidth, 5, truncateText(substr($order['or_name'],0,15), 30), 0, 'L', false, 0);
    $pdf->SetFont('dejavusans', 'B', 11);
    $pdf->MultiCell($halfWidth, 5, truncateText($order['or_phone'], 20), 0, 'L', false, 1);
    $pdf->Ln(1);

    // العنوان
    $pdf->SetFont('dejavusans', '', $fontSmall);
    $pdf->Cell($usableWidth, 4, 'Adresse', 0, 1);
    $pdf->SetFont('dejavusans', 'B', $fontBig);
    $pdf->MultiCell($usableWidth, 5, truncateText($order['or_address'], 120), 0, 'L');
    $pdf->Ln(1);

    // المبلغ والمنتج
    $pdf->SetFont('dejavusans', '', $fontSmall);
    $pdf->Cell($usableWidth * 0.33, 4, 'Montant', 0, 0);
    $pdf->Cell($usableWidth * 0.66, 4, 'Produit', 0, 1);
    $pdf->SetFont('dejavusans', 'B', $fontBig);
    $html = '<span style="font-size:10px; font-weight:bold;">' . number_format($order['or_total'], 2, ',', ' ') . ' MAD</span>';
    $pdf->writeHTMLCell($usableWidth * 0.33, 5, '', '', $html, 0, 0);
    $pdf->MultiCell($usableWidth * 0.66, 6, $productText, 0, 'L', false, 1);
    $pdf->Ln(1);

    // الملاحظة + QR
    $pdf->SetFont('dejavusans', '', $fontSmall);
    $pdf->Cell($usableWidth, 4, 'Note', 0, 1);
    $pdf->SetFont('dejavusans', 'B', $fontBig);
    $qrSize = $usableWidth * 0.15;
    $noteWidth = $usableWidth - $qrSize - 2;
    $pdf->MultiCell($noteWidth, 5, truncateText($order['or_note'], 100), 0, 'L', false, 0);

    // QR يحتوي معلومات إضافية
    $qrData = "Commande: {$order['or_id']}\nClient: {$order['or_name']}\nMontant: {$order['or_total']} MAD\nVille: {$order['city_name']}";
    $pdf->write2DBarcode($order['or_id'] ?? $order['or_id'], 'QRCODE,H', $margin + $noteWidth + 2, $pdf->GetY(), $qrSize, $qrSize, ['border' => false, 'padding' => 0], 'N');

    $pdf->Ln(6);

    // خيارات Ouverture و Essayage و Échange
    $pdf->SetFont('dejavusans', 'B', 7.2);
    $colWidth = $usableWidth / 2;

    $ouverture = 'Ouverture: ' . ($order['or_open_package'] ? '[✔] Oui' : '☐ Oui') . ' ' . (!$order['or_open_package'] ? '[✔] Non' : '☐ Non');
    $essayage  = 'Essayage: ' . ($order['or_try'] ? '[✔] Oui' : '☐ Oui') . ' ' . (!$order['or_try'] ? '[✔] Non' : '☐ Non');
    $echange   = 'Échange: ' . ($order['or_change'] ? '[✔] Oui' : '☐ Oui') . ' ' . (!$order['or_change'] ? '[✔] Non' : '☐ Non');

    $pdf->MultiCell($colWidth, 5, $ouverture, 0, 'L', false, 0);
    $pdf->MultiCell($colWidth, 5, $essayage, 0, 'L', false, 1);
    $pdf->MultiCell($usableWidth, 6, $echange, 0, 'L', false, 1);
    $pdf->Ln(2);

    // الفوتر
    $pdf->SetFont('dejavusans', '', 5);
    $pdf->Cell($usableWidth, 2, $set_name . " | Livraison e-commerce", 0, 1, 'C');
    $pdf->Cell($usableWidth, 2, $set_name . " SARL n'est pas responsable de vos achats", 0, 1, 'C');
}

ob_end_clean(); // لتفادي مشاكل header
$pdf->Output("delivery_labels.pdf", 'I');
?>
