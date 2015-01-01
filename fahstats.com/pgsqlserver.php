<?php

if (!isset($_SERVER['HTTP_USER_AGENT'])) exit(0);

if (strpos($_SERVER['HTTP_USER_AGENT'], 'Mediapartners') === false) {
   
#   $port = '5432'; # local
#   $port = '5434'; # d3
#   $port = '5433'; # s1
   $port = '6432'; # pgbouncer
   $localhost='localhost';
#   $localhost='10.1.1.104';
   $conn_string = "host=$localhost port=$port dbname=fahstats user='phpUser' password=sompassword";
#   echo 'Database maintenance';
#   echo '<a href="http://forum.kakaostats.com/index.php/topic,1107.0.html" style="color:orange;font-size:x-large;">Connection broken</a>';
#   exit(0);
#	$conn_string = '';
#   error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
}
else {
   include './google_ad_section.html';
   exit(0);
}
?>
