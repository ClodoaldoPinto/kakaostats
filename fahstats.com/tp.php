<?php

if (!isset($_GET['t'])) exit;
$team = $_GET['t'];
if (preg_match('/\D/', $team) == 1 || $team == '') exit;
session_start();
$_SESSION['v'] = '1';
require './pgsqlserver.php';
$link = pg_connect($conn_string);
$query = "
select
    to_char(last_date, 'YYYY-MM-DD HH24:MI') as date,
    extract (hour from last_date) as hour
from last_date
;";
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
  document.getElementById('pwa1' + tb).style.background = 'lightSteelBlue';
  document.getElementById('pwa2' + tb).style.background = 'lightSteelBlue';
  document.getElementById('pwa3' + tb).style.background = 'lightSteelBlue';
  document.getElementById(pwId + tb).style.background = '#ffd800';
  var show = 'table-cell';
  if (is_ie) show = 'block';
  var noshow = 'none';
  var total_col_header = tb == 'uup' ? 'total_col_header_update' : 'total_col_header_daily';
  total_col_header = document.getElementById(total_col_header);
  total_col_header.innerHTML = pwa == 'pw' ? 'Average' : 'Total'
  var tblChilds = document.getElementById (tb).childNodes;
  for (var i=0; i<tblChilds.length; i++) {
    var trChilds = tblChilds[i].childNodes;
    for (j=0; j<trChilds.length; j++)
      if (trChilds[j].nodeType == 1) {
        if (trChilds[j].className == 'pt')
          trChilds[j].style.display = pwa == 'pt' ? show:noshow;
        else if (trChilds[j].className == 'wu')
          trChilds[j].style.display = pwa == 'wu' ? show:noshow;
        else if (trChilds[j].className == 'pw')
          trChilds[j].style.display = pwa == 'pw' ? show:noshow;
        }
    }
  }
