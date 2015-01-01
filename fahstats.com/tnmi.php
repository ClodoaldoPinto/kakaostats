<?php
if (!isset($_GET['t'])) exit;
$team = $_GET['t'];
if (preg_match('/\D/', $team) == 1 || $team == '') exit;
session_start();
$_SESSION['v'] = '1';
require './pgsqlserver.php';
$link = pg_pconnect($conn_string);
$query = "select to_char (last_date, 'YYYY-MM-DD HH24:MI') as date,".
"  extract (hour from last_date) as hour from last_date;";
$result = pg_query($link, $query);
$line = pg_fetch_array($result, NULL, PGSQL_ASSOC);
$date = $line["date"];
$lastDayUpdate = $line['hour'] >= 21 ? 1 : 0;
$query = "
  select ti.time_nome as name
  from times_indice as ti
  where ti.n_time = $team
;";
$result = pg_query($link, $query);
$line = pg_fetch_array($result, NULL, PGSQL_ASSOC);
$teamName = $line["name"];
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
<?php include "./meta.html"; ?>
<LINK REL=StyleSheet HREF="/script/ks2-2.css" TYPE="text/css" MEDIA=screen>
<LINK REL="SHORTCUT ICON" href="/favicon.ico">
<title>Folding@Home Stats - <?php echo $teamName ?>'s New Members</title>
<script type="text/javascript" src="/script/js.js"></script>
</head>
<body style="text-align:center;"
  onload="
  document.getElementById('ttu').innerHTML = timeToUpdate(time_of_update);
  window.setInterval('document.getElementById(\'ttu\').innerHTML = timeToUpdate(time_of_update)', 1000);
  ">
<?php
$menu = array(
  'anchors' => True,
  'anchors_all_donors' => True,
  'anchors_all_teams' => True,
  'team' => True,
  'team_radar' => True,
  'team_history' => True,
  'team_members' => True
  );
include './menu.php';
include './adrotator.php';
?>
<p style="text-align:center;margin:0 auto 0.5em auto;font-size:22px;color:floralWhite;">
<?php
  echo htmlentities($teamName,ENT_QUOTES), " at ",
  "<span style=\"font-size:18px;\">",
  "<script type='text/javascript'>",
  "document.write(wDate('$date')+' '+timeZone());",
  "</script></span>";
?></p>
<div style="margin: 0 auto 1em;padding:0;">
<a href="http://forum.fahstats.com/viewtopic.php?p=14"
 title="Link this image in your forum! Click to view instructions"
 <img src="/nmi/<?php echo $team,'-14.png' ?>"
  width=320 alt="Team New Members"
  style="border:1px solid black;padding:0;margin:0px;">
</a>
</div>
<?php include "./footer.html"; ?>
</body></html>
