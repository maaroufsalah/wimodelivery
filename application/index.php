<?php



require_once('./application/_Globals.php');

require_once('./application/routes/Routes.php');



spl_autoload_register(function  ($class_name) {
    require_once './application/classes/'.$class_name.'.php';
});

spl_autoload_register($class_name);

$how = new How();
$how->run();

