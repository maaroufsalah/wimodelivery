<?php


class Builder {
  /*
   * If the route is valid create the view and the view controller.
   * If the route is invalid do nothing and if something goes wrong
   * checking the route return 0;
  */
  public static function make($builder) {

    if (Route::isRouteValid()) {
        // Create the view and the view controller.
		$GLOBAL_URL = "http://" . $_SERVER['HTTP_HOST'] . BASEDIR ;
        require ($_SERVER['DOCUMENT_ROOT'] . BASEDIR  . 'application/views/files/Get_Config.php');

        require_once( './application/controllers/'.$builder.'.php' );
        require_once( './application/views/'.$builder.'.php' );
        return 1;
    }

  }

}