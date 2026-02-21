<?php 

global $con;


setcookie("login_session", "", time() - 3600);


if (function_exists('load_url')) {
load_url("login_account", 1);
}

