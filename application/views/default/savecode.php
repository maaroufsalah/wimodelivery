<?php 

$howz = new Bootstrap(); $howz->runBootstrap(); 

if ($_SERVER['REQUEST_METHOD'] == 'POST'){
$template = "default";
	
$NEWDATA = $_POST ['code'];
$NEWDATAPAGE = $_POST ['NEWDATAPAGE'];
	
	
if (empty($NEWDATA)){
    
print "<div class='alert alert-danger' style='border-radius:0rem'>insert code</div>";
    


}else{
	
Function EDITFILE ($sources,$url){
	$ids = fopen("$url", "w") or die("Unable to open file!");
    $code = $sources;
    fwrite($ids, $code);
    fclose($ids);
}

	
$sources = $NEWDATA;
$url     = "application/views/$template/".$NEWDATAPAGE . ".php";;
EDITFILE ($sources,$url);


$name  = "Mise à jour réussie";
$color = "success mb-0 PWSAVE";
print "<div class='alert alert-$color' style='border-radius:0rem'>$name</div>";


print "

<script>
setTimeout(function() { 
$('.PWSAVE').fadeOut('fast'); 
}, 6000); 
</script>


";


}






	
}
	


?>