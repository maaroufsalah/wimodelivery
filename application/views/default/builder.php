<?php

include ("application/config/function/pwd.php");
/*
$fileName = isset($_GET["file"]) ? $_GET["file"] : ''; 
*/
$fileId = isset($_GET["file"]) ? $_GET["file"] : ''; 



if (isset($_GET['file'])) {


$file_path = "application/views/default/" . basename($_GET['file']) . ".php";
$admin_path = "application/views/default/Admin/" . basename($_GET['file']) . ".php";
$dashboard_path = "application/views/default/Dashboard/" . basename($_GET['file']) . ".php";
$app_path = "application/views/default/App/" . basename($_GET['file']) . ".php";


if (substr($fileId, 0,14) == 'files/sql/get/'){

$sql_path = "application/views/default/files/sql/get/" .basename($_GET['file']). ".php";

}elseif (substr($fileId, 0,17) == 'files/sql/insert/'){

$sql_path = "application/views/default/files/sql/insert/" .basename($_GET['file']). ".php";

}elseif (substr($fileId, 0,17) == 'files/sql/update/'){

$sql_path = "application/views/default/files/sql/update/" .basename($_GET['file']). ".php";

}elseif (substr($fileId, 0,17) == 'files/sql/unlink/'){

$sql_path = "application/views/default/files/sql/unlink/" .basename($_GET['file']). ".php";

}else{
   

}

if (file_exists($file_path)) {
$file_contents = file_get_contents($file_path);
}elseif(file_exists($admin_path)){
$file_contents = file_get_contents($admin_path);
}elseif(file_exists($dashboard_path)){
$file_contents = file_get_contents($dashboard_path);
}elseif(file_exists($app_path)){
$file_contents = file_get_contents($app_path);
}elseif(file_exists($sql_path)){
$file_contents = file_get_contents($sql_path);

} else {
echo "File does not exist.";
exit;
}
}


?>

<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Aodev Code</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.43.0/min/vs/loader.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<style>
html, body { margin: 0; padding: 0; }
#editor { height: 800px; border: 1px solid #ddd; }
textarea { width: 100%; height: 500px; margin-top: 10px; }
.radius-0 {border-radius:0rem;}



</style>
</head>
<body>

















<div class="container mt-4">


<div class='text-center my-5'>

<a href='?do=new' class='btn btn-dark btn-sm radius-0' style=''>Add Root Activity</a>    
<a href='?do=new_admin' class='btn btn-danger btn-sm radius-0' style=''>Add Admin Activity</a>    
<a href='?do=new_dashboard' class='btn btn-info btn-sm radius-0' style=''>Add Dashboard Activity</a>    
<a href='?do=new_app' class='btn btn-success btn-sm radius-0' style=''>Add App Activity</a>    
<a href='?do=new_sql' class='btn btn-warning btn-sm radius-0' style=''>Add Sql Activity</a>    

</div>    



<?php  

$do = isset ($_GET['do']) ? $_GET ['do'] : 'Manage' ;


