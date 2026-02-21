<?php




$Routes = array();


define( 'BASEDIR', '/' );
define( 'themes', 'default' );




require_once('./application/routes/Routes.php');






class Route {

/*
* Checks if the current route is valid. Checks the route
* against the global $Routes array.
*/
public static function isRouteValid() {
global $Routes;
$uri = $_SERVER['REQUEST_URI'];

if (!in_array(explode('?',$uri)[0], $Routes)) {
return 0;
} else {
return 1;
}
}

// Insert the route into the $Routes array.
private static function registerRoute($route) {

global $Routes;
$Routes[] = BASEDIR.$route;

}

// This method creates dynamic routes.
public static function dyn($dyn_routes) {
// Split the route on '/', i.e user/<1>
$route_components = explode('/', $dyn_routes);
// Split the URI on '/', i.e user/francis
$uri_components = explode('/', substr($_SERVER['REQUEST_URI'], strlen(BASEDIR)-1));

// Loop through $route_components, this allows infinite dynamic parameters in the future.
for ($i = 0; $i < count($route_components); $i++) {
// Ensure we don't go out of range by enclosing in an if statement.
if ($i+1 <= count($uri_components)-1) {
// Replace every occurrence of <n> with a parameter.
$route_components[$i] = str_replace("<$i>", $uri_components[$i+1], $route_components[$i]);
}
}
// Join the array back into a string.
$route = implode($route_components, '/');
// Return the route.
return $route;
}

// Register the route and run the closure using __invoke().
public static function set($route, $closure) {
if ($_SERVER['REQUEST_URI'] == BASEDIR.$route) {
self::registerRoute($route);
$closure->__invoke();
} else if (explode('?', $_SERVER['REQUEST_URI'])[0] == BASEDIR.$route) {
self::registerRoute($route);
$closure->__invoke();
} else if (isset($_GET['url']) && $_GET['url'] == explode('/', $route)[0]) {
self::registerRoute(self::dyn($route));
$closure->__invoke();
}
}
}











class URI {

public static function get($param) {

if (strpos($_SERVER['REQUEST_URI'], '?') !== false) {
return explode('&', explode($param.'=', $_SERVER['REQUEST_URI'])[1])[0];
} else {
return false;
}

}

}




class How {

/*
* getRoute() is the method that actually checks if the current
* route is valid or not.
*/
public function getRoute() {

global $Routes;
$uri = $_SERVER['REQUEST_URI'];

// Check if the route is in $Routes
if (!in_array(explode('?',$uri)[0], $Routes)) {
die( 'Invalid route.' );
}

return $uri;

}

/*
* The run() method is the first method that runs.
* run() gets the current route and checks if it is valid.
* If the route is invalid the app doesn't proceed any further.
*/
public function run() {

// Should be capturing the output of this method. We will at some point.
$this->getRoute();

}

}






















class View {
/*
* If the route is valid create the view and the view controller.
* If the route is invalid do nothing and if something goes wrong
* checking the route return 0;
*/


public static function make($view) {

if (Route::isRouteValid()) {

require_once( './application/controllers/'.$view.'.php' );
require_once( './application/views/'.themes.'/'.$view.'.php' );
return 1;

}

}



}
















class Request {

public static function post($data) {

$file = file_get_contents("php://input");
$file = explode("&", $file);
for ($i = 0; $i < count($file); $i++) {
$sub = explode('=', $file[$i]);
if ($sub[0] == $data) {
return utf8_decode(urldecode($sub[1]));
}
}

}

}








spl_autoload_register(function ($class) {
require_once( './application/controllers/'.$class.'.php' );



require_once( './application/views/'.themes.'/'.$class.'.php' );

});









$how = new How();
$how->run();



?>