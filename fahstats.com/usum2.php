<?php
if (!isset($_GET['u'])) exit;
$donor = $_GET['u'];
if (preg_match('/\D/', $donor) == 1 || $donor == '') exit;
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
$query = "select ui.n_time as team, ui.usuario_nome as name ".
  "from usuarios_indice as ui ".
  "where ui.usuario_serial=".$donor.";";
$result = pg_query($link, $query);
$line = pg_fetch_array($result, NULL, PGSQL_ASSOC);
$donorName = $line["name"];
$team = $line ['team'];
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
<?php include "./meta.html"; ?>
<script type="text/javascript">
if (self != top) top.location.href = location.href;
var agt=navigator.userAgent.toLowerCase();
var appVer = navigator.appVersion.toLowerCase();
var is_safari = ((agt.indexOf('safari')!=-1)&&(agt.indexOf('mac')!=-1))?true:false;
var is_konq = false;
var kqPos   = agt.indexOf('konqueror');
if (kqPos !=-1) {
   is_konq  = true;
}
var is_khtml  = (is_safari || is_konq);
var is_opera = (agt.indexOf("opera") != -1);
var iePos  = appVer.indexOf('msie');
is_ie   = ((iePos!=-1) && (!is_opera) && (!is_khtml));

function stopError() {
  return true;
  }
function changeDisplay(pwa, pwId, tb) {
  document.getElementById("pwa1" + tb).style.background = "lightSteelBlue";
  document.getElementById("pwa2" + tb).style.background = "lightSteelBlue";
  document.getElementById("pwa3" + tb).style.background = "lightSteelBlue";
  document.getElementById(pwId + tb).style.background = "blanchedAlmond";
  var show = "table-cell";
  if (is_ie) show = "block";
  var noshow = "none";
  var tblChilds = document.getElementById (tb).childNodes;
  for (var i=0; i<tblChilds.length; i++) {
    var trChilds = tblChilds[i].childNodes;
    for (j=0; j<trChilds.length; j++)
      if (trChilds[j].nodeType == 1) {
        if (trChilds[j].className == "pt")
          trChilds[j].style.display = pwa == "pt" ? show:noshow;
        else if (trChilds[j].className == "wu")
          trChilds[j].style.display = pwa == "wu" ? show:noshow;
        else if (trChilds[j].className == "pw")
          trChilds[j].style.display = pwa == "pw" ? show:noshow;
        }
    }
  }
</script>
<LINK REL=StyleSheet HREF="./script/ks2-2.css" TYPE="text/css" MEDIA=screen>
<LINK REL="SHORTCUT ICON" href="./favicon.ico">
<style type="text/css">
.pwa {color: blue;}
a.pwa:hover {background: blanchedAlmond;}
.pt {display : table-cell;}
.wu {display : none;}
.pw {display : none;}
th {width: 6em;}
</style>
<title>Folding@Home Stats - Production History -
  <?php echo $donorName; ?></title>
<script type="text/javascript" src="./script/js.js"></script>
<script type="text/javascript" src="./overlib/mini/overlib_mini.js"><!-- overLIB (c) Erik Bosrup --></script>
</head>
<body style="text-align:center;"
  onload="
  document.getElementById('ttu').innerHTML = timeToUpdate(time_of_update);
  window.setInterval('document.getElementById(\'ttu\').innerHTML = timeToUpdate(time_of_update)', 1000);
  ">
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>
<?php
$menu = array(
  'anchors' => True,
  'anchors_all_donors' => True,
  'anchors_all_teams' => True,
  'team' => True,
  'team_radar' => True,
  'team_history' => True,
  'team_members' => True,
  'team_milestones' => True,
  'team_new_members' => True,
  'donor' => True,
  'donor_radar' => True,
  'donor_milestones' => True,
  'donor_history' => True
  );
include './menu.php';
include './adrotator.php';
?>
<p style="text-align:center;margin:0 auto 0.5em auto;font-size:22px;color:floralWhite;">
<?php echo htmlentities($donorName, ENT_QUOTES); ?>'s Summary
<?php
  echo " at ",
  "<span style=\"font-size:18px;\">",
  "<script type='text/javascript'>",
  "document.write(wDate('$date')+' '+timeZone());",
  "</script></span>";
?>
</p>
<!-- ####################################################### -->
<?php
$query = 'select time_nome as name from times_indice where n_time=' .$team. ';';
$result = pg_query($link, $query);
$line = pg_fetch_array($result, NULL, PGSQL_ASSOC);
$team_name = $line ['name'];

$query = <<<query
select
  d.usuario_nome as name,
  d.usuario_serial as number,
  rank_0, rank_24, rank_7, rank_30,
  rank_0_time as rank_0_team,
  rank_24_time as rank_24_team,
  rank_7_time as rank_7_team,
  rank_30_time as rank_30_team,
  pontos_0 as points_0,
  pontos_24 as points_24,
  pontos_7 as points_7
from usuarios_producao as t
inner join usuarios_indice as d on t.usuario=d.usuario_serial
where t.usuario = $donor
;
query;
$result = pg_query($link, $query);
?>
<table class=tabela cellspacing="0">
<tr>
<td style="" class=top>
<table cellspacing="0" class="cab top">
<tr>
<td style="text-align:center;font-size:150%;margin-right:0;padding:0.3em 0;line-height:150%;" class="cab">
  Team Ranking
