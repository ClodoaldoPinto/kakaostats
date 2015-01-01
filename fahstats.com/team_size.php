<?php
if (!isset($_GET['t'])) exit;
$team = $_GET['t'];
if (preg_match('/\D/', $team) == 1 || $team == '') exit;
$host = $_SERVER['HTTP_HOST'];
$uri  = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
header('HTTP/1.1 301 OK');
header("Location: http://$host$uri/ts.php?t=$team");
exit;
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
$query = "select ti.time_nome as name ".
  "from times_indice as ti ".
  "where ti.n_time=".$team.";";
$result = pg_query($link, $query);
$line = pg_fetch_array($result, NULL, PGSQL_ASSOC);
$teamName = $line["name"];
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
<?php include "./meta.html"; ?>
<script type="text/javascript">
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
function changeDisplay(pwa, pwId) {
  document.getElementById("pwa1").style.background = "lightSteelBlue";
  document.getElementById("pwa2").style.background = "lightSteelBlue";
  document.getElementById(pwId).style.background = "blanchedAlmond";
  var show = "table-cell";
  if (is_ie) show = "block";
  var noshow = "none";
  var aId = ['tp', 'th'];
  for (var k=0; k < aId.length; k++) {
    var tblChilds = document.getElementById(aId[k]).childNodes;
    for (var i=0; i<tblChilds.length; i++) {
      var trChilds = tblChilds[i].childNodes;
      for (j=0; j<trChilds.length; j++)
        if (trChilds[j].nodeType == 1) {
          if (trChilds[j].className == "pt")
            trChilds[j].style.display = pwa == "pt" ? show:noshow;
          else if (trChilds[j].className == "wu")
            trChilds[j].style.display = pwa == "wu" ? show:noshow;
          }
      }
    }
  }
</script>
<LINK REL=StyleSheet HREF="/script/ks2-2.css" TYPE="text/css" MEDIA=screen>
<LINK REL="SHORTCUT ICON" href="/favicon.ico">
<style type="text/css">
.pwa {color: blue;}
a.pwa:hover {background: blanchedAlmond;}
.pt {display : none;}
.wu {display : table-cell;}
.pw {display : none;}
th {width: 6em;}
</style>
<title>Folding@Home Stats</title>
<script type="text/javascript" src="/script/js.js"></script>
<script type="text/javascript" src="/overlib/mini/overlib_mini.js"><!-- overLIB (c) Erik Bosrup --></script>
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
<img src="/team_size_chart.php?t=<?php echo $team ?>&amp;l=<?php echo $lastDayUpdate ?>"
  width=740 height=360
  alt="Team New Members History Chart"
  style="border:1px solid black;padding:0;margin:0;">
</div>
<table class="tabela" cellspacing="0" cellpadding="0"
  style="margin:0 auto;padding:0;">
<tr>
<td class="top">
<table border="0" cellspacing="0" cellpadding="0" class="cab top">
<tr>
<td style="font-size:150%;text-align:center;">
<a id="pwa2" class="pwa"
  href="javascript:changeDisplay('wu', 'pwa2');void 0;"
  style="background:blanchedAlmond;">
  Active Members</a> -
<a id="pwa1" class="pwa"
  href="javascript:changeDisplay('pt', 'pwa1');void 0;">
  New Members</a>
</td></tr></table></td></tr>
<tr>
<td class="top">
<table cellspacing="0" cellpadding="0" border="0" class="corpo">
  <thead id="th">
  <tr>
    <th style="border-left:0;">&nbsp;</th>
    <th>Mon</th><th>Tue</th><th>Wed</th>
    <th>Thu</th><th>Fri</th><th>Sat</th>
    <th>Sun</th>
    <th class="pt">Total</th><th class="wu">Average</th>
  </tr>
  </thead>
