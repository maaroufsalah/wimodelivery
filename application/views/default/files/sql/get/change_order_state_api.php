<?php
global $con;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        "success" => false,
        "message" => "Méthode non autorisée. Utilisez POST."
    ]);
    exit;
}

$email          = trim($_POST['email'] ?? '');
$password       = trim($_POST['password'] ?? '');
$order_id       = intval($_POST['order_id'] ?? 0);
$state_name     = trim($_POST['state_name'] ?? '');
$postponed_date = trim($_POST['postponed_date'] ?? '');
$note           = trim($_POST['note'] ?? '') . " - Par Api";

if (!$email || !$password || !$order_id || !$state_name) {
    echo json_encode([
        "success" => false,
        "message" => "Champs requis manquants."
    ]);
    exit;
}

// ✅ Authentification
$stmt = $con->prepare("SELECT user_id, user_pass FROM users WHERE user_email = :email LIMIT 1");
$stmt->execute([':email' => $email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || !password_verify($password, $user['user_pass'])) {
    echo json_encode([
        "success" => false,
        "message" => "Identifiants incorrects."
    ]);
    exit;
}
$user_id = $user['user_id'];

// ✅ Normalisation des chaînes
function normalize($str) {
    $str = trim(mb_strtolower($str, 'UTF-8'));
    $str = str_replace(
        ['é','è','ê','ë','à','â','ä','î','ï','ô','ö','û','ü','ç'],
        ['e','e','e','e','a','a','a','i','i','o','o','u','u','c'],
        $str
    );
    return $str;
}
$normalized_input = normalize($state_name);

// ✅ Récupération des états
$stmt = $con->prepare("SELECT state_id, state_name FROM state WHERE state_unlink = 0");
$stmt->execute();
$all_states = $stmt->fetchAll(PDO::FETCH_ASSOC);

$state_id = null;
$matched_state_name = null;
foreach ($all_states as $state) {
    if (normalize($state['state_name']) === $normalized_input) {
        $state_id = $state['state_id'];
        $matched_state_name = $state['state_name'];
        break;
    }
}

if (!$state_id) {
    echo json_encode([
        "success" => false,
        "message" => "Aucun état correspondant trouvé pour: $state_name"
    ]);
    exit;
}

// ✅ Vérifier que la commande appartient bien à ce user
$stmt = $con->prepare("
    SELECT or_id 
    FROM orders 
    WHERE or_id = :order_id 
      AND or_delivery_user = :user_id
    LIMIT 1
");
$stmt->execute([
    ':order_id' => $order_id,
    ':user_id'  => $user_id
]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    echo json_encode([
        "success" => false,
        "message" => "Commande introuvable ou non autorisée."
    ]);
    exit;
}

// ✅ Vérif Reporté
if (normalize($matched_state_name) === 'reporte') {
    if (!$postponed_date) {
        echo json_encode([
            "success" => false,
            "message" => "La date de report est obligatoire pour 'Reporté'."
        ]);
        exit;
    }
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $postponed_date)) {
        echo json_encode([
            "success" => false,
            "message" => "Format de la date invalide (attendu: YYYY-MM-DD)."
        ]);
        exit;
    }
}

try {
    $con->beginTransaction();

    $delivered_date = (normalize($matched_state_name) === 'livre') ? date('Y-m-d H:i:s') : null;

    // ✅ Mise à jour principale
    $stmt = $con->prepare("
        UPDATE orders 
        SET or_state_delivery = :state_id, 
            or_delivered = :delivered_date 
        WHERE or_id = :order_id
    ");
    $stmt->execute([
        ':state_id'      => $state_id,
        ':delivered_date'=> $delivered_date,
        ':order_id'      => $order_id
    ]);

    // ✅ Mise à jour report si besoin
    if (normalize($matched_state_name) === 'reporte') {
        $stmt = $con->prepare("
            UPDATE orders 
            SET or_postponed = :postponed_date 
            WHERE or_id = :order_id
        ");
        $stmt->execute([
            ':postponed_date' => $postponed_date,
            ':order_id'       => $order_id
        ]);
    }

    // ✅ Historique état
    $stmt = $con->prepare("
        INSERT INTO state_activity (sa_date, sa_state, sa_order, sa_user, sa_note)
        VALUES (NOW(), :state_id, :order_id, :user_id, :note)
    ");
    $stmt->execute([
        ':state_id' => $state_id,
        ':order_id' => $order_id,
        ':user_id'  => $user_id,
        ':note'     => $note
    ]);

    $con->commit();

    echo json_encode([
        "success" => true,
        "message" => "État changé avec succès",
        "data" => [
            "order_id"       => $order_id,
            "state"          => $matched_state_name,
            "postponed_date" => $postponed_date ?: null,
            "delivered_date" => $delivered_date
        ]
    ]);

} catch (Exception $e) {
    $con->rollBack();
    echo json_encode([
        "success" => false,
        "message" => "Erreur: " . $e->getMessage()
    ]);
}

$con = null;