</td>
</tr>
</table></td></tr>
<tr><td class=top>
<table cellspacing=0 class=corpo>
<thead>
  <tr>
  <th id=th1 rowspan=2
    onclick="load(1);"
    onmouseover="javaScript:mOverCab(this);
      return overlib(
      'Members who scored at least 1 point in the last 14 days<br />' +
      'Click to sort<br />' +
      'Color mean the weekly points:<br />' +
      '<span class=\'c1\'>Red: 11,200+</span><br/ >' +
      '<span class=\'c2\'>Dark Red: 8,400+</span><br />' +
      '<span class=\'c3\'>Orange: 5,600+</span><br />' +
      '<span class=\'c4\'>Lime: 2,800+</span><br />' +
      '<span class=\'c5\'>Blue: 1,400+</span><br />' +
      '<span class=\'c6\'>Green: 700+</span><br />' +
      '<span class=\'c7\'>Black: 1+</span>'
      );"
    onmouseout="javaScript:mOutCab(this);return nd();"
    style="width:20em;"
    >Member</th>
  <th colspan=4>Team Ranking</th>
  <th colspan=4>Project Ranking</th>
  <th colspan=3 style="border-right:0;">Points</th></tr>
  <tr>
  <th id=th2
    onclick="load(2);"
    onmouseover="javaScript:mOverCab(this);
      return overlib('Current member team ranking<br />Click to sort');"
    onmouseout="javaScript:mOutCab(this);return nd();"
    >Cur-<br />rent</th>
  <th id=th3 onclick="load(3);"
    onmouseover="javaScript:mOverCab(this);
      return overlib('Future member team ranking at the end of the next 24 hours<br />Click to sort');"
    onmouseout="javaScript:mOutCab(this);return nd();"
    >In 24 Hs</th>
  <th id=th4
    onclick="load(4);"
    onmouseover="javaScript:mOverCab(this);
      return overlib('Future member team ranking at the end of the next 7 days<br />Click to sort');"
    onmouseout="javaScript:mOutCab(this);return nd();"
    >In 7 days</th>
  <th id=th5
    onclick="load(5);"
    onmouseover="javaScript:mOverCab(this);
      return overlib('Future member team ranking at the end of the next 30 days<br />Click to sort');"
    onmouseout="javaScript:mOutCab(this);return nd();"
    >In 30 days</th>
  <th id=th6 onclick="load(6);"
    onmouseover="javaScript:mOverCab(this);
      return overlib('Current member project ranking<br />Click to sort');"
    onmouseout="javaScript:mOutCab(this);return nd();"
    >Cur-<br />rent</th>
  <th id=th7 onclick="load(7);"
    onmouseover="javaScript:mOverCab(this);
      return overlib('Future member project ranking at the end of the next 24 hours<br />Click to sort');"
    onmouseout="javaScript:mOutCab(this);return nd();"
    >In 24 Hs</th>
  <th id=th8 onclick="load(8);"
    onmouseover="javaScript:mOverCab(this);
      return overlib('Future member project ranking at the end of the next 7 days<br />Click to sort');"
    onmouseout="javaScript:mOutCab(this);return nd();"
    >In 7 days</th>
  <th id=th9 onclick="load(9);"
    onmouseover="javaScript:mOverCab(this);
      return overlib('Future member project ranking at the end of the next 30 days<br />Click to sort');"
    onmouseout="javaScript:mOutCab(this);return nd();"
    >In 30 days</th>
  <th id=th10 onclick="load(10);"
    onmouseover="javaScript:mOverCab(this);
      return overlib('Points scored in the last 24 hours<br />Click to sort');"
    onmouseout="javaScript:mOutCab(this);return nd();"
    >24 hrs</th>
  <th id=th11 onclick="load(11);"
    onmouseover="javaScript:mOverCab(this);
      return overlib('Points scored in the last 7 days<br />Click to sort');"
    onmouseout="javaScript:mOutCab(this);return nd();"
    >7 days</th>
  <th id=th12 onclick="load(12);"
    onmouseover="javaScript:mOverCab(this);
      return overlib('Total member points<br />Click to sort');"
    onmouseout="javaScript:mOutCab(this);return nd();"
    style="border-right:0;"
    >Total</th>
  </tr></thead>
<tbody id=tdados class=tcorpo>
<?php

$query = <<<query
select
  pontos_0 as points_0,
  pontos_0 - pontos_24 as points_24,
  pontos_0 - pontos_7 as points_7,
  rank_0,
  rank_7,
  rank_24,
  rank_30
from times_producao
where n_time = $team
query;
$line = pg_fetch_array(pg_query($link, $query), NULL, PGSQL_ASSOC);

$team_name = preg_replace('/([^\A])_([^\Z])/','$1 $2',
  htmlentities($team_name, ENT_QUOTES));
$team_name = preg_replace('/([^\s]{10}\B)/', "$1<wbr>&shy;", $team_name);

