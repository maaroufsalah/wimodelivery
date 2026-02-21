<?php
global $con;
include get_file("files/sql/get/session");

if ($loginRank == "admin"){
include get_file("Admin/a_nav_left");

}elseif($loginRank == "user"){
include get_file("Admin/u_nav_left");

}elseif($loginRank == "delivery"){
include get_file("Admin/d_nav_left");

}elseif($loginRank == "aide"){
include get_file("Admin/s_nav_left");

}


?>


