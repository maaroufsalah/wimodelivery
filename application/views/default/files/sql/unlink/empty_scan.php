<?php 

global $con;


$update = $con->prepare("UPDATE orders SET or_scan = 0 ");
$update->execute();

load_url ("",1);

?>