if ($do == 'Manage'){

$form   = "update_code";
$result = "Results_update_code";
FAjax ($form,$result);

$id      =  $form ;
$method  =  "POST" ;
$action  =  "savecode" ;
Form ($id,$method,$action);




?>    


<div class="row">
<div class="col-sm-4">

















<a class="btn btn-dark radius-0 w-100" onclick="openRoute();">Route</a>

<div id="route" style='display:none'>
<?php 
$files = array();
$dir = new DirectoryIterator('application/views/default/');
foreach ($dir as $fileinfo) {
$fileName = $fileinfo->getFilename();
$extension = pathinfo($fileName, PATHINFO_EXTENSION);

if ($extension === 'php' && !in_array(pathinfo($fileName, PATHINFO_FILENAME), ['builder', 'bootstrap', 'savecode'])) {
echo "<a class='btn btn-white radius-0 w-100 btn-sm' href='?file=" . htmlspecialchars(pathinfo($fileName, PATHINFO_FILENAME)) . "'>" . pathinfo($fileName, PATHINFO_FILENAME) . " {.Route Activity}</a><br>";
}
}
?>
</div>



<a class="btn btn-danger radius-0 w-100" onclick="openAdmin();">Admin</a>

<div class="" id="admin"  style='display:none'>
<?php 
$files = array();
$dir = new DirectoryIterator('application/views/default/Admin/');
foreach ($dir as $fileinfo) {
$fileName = $fileinfo->getFilename();
$extension = pathinfo($fileName, PATHINFO_EXTENSION);

if ($extension === 'php' && !in_array(pathinfo($fileName, PATHINFO_FILENAME), ['builder', 'bootstrap', 'savecode'])) {
echo "<a class='btn btn-white radius-0 w-100 btn-sm' href='?file=Admin/" . htmlspecialchars(pathinfo($fileName, PATHINFO_FILENAME)) . "'>" . pathinfo($fileName, PATHINFO_FILENAME) . "{.Admin Activity}</a><br>";
}
}
?>
</div>






<a class="btn btn-info radius-0 w-100" onclick="openDashboard();">Dashboard</a>

<div class="" id="dashboard"  style='display:none'>
<?php 
$files = array();
$dir = new DirectoryIterator('application/views/default/Dashboard/');
foreach ($dir as $fileinfo) {
$fileName = $fileinfo->getFilename();
$extension = pathinfo($fileName, PATHINFO_EXTENSION);

if ($extension === 'php' && !in_array(pathinfo($fileName, PATHINFO_FILENAME), ['builder', 'bootstrap', 'savecode'])) {
echo "<a class='btn btn-white radius-0 w-100 btn-sm' href='?file=Dashboard/" . htmlspecialchars(pathinfo($fileName, PATHINFO_FILENAME)) . "'>" . pathinfo($fileName, PATHINFO_FILENAME) . "{.Dashboard Activity}</a><br>";
}
}
?>
</div>






<a class="btn btn-success radius-0 w-100" onclick="openApp();">App</a>

<div class="" id="app"  style='display:none'>
<?php 
$files = array();
$dir = new DirectoryIterator('application/views/default/App/');
foreach ($dir as $fileinfo) {
$fileName = $fileinfo->getFilename();
$extension = pathinfo($fileName, PATHINFO_EXTENSION);

if ($extension === 'php' && !in_array(pathinfo($fileName, PATHINFO_FILENAME), ['builder', 'bootstrap', 'savecode'])) {
echo "<a class='btn btn-white radius-0 w-100 btn-sm' href='?file=App/" . htmlspecialchars(pathinfo($fileName, PATHINFO_FILENAME)) . "'>" . pathinfo($fileName, PATHINFO_FILENAME) . "{.App Activity}</a><br>";
}
}
?>
</div>






<a class="btn btn-warning radius-0 w-100" onclick="openSql();">Sql</a>

<div class="" id="sql"  style='display:none'>
    
<?php 
$files = array();
$dir = new DirectoryIterator('application/views/default/files/sql/get');
foreach ($dir as $fileinfo) {
$fileName = $fileinfo->getFilename();
$extension = pathinfo($fileName, PATHINFO_EXTENSION);

if ($extension === 'php' && !in_array(pathinfo($fileName, PATHINFO_FILENAME), ['builder', 'bootstrap', 'savecode'])) {
echo "<a class='btn btn-white text-info radius-0 w-100 btn-sm' href='?file=files/sql/get/" . htmlspecialchars(pathinfo($fileName, PATHINFO_FILENAME)) . "'>" . pathinfo($fileName, PATHINFO_FILENAME) . "{.get}</a><br>";
}
}
?>

   
<?php 
$files = array();
$dir = new DirectoryIterator('application/views/default/files/sql/insert');
foreach ($dir as $fileinfo) {
$fileName = $fileinfo->getFilename();
$extension = pathinfo($fileName, PATHINFO_EXTENSION);

if ($extension === 'php' && !in_array(pathinfo($fileName, PATHINFO_FILENAME), ['builder', 'bootstrap', 'savecode'])) {
echo "<a class='btn btn-white text-dark radius-0 w-100 btn-sm' href='?file=files/sql/insert/" . htmlspecialchars(pathinfo($fileName, PATHINFO_FILENAME)) . "'>" . pathinfo($fileName, PATHINFO_FILENAME) . "{.insert}</a><br>";
}
}
?>

   
<?php 
$files = array();
$dir = new DirectoryIterator('application/views/default/files/sql/update');
foreach ($dir as $fileinfo) {
$fileName = $fileinfo->getFilename();
$extension = pathinfo($fileName, PATHINFO_EXTENSION);

if ($extension === 'php' && !in_array(pathinfo($fileName, PATHINFO_FILENAME), ['builder', 'bootstrap', 'savecode'])) {
echo "<a class='btn btn-white text-success radius-0 w-100 btn-sm' href='?file=files/sql/update/" . htmlspecialchars(pathinfo($fileName, PATHINFO_FILENAME)) . "'>" . pathinfo($fileName, PATHINFO_FILENAME) . "{.update}</a><br>";
}
}
?>


   
<?php 
$files = array();
$dir = new DirectoryIterator('application/views/default/files/sql/unlink');
foreach ($dir as $fileinfo) {
$fileName = $fileinfo->getFilename();
$extension = pathinfo($fileName, PATHINFO_EXTENSION);

if ($extension === 'php' && !in_array(pathinfo($fileName, PATHINFO_FILENAME), ['builder', 'bootstrap', 'savecode'])) {
echo "<a class='btn btn-white text-danger radius-0 w-100 btn-sm' href='?file=files/sql/unlink/" . htmlspecialchars(pathinfo($fileName, PATHINFO_FILENAME)) . "'>" . pathinfo($fileName, PATHINFO_FILENAME) . "{.unlink}</a><br>";
}
}
?>



</div>











</div>





<div class="col-sm-8">


<?php
if (isset($_GET['file'])) {
?>    
    
<div id="editor"></div>
<textarea id="output" name="code" class="form-control" style="display:none"></textarea>








<div class="fixed-bottom text-end p-3">
<?php print Ajax ($result); ?>


<?php

$class = "text-center";
$color = "danger";
$style = "border-radius: 0rem;   position: fixed;   left: 0;";
$value = "<i class='fas fa-trash'></i> Delete";
$href  = "?do=unlink&file=$fileId";
$NT    = false;
BTN ($class,$color,$style,$value,$href,$NT);

?>



<button type="submit" class="btn btn-primary radius-0">Update Code</button>


<?php 
function string_between_two_string($str, $starting_word, $ending_word) { 
    $subtring_start = strpos($str, $starting_word);
    
    if ($subtring_start === false) {
        return false; // لم يتم العثور على الكلمة الأولى
    }

    $subtring_start += strlen($starting_word);
    $subtring_end = strpos($str, $ending_word, $subtring_start);

    if ($subtring_end === false) {
        return false; // لم يتم العثور على الكلمة الأخيرة
    }
    
    return substr($str, $subtring_start, $subtring_end - $subtring_start);
}





$str = $file_path;

if (substr($fileId, 0,14) == 'files/sql/get/'){

$substring = string_between_two_string($str, "/*/$fileId*/
Route::set('", "', function() {
View::make('/$fileId')"); 

$demo =  substr($fileId, 14);

}elseif (substr($fileId, 0,17) == 'files/sql/insert/'){

$substring = string_between_two_string($str, "/*/$fileId*/
Route::set('", "', function() {
View::make('/$fileId')"); 
$demo =  substr($fileId, 17);


}elseif (substr($fileId, 0,17) == 'files/sql/update/'){

$substring = string_between_two_string($str, "/*/$fileId*/
Route::set('", "', function() {
View::make('/$fileId')"); 
$demo =  substr($fileId, 17);


}elseif (substr($fileId, 17) == 'files/sql/unlink/'){

$substring = string_between_two_string($str, "/*/$fileId*/
Route::set('", "', function() {
View::make('/$fileId')"); 
$demo =  substr($fileId, 17);


}elseif (substr($fileId, 0,6) == 'Admin/'){

$substring = string_between_two_string($str, "/*$fileId*/
Route::set('", "', function() {
View::make('/$fileId')"); 

$demo =  substr($fileId, 6);


}elseif (substr($fileId, 0,10) == 'Dashboard/'){

$substring = string_between_two_string($str, "/*$fileId*/
Route::set('", "', function() {
View::make('/$fileId')"); 
$demo =  substr($fileId, 10);



}elseif (substr($fileId, 0,4) == 'App/'){

$substring = string_between_two_string($str, "/*$fileId*/
Route::set('", "', function() {
View::make('/$fileId')"); 

$demo =  substr($fileId, 4);


}else{

$substring = $fileId;
}






if ($fileId == 'Root'){


$class = "text-center mt-0 mb-0 ml-0 mr-0";
$color = "info";
$style = "border-radius:0rem";
$value = "<i class='fas fa-eye'></i> preview";
$href  = "./";
$NT    = true;
BTN ($class,$color,$style,$value,$href,$NT);




}else{

$class = "text-center mt-0 mb-0 ml-0 mr-0";
$color = "info";
$style = "border-radius:0rem";
$value = "<i class='fas fa-eye'></i> preview";
$href  = "./" . $demo;
$NT    = true;
BTN ($class,$color,$style,$value,$href,$NT);

}


$titre = "";
$name  = "NEWDATAPAGE";
$icon  = "";
$value = $fileId;
$type  = "hidden";
$class = "";
$style = "";
$ph    = "";
$error = "";
DII ($titre,$name,$icon,$type,$value,$class,$style,$ph,$error);



?>

</div>


<?php
}
?>


</div>
</div>




















<?php



FormEnd ();







}elseif($do == "new"){






Text ("h5","text-left font-weight-bold text-dark mt-3 mb-3","Create Route Activity","");


LINE ();





$id      =  "new_activity" ;
$method  =  "POST" ;
$action  =  "builder?do=new" ;
Form ($id,$method,$action);


print '
<div class="input-group radius-0 mb-3">
<span class="input-group-text"><i class="fa fa-file-code"></i></span>
<input name = "name" type="text" class="form-control" placeholder="Activity Name" name="">
</div>
';


print '
<div class="input-group radius-0 mb-3">
<span class="input-group-text"><i class="fa fa-window-restore"></i></span>
<input name = "route" type="text" class="form-control" placeholder="Activity Route" name="">
</div>
';

print '<div class="text-center">';
print '<button class="btn btn-primary my-3 radius-0">Add</button>';
print '</div>';


FormEnd ();






if ($_SERVER['REQUEST_METHOD'] == 'POST'){


$PWD_AC = $_POST ['name'];
$PWD_AN = $_POST ['route'];


$PWD_word = "View::make('$PWD_AC');";
$PWD_AC_F = "application/routes/Routes.php";
$OPENAC = fopen($PWD_AC_F, "r") or die("Unable to open file!");
$READAC = fread($OPENAC,filesize($PWD_AC_F));


$mystring = $READAC;


$formErrors = array();



if (empty($PWD_AC)){

$msg   = "Please fill in all fields";
$color = "danger";
alert ($msg,$color);
exit ();

}

if (empty($PWD_AN)){

$msg   = "Please fill in all fields";
$color = "danger";
alert ($msg,$color);
exit ();

}



$CheckFile = "application/controllers/" . $PWD_AN . ".php" ;
if (file_exists($CheckFile)) {

$msg   = "Activity Name Not valid or already exists";
$color = "danger";
alert ($msg,$color);
exit ();

}






if(strpos($mystring, $PWD_AC) !== false){

$msg   = "Activity Class Not valid or already exists";
$color = "danger";
alert ($msg,$color);
exit ();

} 



foreach ($formErrors as $error){
echo $error;
}




if (empty($formErrors)){




/*	
1--. GO TO ==> 'application/controllers'
----------->Create File {%FILENAME%}
..........................................                                        .
.                                        .
.                                        .
class %FILENAME% {}            
.                                        .  
.                                        .
..........................................
*/

$newClass = fopen("application/controllers/" . $PWD_AN . ".php", "w") or die("Unable to open file!");
$oneClass = "<?php class $PWD_AN {} ?>";
fwrite($newClass, $oneClass);
fclose($newClass);



/*
2--. GO TO ==> 'application/views'
----------->Create File {%FILENAME%}


..........................................                                      
.                                        .
.                                        .
$howz = new Bootstrap();         
$howz->runBootstrap();          
.                                        .  
.                                        .
..........................................
*/


$newClass = fopen("application/views/".themes."/" . $PWD_AN . ".php", "w") or die("Unable to open file!");
$TowClass = '<?php 

$howz = new Bootstrap(); $howz->runBootstrap(); 



?>';
fwrite($newClass, $TowClass);
fclose($newClass);

/*

3--. GO TO ==> 'application/routes/'
------>OPEN File {%Routes.php%}
------>ADD File CODE DOWN {%Routes.php%}


..........................................                                      
.                                        .
.                                        .
Route::set('ADDCODE', function() {
View::make('files/sql/insert/CODE');
});         
.                                        .  
.                                        .
..........................................

*/



$readClass  = "application/routes/Routes.php";
$OneFile = fopen($readClass, "r") or die("Unable to open file!");
$GetSource  = fread($OneFile,filesize($readClass));

$TO = $readClass;

$File = fopen($TO, "w") or die("Unable to open file!");


$NewCode = "
/*$PWD_AN*/
Route::set('$PWD_AC', function() {
View::make('$PWD_AN');
});

";

/* 
DETELE CLASS FROM ROUTES
$NewCode = str_replace($NewCode, "", $readClass);
*/
$Code = $GetSource . " " . $NewCode;

fwrite($File, $Code);
fclose($File);


LoadUrl ("builder?file=$PWD_AN");


}


}


















}elseif($do == "new_admin"){






Text ("h5","text-left font-weight-bold text-dark mt-3 mb-3","Create Admin Activity","");


LINE ();





$id      =  "new_activity" ;
$method  =  "POST" ;
$action  =  "builder?do=new_admin" ;
Form ($id,$method,$action);


print '
<div class="input-group radius-0 mb-3">
<span class="input-group-text"><i class="fa fa-file-code"></i></span>
<input name = "name" type="text" class="form-control" placeholder="Activity Name" name="">
</div>
';


print '
<div class="input-group radius-0 mb-3">
<span class="input-group-text"><i class="fa fa-window-restore"></i></span>
<input name = "route" type="text" class="form-control" placeholder="Activity Route" name="">
</div>
';

print '<div class="text-center">';
print '<button class="btn btn-primary my-3 radius-0">Add</button>';
print '</div>';


FormEnd ();






if ($_SERVER['REQUEST_METHOD'] == 'POST'){


$PWD_AC = $_POST ['name'];
$PWD_AN = $_POST ['route'];


$PWD_word = "View::make('$PWD_AC');";
$PWD_AC_F = "application/routes/Routes.php";
$OPENAC = fopen($PWD_AC_F, "r") or die("Unable to open file!");
$READAC = fread($OPENAC,filesize($PWD_AC_F));


$mystring = $READAC;


$formErrors = array();



if (empty($PWD_AC)){

$msg   = "Please fill in all fields";
$color = "danger";
alert ($msg,$color);
exit ();

}

if (empty($PWD_AN)){

$msg   = "Please fill in all fields";
$color = "danger";
alert ($msg,$color);
exit ();

}



$CheckFile = "application/controllers/Admin/" . $PWD_AN . ".php" ;
if (file_exists($CheckFile)) {

$msg   = "Activity Name Not valid or already exists";
$color = "danger";
alert ($msg,$color);
exit ();

}






if(strpos($mystring, $PWD_AC) !== false){

$msg   = "Activity Class Not valid or already exists";
$color = "danger";
alert ($msg,$color);
exit ();

} 



foreach ($formErrors as $error){
echo $error;
}




if (empty($formErrors)){




/*	
1--. GO TO ==> 'application/controllers'
----------->Create File {%FILENAME%}
..........................................                                        .
.                                        .
.                                        .
class %FILENAME% {}            
.                                        .  
.                                        .
..........................................
*/

$newClass = fopen("application/controllers/Admin/" . $PWD_AN . ".php", "w") or die("Unable to open file!");
$oneClass = "<?php class $PWD_AN {} ?>";
fwrite($newClass, $oneClass);
fclose($newClass);



/*
2--. GO TO ==> 'application/views'
----------->Create File {%FILENAME%}


..........................................                                      
.                                        .
.                                        .
$howz = new Bootstrap();         
$howz->runBootstrap();          
.                                        .  
.                                        .
..........................................
*/


$newClass = fopen("application/views/".themes."/Admin/" . $PWD_AN . ".php", "w") or die("Unable to open file!");
$TowClass = '<?php 

$howz = new Bootstrap(); $howz->runBootstrap(); 



?>';
fwrite($newClass, $TowClass);
fclose($newClass);

/*

3--. GO TO ==> 'application/routes/'
------>OPEN File {%Routes.php%}
------>ADD File CODE DOWN {%Routes.php%}


..........................................                                      
.                                        .
.                                        .
Route::set('ADDCODE', function() {
View::make('files/sql/insert/CODE');
});         
.                                        .  
.                                        .
..........................................

*/



$readClass  = "application/routes/Routes.php";
$OneFile = fopen($readClass, "r") or die("Unable to open file!");
$GetSource  = fread($OneFile,filesize($readClass));

$TO = $readClass;

$File = fopen($TO, "w") or die("Unable to open file!");


$NewCode = "
/*Admin/$PWD_AN*/
Route::set('$PWD_AC', function() {
View::make('Admin/$PWD_AN');
});

";

/* 
DETELE CLASS FROM ROUTES
$NewCode = str_replace($NewCode, "", $readClass);
*/
$Code = $GetSource . " " . $NewCode;

fwrite($File, $Code);
fclose($File);


LoadUrl ("builder?file=Admin/$PWD_AN");


}


}



















}elseif($do == "new_dashboard"){






Text ("h5","text-left font-weight-bold text-dark mt-3 mb-3","Create Dashboard Activity","");


LINE ();





$id      =  "new_activity" ;
$method  =  "POST" ;
$action  =  "builder?do=new_dashboard" ;
Form ($id,$method,$action);


print '
<div class="input-group radius-0 mb-3">
<span class="input-group-text"><i class="fa fa-file-code"></i></span>
<input name = "name" type="text" class="form-control" placeholder="Activity Name" name="">
</div>
';


print '
<div class="input-group radius-0 mb-3">
<span class="input-group-text"><i class="fa fa-window-restore"></i></span>
<input name = "route" type="text" class="form-control" placeholder="Activity Route" name="">
</div>
';

print '<div class="text-center">';
print '<button class="btn btn-primary my-3 radius-0">Add</button>';
print '</div>';


FormEnd ();






if ($_SERVER['REQUEST_METHOD'] == 'POST'){


$PWD_AC = $_POST ['name'];
$PWD_AN = $_POST ['route'];


$PWD_word = "View::make('$PWD_AC');";
$PWD_AC_F = "application/routes/Routes.php";
$OPENAC = fopen($PWD_AC_F, "r") or die("Unable to open file!");
$READAC = fread($OPENAC,filesize($PWD_AC_F));


$mystring = $READAC;


$formErrors = array();



if (empty($PWD_AC)){

$msg   = "Please fill in all fields";
$color = "danger";
alert ($msg,$color);
exit ();

}

if (empty($PWD_AN)){

$msg   = "Please fill in all fields";
$color = "danger";
alert ($msg,$color);
exit ();

}



$CheckFile = "application/controllers/Dashboard/" . $PWD_AN . ".php" ;
if (file_exists($CheckFile)) {

$msg   = "Activity Name Not valid or already exists";
$color = "danger";
alert ($msg,$color);
exit ();

}






if(strpos($mystring, $PWD_AC) !== false){

$msg   = "Activity Class Not valid or already exists";
$color = "danger";
alert ($msg,$color);
exit ();

} 



foreach ($formErrors as $error){
echo $error;
}




if (empty($formErrors)){




/*	
1--. GO TO ==> 'application/controllers'
----------->Create File {%FILENAME%}
..........................................                                        .
.                                        .
.                                        .
class %FILENAME% {}            
.                                        .  
.                                        .
..........................................
*/

$newClass = fopen("application/controllers/Dashboard/" . $PWD_AN . ".php", "w") or die("Unable to open file!");
$oneClass = "<?php class $PWD_AN {} ?>";
fwrite($newClass, $oneClass);
fclose($newClass);



/*
2--. GO TO ==> 'application/views'
----------->Create File {%FILENAME%}


..........................................                                      
.                                        .
.                                        .
$howz = new Bootstrap();         
$howz->runBootstrap();          
.                                        .  
.                                        .
..........................................
*/


$newClass = fopen("application/views/".themes."/Dashboard/" . $PWD_AN . ".php", "w") or die("Unable to open file!");
$TowClass = '<?php 

$howz = new Bootstrap(); $howz->runBootstrap(); 



?>';
fwrite($newClass, $TowClass);
fclose($newClass);

/*

3--. GO TO ==> 'application/routes/'
------>OPEN File {%Routes.php%}
------>ADD File CODE DOWN {%Routes.php%}


..........................................                                      
.                                        .
.                                        .
Route::set('ADDCODE', function() {
View::make('files/sql/insert/CODE');
});         
.                                        .  
.                                        .
..........................................

*/



$readClass  = "application/routes/Routes.php";
$OneFile = fopen($readClass, "r") or die("Unable to open file!");
$GetSource  = fread($OneFile,filesize($readClass));

$TO = $readClass;

$File = fopen($TO, "w") or die("Unable to open file!");


$NewCode = "
/*Dashboard/$PWD_AN*/
Route::set('$PWD_AC', function() {
View::make('Dashboard/$PWD_AN');
});

";

/* 
DETELE CLASS FROM ROUTES
$NewCode = str_replace($NewCode, "", $readClass);
*/
$Code = $GetSource . " " . $NewCode;

fwrite($File, $Code);
fclose($File);


LoadUrl ("builder?file=Dashboard/$PWD_AN");


}


}






















}elseif($do == "new_app"){






Text ("h5","text-left font-weight-bold text-dark mt-3 mb-3","Create App Activity","");


LINE ();





$id      =  "new_activity" ;
$method  =  "POST" ;
$action  =  "builder?do=new_app" ;
Form ($id,$method,$action);


print '
<div class="input-group radius-0 mb-3">
<span class="input-group-text"><i class="fa fa-file-code"></i></span>
<input name = "name" type="text" class="form-control" placeholder="Activity Name" name="">
</div>
';


print '
<div class="input-group radius-0 mb-3">
<span class="input-group-text"><i class="fa fa-window-restore"></i></span>
<input name = "route" type="text" class="form-control" placeholder="Activity Route" name="">
</div>
';

print '<div class="text-center">';
print '<button class="btn btn-primary my-3 radius-0">Add</button>';
print '</div>';


FormEnd ();






if ($_SERVER['REQUEST_METHOD'] == 'POST'){


$PWD_AC = $_POST ['name'];
$PWD_AN = $_POST ['route'];


$PWD_word = "View::make('$PWD_AC');";
$PWD_AC_F = "application/routes/Routes.php";
$OPENAC = fopen($PWD_AC_F, "r") or die("Unable to open file!");
$READAC = fread($OPENAC,filesize($PWD_AC_F));


$mystring = $READAC;


$formErrors = array();



if (empty($PWD_AC)){

$msg   = "Please fill in all fields";
$color = "danger";
alert ($msg,$color);
exit ();

}

if (empty($PWD_AN)){

$msg   = "Please fill in all fields";
$color = "danger";
alert ($msg,$color);
exit ();

}



$CheckFile = "application/controllers/App/" . $PWD_AN . ".php" ;
if (file_exists($CheckFile)) {

$msg   = "Activity Name Not valid or already exists";
$color = "danger";
alert ($msg,$color);
exit ();

}






if(strpos($mystring, $PWD_AC) !== false){

$msg   = "Activity Class Not valid or already exists";
$color = "danger";
alert ($msg,$color);
exit ();

} 



foreach ($formErrors as $error){
echo $error;
}




if (empty($formErrors)){




/*	
1--. GO TO ==> 'application/controllers'
----------->Create File {%FILENAME%}
..........................................                                        .
.                                        .
.                                        .
class %FILENAME% {}            
.                                        .  
.                                        .
..........................................
*/

$newClass = fopen("application/controllers/App/" . $PWD_AN . ".php", "w") or die("Unable to open file!");
$oneClass = "<?php class $PWD_AN {} ?>";
fwrite($newClass, $oneClass);
fclose($newClass);



/*
2--. GO TO ==> 'application/views'
----------->Create File {%FILENAME%}


..........................................                                      
.                                        .
.                                        .
$howz = new Bootstrap();         
$howz->runBootstrap();          
.                                        .  
.                                        .
..........................................
*/


$newClass = fopen("application/views/".themes."/App/" . $PWD_AN . ".php", "w") or die("Unable to open file!");
$TowClass = '<?php 

$howz = new Bootstrap(); $howz->runBootstrap(); 



?>';
fwrite($newClass, $TowClass);
fclose($newClass);

/*

3--. GO TO ==> 'application/routes/'
------>OPEN File {%Routes.php%}
------>ADD File CODE DOWN {%Routes.php%}


..........................................                                      
.                                        .
.                                        .
Route::set('ADDCODE', function() {
View::make('files/sql/insert/CODE');
});         
.                                        .  
.                                        .
..........................................

*/



$readClass  = "application/routes/Routes.php";
$OneFile = fopen($readClass, "r") or die("Unable to open file!");
$GetSource  = fread($OneFile,filesize($readClass));

$TO = $readClass;

$File = fopen($TO, "w") or die("Unable to open file!");


$NewCode = "
/*Dashboard/$PWD_AN*/
Route::set('$PWD_AC', function() {
View::make('App/$PWD_AN');
});

";

/* 
DETELE CLASS FROM ROUTES
$NewCode = str_replace($NewCode, "", $readClass);
*/
$Code = $GetSource . " " . $NewCode;

fwrite($File, $Code);
fclose($File);


LoadUrl ("builder?file=App/$PWD_AN");


}


}



















}elseif($do == "new_sql"){






Text ("h5","text-left font-weight-bold text-dark mt-3 mb-3","Create {SQL} Activity","");


LINE ();





$id      =  "new_activity" ;
$method  =  "POST" ;
$action  =  "builder?do=new_sql" ;
Form ($id,$method,$action);


print '
<div class="input-group radius-0 mb-3">
<span class="input-group-text"><i class="fa fa-file-code"></i></span>
<input name = "name" type="text" class="form-control" placeholder="Activity Name" name="">
</div>
';


print '
<div class="input-group radius-0 mb-3">
<span class="input-group-text"><i class="fa fa-window-restore"></i></span>
<input name = "route" type="text" class="form-control" placeholder="Activity Route" name="">
</div>
';


print '



<input type="radio" class="btn-check" name="sql" value="get" id="option5" autocomplete="off">
<label class="btn" for="option5">Get</label>

<input type="radio" class="btn-check" name="sql" value="insert" id="option6" autocomplete="off">
<label class="btn" for="option6">Insert</label>
 
<input type="radio" class="btn-check" name="sql" value="update" id="option8" autocomplete="off">
<label class="btn" for="option8">Update</label>
 
<input type="radio" class="btn-check" name="sql" value="unlink" id="option9" autocomplete="off">
<label class="btn" for="option9">Unlink</label>



';


print '<div class="text-center">';
print '<button class="btn btn-primary my-3 radius-0">Add</button>';
print '</div>';


FormEnd ();






if ($_SERVER['REQUEST_METHOD'] == 'POST'){


$PWD_AC = $_POST ['name'];
$PWD_AN = $_POST ['route'];
$sql = $_POST ['sql'];





$PWD_word = "View::make('$PWD_AC');";
$PWD_AC_F = "application/routes/Routes.php";
$OPENAC = fopen($PWD_AC_F, "r") or die("Unable to open file!");
$READAC = fread($OPENAC,filesize($PWD_AC_F));


$mystring = $READAC;


$formErrors = array();



if (empty($PWD_AC)){

$msg   = "Please fill in all fields";
$color = "danger";
alert ($msg,$color);
exit ();

}

if (empty($PWD_AN)){

$msg   = "Please fill in all fields";
$color = "danger";
alert ($msg,$color);
exit ();

}

if (empty($sql)){

$msg   = "Please fill in all fields";
$color = "danger";
alert ($msg,$color);
exit ();

}

if ($sql == "get"){
$sDir = "files/sql/get";
}elseif($sql == "update"){
$sDir = "files/sql/update";
}elseif($sql == "insert"){
$sDir = "files/sql/insert";
}elseif($sql == "unlink"){
$sDir = "files/sql/unlink";
}



$CheckFile = "application/controllers/$sDir/" . $PWD_AN . ".php" ;
if (file_exists($CheckFile)) {

$msg   = "Activity Name Not valid or already exists";
$color = "danger";
alert ($msg,$color);
exit ();

}






if(strpos($mystring, $PWD_AC) !== false){

$msg   = "Activity Class Not valid or already exists";
$color = "danger";
alert ($msg,$color);
exit ();

} 



foreach ($formErrors as $error){
echo $error;
}




if (empty($formErrors)){




/*	
1--. GO TO ==> 'application/controllers'
----------->Create File {%FILENAME%}
..........................................                                        .
.                                        .
.                                        .
class %FILENAME% {}            
.                                        .  
.                                        .
..........................................
*/

$newClass = fopen("application/controllers/$sDir/" . $PWD_AN . ".php", "w") or die("Unable to open file!");
$oneClass = "<?php class $PWD_AN {} ?>";
fwrite($newClass, $oneClass);
fclose($newClass);



/*
2--. GO TO ==> 'application/views'
----------->Create File {%FILENAME%}


..........................................                                      
.                                        .
.                                        .
$howz = new Bootstrap();         
$howz->runBootstrap();          
.                                        .  
.                                        .
..........................................
*/


$newClass = fopen("application/views/".themes."/$sDir/" . $PWD_AN . ".php", "w") or die("Unable to open file!");
$TowClass = '<?php 

$howz = new Bootstrap(); $howz->runBootstrap(); 



?>';
fwrite($newClass, $TowClass);
fclose($newClass);

/*

3--. GO TO ==> 'application/routes/'
------>OPEN File {%Routes.php%}
------>ADD File CODE DOWN {%Routes.php%}


..........................................                                      
.                                        .
.                                        .
Route::set('ADDCODE', function() {
View::make('files/sql/insert/CODE');
});         
.                                        .  
.                                        .
..........................................

*/



$readClass  = "application/routes/Routes.php";
$OneFile = fopen($readClass, "r") or die("Unable to open file!");
$GetSource  = fread($OneFile,filesize($readClass));

$TO = $readClass;

$File = fopen($TO, "w") or die("Unable to open file!");


$NewCode = "
/*$sDir/$PWD_AN*/
Route::set('$PWD_AC', function() {
View::make('$sDir/$PWD_AN');
});

";

/* 
DETELE CLASS FROM ROUTES
$NewCode = str_replace($NewCode, "", $readClass);
*/
$Code = $GetSource . " " . $NewCode;

fwrite($File, $Code);
fclose($File);


LoadUrl ("builder?file=$sDir/$PWD_AN");


}


}


















}elseif($do == "unlink"){










unlink ("application/views/".themes."/$fileId.php");
unlink ("application/controllers/$fileId.php");


$readClass  = "application/routes/Routes.php";
$OneFile = fopen($readClass, "r") or die("Unable to open file!");
$GetSource  = fread($OneFile,filesize($readClass));

$TO = $readClass;

$File = fopen($TO, "w") or die("Unable to open file!");


function string_between_two_string($str, $starting_word, $ending_word) 
{ 
$subtring_start = strpos($str, $starting_word); 
//Adding the strating index of the strating word to  
//its length would give its ending index 
$subtring_start += strlen($starting_word);   
//Length of our required sub string 
$size = strpos($str, $ending_word, $subtring_start) - $subtring_start;   
// Return the substring from the index substring_start of length size  
return substr($str, $subtring_start, $size);   
} 

$str = $GetSource;
$substring = string_between_two_string($str, "/*$fileId*/
Route::set('", "', function() {
View::make('$fileId')"); 

$y = $substring;



if(substr($fileId, 0,6) == 'Admin/'){



$NewCode = "
/*$fileId*/
Route::set('".substr($fileId,6)."', function() {
View::make('$fileId');
});";



}

if(substr($fileId, 0,10) == 'Dashboard/'){

$NewCode = "
/*$fileId*/
Route::set('".substr($fileId,10)."', function() {
View::make('$fileId');
});";




}


if(substr($fileId, 0,4) == 'App/'){

$NewCode = "
/*$fileId*/
Route::set('".substr($fileId,4)."', function() {
View::make('$fileId');
});";




}



if(substr($fileId, 0,14) == 'files/sql/get/'){

$NewCode = "
/*$fileId*/
Route::set('".substr($fileId,14)."', function() {
View::make('$fileId');
});";


}





if(substr($fileId, 0,17) == 'files/sql/insert/'){

$NewCode = "
/*$fileId*/
Route::set('".substr($fileId,17)."', function() {
View::make('$fileId');
});";


}





if(substr($fileId, 0,17) == 'files/sql/update/'){

$NewCode = "
/*$fileId*/
Route::set('".substr($fileId,17)."', function() {
View::make('$fileId');
});";


}



if(substr($fileId, 0,17) == 'files/sql/unlink/'){

$NewCode = "
/*$fileId*/
Route::set('".substr($fileId,17)."', function() {
View::make('$fileId');
});";


}








if(substr($fileId, 0,1) == '/'){

    
$NewCode = "
/*$fileId*/
Route::set('$fileId', function() {
View::make('$fileId');
});";
    
    
}



$Code = str_replace($NewCode, "", $GetSource);



fwrite($File, $Code);
fclose($File);



LoadUrl ("builder");
























}else{





}
?>







</div>

<?php

?>



<script>
function openRoute(){
var x = document.getElementById('route');
if (x.style.display === 'none') {
x.style.display = 'block';
}else{
x.style.display = 'none';
}
}
</script>	







<script>
function openAdmin(){
var x = document.getElementById('admin');
if (x.style.display === 'none') {
x.style.display = 'block';
}else{
x.style.display = 'none';
}
}
</script>	






<script>
function openDashboard(){
var x = document.getElementById('dashboard');
if (x.style.display === 'none') {
x.style.display = 'block';
}else{
x.style.display = 'none';
}
}
</script>	




<script>
function openApp(){
var x = document.getElementById('app');
if (x.style.display === 'none') {
x.style.display = 'block';
}else{
x.style.display = 'none';
}
}
</script>	



<script>
function openSql(){
var x = document.getElementById('sql');
if (x.style.display === 'none') {
x.style.display = 'block';
}else{
x.style.display = 'none';
}
}
</script>	







<script>
let editor;

require.config({ paths: { 'vs': 'https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.43.0/min/vs' } });

require(["vs/editor/editor.main"], function () {
let theme = window.matchMedia("(prefers-color-scheme: dark)").matches ? "vs-dark" : "vs";

editor = monaco.editor.create(document.getElementById("editor"), {
value: <?php echo json_encode($file_contents ?? ''); ?>,  // استخدم json_encode لضمان التعامل مع المحتوى بشكل صحيح
language: "php",
theme: theme,
fontSize: 14,
lineNumbers: "on",
minimap: { enabled: false }
});

editor.onDidChangeModelContent(() => {
document.getElementById("output").value = editor.getValue();
});

window.matchMedia("(prefers-color-scheme: dark)").addEventListener("change", e => {
monaco.editor.setTheme(e.matches ? "vs-dark" : "vs");
});

const divElem = document.getElementById('editor');
new ResizeObserver(() => editor.layout()).observe(divElem);
});

function saveContent() {
document.getElementById("output").value = editor.getValue();
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
