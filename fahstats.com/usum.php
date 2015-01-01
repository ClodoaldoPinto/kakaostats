<?php

if (!isset($_GET['u'])) exit;
$donor = $_GET['u'];
if (preg_match('/\D/', $donor) == 1 || $donor == '' || $donor == 891672) exit;
session_start();
$_SESSION['v'] = '1';
include 'function_color.php';
require './pgsqlserver.php';
$link = pg_connect($conn_string);
$query = "select in_now from maintenance;";
$result = pg_query($link, $query);
$in_maintenance = pg_fetch_result($result, 'in_now');
$query = "select to_char (last_date, 'YYYY-MM-DD HH24:MI') as date,".
"  extract (hour from last_date) as hour from last_date;";
$result = pg_query($link, $query);
$line = pg_fetch_array($result, NULL, PGSQL_ASSOC);
$date = $line["date"];
$lastDayUpdate = $line['hour'] >= 21 ? 1 : 0;
$query = "
select
   ui.n_time as team,
   ui.usuario_nome as name,
   ti.time_nome
from usuarios_indice as ui
inner join times_indice ti on ui.n_time = ti.n_time
where ui.usuario_serial = $donor
;";
$result = pg_query($link, $query);
$line = pg_fetch_array($result, NULL, PGSQL_ASSOC);
$donorName = utf8_encode($line["name"]);
$team = $line ['team'];
$team_name = utf8_encode($line ['time_nome']);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
   "http://www.w3.org/TR/html4/loose.dtd">
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
  <?php echo htmlentities($donorName, ENT_QUOTES, 'UTF-8'); ?></title>
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
$page_name = 'donor_summary';
include './menu.php';
?>
<p style="text-align:center;margin:25px auto 10px auto;font-size:22px;color:floralWhite;">
<?php
echo htmlentities($donorName, ENT_QUOTES, 'UTF-8'), <<<html
's Summary at
  <span style="font-size:18px;">
  <script type="text/javascript">
  document.write(wDate('$date') + ' ' + timeZone());
  </script></span>
</p>
<!-- ####################################################### -->
html;

$query = <<<query
select *
from (
    select
        d.usuario_nome as d_name,
        d.usuario_serial as d_number,
        rank_0 as d_rank_0,
        rank_24 as d_rank_24,
        rank_7 as d_rank_7,
        rank_30 as d_rank_30,
        rank_0_time as d_rank_0_team,
        rank_24_time as d_rank_24_team,
        rank_7_time as d_rank_7_team,
        rank_30_time as d_rank_30_team,
        pontos_0 as d_points_0,
        pontos_24 as d_points_24,
        pontos_up as d_points_up,
        pontos_7 as d_points_7
    from donors_production as t
    inner join usuarios_indice as d on t.usuario=d.usuario_serial
    where t.usuario = $donor
) d inner join (
    select
        pontos_0 as t_points_0,
        pontos_24 as t_points_24,
        pontos_up as t_points_up,
        pontos_7 as t_points_7,
        rank_0 as t_rank_0,
        rank_7 as t_rank_7,
        rank_24 as t_rank_24,
        rank_30 as t_rank_30
    from teams_production
    where n_time = $team
) t on true;
query;
$result = pg_query($link, $query);
?>
<table class="tabela" cellspacing="0" style="margin:15px auto;">
<tr>
<td class="top">
<table cellspacing="0" class="cab top">
<tr>
<td style="text-align:center;font-size:150%;margin-right:0;padding:0.3em 0;line-height:150%;" class="cab">
  Team Ranking
