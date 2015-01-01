<?php

if (!isset($_GET['t'])) exit;
$team = $_GET['t'];
if (preg_match('/\D/', $team) == 1 || $team == '') exit;
require './pgsqlserver.php';
include 'function_color.php';
$link = pg_connect($conn_string);
$queryDate = "select to_char (last_date, 'YYYY-MM-DD HH24:MI') as date from last_date;";
$resultDate = pg_query($link, $queryDate);
$lineDate = pg_fetch_array($resultDate, NULL, PGSQL_ASSOC);
$date = $lineDate["date"];

$query = "
  select
  time_nome as team_name
  from times_indice
  where n_time = $team
;";
$result = pg_query($link, $query);
$line = pg_fetch_array($result, NULL, PGSQL_ASSOC);
$team_name = $line['team_name'];
#------------------------------------
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html><head>
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
  var show = "table-row";
  if (is_ie) show = "block";
  var noshow = "none";
  var tblChilds = document.getElementById("tp").childNodes;
  for (var i=0; i<tblChilds.length; i++) {
    if (tblChilds[i].nodeType == 1) {
      if (tblChilds[i].className.indexOf('pt') > -1) {
        tblChilds[i].style.display = pwa == "pt" ? show:noshow;
      }
      else
        tblChilds[i].style.display = pwa == "wu" ? show:noshow;
    }
  }
}
</script>
<script type="text/javascript" src="/script/js.js"></script>
<LINK REL=StyleSheet HREF="/script/ks2-2.css" TYPE="text/css" MEDIA=screen>
<LINK REL="SHORTCUT ICON" href="/favicon.ico">
<style type="text/css">
.pwa {color: blue;}
a.pwa:hover {background: #ffd800;}
.pt {display : table-row;}
.wu {display : none;}
th {width: 6em;}
</style>
<title>Kakao Stats - Members Milestones -
  <?php echo htmlentities($team_name, ENT_QUOTES);?></title>
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
$page_name = 'team_milestones';
include './menu.php';
?>
<p style="text-align:center;margin:0 auto 0.5em auto;font-size:22px;color:floralWhite;">
<?php
  echo htmlentities($team_name, ENT_QUOTES);
  echo <<<html
  at
  <span style="font-size:18px;">
  <script type="text/javascript">
  document.write(wDate('$date')+' '+timeZone());
  </script></span>
html;
?>
</p>
<table id="lm" class="tabela" cellspacing="0">
<tr>
<td class=top>
<table cellspacing=0 cellpadding=0 class="cab top">
  <tr>
  <td style="font-size:150%;text-align:center;line-height:200%;">
    <a id="pwa1" class="pwa"
      href="javascript:changeDisplay('pt', 'pwa1');void 0;"
      style="background:#ffd800;">Achieved Milestones</a> -
    <a id="pwa2" class="pwa"
      href="javascript:changeDisplay('wu', 'pwa2');void 0;">
      Target Milestones</a>
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
<th rowspan="2" style="border-left:0;width:9.5em;" onmouseover="
  return overlib('Date in which the milestone was achieved');"
  onmouseout="return nd();"
  >Date</th>
<th rowspan="2" style=""
  onmouseover="return overlib(
    'Team member<br />' +
    'Color mean the weekly points:<br />' +
      '<span class=\'c1\'>Red: 700,000+</span><br/ >' +
      '<span class=\'c2\'>Dark Red: 350,000+</span><br />' +
      '<span class=\'c3\'>Orange: 70,000+</span><br />' +
      '<span class=\'c4\'>Lime: 35,000+</span><br />' +
      '<span class=\'c5\'>Blue: 7,000+</span><br />' +
      '<span class=\'c6\'>Green: 700+</span><br />' +
      '<span class=\'c7\'>Black: 1+</span>'
    );"
  onmouseout="return nd();"
  >Member</th>
<th rowspan="2" onmouseover="return overlib(
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
<th rowspan="2" onmouseover="
  return overlib('Current ranking of in the team');"
  onmouseout="return nd();"
  >Ran-<br />king</th>
<th colspan="4">Points</th></tr>
<tr>
<th onmouseover="
  return overlib('Points scored in the last update');"
  onmouseout="return nd();"
  >Update</th>
<th onmouseover="
  return overlib('Points scored in the last 24 hours');"
  onmouseout="return nd();"
  >24 hrs</th>
<th onmouseover="
  return overlib('Points scored in the last 7 days');"
  onmouseout="return nd();"
  >7 days</th>
<th onmouseover="
  return overlib('Total points');"
  onmouseout="return nd();"
  >Total</th>
</tr>
</thead>
<tbody id="tp">
<?php
$query = <<<query
select
  ui.usuario_nome as donor_name,
  dm.donor as donor_number,
  dmr.milestone_points,
  dm.date,
  up.pontos_0 as points_0,
  up.pontos_up as points_up,
  up.pontos_7 as points_7,
  up.pontos_24 as points_24,
  up.rank_0_time as rank
from (
      select dm.donor, dm.milestone, min(d.data) as date
      from donor_milestones dm
      inner join datas d on d.data_serial = dm.serial_date
      group by dm.donor, dm.milestone
      ) as dm
