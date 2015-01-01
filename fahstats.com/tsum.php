<?php

if (!isset($_GET['t'])) exit;
$team = $_GET['t'];
if (preg_match('/\D/', $team) == 1 || $team == '') exit(0);
session_start();
$_SESSION['v'] = '1';

include('function_color.php');
require './pgsqlserver.php';
$link = pg_connect($conn_string);

$query = <<<query
select
  to_char (last_date, 'YYYY-MM-DD HH24:MI:SS') as "date",
  (
    select extract(epoch from "datetime") as unixtimestamp
    from processing_end
  ) as unixtimestamp,
  extract(
    epoch from(select "datetime" from processing_end)
    +
    '3 hours 3 minutes'::interval - current_timestamp(0)
  )::bigint * 1000 as ttu
from last_date;
query;
$result = pg_query($link, $query);
$row = pg_fetch_row($result);
$updateDate = $row[0];
$update_unix_timestamp = $row[1];
$ttu = $row[2];
$filename = './html_file_cache/tsum.html?t='.$team;

if (file_exists($filename)) {
  if (filemtime($filename) >= $update_unix_timestamp) {
    $file_string = file_get_contents($filename);
    echo(str_replace('var time_to_update = ',
                     'var time_to_update = '.$ttu.';// ', $file_string));
    exit(0);
  }
}

/*
$query = "select to_char (last_date, 'YYYY-MM-DD HH24:MI') as date,".
"  extract (hour from last_date) as hour from last_date;";
$result = pg_query($link, $query);
$line = pg_fetch_array($result, NULL, PGSQL_ASSOC);
$date = $line["date"];
$lastDayUpdate = $line['hour'] >= 21 ? 1 : 0;
*/
$query = "
select ti.time_nome as name
from times_indice as ti
where ti.n_time = $team
;";
$result = pg_query($link, $query);
$line = pg_fetch_array($result, NULL, PGSQL_ASSOC);
$teamName = utf8_encode($line["name"]);

ob_start();
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
function changeDisplay2(pwa, pwId) {
  document.getElementById("pwa12").style.background = "lightSteelBlue";
  document.getElementById("pwa22").style.background = "lightSteelBlue";
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
          if (trChilds[j].className == "pt2")
            trChilds[j].style.display = pwa == "pt2" ? show:noshow;
          else if (trChilds[j].className == "wu2")
            trChilds[j].style.display = pwa == "wu2" ? show:noshow;
          }
      }
    }
  }