</script>
<LINK REL=StyleSheet HREF="/script/ks2-2.css" TYPE="text/css" MEDIA=screen>
<LINK REL="SHORTCUT ICON" href="/favicon.ico">
<style type="text/css">
.pwa {color: blue;}
a.pwa:hover {background: #ffd800;}
.pt {display : table-cell;}
.wu {display : none;}
.pw {display : none;}
thead.dh th {width: 7.5em;}
.ls { background: gainsboro; }
.ls:hover { background-color:#ffd800; }
.ln { background: floralWhite; }
.ln:hover { background-color:#ffd800; }
</style>
<title>Kakao Stats - Production History -
  <?php echo $teamName; ?></title>
<script type="text/javascript" src="/script/js.js"></script>
<script type="text/javascript" src="/overlib/mini/overlib_mini.js"><!-- overLIB (c) Erik Bosrup --></script>
</head>
<body style="text-align:center;"
  onload="
  document.getElementById('ttu').innerHTML = timeToUpdate(time_of_update);
  window.setInterval('document.getElementById(\'ttu\').innerHTML = timeToUpdate(time_of_update)', 1000);
  ">
<!-- google_ad_section_start(weight=ignore) -->
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>
<?php
$page_name = 'team_history';
include './menu.php';
?>
<p style="text-align:center;margin:1em auto 1em auto;font-size:22px;color:floralWhite;">
<?php
  echo htmlentities($teamName,ENT_QUOTES), ' at ',
  '<span style="font-size:18px;">',
  '<script type="text/javascript">',
  "document.write(wDate('$date')+' '+timeZone());",
  '</script></span>';
?></p>
<div style="margin: 0 auto 1em;padding:0;">
<img src="./tpchart.php?id=<?php echo $team ?>&amp;l=<?php echo $lastDayUpdate ?>"
  width=740 height=370 alt="Team Production History Chart"
   style="border:1px solid black;padding:0;margin:0;">
</div>
<?php
include './script/entrelacado_ad.html';
$query = <<<query
select extract('hour' from min(data)) as hour
from datas
where data >= date_trunc('day', (select max(data) from datas))
;
query;
$result = pg_query($link, $query);
$line = pg_fetch_array($result, 0, PGSQL_ASSOC);
$hour = $line['hour'];

function eval_array($a)
{
   return eval('return ' . $a . ';');
}
$server_name = $_SERVER['SERVER_NAME'];
$url = "http://$server_name/python/mp_history.py?d=$team&t=team_update%3bteam_daily";
$big = eval_array(file_get_contents($url));
$update = $big[0][0];
$daily = $big[1][0];
$averages = array($big[0][1], $big[1][1]);
$averages = array($averages[0][0], $averages[0][1], $averages[1][1]);
unset($big);
?>
<table class="tabela" cellspacing="0" cellpadding="0" style="margin:2em auto;padding:0;">
<tr>
<td class="top">
<table cellspacing="0" class="cab top">
<tr>
<td style="font-size:150%;text-align:center;">Averages
</td></tr></table></td></tr>
<tr>
<td class="top">
<table cellspacing="0" class="corpo">
  <thead class="dh">
  <tr>
    <th style="width:12em;"></th>
    <th>Points</th>
    <th>WUs</th>
    <th>Points/Wu</th>
    </tr>
    </thead>
<tbody>
<?php
$titulo_linha = array("Update - 14 days", "Daily - 14 days", "Weekly - 8 weeks");
$n_linha = 0;
$decimals = $averages[2][0] < 100000 ? 2 : 1;
foreach ($averages as $row)
{
   if ($n_linha++ % 2 == 0) $classe = 'ls';
   else $classe = 'ln';
   echo <<<html
   <tr class="$classe">
   <td>{$titulo_linha[$n_linha -1]}</td>
html;
   foreach ($row as $col)
   {
      echo "<td>", $col == 0 ? "" : number_format($col, $decimals), "</td>";
   }
   echo "</tr>\n";
}
?>
</tbody></table></td></tr></table>

<!--=============================================================-->

<table class="tabela" cellspacing="0" cellpadding="0" style="margin:0 auto 0.5em;padding:0;">
<tr>
<td class="top">
<table cellspacing="0" class="cab top">
<tr>
<td style="font-size:150%;text-align:center;">Update History UTC time:
<a id='pwa1uup' class="pwa"
  href="javascript:changeDisplay('pt','pwa1','uup'); void 0;"
  style="background:#ffd800;">Points</a> -
<a id='pwa2uup' class="pwa"
  href="javascript:changeDisplay('wu','pwa2','uup'); void 0;">WUs</a> -
<a id='pwa3uup' class="pwa"
  href="javascript:changeDisplay('pw','pwa3','uup'); void 0;">Points/WU</a>
</td></tr></table></td></tr>
<tr>
<td class="top">
<table cellspacing="0" class="corpo">
  <thead class="dh">
  <tr>
    <th style="border-left:0;">&nbsp;</th>
    <?php
    for ($i = 0; $i < 8; $i++)
      echo '<th style="width:5em;">', ($hour % 3) + $i * 3, ':00</th>'
    ?>
    <th id="total_col_header_update">Total</th>
    </tr>
    </thead>
<tbody id='uup'>
<?php
$n_linha = 0;
foreach ($update as $row)
{
   if ($n_linha++ % 2 == 0) $classe = 'class="ls"';
   else $classe = 'class="ln"';
   echo "<tr $classe>";
   echo "<td>$row[0]</td>";
   foreach ($row[1] as $col)
   {
      echo "<td class=pt>", $col[0] == 0 ? "" : number_format($col[0], 0), "</td>";
      echo "<td class=wu>", $col[1] == 0 ? "" : number_format($col[1], 0), "</td>";
      echo "<td class=pw>", $col[2] == 0 ? "" : number_format($col[2], 0), "</td>";
   }
   echo "</tr>\n";
}
unset($update)
?>
</tbody></table></td></tr></table>

<?php
include './script/entrelacado_ad.html';
?>

<!--===================================================-->

<table class="tabela" cellspacing="0" cellpadding=""
  style="margin:2em auto 0;padding:0;">
<tr>
<td class="top">
<table cellspacing="0" cellpadding="0" class="cab top">
<tr>
<td style="font-size:150%;text-align:center;">
Daily History:
<a id="pwa1ud" class="pwa" href="javascript:changeDisplay('pt','pwa1','ud'); void 0;"
  style="background:#ffd800;">Points</a> -
<a id="pwa2ud" class="pwa" href="javascript:changeDisplay('wu','pwa2','ud'); void 0;">WUs</a> -
<a id="pwa3ud" class="pwa" href="javascript:changeDisplay('pw','pwa3','ud'); void 0;">Points/WU</a>
</td>
</tr></table></td></tr>
<tr>
<td class="top">
<table cellspacing="0" cellpadding="0" class="corpo">
  <thead class="dh">
  <tr>
    <th style="border-left:0;">&nbsp;</th>
    <th style="width:5em;">Mon</th><th style="width:5em;">Tue</th>
    <th style="width:5em;">Wed</th>
    <th style="width:5em;">Thu</th><th style="width:5em;">Fri</th>
    <th style="width:5em;">Sat</th><th style="width:5em;">Sun</th>
    <th id="total_col_header_daily">Total</th>
    </tr>
    </thead>
<tbody id='ud'>
<?php
$n_linha = 0;
foreach ($daily as $row)
{
   if ($n_linha++ % 2 == 0) $classe = 'class="ls"';
   else $classe = 'class="ln"';
   echo "<tr $classe>";
   echo "<td>$row[0]</td>";
   foreach ($row[1] as $col)
   {
      echo "<td class=pt>", $col[0] == 0 ? "" : number_format($col[0], 0), "</td>";
      echo "<td class=wu>", $col[1] == 0 ? "" : number_format($col[1], 0), "</td>";
      echo "<td class=pw>", $col[2] == 0 ? "" : number_format($col[2], 0), "</td>";
   }
   echo "</tr>\n";
}
unset($daily);
?>
</tbody></table></td></tr></table>
<!-- google_ad_section_end -->
<?php include "./footer.html"; ?>
</body></html>