</td>
</tr>
</table></td></tr>
<tr><td class="top">
<table cellspacing="0" class="corpo">
<thead>
  <tr>
  <th rowspan="2"
    onmouseover="return overlib(
      'Color mean the weekly points:<br>' +
      '<span class=\'c1\'>Red: 700,000+</span><br/ >' +
      '<span class=\'c2\'>Dark Red: 350,000+</span><br>' +
      '<span class=\'c3\'>Orange: 70,000+</span><br>' +
      '<span class=\'c4\'>Lime: 35,000+</span><br>' +
      '<span class=\'c5\'>Blue: 7,000+</span><br>' +
      '<span class=\'c6\'>Green: 700+</span><br>' +
      '<span class=\'c7\'>Black: 1+</span>'
      );"
    onmouseout="return nd();"
    style="width:20em;"
    >Member</th>
  <th colspan="4">Team Ranking</th>
  <th colspan="4">Project Ranking</th>
  <th colspan="4" style="border-right:0;">Points</th>
  </tr>
  <tr>
  <th
    onmouseover="return overlib('Current member team ranking');"
    onmouseout="return nd();"
    >Cur-<br>rent</th>
  <th
    onmouseover="return overlib('Future member team ranking at the end of the next 24 hours');"
    onmouseout="return nd();"
    >In 24<br>Hs</th>
  <th
    onmouseover="return overlib('Future member team ranking at the end of the next 7 days');"
    onmouseout="return nd();"
    >In 7<br>days</th>
  <th
    onmouseover="return overlib('Future member team ranking at the end of the next 30 days');"
    onmouseout="return nd();"
    >In 30<br>days</th>
  <th
    onmouseover="return overlib('Current member project ranking');"
    onmouseout="return nd();"
    >Cur-<br>rent</th>
  <th
    onmouseover="return overlib('Future member project ranking at the end of the next 24 hours');"
    onmouseout="return nd();"
    >In 24<br>Hs</th>
  <th
    onmouseover="return overlib('Future member project ranking at the end of the next 7 days');"
    onmouseout="return nd();"
    >In 7<br>days</th>
  <th
    onmouseover="return overlib('Future member project ranking at the end of the next 30 days');"
    onmouseout="return nd();"
    >In 30<br>days</th>
  <th
    onmouseover="return overlib('Points scored in the last update');"
    onmouseout="return nd();"
    >Up-<br>date</th>
  <th
    onmouseover="return overlib('Points scored in the last 24 hours');"
    onmouseout="return nd();"
    >24<br>hrs</th>
  <th
    onmouseover="return overlib('Points scored in the last 7 days');"
    onmouseout="return nd();"
    >7<br>days</th>
  <th
    onmouseover="return overlib('Total member points');"
    onmouseout="return nd();"
    style="border-right:0;"
    >Total</th>
  </tr></thead>
<tbody class="tcorpo">
<?php

$line = pg_fetch_array($result, NULL, PGSQL_ASSOC);

  $cor_7 = cor_donor($line['t_points_7']);

   $team_name = preg_replace(
     '/([^\s]{12}\B)/S','$1<wbr>&shy;',
     preg_replace('/([^\A])_([^\Z])/S','$1 $2', $team_name)
     );
   $team_name = str_replace(
     '&lt;wbr&gt;&amp;shy;','<wbr>&shy;',
     htmlentities($team_name, ENT_QUOTES, 'UTF-8')
     );

echo <<<html
<tr class="ln">
<td class="txt $cor_7">
   <a class="$cor_7" href="tsum.php?t=$team">$team_name</a></td>
<td></td><td></td><td></td><td></td>
html;
echo '<td>', number_format($line['t_rank_0'], 0, '.', ','), '</td>';
echo '<td>', number_format($line['t_rank_24'], 0, '.', ','), '</td>';
echo '<td>', number_format($line['t_rank_7'], 0, '.', ','), '</td>';
echo '<td>', number_format($line['t_rank_30'], 0, '.', ','), '</td>';
echo '<td>', number_format($line['t_points_up'], 0, '.', ','), '</td>';
echo '<td>', number_format($line['t_points_24'], 0, '.', ','), '</td>';
echo '<td>', number_format($line['t_points_7'], 0, '.', ','), '</td>';
echo '<td>', number_format($line['t_points_0'], 0, '.', ','), '</td>';
echo <<<html
</tr>\n
<tr class="ls">
<td style="line-height:0.1em;">&nbsp;</td>
<td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
<td></td><td></td><td></td><td></td></tr>\n
html;

$n_linha = 0;
if (++$n_linha % 2 == 0) $classe = 'class="ls"';
else $classe = 'class="ln"';
$cor7 = cor_donor($line['d_points_7']);

$line_name = preg_replace(
  '/([^\s]{12}\B)/S','$1<wbr>&shy;',
  preg_replace('/([^\A])_([^\Z])/S','$1 $2', utf8_encode($line['d_name']))
  );
$line_name = str_replace(
  '&lt;wbr&gt;&amp;shy;','<wbr>&shy;',
  htmlentities($line_name, ENT_QUOTES, 'UTF-8')
  );

echo <<<html
<tr $classe>
<td class="txt $cor7">
<a class="$cor7" href="./up.php?u={$line['d_number']}">$line_name</a></td>
html;

