<?php
header('Content-Type: application/json');

$banks = [

'007' => 'Attijariwafa Bank',
'00102' => 'Banque Populaire',
'011' => 'BMCE Bank - Bank of africa',
'022' => 'SGMB - Saham Bank',
'021' => 'Crédit du Maroc',
'00107' => 'Al Barid Bank',
'230' => 'Crédit Immobilier et Hôtelier (CIH Bank)',
'00109' => 'Banque Marocaine pour le Commerce et l’Industrie (BMCI)',
'00110' => 'Arab Bank Maroc',
'00111' => 'Banque Atlantique Maroc',
'00112' => 'Crédit Agricole du Maroc',
'00113' => 'Banque Centrale Populaire',
'00114' => 'Banque Chaabi du Maroc',
'00115' => 'Bank Al-Maghrib (Banque Centrale)',
'00116' => 'Banque Al-Amal',
'00117' => 'Société Marocaine de Dépôt et de Crédit',
'00118' => 'Barclays Bank Maroc',
'00119' => 'BNP Paribas Maroc',
'00120' => 'HSBC Maroc',
'00121' => 'Citibank Maroc',
'00122' => 'Crédit Suisse Maroc',
'00123' => 'Deutsche Bank Maroc',
'00124' => 'Crédit Lyonnais Maroc',

];


$rib = $_GET['rib'] ?? '';

$bankName = '';

if (strlen($rib) >= 3) {
$code = substr($rib, 0, 3);
if (isset($banks[$code])) {
$bankName = $banks[$code];
} else {
$bankName = "Banque inconnue";
}
} else {
$bankName = "";
}

echo json_encode(['bankName' => $bankName]);
