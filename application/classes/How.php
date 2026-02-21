<?php



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