echo '<tr onmouseover="ron(this);" onmouseout="roff(this);">';
echo '<td class="txt" style="font-weight:bold;">', $team_name, '</td>';
echo '<td></td>';
echo '<td></td>';
echo '<td></td>';
echo '<td></td>';
echo '<td>', number_format($line['rank_0'], 0, '.', ','), '</td>';
echo '<td>', number_format($line['rank_24'], 0, '.', ','), '</td>';
echo '<td>', number_format($line['rank_7'], 0, '.', ','), '</td>';
echo '<td>', number_format($line['rank_30'], 0, '.', ','), '</td>';
echo '<td>', number_format($line['points_24'], 0, '.', ','), '</td>';
echo '<td>', number_format($line['points_7'], 0, '.', ','), '</td>';
echo '<td>', number_format($line['points_0'], 0, '.', ','), '</td>';
echo "</tr>\n";
echo '<tr class="ls">';
echo '<td></td>';
echo '<td></td>';
echo '<td></td>';
echo '<td></td>';
echo '<td></td>';
echo '<td></td>';
echo '<td></td>';
echo '<td></td>';
echo '<td></td>';
echo '<td></td>';
echo '<td></td>';
echo '<td></td>';
echo "</tr>\n";

$n_linha = 0;
while ($line = pg_fetch_array($result, NULL, PGSQL_ASSOC)) {
  if (++$n_linha % 2 == 0) $classe = "class=ls";
  else $classe = "";
  if ($line['points_7'] >= 1600 * 7) $cor7 = "c1";
  elseif ($line['points_7'] >= 1200 * 7) $cor7 = "c2";
  elseif ($line['points_7'] >= 800 * 7) $cor7 = "c3";
  elseif ($line['points_7'] >= 400 * 7) $cor7 = "c4";
  elseif ($line['points_7'] >= 200 * 7) $cor7 = "c5";
  elseif ($line['points_7'] >= 100 * 7) $cor7 = "c6";
  elseif ($line['points_7'] >= 1) $cor7 = "c7";
  else $cor7 = "c8";
  if ($line['points_24'] >= 1600) $cor24 = "c1";
  elseif ($line['points_24'] >= 1200) $cor24 = "c2";
  elseif ($line['points_24'] >= 800) $cor24 = "c3";
  elseif ($line['points_24'] >= 400) $cor24 = "c4";
  elseif ($line['points_24'] >= 200) $cor24 = "c5";
  elseif ($line['points_24'] >= 100) $cor24 = "c6";
  elseif ($line['points_24'] >= 1) $cor24 = "c7";
  else $cor24 = "c8";

  $line['name'] = preg_replace('/([^\A])_([^\Z])/','$1 $2',
      htmlentities($line['name'], ENT_QUOTES));
  $line['name'] = preg_replace('/([^\s]{10}\B)/', "$1<wbr>&shy;", $line['name']);
/*
  if (strlen($line['name']) > 15) {
    $a = explode("_", $line['name']);
    foreach($a as $key => $value) {
      if (strlen($value) > 15) {
        $a[$key] = chunk_split($value, 15, " ");
        $a[$key] = substr($a[$key], 0, strlen($a[$key]) -1);
        }
      }
    $line['name'] = implode(" ", $a);
    }*/
  echo "<tr $classe onmouseover='ron(this);' onmouseout='roff(this);'>";

  echo "<td class=\"txt $cor7\">";
  echo "<a class=$cor7 href=\"./up.php?u=".$line['number']."\">",
    $line['name'], "</a></td>";

  echo "<td>", number_format($line['rank_0_team'], 0, ".", ","), "</td>";
  echo "<td";
  if ($line['rank_24_team'] < $line['rank_0_team']) echo " class=g";
  elseif ($line['rank_24_team'] > $line['rank_0_team']) echo " class=r";
  echo ">", number_format($line['rank_24_team'], 0, ".", ","), "</td>";
  echo "<td";
  if ($line['rank_7_team'] < $line['rank_0_team']) echo " class=g";
  elseif ($line['rank_7_team'] > $line['rank_0_team']) echo " class=r";
  echo ">", number_format($line['rank_7_team'], 0, ".", ","), "</td>";
  echo "<td";
  if ($line['rank_30_team'] < $line['rank_0_team']) echo " class=g";
  elseif ($line['rank_30_team'] > $line['rank_0_team']) echo " class=r";
  echo ">", number_format($line['rank_30_team'], 0, ".", ","), "</td>";

  echo "<td>", number_format($line['rank_0'], 0, ".", ","), "</td>";
  echo "<td";
  if ($line['rank_24'] < $line['rank_0']) echo " class=g";
  elseif ($line['rank_24'] > $line['rank_0']) echo " class=r";
  echo ">", number_format($line['rank_24'], 0, ".", ","), "</td>";
  echo "<td";
  if ($line['rank_7'] < $line['rank_0']) echo " class=g";
  elseif ($line['rank_7'] > $line['rank_0']) echo " class=r";
  echo ">", number_format($line['rank_7'], 0, ".", ","), "</td>";
  echo "<td";
  if ($line['rank_30'] < $line['rank_0']) echo " class=g";
  elseif ($line['rank_30'] > $line['rank_0']) echo " class=r";
  echo ">", number_format($line['rank_30'], 0, ".", ","), "</td>";
  echo "<td class=", $cor24, ">", number_format($line['points_24'], 0, ".", ","), "</td>";
  echo "<td class=", $cor7, ">", number_format($line['points_7'], 0, ".", ","), "</td>";
  echo "<td>", number_format($line['points_0'], 0, ".", ","), "</td>";
  echo "</tr>\n";
  }