echo "<td>", number_format($line['d_rank_0_team'], 0, ".", ","), "</td>";
echo "<td";
if ($line['d_rank_24_team'] < $line['d_rank_0_team']) echo ' class="g"';
elseif ($line['d_rank_24_team'] > $line['d_rank_0_team']) echo ' class="r"';
echo ">", number_format($line['d_rank_24_team'], 0, ".", ","), "</td>";
echo "<td";
if ($line['d_rank_7_team'] < $line['d_rank_0_team']) echo ' class="g"';
elseif ($line['d_rank_7_team'] > $line['d_rank_0_team']) echo ' class="r"';
echo ">", number_format($line['d_rank_7_team'], 0, ".", ","), "</td>";
echo "<td";
if ($line['d_rank_30_team'] < $line['d_rank_0_team']) echo ' class="g"';
elseif ($line['d_rank_30_team'] > $line['d_rank_0_team']) echo ' class="r"';
echo ">", number_format($line['d_rank_30_team'], 0, ".", ","), "</td>";

echo "<td>", number_format($line['d_rank_0'], 0, ".", ","), "</td>";
echo "<td";
if ($line['d_rank_24'] < $line['d_rank_0']) echo ' class="g"';
elseif ($line['d_rank_24'] > $line['d_rank_0']) echo ' class="r"';
echo ">", number_format($line['d_rank_24'], 0, ".", ","), "</td>";
echo "<td";
if ($line['d_rank_7'] < $line['d_rank_0']) echo ' class="g"';
elseif ($line['d_rank_7'] > $line['d_rank_0']) echo ' class="r"';
echo ">", number_format($line['d_rank_7'], 0, ".", ","), "</td>";
echo "<td";
if ($line['d_rank_30'] < $line['d_rank_0']) echo ' class="g"';
elseif ($line['d_rank_30'] > $line['d_rank_0']) echo ' class="r"';
echo ">", number_format($line['d_rank_30'], 0, ".", ","), "</td>";
echo "<td>", number_format($line['d_points_up'], 0, ".", ","), "</td>";
echo "<td>", number_format($line['d_points_24'], 0, ".", ","), "</td>";
echo '<td class="', $cor7, '">', number_format($line['d_points_7'], 0, ".", ","), "</td>";
echo "<td>", number_format($line['d_points_0'], 0, ".", ","), "</td>";
echo "</tr>\n";

echo '</tbody></table></tr></table>';
include './script/entrelacado_ad.html';
?>
<!-- ####################################################### -->
<?php
if ($in_maintenance != 't') {
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
$url = "http://$server_name/python/mp_history.py?d=$donor&t=donor_update%3bdonor_daily";
$big = eval_array(file_get_contents($url));
$update = $big[0][0];
$daily = $big[1][0];
$averages = array($big[0][1], $big[1][1]);
$averages = array($averages[0][0], $averages[0][1], $averages[1][1]);
unset($big);
?>
<table class="tabela" cellspacing="0" cellpadding="0" style="margin:15px auto;padding:0;">
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
   if ($n_linha++ % 2 == 0) $classe = 'class="ls"';
   else $classe = 'class="ln"';
   echo "<tr $classe>";
   echo "<td>" . $titulo_linha[$n_linha -1] . "</td>";
   foreach ($row as $col)
   {
      echo "<td>", $col == 0 ? "" : number_format($col, $decimals), "</td>";
   }
   echo "</tr>\n";
}
echo '</tbody></table></td></tr></table>';
?>

<!--=============================================================-->

<table class="tabela" cellspacing="0" cellpadding="0"
         style="margin:15px auto;padding:0;">
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
unset($update);
echo '</tbody></table></td></tr></table>';
include './script/entrelacado_ad.html';
?>

<!--===================================================-->
<table class="tabela" cellspacing="0" cellpadding=""
  style="margin:15px auto;padding:0;">
<tr>
<td class="top">
<table cellspacing="0" cellpadding="0" class="cab top">
<tr>
<td style="font-size:150%;text-align:center;">
Daily History:
<a id='pwa1ud' class="pwa" href="javascript:changeDisplay('pt','pwa1','ud'); void 0;"
  style="background:#ffd800;">Points</a> -
