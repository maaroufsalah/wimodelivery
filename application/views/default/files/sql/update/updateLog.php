<?php 

global $con;



if(SRM("GET")){



$id = isset ($_GET['id']) ? $_GET ['id'] : '' ;



if(empty($id)){
print "<div class='alert alert-danger'>Veuillez Choisir Un Bon</div>";
exit ();
}
	

$stmt = $con->prepare ("SELECT * FROM log_print  WHERE md5(lp_id) = '$id'  LIMIT 1");
$stmt->execute();
$productCount = $stmt->rowCount();
$log = $stmt->fetch();

		

$TABLE   = "log_print";
$SQL     = "lp_state = '1'  WHERE md5(lp_id) = '$id'";

$sql = "
UPDATE $TABLE SET 
 $SQL
";


$stmt = $con->prepare($sql);











if ($stmt->execute()) {






if ($log['lp_type'] == "pickup"){

if (function_exists('load_url')) {
load_url("pickup", 1); 
}

}elseif ($log['lp_type'] == "outlog_user"){
		
if (function_exists('load_url')) {
load_url("outLogUser", 1); 
}		

}elseif ($log['lp_type'] == "outlog_delivery"){

if (function_exists('load_url')) {
load_url("outLogDelivery", 1); 
}	

}


}
		
		
		
		
		
		
}


?>