?>
</tbody>
</table>
</tr>
</table>
<!-- ####################################################### -->
<?php
$query = <<<query
select extract ('hour' from date) as hour
from user_update_production ($donor)
order by date_trunc ('day', date) desc, extract ('hour' from date)
limit 1;
query;
$result = pg_query($link, $query);
$line = pg_fetch_array($result, 0, PGSQL_ASSOC);
$hour = $line['hour'];

$query = <<<query
select
  coalesce(points, 0) as points,
  coalesce(wus, 0) as wus,
  to_char (date_trunc('day', de.data), 'YYYY-MM-DD') as day,
  de.hour as dow
from user_update_production($donor) as uup
right outer join (
  select
    di.data,
    extract ('hour' from di.data) as hour
  from user_update_production($donor) as uup
  inner join datas as di on yearweek(di.data) = yearweek(uup.date)
  group by di.data
) as de
on uup.date = de.data
where date_trunc('day', de.data) >= (
  select date_trunc('day', date)
  from user_update_production($donor)
  order by date
  limit 1
  )
order by date_trunc('day', de.data) desc, de.hour;
query;
$result = pg_query($link, $query);
?>
<table class="tabela" cellspacing="0" cellpadding="0" style="margin:1em auto 0;padding:0;">
<tr>
<td class="top">
<table cellspacing="0" cellpadding="0" class="cab top">
<tr>
<td style="font-size:150%;text-align:center;">
Update History UTC time:
<a id='pwa1uup' class="pwa" href="javascript:changeDisplay('pt','pwa1','uup'); void 0;"
  style="background:blanchedAlmond;">Points</a> -
<a id='pwa2uup' class="pwa" href="javascript:changeDisplay('wu','pwa2','uup'); void 0;">WUs</a> -
<a id='pwa3uup' class="pwa" href="javascript:changeDisplay('pw','pwa3','uup'); void 0;">Points/WUs</a>
</td></tr></table></td></tr>
<tr>
<td class="top">
<table cellspacing="0" cellpadding="0" class="corpo">
  <thead>
  <tr>
    <th style="border-left:0;width:6em;">&nbsp;</th>
    <?php
    for ($i = 0; $i < 8; $i++)
      echo '<th style="width: 5em;">', ($hour % 3) + $i * 3, ':00</th>'
    ?>
    <th style="width: 5.5em;">Total</th>
    </tr>
    </thead>
<tbody id='uup'>
<?php
$aDow = array(0=>0,1=>0,2=>0,3=>1,4=>1,5=>1,6=>2,7=>2,8=>2,9=>3,10=>3,11=>3,12=>4,13=>4,14=>4,15=>5,16=>5,17=>5,18=>8,19=>6,20=>6,21=>7,22=>7,23=>7);
$n_linha = 0;
$column = 0;
$i = 0;
$secondsADay = 60 * 60 * 24;
$dowTotal = array(0=>0, 1=>0, 2=>0, 3=>0, 4=>0, 5=>0, 6=>0, 7=>0);
$ocur = array(0=>0, 1=>0, 2=>0, 3=>0, 4=>0, 5=>0, 6=>0, 7=>0);
$weekAverage = 0;
$dowTotalWus = array(0=>0, 1=>0, 2=>0, 3=>0, 4=>0, 5=>0, 6=>0, 7=>0);
$weekAverageWus = 0;
$resultPointer = 0;
while ($line = pg_fetch_array($result, null, PGSQL_ASSOC)) {
  $dow = $aDow[$line['dow']];
  $day = $line['day'];
  $points = $line['points'];
  $wus = $line['wus'];
  if ($column % 8 == 0 || $dow < $column) {
    if ($n_linha >= 1) {
      for ($i=$column; $i<8; $i++) echo "<td></td>";
      echo "<td class=pt>", number_format($weekTotal, 0), "</td>";
      echo "<td class=wu>", number_format($weekTotalWus, 0), "</td>\n";
      echo "<td class=pw>",
        number_format($weekTotalWus == 0 ? 0 : $weekTotal/$weekTotalWus, 0),
        "</td>","</tr>\n";
      }
    $weekTotal = 0;
    $weekTotalWus = 0;
    $column = 0;
    $n_linha++;
    if ($n_linha % 2 == 0) $classe = " class='ls'";
    else $classe = "";
    echo "<tr", $classe, " onmouseover='ron(this);' onmouseout='roff(this);'><td>";
    if ($dow == 0) echo $day;
    else echo gmdate("Y-m-d", gmmktime(0, 0, 0, substr($day, 5, 2), substr($day, 8), substr($day, 0, 4)));
    echo "</td>";
    }
  $dowTotal[$dow] += $points;
  $ocur[$dow]++;
  $i++;
  $weekTotal += $points;
  $dowTotalWus[$dow] += $wus;
  $weekTotalWus += $wus;
  $column++;
  for ($i=$column; $i<=$dow; $i++) {
    echo "<td></td>";
    $column++;
    }
  echo "<td class=pt>", $points == 0 ? "" : number_format($points, 0), "</td>";
  echo "<td class=wu>", $wus == 0 ? "" : number_format($wus, 0), "</td>";
  echo "<td class=pw>", $wus == 0 ? "" : number_format($points/$wus, 0), "</td>";
  }
  for ($i = $column; $i < 8; $i++) {
    echo "<td></td>";
  }