function changeDisplay3(pwa, pwId) {
  document.getElementById("pwa13").style.background = "lightSteelBlue";
  document.getElementById("pwa23").style.background = "lightSteelBlue";
  document.getElementById(pwId).style.background = "#ffd800";
  var show = "table-row";
  if (is_ie) show = "block";
  var noshow = "none";
  var tblChilds = document.getElementById("tp2").childNodes;
  for (var i=0; i<tblChilds.length; i++) {
    if (tblChilds[i].nodeType == 1) {
      if (tblChilds[i].className.indexOf('pt3') > -1) {
        tblChilds[i].style.display = pwa == "pt3" ? show:noshow;
      }
      else
        tblChilds[i].style.display = pwa == "wu" ? show:noshow;
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
.pt3 {display : table-row;}
.pt2 {display : none;}
.wu2 {display : table-cell;}
.pw {display : none;}
thead.dh th {width: 7.5em;}
.ls { background: gainsboro; }
.ls:hover { background-color:#ffd800; }
.ln { background: floralWhite; }
.ln:hover { background-color:#ffd800; }
</style>
<title>Kakao Stats - Team Summary -
  <?php echo htmlentities($teamName, ENT_QUOTES, 'UTF-8'); ?></title>
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
$page_name = 'team_summary';
include './menu.php';
?>
<p style="text-align:center;margin:20px auto;font-size:22px;color:floralWhite;">
<?php
  echo htmlentities($teamName, ENT_QUOTES, 'UTF-8'), <<<html
  Summary at
  <span style="font-size:18px;">
  <script type="text/javascript">
  document.write(wDate('$updateDate')+' '+timeZone());
  </script></span>
html;
?>
</p>
<?php
$query = <<<query
select
    active_members as active_number,
    tp.n_time as team_number,
    rank_0, rank_24, rank_7, rank_30,
    pontos_0 /1000 as points_0,
    pontos_24 as points_24,
    pontos_7 as points_7,
    pontos_up as points_up,
    ti.time_nome as team_name,
    new_members
from teams_production as tp
inner join times_indice as ti on tp.n_time = ti.n_time
where
    tp.n_time in ($team, -1)
order by tp.n_time
;
query;
$result = pg_query($link, $query);
?>
<table class="tabela" cellspacing="0" style="margin:15px auto;">
<tr>
<td style="" class=top><table cellspacing="0" class="cab top">
<tr>
<td style="text-align:center;font-size:150%;margin-right:0;height:2em" class="cab">Team Rank
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
<th rowspan="1" colspan="2">
  Members</th>
<th rowspan="3"
  onmouseover="return overlib(
    'Color mean the weekly points:<br>' +
    '<span class=\'c1\'>Red: 10,000,000+</span><br>' +
    '<span class=\'c2\'>Dark Red: 5,000,000+</span><br>' +
    '<span class=\'c3\'>Orange: 1,000,000+</span><br>' +
    '<span class=\'c4\'>Lime: 500,000+</span><br>' +
    '<span class=\'c5\'>Blue: 100,000+</span><br>' +
    '<span class=\'c6\'>Green: 50,000+</span><br>' +
    '<span class=\'c7\'>Black: 1+</span>'
  );"
  onmouseout="return nd();"
  style="width:15em;"
  >Team</th>
<th colspan="4">
  Rank</th>
<th colspan="5" style="border-right:0;">
  Points</th>
</tr>
<tr>
<th rowspan="2"
  onmouseover="return overlib('Number of members who scored at least 1 point in the last 50 days');"
  onmouseout="return nd();"
  >Ac-<br>tive</th>
<th rowspan="2"
  onmouseover="return overlib('New members in the last 14 days');"
  onmouseout="return nd();"
  >New</th>
<th rowspan="2"
  onmouseover="return overlib('Current team ranking');"
  onmouseout="return nd();"
  >Cur-<br>rent</th>
<th rowspan="2"
  onmouseover="return overlib('Future team ranking at the end of the next 24 hours');"
  onmouseout="return nd();"
  >In 24<br>Hs</th>
<th rowspan="2"
  onmouseover="return overlib('Future team ranking at the end of the next 7 days');"
  onmouseout="return nd();"
  >In 7<br>days</th>
<th rowspan="2"
  onmouseover="return overlib('Future team ranking at the end of the next 30 days');"
  onmouseout="return nd();"
  >In 30<br>days</th>
<th rowspan="2"
  onmouseover="return overlib('Points scored in the last update');"
  onmouseout="return nd();"
  >Up-<br>date</th>
<th rowspan="2"
  onmouseover="return overlib('Points scored in the last 24 hours');"
  onmouseout="return nd();"
  >24 hours</th>
<th colspan="2" style="width:12em;">
  7 days</th>
<th rowspan="2"
  onmouseover="return overlib('Total team points');"
  onmouseout="return nd();"
  style="border-right:0;"
  >Total<br><span style="font-weight:normal">(1,000)</span></th>
  </tr>
<tr><th
  onmouseover="return overlib('Points scored in the last 7 days');"
  onmouseout="return nd();"
  >Total</th>
<th
  onmouseover="return overlib('Points scored in the last 7 days divided by the number of active team members');"
  onmouseout="return nd();"
  >Per Active</th></tr>
</thead>
<tbody class="tcorpo">
<?php

$line = pg_fetch_array($result, NULL, PGSQL_ASSOC);

$line['team_name'] = preg_replace(
  '/([^\s]{18}\B)/S','$1<wbr>&shy;',
  preg_replace('/([^\A])_([^\Z])/S','$1 $2', utf8_encode($line['team_name']))
  );
$line['team_name'] = str_replace(
  '&lt;wbr&gt;&amp;shy;','<wbr>&shy;',
  htmlentities($line['team_name'], ENT_QUOTES, 'UTF-8')
  );

echo '<tr class="ln">';
echo "<td>", number_format($line['active_number'], 0, ".", ","), "</td>";
echo "<td>", number_format($line['new_members'], 0, ".", ","), "</td>";
echo '<td class="txt" style="font-weight:bold;">';
echo $line['team_name'], '</td>';
echo <<<html
<td></td><td></td><td></td><td></td>
html;
echo "<td>", number_format($line['points_up'], 0, ".", ","), "</td>";
echo "<td>", number_format($line['points_24'], 0, ".", ","), "</td>";
echo "<td>", number_format($line['points_7'], 0, ".", ","), "</td>";
echo "<td>",
  number_format($line['points_7']/$line['active_number'], 0, ".", ","),
  "</td>";
echo "<td>", number_format($line['points_0'], 0, ".", ","), "</td>";
echo <<<html
</tr>\n
<tr class="ls">
<td style="line-height:0.1em;">&nbsp;</td>
<td></td><td></td><td></td><td></td><td></td><td></td>
<td></td><td></td><td></td><td></td><td></td>
</tr>\n
html;

$line = pg_fetch_array($result, NULL, PGSQL_ASSOC);
$classe = 'class="ln"';

  $cor_7 = cor_time($line['points_7']);

  $line['team_name'] = preg_replace(
      '/([^\s]{18}\B)/S','$1<wbr>&shy;',
      preg_replace('/([^\A])_([^\Z])/S','$1 $2', utf8_encode($line['team_name']))
      );
  $line['team_name'] = str_replace(
      '&lt;wbr&gt;&amp;shy;','<wbr>&shy;',
      htmlentities($line['team_name'], ENT_QUOTES, 'UTF-8')
      );

  echo "<tr $classe>";
  echo "<td>", number_format($line['active_number'], 0, ".", ","), "</td>";
  echo "<td>", number_format($line['new_members'], 0, ".", ","), "</td>";
  echo "<td class=\"txt $cor_7\">";
  echo "<a class=\"$cor_7\" href=\"/t.php?t={$line['team_number']}\">{$line['team_name']}</a>";
  echo "</td>";
  echo "<td>",
    $line['rank_0'] == 0 ? '' : number_format($line['rank_0'], 0, ".", ","),
    "</td>";
  echo "<td";
  if ($line['rank_24'] < $line['rank_0']) echo ' class="g"';
  elseif ($line['rank_24'] > $line['rank_0']) echo ' class="r"';
  echo ">",
    $line['rank_24'] == 0 ? '' : number_format($line['rank_24'], 0, ".", ","),
    "</td>";
  echo "<td";
  if ($line['rank_7'] < $line['rank_0']) echo ' class="g"';
  elseif ($line['rank_7'] > $line['rank_0']) echo ' class="r"';
  echo ">",
    $line['rank_7'] == 0 ? '' : number_format($line['rank_7'], 0, ".", ","),
    "</td>";
  echo "<td";
  if ($line['rank_30'] < $line['rank_0']) echo ' class="g"';
  elseif ($line['rank_30'] > $line['rank_0']) echo ' class="r"';
  echo ">",
    $line['rank_30'] == 0 ? '' : number_format($line['rank_30'], 0, ".", ","),
    "</td>";
  echo "<td>", number_format($line['points_up'], 0, ".", ","), "</td>";
  echo "<td>", number_format($line['points_24'], 0, ".", ","), "</td>";
  echo '<td class="',$cor_7,'">',
    number_format($line['points_7'], 0, ".", ","), "</td>";
  echo "<td>",
    $line['active_number'] == 0 ? '' :
      number_format($line['points_7']/$line['active_number'], 0, ".", ","),
    "</td>";
  echo "<td>", number_format($line['points_0'], 0, ".", ","), "</td>";
  echo "</tr>\n";
echo '</tbody></table></td></tr></table>';
include './script/entrelacado_ad.html';
?>
<!-- ####################################################### -->
<?php
$query = "
select extract('hour' from min(data)) as hour
from datas
where data >= date_trunc('day', (select max(data) from datas))
;";
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
<table class="tabela" cellspacing="0" cellpadding="0"
       style="margin:15px auto;padding:0;">
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
   echo '<tr ',$classe,'>';
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

<table class="tabela" cellspacing="0" cellpadding="0" style="margin:15px auto;padding:0;">
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
<a id="pwa1ud" class="pwa" href="javascript:changeDisplay('pt','pwa1','ud'); void 0;"
  style="background:#ffd800;">Points</a> -
<a id="pwa2ud" class="pwa" href="javascript:changeDisplay('wu','pwa2','ud'); void 0;">WUs</a> -
<a id="pwa3ud" class="pwa" href="javascript:changeDisplay('pw','pwa3','ud'); void 0;">Points/WU</a>
</td>
<td style="text-align:center;width=10em;">
   <a href="javascript:var w = window.open(
   'pop_up_chart.php?chart=tdaily&amp;id=<?php echo $team ?>&amp;name=<?php echo urlencode($teamName) ?>',
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
unset($daily);
echo '</tbody></table></td></tr></table>';
flush();
?>
<!-- ############################################################ -->

<table class="tabela" cellspacing="0" style="margin:15px auto;">
<tr>
<td class="top">
<table cellspacing="0" class="cab top">
  <tr>
   <td style="text-align:center;font-size:150%;width:80%;">Radar Table
   </td>
   <td style="text-align:center;">
      <span style="font-size:120%;">
      <a href="javascript:var w = window.open(
      'pop_up_chart.php?chart=tradar&amp;id=<?php echo $team ?>&amp;name=<?php echo urlencode($teamName) ?>',
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
<thead><?php include('table_head_team_radar.html'); ?></thead>
<tbody>
<?php
$query = <<<query
with t0 as (
    select
        n_time,
        rank_0,
        pontos_0,
        pontos_up,
        pontos_7,
        pontos_24
    from teams_production
    where n_time = $team
)
select
    time_nome as team_name,
    days,
    "date",
    ti.n_time as team_number,
    rank_0 as challengerRank,
    pontos_0 as points_0,
    pontos_up as points_up,
    pontos_7 as points_7,
    pontos_24 as points_24,
    pontos_0_diff,
    pontos_up_diff,
    pontos_7_diff,
    pontos_24_diff,
    alerta
from (
    select
        null as days,
        (select last_date from last_date) as "date",
        n_time,
        rank_0,
        pontos_0,
        pontos_up,
        pontos_7,
        pontos_24,
        null as pontos_0_diff,
        null as pontos_up_diff,
        null as pontos_7_diff,
        null as pontos_24_diff,
        'p' as alerta
    from t0
    union
    select
        (t0.pontos_0 - t1.pontos_0) * 7 / (t1.pontos_7 - t0.pontos_7) days,
        (select last_date from last_date) +
            (((t0.pontos_0 - t1.pontos_0) /
            (t1.pontos_7 - t0.pontos_7))::text
            || ' week')::interval
        as "date",
        t1.n_time,
        t1.rank_0,
        t1.pontos_0,
        t1.pontos_up,
        t1.pontos_7,
        t1.pontos_24,
        t1.pontos_0 - t0.pontos_0 as pontos_0_diff,
        t1.pontos_up - t0.pontos_up as pontos_up_diff,
        t1.pontos_7 - t0.pontos_7 as pontos_7_diff,
        t1.pontos_24 - t0.pontos_24 as pontos_24_diff,
        case when t0.pontos_0 > t1.pontos_0 then 'r' else 'g' end as alerta
    from teams_production t1
    inner join t0 on true
    where active and (
        (
            t0.pontos_7 < t1.pontos_7
            and
            t0.pontos_0 > t1.pontos_0
        )
        or
        (
            t0.pontos_7 > t1.pontos_7
            and
            t0.pontos_0 < t1.pontos_0
        )
    )
) ss
inner join times_indice as ti on ss.n_time = ti.n_time
order by
    ti.n_time = $team desc,
    days, pontos_7, pontos_0
limit 15
;
query;
$result = pg_query($link, $query);
$classe = "-";
$n_linha_head = 0;
while ($line = pg_fetch_array($result, NULL, PGSQL_ASSOC))
{
  $cor7 = cor_time($line['points_7']);

  $line['team_name'] = preg_replace('/([^\A])_([^\Z])/','$1 $2',
      htmlentities($line['team_name'], ENT_QUOTES));
  $line['team_name'] = preg_replace('/([^\s]{15}\B)/', "$1<wbr>&shy;", $line['team_name']);

  if ($classe == "") $classe = ' class="ls"';
  else $classe = "";
  if (++$n_linha_head % 16 == 0) include('table_head_team_radar.html');

  echo "<tr $classe onmouseover=\"ron(this);\" onmouseout=\"roff(this);\">";
  echo "<td class=\"${line['alerta']}\">",
    is_null($line['days']) ? "" : number_format($line['days'], 0, ".", ","), "</td>";
  echo <<<html
  <td class="${line['alerta']}" style="text-align:center;">
  <script type='text/javascript'>
  document.write(wDate2('{$line['date']}'));
  </script>
  </td>
  <td style="" class="txt $cor7">
  <a href="/tr.php?t={$line['team_number']}" class="$cor7">{$line['team_name']}</a>
html;
  echo "</td>";
  echo "<td>", number_format($line['challengerrank'], 0, ".", ","), "</td>";
  echo "<td>", number_format($line['points_up'], 0, ".", ","), "</td>";
  echo "<td>", number_format($line['points_24'], 0, ".", ","), "</td>";
  echo "<td class=\"$cor7\">", number_format($line['points_7'], 0, ".", ","), "</td>";
  echo "<td>", number_format($line['points_0'], 0, ".", ","), "</td>";
  echo "<td>",
    is_null($line['pontos_up_diff']) ? "" : number_format($line['pontos_up_diff'], 0, ".", ","),
    "</td>";
  echo "<td>",
    is_null($line['pontos_24_diff']) ? "" : number_format($line['pontos_24_diff'], 0, ".", ","),
    "</td>";
  echo "<td>",
    is_null($line['pontos_7_diff']) ? "" : number_format($line['pontos_7_diff'], 0, ".", ","),
    "</td>";
  echo "<td>",
    is_null($line['pontos_0_diff']) ? "" : number_format($line['pontos_0_diff'], 0, ".", ","),
    "</td>";
  echo "</tr>\n";
};
echo '</tbody></table></td></tr></table>';
include './script/entrelacado_ad.html';
?>
<!-- ####################################################### -->
<table class="tabela" cellspacing="0"
  style="margin:15px auto;padding:0;">
<tr>
<td class="top">
<table border="0" cellspacing="0" class="cab top">
<tr>
<td style="font-size:150%;text-align:center;">
<a id="pwa22" class="pwa"
  href="javascript:changeDisplay2('wu2', 'pwa22');void 0;"
  style="background:#ffd800;">
  Active Members</a> -
<a id="pwa12" class="pwa"
  href="javascript:changeDisplay2('pt2', 'pwa12');void 0;">
  New Members</a>
</td>
<td style="text-align:center;">
   <span style="font-size:120%;">
   <a href="javascript:var w = window.open(
   'pop_up_chart.php?chart=tsize&amp;id=<?php echo $team ?>&amp;name=<?php echo urlencode($teamName) ?>',
   '', 'width=770,height=520'); w.focus();" style="background-color:gold;">
      Show Chart</a></span>
</td>
</tr></table></td></tr>
<tr>
<td class="top">
<table cellspacing="0" cellpadding="0" border="0" class="corpo">
  <thead id="th" class="dh">
  <tr>
    <th style="border-left:0;">&nbsp;</th>
    <th style="width:5em;">Mon</th><th style="width:5em;">Tue</th>
    <th style="width:5em;">Wed</th>
    <th style="width:5em;">Thu</th><th style="width:5em;">Fri</th>
    <th style="width:5em;">Sat</th><th style="width:5em;">Sun</th>
    <th class="pt2">Total</th><th class="wu2">Average</th>
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
    am.dia > (select max(data) - (8::text || ' week')::interval from datas)::date
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
      echo '<td class="pt2">',
        $weekTotal == 0 ? '' : number_format($weekTotal, 0), "</td>";
      echo '<td class="wu2">',
        number_format($weekTotalActive / $ocurWeek, 0), "</td>";
      echo "</tr>\n";
      }
    $ocurWeek = 0;
    $weekTotal = 0;
    $weekTotalActive = 0;
    $column = 0;
    $n_linha++;
    if ($n_linha % 2 == 0) $classe = 'class="ls"';
    else $classe = 'class="ln"';
    echo "<tr ",$classe,'>','<td style="text-align:center;">';
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
  echo '<td class="pt2">',
    $points == 0 ? '' : number_format($points, 0), "</td>";
  echo '<td class="wu2">', number_format($active, 0), "</td>";
  }
  for ($i = $column; $i < 7; $i++) {
    echo "<td></td>";
  }
echo '<td class="pt2">',
  $weekTotal == 0 ? '' : number_format($weekTotal, 0), "</td>";
echo '<td class="wu2">',
  $ocurWeek == 0 ? 0 : number_format($weekTotalActive / $ocurWeek, 0),
  "</td>";
echo "</tr>\n";
$n_linha++;
if ($n_linha % 2 == 0) $classe = 'class="ls"';
else $classe = 'class="ln"';
echo "<tr $classe><td>Average:</td>";
for ($i=0; $i<7; $i++) {
  $dowAverage = $ocur [$i] == 0 ? 0 : $dowTotal[$i] / $ocur[$i];
  $weekAverage += $dowAverage;
  echo '<td class="pt2">',
    $dowAverage == 0 ? '' : number_format($dowAverage, 0),
    '</td>';
  $dowAverageActive = $ocur [$i] == 0 ? 0 : $dowTotalActive[$i] / $ocur[$i];
  $weekAverageActive += $dowAverageActive;
  echo '<td class="wu2" >',number_format($dowAverageActive, 0),'</td>';
  }
echo '<td class="pt2">',
  $weekAverage == 0 ? '' : number_format($weekAverage, 0),
  "</td>";
echo '<td class="wu2">',number_format($weekAverageActive /7, 0),'</td>';
echo "</tr>\n";
echo '</tbody></table></td></tr></table>';
?>
<!-- google_ad_section_end -->
<p>&nbsp;</p>
</body></html>

<?php
$f = fopen($filename, 'wb');
if (flock($f, LOCK_EX)) {
  fwrite($f, ob_get_contents());
  flock($f, LOCK_UN);
}
fclose($f);

ob_end_flush();
?>