<tbody id="tp">
<?php
$n_linha = 0;
$column = 0;
$i = 0;
$secondsADay = 60 * 60 * 24;
$dowTotal = array(0=>0, 1=>0, 2=>0, 3=>0, 4=>0, 5=>0, 6=>0);
$ocur = array(0=>0, 1=>0, 2=>0, 3=>0, 4=>0, 5=>0, 6=>0);
$weekAverage = 0;
$ocurWeek = 0;
$dowTotalActive = array(0=>0, 1=>0, 2=>0, 3=>0, 4=>0, 5=>0, 6=>0);
$weekAverageActive = 0;
$query = "
  select new_members as nm, active_members as am, day, dow
  from select_team_size_table($team, 6)
  order by yearweek(day) desc, isodow(day)
  ;";
$result = pg_query($link, $query);
while ($line = pg_fetch_array($result, NULL, PGSQL_ASSOC)) {
  $dow = $line['dow'];
  $day = $line['day'];
  $points = $line['nm'];
  $active = $line['am'];
  if ($column % 7 == 0 || $dow < $column) {
    if ($n_linha >= 1) {
      for ($i=$column; $i<7; $i++) echo "<td></td>";
      echo "<td class=pt>",
        $weekTotal == 0 ? '' : number_format($weekTotal, 0), "</td>";
      echo "<td class=wu>",
        number_format($weekTotalActive / $ocurWeek, 0), "</td>";
      echo "</tr>\n";
      }
    $ocurWeek = 0;
    $weekTotal = 0;
    $weekTotalActive = 0;
    $column = 0;
    $n_linha++;
    if ($n_linha % 2 == 0) $classe = " class='ls'";
    else $classe = "";
    echo "<tr", $classe,
      " onmouseover='ron(this);' onmouseout='roff(this);'><td>";
    if ($dow == 0) echo $day;
    else echo gmdate("Y-m-d",
      gmmktime(0, 0, 0, substr($day, 5, 2),
        substr($day, 8), substr($day, 0, 4)) -
        $secondsADay * ($dow));
    echo "</td>";
    }
  $dowTotal[$dow] += $points;
  $ocur[$dow]++;
  $ocurWeek++;
  $i++;
  $weekTotal += $points;
  $dowTotalActive[$dow] += $active;
  $weekTotalActive += $active;
  $column++;
  for ($i=$column; $i<=$dow; $i++) {
    echo "<td></td>";
    $column++;
    }
  echo "<td class=pt>",
    $points == 0 ? '' : number_format($points, 0), "</td>";
  echo "<td class=wu>", number_format($active, 0), "</td>";
  }
echo "<td class=pt>",
  $weekTotal == 0 ? '' : number_format($weekTotal, 0), "</td>";
echo "<td class=wu>",
  number_format($weekTotalActive / $ocurWeek, 0), "</td>";
echo "</tr>\n";
$n_linha++;
if ($n_linha % 2 == 0) $classe = " class='ls'";
else $classe = "";
echo "<tr $classe onmouseover='ron(this);' onmouseout='roff(this);'><td>Average:</td>";
for ($i=0; $i<7; $i++) {
  $dowAverage = $ocur [$i] == 0 ? 0 : $dowTotal[$i] / $ocur[$i];
  $weekAverage += $dowAverage;
  echo "<td class=pt>",
    $dowAverage == 0 ? '' : number_format($dowAverage, 0),
    "</td>";
  $dowAverageActive = $ocur [$i] == 0 ? 0 : $dowTotalActive[$i] / $ocur[$i];
  $weekAverageActive += $dowAverageActive;
  echo "<td class=wu>", number_format($dowAverageActive, 0), "</td>";
  }
echo "<td class=pt>",
  $weekAverage == 0 ? '' : number_format($weekAverage, 0),
  "</td>";
echo "<td class=wu>", number_format($weekAverageActive /7, 0), "</td>";
echo "</tr>\n"
?>
</tbody></table></td></tr></table>
<div style="margin: 1em auto 1em;padding:0;">
<a href="http://forum.fahstats.com/viewtopic.php?p=14"
 title="Link this image in your forum! Click to view instructions">
<img src="/nmi/<?php echo $team,'-14.png' ?>"
  width=320 alt="Team New Members"
  style="border:1px solid black;padding:0;margin:0px;">
</a>
</div>
<?php include "./footer.html"; ?>
</body></html>