echo "<td class=pt>", number_format($weekTotal, 0), "</td>";
echo "<td class=wu>", number_format($weekTotalWus, 0), "</td>";
echo "<td class=pw>",
  number_format($weekTotalWus == 0 ? 0 : $weekTotal/$weekTotalWus, 0),
  "</td></tr>\n";
$n_linha++;
if ($n_linha % 2 == 0) $classe = " class='ls'";
else $classe = "";
echo "<tr $classe onmouseover='ron(this);' onmouseout='roff(this);'><td>Average:</td>";
for ($i=0; $i<8; $i++) {
  $dowAverage = $ocur [$i] == 0 ? 0 : $dowTotal[$i] / $ocur[$i];
  $weekAverage += $dowAverage;
  echo "<td class=pt>", number_format($dowAverage, 0), "</td>";
  $dowAverageWus = $ocur [$i] == 0 ? 0 : $dowTotalWus[$i] / $ocur[$i];
  $weekAverageWus += $dowAverageWus;
  echo "<td class=wu>", number_format($dowAverageWus, 0), "</td>";
  echo "<td class=pw>",
    number_format($dowAverageWus == 0 ? 0 : $dowAverage/$dowAverageWus, 0),
    "</td>";
  }
echo "<td class=pt>", number_format($weekAverage, 0), "</td>";
echo "<td class=wu>", number_format($weekAverageWus, 0), "</td>";
echo "<td class=pw>",
  $weekAverageWus == 0 ? 0 : number_format($weekAverage/$weekAverageWus, 0),
  "</td>";
echo "</tr>\n"
?>
</tbody></table></td></tr></table>
<!-- - - - - - - - - - - - - - - - - - - - - - - - - - - - -->
<table class="tabela" cellspacing="0" cellpadding=""
  style="margin:1em auto 0;padding:0;">
<tr>
<td class="top">
<table cellspacing="0" cellpadding="0" class="cab top">
<tr>
<td style="font-size:150%;text-align:center;">
Daily History:
<a id='pwa1ud' class="pwa" href="javascript:changeDisplay('pt','pwa1','ud'); void 0;"
  style="background:blanchedAlmond;">Points</a> -
<a id='pwa2ud' class="pwa" href="javascript:changeDisplay('wu','pwa2','ud'); void 0;">WUs</a> -
<a id='pwa3ud' class="pwa" href="javascript:changeDisplay('pw','pwa3','ud'); void 0;">Points/WUs</a>
</td></tr></table></td></tr>
<tr>
<td class="top">
<table cellspacing="0" cellpadding="0" class="corpo">
  <thead>
  <tr>
    <th style="border-left:0;width:6.5em;">&nbsp;</th>
    <th>Mon</th><th>Tue</th><th>Wed</th>
    <th>Thu</th><th>Fri</th><th>Sat</th><th>Sun</th><th>Total</th>
    </tr>
    </thead>
<tbody id='ud'>
<?php
$n_linha = 0;
$column = 0;
$i = 0;
$secondsADay = 60 * 60 * 24;
$dowTotal = array(0=>0, 1=>0, 2=>0, 3=>0, 4=>0, 5=>0, 6=>0);
$ocur = array(0=>0, 1=>0, 2=>0, 3=>0, 4=>0, 5=>0, 6=>0);
$weekAverage = 0;
$dowTotalWus = array(0=>0, 1=>0, 2=>0, 3=>0, 4=>0, 5=>0, 6=>0);
$weekAverageWus = 0;
$query = <<<query
select points, wus, day, dow
from (
  select
    sum(coalesce(points, 0)) as points,
    sum(coalesce(wus, 0)) as wus,
    to_char (date_trunc ('day', de.data), 'YYYY-MM-DD') as day,
    isodow (de.data) as dow
  from user_production ($donor) as up
  right outer join (
    select di.data
    from user_production($donor) as up
    inner join datas as di on yearweek(di.data) = yearweek(up.date)
    group by di.data
  ) as de
  on up.date = de.data
  group by day, dow
) as s
order by yearweek (day::date) desc, isodow (day::date)
;
query;
$result = pg_query($link, $query);
while ($line = pg_fetch_array($result, NULL, PGSQL_ASSOC)) {
  $dow = $line['dow'];
  $day = $line['day'];
  $points = $line['points'];
  $wus = $line['wus'];
  if ($column % 7 == 0 || $dow < $column) {
    if ($n_linha >= 1) {
      for ($i=$column; $i<7; $i++) echo "<td></td>";
      echo "<td class=pt>", number_format($weekTotal, 0), "</td>";
      echo "<td class=wu>", number_format($weekTotalWus, 0), "</td>\n";
      echo "<td class=pw>", number_format($weekTotalWus == 0 ? 0 : $weekTotal/$weekTotalWus, 0), "</td>","</tr>\n";
      }
    $weekTotal = 0;
    $weekTotalWus = 0;
    $column = 0;
    $n_linha++;
    if ($n_linha % 2 == 0) $classe = " class='ls'";
    else $classe = "";
    echo "<tr", $classe, " onmouseover='ron(this);' onmouseout='roff(this);'><td>";
    if ($dow == 0) echo $day;
    else echo gmdate("Y-m-d", gmmktime(0, 0, 0, substr($day, 5, 2), substr($day, 8), substr($day, 0, 4)) - $secondsADay * ($dow));
    echo "</td>";
    }
  $dowTotal[$dow] += $points;
  $ocur[$dow]++;
  $i++;
  $weekTotal += $points;
  $dowTotalWus[$dow] += $wus;
  $weekTotalWus += $wus;
  $column++;
  for ($i=$column; $i<=$dow; $i++) {
    echo "<td></td>";
    $column++;
    }
  echo "<td class=pt>", $points == 0 ? "" : number_format($points, 0), "</td>";
  echo "<td class=wu>", $wus == 0 ? "" : number_format($wus, 0), "</td>";
  echo "<td class=pw>", $wus == 0 ? "" : number_format($points/$wus, 0), "</td>";
  }
  for ($i = $column; $i < 7; $i++) {
    echo "<td></td>";
  }
