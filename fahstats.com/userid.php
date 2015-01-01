<?php

header('Content-type: text/plain');
if (!isset($_GET['un']) || !isset($_GET['t'])) exit;
$un = pg_escape_string($_GET['un']);
$team = $_GET['t'];
if (preg_match('/\D/', $team) == 1 || $team == '') exit;
require './pgsqlserver.php';
$link = pg_connect($conn_string);
$query = <<<query
select usuario_serial as uid
from usuarios_indice
where n_time = $team and usuario_nome = '$un'
query;
$result = pg_query($link, $query);
$line = pg_fetch_array($result, NULL, PGSQL_ASSOC);
$uid = $line['uid'];
echo $uid;
?>

