<?php 
global $con;

function getStateActivity($order_id, $order_created, $con) {
    $stmt = $con->prepare("SELECT sa.*, 
        u.user_name, 
        s.state_name 
        FROM state_activity sa 
        LEFT JOIN users u ON u.user_id = sa.sa_user 
        LEFT JOIN state s ON s.state_id = sa.sa_state 
        WHERE sa.sa_order = ? 
        ORDER BY sa.sa_id ASC");

    $stmt->execute([$order_id]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($rows) == 0) {
        echo "<div class='alert alert-info'>--</div>";
    } else {
        // الحالة الابتدائية (إنشاء الطلب)
        echo '
        <ul class="timeline">
            <li class="timeline-item mb-5">
                <h6 class="fw-bold">En Attente</h6>
                <p class="text-muted mb-2"><small><i class="bi bi-calendar3"></i> ' . $order_created . '</small></p>
            </li>
        </ul>
        ';

        // باقي الحالات
        echo '<ul class="timeline">';
        foreach ($rows as $row) {
            echo '
            <li class="timeline-item mb-5">
                <h6 class="fw-bold">' . html_entity_decode($row["state_name"]) . '</h6>
                <p class="text-muted mb-2"><small><i class="bi bi-calendar3"></i> ' . $row["sa_date"] . '</small></p>
                <p class="mb-1">' . html_entity_decode($row["sa_note"]) . '</p>
            </li>';
        }
        echo '</ul>';
    }
}

if (!isset($_POST['id'])) die("ID manquant");

$order_id = htmlspecialchars(trim($_POST['id']));
$stmt = $con->prepare("SELECT or_id, or_created FROM orders WHERE or_id = ? LIMIT 1");
$stmt->execute([$order_id]);
$order = $stmt->fetch();

if (!$order) {
  echo "<div class='alert alert-warning'>Aucune commande trouvée pour ce code à barres.</div>";
  exit;
}

// عرض الحالة
getStateActivity($order['or_id'], $order['or_created'], $con);
?>