echo "<td class=pt>", number_format($weekTotal, 0), "</td>";
echo "<td class=wu>", number_format($weekTotalWus, 0), "</td>";
echo "<td class=pw>", number_format($weekTotalWus == 0 ? 0 : $weekTotal/$weekTotalWus, 0), "</td></tr>\n";
$n_linha++;
if ($n_linha % 2 == 0) $classe = " class='ls'";
else $classe = "";
echo "<tr $classe onmouseover='ron(this);' onmouseout='roff(this);'><td>Average:</td>";
for ($i=0; $i<7; $i++) {
  $dowAverage = $ocur [$i] == 0 ? 0 : $dowTotal[$i] / $ocur[$i];
  $weekAverage += $dowAverage;
  echo "<td class=pt>", number_format($dowAverage, 0), "</td>";
  $dowAverageWus = $ocur [$i] == 0 ? 0 : $dowTotalWus[$i] / $ocur[$i];
  $weekAverageWus += $dowAverageWus;
  echo "<td class=wu>", number_format($dowAverageWus, 0), "</td>";
  echo "<td class=pw>",
    number_format($dowAverageWus == 0 ? 0 : $dowAverage/$dowAverageWus, 0),
    "</td>";
  }
echo "<td class=pt>", number_format($weekAverage, 0), "</td>";
echo "<td class=wu>", number_format($weekAverageWus, 0), "</td>";
echo "<td class=pw>",
  number_format($weekAverageWus == 0 ? 0 : $weekAverage/$weekAverageWus, 0),
  "</td>";
echo "</tr>\n";
?>
</tbody>
</table></td></tr>
</table>
<!-- ###################################################### -->
<table class="tabela" cellspacing="0" style="margin-top: 1em;">
<tr>
<td class="top">
<table cellspacing="0" class="cab top">
<tr>
<td style="font-size:22px;text-align:center;"
  >Radar Table
</td>
</tr>
</table>
</td>
</tr>
<tr>
<td class="top">
<table cellspacing="0" class="corpo">
<thead>
<tr>
<th colspan="3" style="border-left:0;"
  >Overtake</th>
<th rowspan="3"
  onmouseover="return overlib(
    'Team member overtaking or being overtaked<br />' +
    'Color mean the weekly points:<br />' +
    '<span class=\'c1\'>Red: 11,200+</span><br/ >' +
    '<span class=\'c2\'>Dark Red: 8,400+</span><br />' +
    '<span class=\'c3\'>Orange: 5,600+</span><br />' +
    '<span class=\'c4\'>Lime: 2,800+</span><br />' +
    '<span class=\'c5\'>Blue: 1,400+</span><br />' +
    '<span class=\'c6\'>Green: 700+</span><br />' +
    '<span class=\'c7\'>Black: 1+</span><br />' +
    '<span class=\'c8\'>Gray: Inactive</span>'
    );"
  onmouseout="return nd();"
  >Challenging Member</th>
<th colspan="8"
  >Challenging Member Stats</th>
</tr>
<tr>
<th rowspan="2" style="border-left:0;" onmouseover="
  return overlib('Future member team ranking<br />Green - conquered position<br />Red - lost position');"
  onmouseout="return nd();"
  >Team<br />ran-<br />king</th>
<th rowspan="2"
  onmouseover="return overlib('Days until the ranking change');"
  onmouseout="return nd();"
  >Days</th>
<th rowspan="2"
  onmouseover="return overlib('Date of the ranking change');"
  onmouseout="return nd();"
  style="width:22em;"
  >Date</th>
<th rowspan="1" colspan="2"
  >Ranking</th>
<th colspan="3"
  > Total Points</th>
<th colspan="3"
  > Points Difference</th>
</tr>
<tr>
<th onmouseover="
  return overlib('Current team ranking of the challenging member');"
  onmouseout="return nd();"
  >Team</th>
<th onmouseover="
  return overlib('Current project ranking of the challenging member');"
  onmouseout="return nd();"
  >Project</th>
<th onmouseover="
  return overlib('Points scored by the challenging member in the last 24 hours');"
  onmouseout="return nd();"
  >24 hrs</th>
<th onmouseover="
  return overlib('Points scored by the challenging member in the last seven days');"
  onmouseout="return nd();"
  >7 days</th>
