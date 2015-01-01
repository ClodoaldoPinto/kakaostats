<?php
if (
  !isset($_POST['unitinfo']) ||
  !isset($_POST['client']) ||
  !isset($_POST['sys_platform']) ||
  !isset($_POST['client_name']) ||
  !isset($_POST['mdoct'])
  ) exit();
  
$nr = '[\n\r]+';

$client_name = $_POST['client_name'];
//if (preg_match('/^\w{1,20}$/',$client_name) == 0) exit();
$sys_platform = $_POST['sys_platform'];
//if (preg_match('/^\w{1,20}$/',$sys_platform) == 0) exit();

$datePattern = '\s*[a-zA-Z]{4,9}\s+\d{1,2}\s+\d\d:\d\d:\d\d$';
$unitinfo = $_POST['unitinfo'];
//if (strlen($unitinfo) > 300 || strlen($unitinfo) == 0) exit();
$protein_name = '^Name:\s*(\w+)\s*$';
$download_date = '^Download time:\s*('.$datePattern.')\s*$';
$due_date = '^Due time:\s*('.$datePattern.')\s*$';
$progress = '^Progress:\s*(\d{1,3})%.*$';
$pattern = $protein_name.$nr.$download_date.$nr.$due_date.$nr.$progress;
$nMatches = preg_match('/'.$pattern.'/im',$unitinfo,$matches);
/*
if ($nMatches != 1) {
  exit();
  }
*/
$protein_name = pg_escape_string($matches[1]);
$download_date = '2005 ' . pg_escape_string($matches[2]);
$due_date = '2006 '. pg_escape_string($matches[3]);
$progress = $matches[4];

/*if (
  strlen($protein_name) > 60 ||
  $progress < 0 || $progress > 100
  ) exit();
*/
$client = $_POST['client'];
//if (strlen($client) > 200) exit();
$user_name = '^username=\s*(\w*)\s*$';
$team = '^team=\s*(\d+)\s*$';
$asknet = '^asknet=.*$';
$machine_id = '^machineid=\s*(\d+)\s*$';
$pattern = $user_name.$nr.$team.$nr.$nr.$machine_id;
$nMatches = preg_match('/'.$pattern.'/im',$client,$matches);
//if ($nMatches != 1) exit();
$user_name = $matches[1];
$team = $matches[2];
$machine_id = $matches[3];
/*
if (
  strlen($user_name) > 100 ||
  $machine_id < 1 || $machine_id > 8 ||
  $team > 500000
  ) exit();
*/
$mdoct = $_POST['mdoct'];
//if (preg_match('/(\\\[0-7]{3}){8}/',$mdoct) != 1) exit();

require '../pgsqlserver.php';

$link = pg_pconnect($conn_string);
$query =
  "select insert_client_progress ( ".
  "  $team, '$user_name', $machine_id, '$protein_name', ".
  "  '$due_date', '$download_date', $progress, ".
  "  '$mdoct' ".
  "  );";
$result = pg_query($link, $query);
?>
