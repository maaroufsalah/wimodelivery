<?php
header('Content-Type: application/json; charset=utf-8');
// Connexion DB
require_once __DIR__ . '/../application/config/config.php';

// Log file — même que api_list.php
$log_file = __DIR__ . '/../logs/webhook_chamel.log';
if (!is_dir(dirname($log_file))) {
    mkdir(dirname($log_file), 0755, true);
}
function wh_log($message, $log_file) {
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($log_file, "[$timestamp] $message\n", FILE_APPEND | LOCK_EX);
}

// Secret token
$WEBHOOK_SECRET = "WIMO_WH_7a3f8k2m9x4q1n6p0r5s8t3w2v9z4b1c";

// ============================================
// 1. LOGGER LA REQUÊTE ENTRANTE
// ============================================
wh_log("============================================", $log_file);
wh_log("=== WEBHOOK CHAMEL EXPRESS — REÇU ===", $log_file);
wh_log("IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'), $log_file);
wh_log("Méthode: " . ($_SERVER['REQUEST_METHOD'] ?? 'unknown'), $log_file);

$receivedToken   = $_SERVER['HTTP_SPECIAL_TOKEN']   ?? ($_SERVER['HTTP_X_WEBHOOK_SECRET'] ?? '');
$contentType     = $_SERVER['HTTP_CONTENT_TYPE']    ?? ($_SERVER['CONTENT_TYPE'] ?? '');
wh_log("Headers: Special-Token=" . $receivedToken . " | Content-Type=" . $contentType, $log_file);

$rawPayload = file_get_contents('php://input');
wh_log("Payload brut: " . $rawPayload, $log_file);

// ============================================
// 2. VÉRIFIER LE TOKEN
// ============================================
wh_log("--- VALIDATION ---", $log_file);

if (empty($receivedToken) || $receivedToken !== $WEBHOOK_SECRET) {
    wh_log("REJETÉ: token invalide (reçu: " . $receivedToken . ")", $log_file);
    wh_log("============================================", $log_file);
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(["status" => "error", "message" => "Token invalide"], JSON_UNESCAPED_UNICODE);
    exit;
}

wh_log("Token: VALIDE", $log_file);

// ============================================
// 3. PARSER LE JSON
// ============================================
$data = json_decode($rawPayload, true);

if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
    wh_log("ERREUR: JSON invalide — " . json_last_error_msg(), $log_file);
    wh_log("============================================", $log_file);
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(["status" => "error", "message" => "JSON invalide"], JSON_UNESCAPED_UNICODE);
    exit;
}

$reference = $data['reference'] ?? '';
$status    = $data['status']    ?? '';
$reporter  = $data['reporter']  ?? '';
$comment   = $data['comment']   ?? '';

wh_log("reference: " . $reference, $log_file);
wh_log("status reçu: " . $status, $log_file);
wh_log("reporter: " . $reporter, $log_file);
wh_log("comment: " . $comment, $log_file);

// ============================================
// 4. EXTRAIRE or_id
// ============================================
if (!preg_match('/^WMD-(\d+)$/', $reference, $matches)) {
    wh_log("ERREUR: format reference invalide — " . $reference, $log_file);
    wh_log("============================================", $log_file);
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(["status" => "error", "message" => "Format référence invalide"], JSON_UNESCAPED_UNICODE);
    exit;
}

$or_id = intval($matches[1]);
wh_log("or_id extrait: " . $or_id, $log_file);

// ============================================
// 5. MAPPER LE STATUT
// ============================================
wh_log("--- MAPPING ---", $log_file);

$statusMap = [
    'LIVRÉ'    => 1,  // Livré
    'LIVRE'    => 1,  // sans accent
    'ANNULÉ'   => 2,  // Annulé
    'ANNULE'   => 2,
    'REFUSÉ'   => 3,  // Refusé
    'REFUSE'   => 3,
    'EN COURS' => 51, // En cours de livraison
    'NOUVELLE' => 52, // Ramassé
];

$statusNorm  = strtoupper(trim($status));
$mapped_state = $statusMap[$statusNorm] ?? null;

if ($mapped_state === null) {
    wh_log("WARNING: statut inconnu — \"" . $status . "\" — aucune mise à jour de statut", $log_file);
} else {
    wh_log("statut mappé: " . $mapped_state . " (" . $status . " → state_id=" . $mapped_state . ")", $log_file);
}

// ============================================
// 6. MISE À JOUR DB
// ============================================
wh_log("--- BASE DE DONNÉES ---", $log_file);

try {
    $con = Database::getInstance()->getConnection();

    // UPDATE orders.or_state
    if ($mapped_state !== null) {
        $stmt = $con->prepare("UPDATE orders SET or_state = ? WHERE or_id = ?");
        $stmt->execute([$mapped_state, $or_id]);
        $rows = $stmt->rowCount();
        wh_log("UPDATE orders: " . $rows . " ligne(s) affectée(s)", $log_file);
    } else {
        wh_log("UPDATE orders: ignoré (statut inconnu)", $log_file);
    }

    // INSERT state_activity
    // Colonnes: sa_date, sa_state, sa_order, sa_user, sa_note, sa_rank
    if ($mapped_state !== null) {
        $saDate = date('Y-m-d H:i:s');
        $saNote = "Chamel: " . $status . ($comment !== '' ? " — " . $comment : '');
        $stmt2  = $con->prepare(
            "INSERT INTO state_activity (sa_date, sa_state, sa_order, sa_user, sa_note, sa_rank)
             VALUES (?, ?, ?, 0, ?, 'admin')"
        );
        $stmt2->execute([$saDate, $mapped_state, $or_id, substr($saNote, 0, 255)]);
        wh_log("INSERT state_activity: OK (sa_id=" . $con->lastInsertId() . ")", $log_file);
    }

} catch (Exception $e) {
    wh_log("DB ERREUR: " . $e->getMessage(), $log_file);
    // Ne pas bloquer — retourner 200 quand même
}

// ============================================
// 7. RÉPONSE 200
// ============================================
wh_log("--- RÉPONSE ---", $log_file);
wh_log("HTTP 200 — traitement terminé", $log_file);
wh_log("============================================", $log_file);

header('Content-Type: application/json');
http_response_code(200);
echo json_encode(["status" => "ok", "message" => "Webhook traité"], JSON_UNESCAPED_UNICODE);
