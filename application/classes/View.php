<?php

$template = "default";
class View {
  /*
   * If the route is valid create the view and the view controller.
   * If the route is invalid do nothing and if something goes wrong
   * checking the route return 0;
  */
  public static function make($view) {

    if (Route::isRouteValid()) {
        // Create the view and the view controller.
		$GLOBAL_URL = "https://" . $_SERVER['HTTP_HOST'] . BASEDIR ;
        require ($_SERVER['DOCUMENT_ROOT'] . BASEDIR  . 'application/config/Get_Config.php');

        require_once( './application/controllers/'.$view.'.php' );
        require_once( "./application/views/$template/$view.php" );
        return 1;
    }

  }

}
