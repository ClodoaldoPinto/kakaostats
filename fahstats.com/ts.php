<?php

if (!isset($_GET['t'])) exit;
$team = $_GET['t'];
if (preg_match('/\D/', $team) == 1 || $team == '') exit;
session_start();
$_SESSION['v'] = '1';
require './pgsqlserver.php';
$link = pg_connect($conn_string);
$query = <<<html
select
   to_char (last_date, 'YYYY-MM-DD HH24:MI') as "date",
   extract (hour from last_date) as hour
from last_date
;
html;
$result = pg_query($link, $query);
$line = pg_fetch_array($result, NULL, PGSQL_ASSOC);
$date = $line["date"];
$lastDayUpdate = $line['hour'] >= 21 ? 1 : 0;
$query = "
select ti.time_nome as name
from times_indice as ti
where ti.n_time = $team;
";
$result = pg_query($link, $query);
$line = pg_fetch_array($result, NULL, PGSQL_ASSOC);
$teamName = $line["name"];
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
function changeDisplay(pwa, pwId) {
  document.getElementById("pwa1").style.background = "lightSteelBlue";
  document.getElementById("pwa2").style.background = "lightSteelBlue";
  document.getElementById(pwId).style.background = "#ffd800";
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
<LINK REL=StyleSheet HREF="./script/ks2-2.css" TYPE="text/css" MEDIA=screen>
<LINK REL="SHORTCUT ICON" href="./favicon.ico">
<style type="text/css">
.pwa {color: blue;}
a.pwa:hover {background: #ffd800;}
.pt {display : none;}
.wu {display : table-cell;}
.pw {display : none;}
th {width: 7.5em;}
</style>
<title>Kakao Stats - Team Active and New Members Daily History</title>
<script type="text/javascript" src="./script/js.js"></script>
<script type="text/javascript" src="./overlib/mini/overlib_mini.js"><!-- overLIB (c) Erik Bosrup --></script>
</head>
<body style="text-align:center;"
  onload="
  document.getElementById('ttu').innerHTML = timeToUpdate(time_of_update);
  window.setInterval('document.getElementById(\'ttu\').innerHTML = timeToUpdate(time_of_update)', 1000);
  ">
<!-- google_ad_section_start(weight=ignore) -->
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>
<?php
$page_name = 'team_new_members';
include './menu.php';
?>
<p style="text-align:center;margin:1em auto 0.5em auto;font-size:22px;color:floralWhite;">
<?php
  echo htmlentities($teamName,ENT_QUOTES), <<<html
  at
  <span style="font-size:18px;">
  <script type="text/javascript">
  document.write(wDate('$date')+' '+timeZone());
  </script></span>
html;
?></p>
<div style="margin: 0 auto 0.5em;padding:0;">
<img src="./team_size_chart.php?id=<?php echo $team ?>&amp;l=<?php echo $lastDayUpdate ?>"
  width=740 height=360
  alt="Team New Members History Chart"
  style="border:1px solid black;padding:0;margin:0;">
</div>

<?php
include './script/entrelacado_ad.html';
?>

<table class="tabela" cellspacing="0" cellpadding="0"
  style="margin:0 auto;padding:0;">
<tr>
<td class="top">
<table border="0" cellspacing="0" cellpadding="0" class="cab top">
<tr>
<td style="font-size:150%;text-align:center;">
<a id="pwa2" class="pwa"
  href="javascript:changeDisplay('wu', 'pwa2');void 0;"
  style="background:#ffd800;">
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
    <th style="width:5em;">Mon</th><th style="width:5em;">Tue</th>
    <th style="width:5em;">Wed</th>
    <th style="width:5em;">Thu</th><th style="width:5em;">Fri</th>
    <th style="width:5em;">Sat</th><th style="width:5em;">Sun</th>
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
$weekTotal = 0;
$weekTotalActive = 0;
$ocurWeek = 0;
$dowTotalActive = array(0=>0, 1=>0, 2=>0, 3=>0, 4=>0, 5=>0, 6=>0);
$weekAverageActive = 0;
$query = <<<query
select
    active_members as am,
    new_members as nm,
    am.dia as "day",
    isodow(am.dia) as dow
from (
    select
        active_members,
        data::date as dia
    from team_active_members_history tam
    inner join datas d on d.data_serial = tam.serial_date
    where team_number = $team
) am left outer join (
    select
        count(*) as new_members,
        data::date as dia
    from donor_first_wu dfw
    inner join usuarios_indice ui on ui.usuario_serial = dfw.donor
    where ui.n_time = $team
    group by dia
) nm on am.dia = nm.dia
where
    am.dia > (select max(data) - (15::text || ' week')::interval from datas)::date
order by yearweek(am.dia) desc, isodow(am.dia)
;
query;
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
      " onmouseover='ron(this);' onmouseout='roff(this);'>",
      '<td style="text-align:center;">';
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
  echo '<td class="pt">',
    $points == 0 ? '' : number_format($points, 0), "</td>";
  echo '<td class="wu">', number_format($active, 0), "</td>";
  }
  for ($i = $column; $i < 7; $i++) {
    echo "<td></td>";
  }
echo '<td class="pt">',
  $weekTotal == 0 ? '' : number_format($weekTotal, 0), "</td>";
echo '<td class="wu">',
  $ocurWeek == 0 ? 0 : number_format($weekTotalActive / $ocurWeek, 0),
  "</td>";
echo "</tr>\n";
$n_linha++;
if ($n_linha % 2 == 0) $classe = ' class="ls"';
else $classe = 'class="ln"';
echo "<tr $classe><td>Average:</td>";
for ($i=0; $i<7; $i++) {
  $dowAverage = $ocur [$i] == 0 ? 0 : $dowTotal[$i] / $ocur[$i];
  $weekAverage += $dowAverage;
  echo '<td class="pt">',
    $dowAverage == 0 ? '' : number_format($dowAverage, 0),
    "</td>";
  $dowAverageActive = $ocur [$i] == 0 ? 0 : $dowTotalActive[$i] / $ocur[$i];
  $weekAverageActive += $dowAverageActive;
  echo '<td class="wu">', number_format($dowAverageActive, 0), "</td>";
  }
echo '<td class="pt">',
  $weekAverage == 0 ? '' : number_format($weekAverage, 0),
  "</td>";
echo '<td class="wu">', number_format($weekAverageActive /7, 0), "</td>";
echo "</tr>\n"
?>
</tbody></table></td></tr></table>
<div style="margin: 1em auto 1em;padding:0;">
<a href="http://forum.fahstats.com/index.php/topic,10.0.html"
 title="Link this image in your forum! Click for instructions">
<img src="./nmi/<?php echo $team,'-14.png' ?>"
  width=320 alt="Team New Members"
  style="border:1px solid black;padding:0;margin:0px;">
</a>
</div>
<!-- google_ad_section_end -->
<?php include "./footer.html"; ?>
</body></html>