<th onmouseover="
  return overlib('Total points of the challenging member');"
  onmouseout="return nd();"
  >Total</th>
<th onmouseover="
  return overlib('Points difference the last 24 hours (PPD)');"
  onmouseout="return nd();"
  >24 hrs</th>
<th onmouseover="
  return overlib('Points difference in the last seven days (PPW)');"
  onmouseout="return nd();"
  >7 days</th>
<th onmouseover="
  return overlib('Total points difference');"
  onmouseout="return nd();"
  >Total</th>
</tr>
</thead>
<tbody>
<?php
$query = <<<query
select
  usuario_nome as donor_name,
  0 as days,
  '$date' as date,
  usuario_serial as challengerNumber,
  0 as link,
  rank_0_time as challengerTeamRank,
  rank_0 as challengerProjectRank,
  pontos_0 as points_0,
  pontos_7 as points_7,
  pontos_24 as points_24,
  pontos_14 as points_14
from usuarios_producao as d inner join usuarios_indice as di on d.usuario = di.usuario_serial
where usuario = $donor;
query;
$result = pg_query($link, $query);
$line = pg_fetch_array($result, NULL, PGSQL_ASSOC);

$query = <<<query
select
  usuario_nome as donor_name,
  (({$line['points_0']} - pontos_0) /
    (pontos_7 - {$line['points_7']})) *7 as days,
  timestamp '$date' + (to_char(
    ({$line['points_0']} - pontos_0) / (pontos_7 - {$line['points_7']}) +
    0.00000000001, '99999999999999999.99999999999999999999') ||
    ' week')::interval as date,
  usuario_serial as challengerNumber,
  1 as link,
  rank_0_time as challengerTeamRank,
  rank_0 as challengerProjectRank,
  pontos_0 as points_0,
  pontos_7 as points_7,
  pontos_24 as points_24,
  pontos_14 as points_14
from usuarios_producao as d
inner join usuarios_indice as di on d.usuario = di.usuario_serial
where d.n_time = $team
  and
  (
    ({$line['points_7']} < pontos_7
    and
    {$line['challengerteamrank']} < rank_0_time
  )
  or
  (
    {$line['points_7']} > pontos_7
    and
    {$line['challengerteamrank']} > rank_0_time)
  )
  order by
    ({$line['points_0']} - pontos_0) / (pontos_7 - {$line['points_7']}),
    rank_0 desc
limit 3000;
query;
$result = pg_query($link, $query);

$points_0 = $line['points_0'];
$points_7 = $line['points_7'];
$points_24 = $line['points_24'];
$n_linha = 0;
$rank = $line['challengerteamrank'];
$rankDonor0 = $rank;
$points7Donor0 = $line['points_7'];
do {
  if ($rankDonor0 == $line['challengerteamrank']) $rg = 'p';
  elseif ($points7Donor0 > $line['points_7'] and $rankDonor0 > $line['challengerteamrank']) {
    $rank -= 1;
    $rg = 'g';
    }
  else {
    $rank += 1;
    $rg = 'r';
    }
  if (
     $line['points_14'] <= 0
     &&
     $line['challengerteamrank'] > 100
     ) continue;
  $n_linha++;
  if ($line['points_7'] >= 1600 * 7) $cor7 = "c1";
  elseif ($line['points_7'] >= 1200 * 7) $cor7 = "c2";
  elseif ($line['points_7'] >= 800 * 7) $cor7 = "c3";
  elseif ($line['points_7'] >= 400 * 7) $cor7 = "c4";
  elseif ($line['points_7'] >= 200 * 7) $cor7 = "c5";
  elseif ($line['points_7'] >= 100 * 7) $cor7 = "c6";
  elseif ($line['points_7'] >= 1) $cor7 = "c7";
  else $cor7 = "c8";

  if ($line['points_24'] >= 1600) $cor24 = "c1";
  elseif ($line['points_24'] >= 1200) $cor24 = "c2";
  elseif ($line['points_24'] >= 800) $cor24 = "c3";
  elseif ($line['points_24'] >= 400) $cor24 = "c4";
  elseif ($line['points_24'] >= 200) $cor24 = "c5";
  elseif ($line['points_24'] >= 100) $cor24 = "c6";
  elseif ($line['points_24'] >= 1) $cor24 = "c7";
  else $cor24 = "c8";

  $donor_name = $line['donor_name'];
  if (strlen($donor_name) > 15) {
    $a = explode("_", $donor_name);
    foreach($a as $key => $value) {
      if (strlen($value) > 15) {
        $a[$key] = chunk_split($value, 15, " ");
        $a[$key] = substr($a[$key], 0, strlen($a[$key]) -1);
        }
      }
    $donor_name = implode(" ", $a);
    }

  if ($n_linha % 2 == 0) $classe = " class='ls'";
  else $classe = "";
  echo "<tr ", $classe,
    " onmouseover='ron(this);' onmouseout='roff(this);'>";
  echo "<td class=", $rg, ">",
    number_format($rank, 0, ".", ","), "</td>";
  echo "<td class=", $rg, ">",
    number_format($line['days'], 0, ".", ","), "</td>";
  echo "<td style='width:11em;' class=txt>",
    "<script type=text/javascript>",
    "document.write(wDate2('".$line["date"]."'));",
    "</script>",
    "</td>";
  echo "<td style='' class='txt $cor7'>";
  if ($line ['link'])
    echo "<a href=./u.php?u=",
      $line['challengernumber'],
      " class=", $cor7,
      ">",
      str_replace(" ", "&shy; ", htmlentities($donor_name, ENT_QUOTES)),
      "</a>";
  else echo str_replace(" ", "&shy; ", htmlentities($donor_name, ENT_QUOTES));
  echo "</td>";
  echo "<td>",
    number_format($line['challengerteamrank'], 0, ".", ","), "</td>";
  echo "<td>",
    number_format($line['challengerprojectrank'], 0, ".", ","), "</td>";
  echo "<td class=", $cor24, ">",
    number_format($line['points_24'], 0, ".", ","), "</td>";
  echo "<td class=", $cor7, ">",
    number_format($line['points_7'], 0, ".", ","), "</td>";
  echo "<td>",
    number_format($line['points_0'], 0, ".", ","), "</td>";
  echo "<td>",
    number_format($line['points_24'] - $points_24, 0, ".", ","),
    "</td>";
  echo "<td>",
    number_format($line['points_7'] - $points_7, 0, ".", ","),
    "</td>";
  echo "<td>",
    number_format($line['points_0'] - $points_0, 0, ".", ","),
    "</td>";
  echo "</tr>\n";
  } while (
      ($line = pg_fetch_array($result, NULL, PGSQL_ASSOC))
      &&
      $n_linha <= 5
      );