inner join usuarios_indice as ui on ui.usuario_serial = dm.donor
inner join donor_milestones_ref as dmr on dm.milestone = dmr.milestone_ref
inner join donors_production as up on dm.donor = up.usuario
where ui.n_time = $team and dm.date >= '$date'::timestamp - '30 weeks'::interval
order by dm.date desc
limit 100;
query;
$result = pg_query($link, $query);
$n_linha = 0;
while ($line = pg_fetch_array($result,NULL, PGSQL_ASSOC)) {
  $n_linha++;
  $cor7 = cor_donor($line['points_7']);

  $line['donor_name'] = preg_replace('/([^\A])_([^\Z])/','$1 $2',
      htmlentities($line['donor_name'], ENT_QUOTES));
  $line['donor_name'] = preg_replace('/([^\s]{20}\B)/',
                                     "$1<wbr>&shy;",
                                     $line['donor_name']);
  if ($n_linha % 2 == 0) $classe = 'class="pt ls"';
  else $classe = 'class="pt"';
  echo "<tr ", $classe, " onmouseover='ron(this);' onmouseout='roff(this);'>";
  echo '<td style="text-align:center;">', "<script type=text/javascript>",
    "document.write(wDate2('".$line["date"]."'));",
    "</script>", "</td>";
  echo "<td style='' class='txt $cor7'>";
  echo "<a href=./u.php?u=", $line['donor_number'], " class=", $cor7, ">",
    $line['donor_name'], "</a>";
  echo "</td>";
  echo "<td>", number_format($line['milestone_points'], 0, ".", ","), "</td>";
  echo "<td>", number_format($line['rank'], 0, ".", ","), "</td>";
  echo "<td>", number_format($line['points_up'], 0, ".", ","), "</td>";
  echo "<td>", number_format($line['points_24'], 0, ".", ","), "</td>";
  echo "<td class=", $cor7, ">",
    number_format($line['points_7'], 0, ".", ","), "</td>";
  echo "<td>", number_format($line['points_0'], 0, ".", ","), "</td>";
  echo "</tr>\n";
  }
$query = <<<query
select
  (((dmr.milestone_points - up.pontos_0) / up.pontos_7)::text || ' weeks')::interval +
    (select last_date from last_date) as date,
  ui.usuario_nome as donor_name,
  up.usuario as donor_number,
  dmr.milestone_points,
  up.pontos_0 as points_0,
  up.pontos_up as points_up,
  up.pontos_7 as points_7,
  up.pontos_24 as points_24,
  up.rank_0_time as rank
from donors_production as up
inner join usuarios_indice as ui on ui.usuario_serial = up.usuario
inner join donor_milestones_ref as dmr on dmr.milestone_points > up.pontos_0
where
  ui.n_time = $team and up.pontos_7 > 0
  and
  (((dmr.milestone_points - up.pontos_0) / up.pontos_7)::text ||
    ' weeks')::interval + (select last_date from last_date) <
    (select last_date from last_date) + '30 week'::interval
  and
  dmr.milestone_points = (
    select milestone_points
    from donor_milestones_ref
    where milestone_points > up.pontos_0
    order by milestone_points
    limit 1 )
order by date
limit 100;
query;
$result = pg_query($link, $query);
$n_linha = 0;
while ($line = pg_fetch_array($result,NULL, PGSQL_ASSOC)) {
  $n_linha++;
  $cor7 = cor_donor($line['points_7']);

  $line['donor_name'] = preg_replace('/([^\A])_([^\Z])/','$1 $2',
      htmlentities($line['donor_name'], ENT_QUOTES));
  $line['donor_name'] = preg_replace('/([^\s]{20}\B)/', "$1<wbr>&shy;", $line['donor_name']);

  if ($n_linha % 2 == 0) $classe = 'class="wu ls"';
  else $classe = 'class="wu"';
  echo <<<html
  <tr $classe style="display:none;"
    onmouseover="ron(this);" onmouseout="roff(this);">
  <td style="text-align:center;"><script type="text/javascript">
    document.write(wDate2('{$line["date"]}'));
    </script></td>
  <td style="" class="txt $cor7">
html;
  echo "<a href=./u.php?u=", $line['donor_number'], " class=", $cor7, ">",
    $line['donor_name'], "</a>";
  echo "</td>";
  echo "<td>", number_format($line['milestone_points'], 0, ".", ","), "</td>";
  echo "<td>", number_format($line['rank'], 0, ".", ","), "</td>";
  echo "<td>", number_format($line['points_up'], 0, ".", ","), "</td>";
  echo "<td>", number_format($line['points_24'], 0, ".", ","), "</td>";
  echo "<td class=\"$cor7\">",
    number_format($line['points_7'], 0, ".", ","), "</td>";
  echo "<td>", number_format($line['points_0'], 0, ".", ","), "</td>";
  echo "</tr>\n";
  }
?>
</tbody>
</table></td></tr></table>
<!-- google_ad_section_end -->
<?php include "./footer.html"; ?>
</body>
</html>
