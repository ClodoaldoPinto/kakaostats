<?php

if (!isset($_GET['u'])) exit;
$donor = $_GET['u'];
if (preg_match('/\D/', $donor) == 1 || $donor == '') exit;
require './pgsqlserver.php';
$link = pg_connect($conn_string);
$queryDate = "select to_char (last_date, 'YYYY-MM-DD HH24:MI') as date from last_date;";
$resultDate = pg_query($link, $queryDate);
$lineDate = pg_fetch_array($resultDate, NULL, PGSQL_ASSOC);
$date = $lineDate["date"];

$query = 'select n_time as team, usuario_nome as donor_name from usuarios_indice where usuario_serial=' .$donor;
$result = pg_query($link, $query);
$line = pg_fetch_array($result, NULL, PGSQL_ASSOC);
$team = $line ['team'];
$donor_name = $line ['donor_name'];
#------------------------------------
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
<?php include "./meta.html"; ?>
<script type="text/javascript" src="/script/js.js"></script>
<LINK REL=StyleSheet HREF="/script/ks2-2.css" TYPE="text/css" MEDIA=screen>
<LINK REL="SHORTCUT ICON" href="/favicon.ico">
<title>Kakao Stats - <?php echo htmlentities($donor_name, ENT_QUOTES);?>  Milestones</title>
<script type="text/javascript">
if (self != top) top.location.href = location.href;
</script>
<script type="text/javascript" src="/overlib/mini/overlib_mini.js"><!-- overLIB (c) Erik Bosrup --></script>
</head>
<body style="text-align:center;"
  onload="
  document.getElementById('ttu').innerHTML = timeToUpdate(time_of_update);
  window.setInterval('document.getElementById(\'ttu\').innerHTML = timeToUpdate(time_of_update)', 1000);
  ">
<!-- google_ad_section_start(weight=ignore) -->
<div id="overDiv"
  style="position:absolute; visibility:hidden; z-index:1000;">
</div>
<?php
$page_name = 'donor_milestones';
include './menu.php';
?>
<p style="text-align:center;margin:0.5em auto 0.5em auto;font-size:22px;color:floralWhite;">
<?php
  echo htmlentities($donor_name,ENT_QUOTES), " at ",
  "<span style=\"font-size:18px;\">",
  "<script type='text/javascript'>",
  "document.write(wDate('$date')+' '+timeZone());",
  "</script></span>";
?>
</p>
<table class="tabela" style="width:240px;"
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
<th style="border-left:0;" onmouseover="
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
from donors_production as up
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
  dm.date,
  dmr.milestone_points
from (
      select dm.donor, dm.milestone, min(d.data) as date
      from donor_milestones dm
      inner join datas d on d.data_serial = dm.serial_date
      where dm.donor = $donor
      group by dm.donor, dm.milestone
      ) as dm
right outer join donor_milestones_ref as dmr on dm.milestone = dmr.milestone_ref
where dmr.milestone_points < (
  select pontos_0
  from donors_production
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
<!-- google_ad_section_end -->
<?php include "./footer.html"; ?>
</body>
</html>