?>
</tbody>
</table>
</td>
</tr>
</table>
<!-- ###################################################### -->
<table class="tabela"
  style="width:240px;margin-top:1em;"
  cellspacing="0">
<tr>
<td class="top">
<table cellspacing="0" class="cab top">
  <tr>
  <td style="font-size:150%;text-align:center;line-height:200%;">
    Milestones
  </td>
  </tr>
</table>
</td>
</tr>
<tr>
<td class="top">
<table cellspacing="0" class="corpo">
<thead>
<tr>
<th style="border-left:0;width:12em;" onmouseover="
  return overlib('Date in which the milestone was achieved');"
  onmouseout="return nd();"
  >Date</th>
<th onmouseover="return overlib(
    'Milestones:<br />' +
      '1,000<br/ >' +
      '2,500<br />' +
      '5,000<br />' +
      '10,000<br />' +
      '25,000<br />' +
      '50,000<br />' +
      '100,000<br />' +
      '250,000<br />' +
      '500,000<br />' +
      '1,000,000<br />' +
      '2,500,000<br />' +
      '5,000,000'
    );"
  onmouseout="return nd();"
>Mile-<br />stone</th>
</tr>
</thead>
<tbody id="tp">
<?php
$query = <<<query
select
  (((dmr.milestone_points - up.pontos_0) / up.pontos_7)::text || ' weeks')::interval +
    (select last_date from last_date) as date,
  dmr.milestone_points
from usuarios_producao as up
inner join donor_milestones_ref as dmr on dmr.milestone_points > up.pontos_0
where
  up.usuario = $donor and up.pontos_7 > 0
  and
  dmr.milestone_points = (
    select milestone_points
    from donor_milestones_ref
    where milestone_points > up.pontos_0
    order by milestone_points
    limit 1 )
;
query;
$result = pg_query($link, $query);
$line = pg_fetch_array($result,NULL, PGSQL_ASSOC);
$n_linha = 0;
$n_linha++;

if ($n_linha % 2 == 0) $classe = 'class="ls"';
else $classe = 'class=""';
echo "<tr ", $classe, " onmouseover='ron(this);' onmouseout='roff(this);'>";
echo '<td style="text-align:center;">';
if (is_null($line['date'])) echo 'Unknown';
else {
  echo "<script type=text/javascript>",
    "document.write(wDate2('".$line["date"]."'));",
    "</script>";
}
echo '</td>';
echo "<td>", number_format($line['milestone_points'], 0, ".", ","), "</td>";
echo "</tr>\n";
$query = <<<query
select
  d.data as date,
  dmr.milestone_points
from donor_milestones as dm
inner join datas as d on d.data_serial = dm.serial_date and dm.donor = $donor
right outer join donor_milestones_ref as dmr on dm.milestone = dmr.milestone_ref
where dmr.milestone_points < (
  select pontos_0
  from usuarios_producao
  where usuario = $donor
  )
order by dmr.milestone_points desc
;
query;
$result = pg_query($link, $query);
while ($line = pg_fetch_array($result,NULL, PGSQL_ASSOC)) {
  $n_linha++;

  if ($n_linha % 2 == 0) $classe = 'class="ls"';
  else $classe = 'class=""';
  echo "<tr ", $classe, " onmouseover='ron(this);' onmouseout='roff(this);'>";
  echo '<td style="text-align:center;">';
  if (is_null($line['date'])) echo '';
  else {
    echo "<script type=text/javascript>",
      "document.write(wDate2('".$line["date"]."'));",
      "</script>";
  }
  echo '</td>';
  echo "<td>", number_format($line['milestone_points'], 0, ".", ","), "</td>";
  echo "</tr>\n";
}
?>
</tbody>
</table>
</td>
</tr>
</table>

<?php include "./footer.html"; ?>
</body></html>
