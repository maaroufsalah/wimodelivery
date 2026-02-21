<?php
global $con;
$action = $_POST['action'] ?? '';

if ($action === 'add') {
    header("Content-Type: application/json");
    $user_id = intval($_POST['user_id'] ?? 0);
    $title   = trim($_POST['title'] ?? '');
    $amount  = floatval($_POST['amount'] ?? 0);
    if ($user_id && $title && $amount) {
        $stmt = $con->prepare("INSERT INTO user_expenses (exp_user_id, exp_title, exp_amount, exp_date) VALUES (?,?,?,NOW())");
        $stmt->execute([$user_id, $title, $amount]);
        echo json_encode(["success"=>true]);
    } else echo json_encode(["success"=>false,"message"=>"Champs manquants"]);
    exit;
}

if ($action === 'list') {
    $user_id   = intval($_POST['user_id'] ?? 0);
    $date_from = $_POST['date_from'] ?? '';
    $date_to   = $_POST['date_to'] ?? '';

    $query = "SELECT * FROM user_expenses WHERE exp_user_id = :uid";
    $params = [":uid"=>$user_id];

    if($date_from) $query.=" AND exp_date >= :date_from" and $params[':date_from']=$date_from;
    if($date_to)   $query.=" AND exp_date <= :date_to" and $params[':date_to']=$date_to;

    $query.=" ORDER BY exp_date DESC";
    $stmt = $con->prepare($query);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $html = '';
    $totalAmount = 0;
    $chartLabels = [];
    $chartData = [];

    if($rows){
        $grouped = [];
        foreach($rows as $r){
            $month = date("Y-m", strtotime($r['exp_date']));
            $grouped[$month][] = $r;
        }

        foreach($grouped as $month => $items){
            $monthTotal = array_sum(array_column($items,'exp_amount'));
            $totalAmount += $monthTotal;
            $chartLabels[] = date("F Y", strtotime($month.'-01'));
            $chartData[] = $monthTotal;

            $html .= "<h5 class='mt-3'>".date("F Y", strtotime($month."-01"))." (Total: ".number_format($monthTotal,2)." DH)</h5>";
            $html .= '<table class="table table-bordered">';
            $html .= '<tr><th>Date</th><th>Titre</th><th>Montant</th><th>Action</th></tr>';
            foreach($items as $r){
                $html .= "<tr>
                    <td>".date("Y-m-d", strtotime($r['exp_date']))."</td>
                    <td>".htmlspecialchars($r['exp_title'])."</td>
                    <td>".number_format($r['exp_amount'],2)."</td>
                    <td>
                      <button class='btn btn-warning btn-sm editExpense'
                        data-id='".$r['exp_id']."'
                        data-date='".$r['exp_date']."'
                        data-title='".htmlspecialchars($r['exp_title'],ENT_QUOTES)."'
                        data-amount='".$r['exp_amount']."'>Modifier</button>
                      <button class='btn btn-danger btn-sm deleteExpense' data-id='".$r['exp_id']."'>Supprimer</button>
                    </td>
                  </tr>";
            }
            $html .= '</table>';
        }
    } else $html="<p>Aucune dépense trouvée.</p>";

    header("Content-Type: application/json");
    echo json_encode([
        'html'=>$html,
        'total'=>number_format($totalAmount,2),
        'chartLabels'=>$chartLabels,
        'chartData'=>$chartData
    ]);
    exit;
}

if ($action==='update'){
    header("Content-Type: application/json");
    $exp_id = intval($_POST['exp_id']??0);
    $title = trim($_POST['title']??'');
    $amount = floatval($_POST['amount']??0);
    $date = $_POST['date']??'';
    if($exp_id && $title && $amount && $date){
        $stmt=$con->prepare("UPDATE user_expenses SET exp_title=?, exp_amount=?, exp_date=? WHERE exp_id=?");
        $stmt->execute([$title,$amount,$date,$exp_id]);
        echo json_encode(['success'=>true]);
    } else echo json_encode(['success'=>false,'message'=>'Champs manquants']);
    exit;
}

if ($action==='delete'){
    header("Content-Type: application/json");
    $exp_id=intval($_POST['exp_id']??0);
    if($exp_id){
        $stmt=$con->prepare("DELETE FROM user_expenses WHERE exp_id=?");
        $stmt->execute([$exp_id]);
        echo json_encode(['success'=>true]);
    } else echo json_encode(['success'=>false,'message'=>'ID manquant']);
    exit;
}
?>