<a id='pwa2ud' class="pwa" href="javascript:changeDisplay('wu','pwa2','ud'); void 0;">WUs</a> -
<a id='pwa3ud' class="pwa" href="javascript:changeDisplay('pw','pwa3','ud'); void 0;">Points/WU</a>
</td>
<td style="text-align:center;width=10em;">
   <a href="javascript:var w = window.open(
   'pop_up_chart.php?id=<?php echo $donor ?>&amp;name=<?php echo urlencode($donorName) ?>&amp;chart=ddaily',
   '', 'width=770,height=520'); w.focus();" style="background-color:gold;font-size:120%;">
      Show Chart</a>
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
?>
</tbody></table></td></tr></table>
<!-- ############################################################ -->
<?php
} # end if (in_maintenance)
else {
   echo '<p style="text-align:center;margin: 1em;font-size:200%;">';
   echo 'Database in maintenance. Come back in one hour.</p>';
}
flush();
?>
<!-- ###################################################### -->
<table class="tabela" cellspacing="0" style="margin: 15px auto;">
<tr>
<td class="top">
<table cellspacing="0" class="cab top">
<tr>
<td style="font-size:22px;width=80%;">
   Radar Table
</td>
<td style="text-align:center;">
   <span style="font-size:120%;">
   <a href="javascript:var w = window.open(
   'pop_up_chart.php?chart=dradar&amp;id=<?php echo $donor ?>&amp;name=<?php echo $donorName ?>',
   '', 'width=770,height=520'); w.focus();" style="background-color:gold;">
      Show Chart</a></span>
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
<th colspan="2" style="border-left:0;"
  >Overtake</th>
<th rowspan="3" style="width:15em;"
  onmouseover="return overlib(
    'Team member overtaking or being overtaked<br>' +
    'Color mean the weekly points:<br>' +
    '<span class=\'c1\'>Red: 700,000+</span><br/ >' +
    '<span class=\'c2\'>Dark Red: 350,000+</span><br>' +
    '<span class=\'c3\'>Orange: 70,000+</span><br>' +
    '<span class=\'c4\'>Lime: 35,000+</span><br>' +
    '<span class=\'c5\'>Blue: 7,000+</span><br>' +
    '<span class=\'c6\'>Green: 700+</span><br>' +
    '<span class=\'c7\'>Black: 1+</span><br>' +
    '<span class=\'c8\'>Gray: Inactive</span>'
    );"
  onmouseout="return nd();"
  >Challenging Member</th>
<th colspan="10">Challenging Member Stats</th>
</tr>
<tr>
<th rowspan="2" style="border-left:0;"
  onmouseover="return overlib('Days until the ranking change');"
  onmouseout="return nd();"
  >Days</th>
<th rowspan="2"
  onmouseover="return overlib('Date of the ranking change');"
  onmouseout="return nd();"
  >Date</th>
<th rowspan="1" colspan="2"
  >Ranking</th>
<th colspan="4"
  > Total Points</th>
<th colspan="4"
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
  return overlib('Points scored by the challenging member in the last update');"
  onmouseout="return nd();"
  >Up-<br>date</th>
<th onmouseover="
  return overlib('Points scored by the challenging member in the last 24 hours');"
  onmouseout="return nd();"
  >24<br>hrs</th>
<th onmouseover="
  return overlib('Points scored by the challenging member in the last seven days');"
  onmouseout="return nd();"
  >7<br>days</th>
<th onmouseover="
  return overlib('Total points of the challenging member');"
  onmouseout="return nd();"
  >Total</th>
<th onmouseover="
  return overlib('Points difference the last update');"
  onmouseout="return nd();"
  >Up-<br>date</th>
<th onmouseover="
  return overlib('Points difference the last 24 hours (PPD)');"
  onmouseout="return nd();"
  >24<br>hrs</th>
<th onmouseover="
  return overlib('Points difference in the last seven days (PPW)');"
  onmouseout="return nd();"
  >7<br>days</th>
<th onmouseover="
  return overlib('Total points difference');"
  onmouseout="return nd();"
  >Total</th>
</tr>
</thead>
<tbody>
<?php
$query = <<<query
with u0 as (
    select
        usuario,
        rank_0,
        rank_0_time,
        pontos_0,
        pontos_up,
        pontos_7,
        pontos_24
    from donors_production
    where usuario = $donor
)
select
    usuario_nome as donor_name,
    days,
    "date",
    usuario as donor_number,
    rank_0 as "challengerProjectRank",
    rank_0_time as "challengerRank",
    pontos_0,
    pontos_up,
    pontos_7,
    pontos_24,
    pontos_0_diff,
    pontos_up_diff,
    pontos_7_diff,
    pontos_24_diff,
    alerta
from (
    select
        null as days,
        (select last_date from last_date) as "date",
        usuario,
        rank_0,
        rank_0_time,
        pontos_0,
        pontos_up,
        pontos_7,
        pontos_24,
        null as pontos_0_diff,
        null as pontos_up_diff,
        null as pontos_7_diff,
        null as pontos_24_diff,
        'p' as alerta
    from u0
    union
    select
        (u0.pontos_0 - u1.pontos_0) * 7 / (u1.pontos_7 - u0.pontos_7) days,
        (select last_date from last_date) + case
            when
                (u0.pontos_0 - u1.pontos_0) / (u1.pontos_7 - u0.pontos_7) > 421200
                then null else
                (((u0.pontos_0 - u1.pontos_0) / (u1.pontos_7 - u0.pontos_7)
                )::text || ' week')::interval
            end
        as "date",
        u1.usuario,
        u1.rank_0,
        u1.rank_0_time,
        u1.pontos_0,
        u1.pontos_up,
        u1.pontos_7,
        u1.pontos_24,
        u1.pontos_0 - u0.pontos_0 as pontos_0_diff,
        u1.pontos_up - u0.pontos_up as pontos_up_diff,
        u1.pontos_7 - u0.pontos_7 as pontos_7_diff,
        u1.pontos_24 - u0.pontos_24 as pontos_24_diff,
        case when u0.pontos_0 > u1.pontos_0 then 'r' else 'g' end as alerta
    from donors_production u1
    inner join u0 on true
    where active
        and n_time = $team
        and (
        (
            u0.pontos_7 < u1.pontos_7
            and
            u0.pontos_0 > u1.pontos_0
        )
        or
        (
            u0.pontos_7 > u1.pontos_7
            and
            u0.pontos_0 < u1.pontos_0
        )
    )
) ss
inner join usuarios_indice as ui on ss.usuario = ui.usuario_serial
order by
    ui.usuario_serial = $donor desc,
    days, pontos_7, pontos_0
limit 11
;
query;
$result = pg_query($link, $query);

$n_linha = 0;
$n_linha_head = 0;
while ($line = pg_fetch_array($result, NULL, PGSQL_ASSOC))
{
  $n_linha++;
  $cor7 = cor_donor($line['pontos_7']);

   $donor_name = preg_replace(
     '/([^\s]{15}\B)/S','$1<wbr>&shy;',
     preg_replace('/([^\A])_([^\Z])/S','$1 $2', utf8_encode($line['donor_name']))
     );
   $donor_name = str_replace(
     '&lt;wbr&gt;&amp;shy;','<wbr>&shy;',
     htmlentities($donor_name, ENT_QUOTES, 'UTF-8')
     );

  if ($n_linha % 2 == 0) $classe = 'class="ls"';
  else $classe = 'class="ln"';
  if (++$n_linha_head % 16 == 0) include('table_head_member_radar.html');
  echo "<tr $classe>";
  echo "<td class=\"{$line['alerta']}\">",
    number_format($line['days'], 0, ".", ","), "</td>";
  echo <<<html
  <td style="width:11em;" class="${line['alerta']}">
    <script type="text/javascript">
    document.write(wDate2('{$line['date']}'));
    </script>
    </td>
    <td class="txt $cor7">
html;
  echo "<a href=\"./u.php?u={$line['donor_number']}\" class=\"$cor7\">{$donor_name}</a>";
  echo "</td>";
  echo "<td>",
    number_format($line['challengerRank'], 0, ".", ","), "</td>";
  echo "<td>",
    number_format($line['challengerProjectRank'], 0, ".", ","), "</td>";
  echo "<td>",
    number_format($line['pontos_up'], 0, ".", ","), "</td>";
  echo "<td>",
    number_format($line['pontos_24'], 0, ".", ","), "</td>";
  echo '<td class="', $cor7, '">',
    number_format($line['pontos_7'], 0, ".", ","), "</td>";
  echo "<td>",
    number_format($line['pontos_0'], 0, ".", ","), "</td>";
  echo "<td>",
    number_format($line['pontos_up_diff'], 0, ".", ","),
    "</td>";
  echo "<td>",
    number_format($line['pontos_24_diff'], 0, ".", ","),
    "</td>";
  echo "<td>",
    number_format($line['pontos_7_diff'], 0, ".", ","),
    "</td>";
  echo "<td>",
    number_format($line['pontos_0_diff'], 0, ".", ","),
    "</td>";
  echo "</tr>\n";
};
echo '</tbody></table></td></tr></table>';
include './script/entrelacado_ad.html';
?>

<!-- google_ad_section_end -->
<p>&nbsp;</p>
</body></html>
