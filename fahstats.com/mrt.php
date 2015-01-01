<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<?php
function format_mil($number) {
	if ($number == 0) return "";
	else return number_format($number, 0, ".", ",");
}
function pager($nLines, $row_count, $offset, $col, $team, $quarterYear) {
   for ($i = 0; $i < ((int)(($nLines/$row_count)+(($nLines%$row_count)>0?1:0))); $i++) {
  		if ($i +1 == ((int)(($row_count+$offset)/$row_count))) echo $i + 1, " ";
  		else {
    		echo "<a class=pghref href='/mrt.php?col=".$col.
      	"&amp;offset=".($row_count * $i +1).
         "&amp;t=".$team."&amp;quarterYear=".$quarterYear.
      	"'>";
    	echo $i + 1, "</a> ";
  		}
	}
}

if (!isset($_GET['t'])) exit;
$team = $_GET['t'];
if (preg_match('/\D/', $team) or $team == '') exit;
require './pgsqlserver.php';
$link = pg_connect($conn_string);

$month_names = array('January','February','March','April','May','June','July','August','September','October','November','December');
$query = <<<query
select to_char (last_date, 'YYYY-MM-DD HH24:MI') as date
from last_date;
query;
$result = pg_query ($link, $query);
$line = pg_fetch_array ($result, NULL, PGSQL_ASSOC);
$date = $line ["date"];

$query = <<<query
select coalesce(dyf.year, s.year) as "year",
   array[
      (select case when (months && array[1,2,3]::smallint[] or s.quarter = 1)
         then 1 end)]
   || array[
      (select case when (months && array[4,5,6]::smallint[] or s.quarter = 2)
         then 2 end)]
   || array[
      (select case when (months && array[7,8,9]::smallint[] or s.quarter = 3)
         then 3 end)]
   || array[
      (select case when (months && array[10,11,12]::smallint[] or s.quarter = 4)
         then 4 end)]
   as months
from donor_yearly_fill as dyf
full outer join
   (
   select extract(year from last_date)::smallint as "year",
      extract(quarter from last_date) as "quarter"
   from last_date
   ) as s
on dyf.year = s.year
order by year desc
query;
$result = pg_query ($link, $query);
$aQuarter = array();
while ($line = pg_fetch_array($result, NULL, PGSQL_ASSOC)) {
	$quarters = explode(',', trim(str_replace('NULL', '', $line['months']), '{},'));
	rsort($quarters, SORT_NUMERIC);
	$aQuarter[$line['year']] = $quarters;
}
$years = array_keys($aQuarter);
$quarterYear = '';
if (isset($_GET['quarterYear'])) $quarterYear = $_GET['quarterYear'];
#elseif (isset($_COOKIE['team_quarterYear'])) $quarterYear = $_COOKIE['team_quarterYear'];
$quarterYear = strtolower($quarterYear);
if (!preg_match('/^\d+$/', $quarterYear)) {
	$quarterYear = $years[0] . $aQuarter[$years[0]][0];
}
#setcookie('team_quarterYear', $quarterYear, time()+60*60*24*365);

$quarter = substr($quarterYear, 4, 2);
$year = substr($quarterYear, 0, 4);

$col = isset($_GET['col']) ? $_GET['col'] : "";
if ($col < 0 || preg_match('/\D/', $col) == 1) $col = 5;
$cur_month = (int)substr($date, 5, 2);
if ($col == '' && $year == substr($date, 0, 4)
    && $quarter *3 >= $cur_month
    && $quarter * 3 -3 < $cur_month) $col = $cur_month - (($quarter -1) *3) +1;
elseif ($col == '') $col = 5;
$offset = isset($_GET['offset']) ? $_GET['offset'] : 0;
if ($offset < 0 || $offset == "" || preg_match('/\D/', $offset) == 1) $offset = 1;
$offset--;
$row_count = 250;
switch ($col) {
  case 1:
    $order = "name";
    break;
  case 2:
    $order = "coalesce(p1, -1) desc, tr1, qt desc";
    break;
  case 3:
    $order = "coalesce(p2, -1) desc, tr2, qt desc";
    break;
  case 4:
    $order = "coalesce(p3, -1) desc, tr3, qt desc";
    break;
  case 5:
    $order = "qt desc";
    break;
  default:
    $order = "qt desc";
    break;
  }
$query = "select time_nome as name from times_indice where n_time = $team;";
$result = pg_query($link, $query);
$line = pg_fetch_array($result, NULL, PGSQL_ASSOC);
$team_name = $line ['name'];
?>
<html><head>
<?php require "./meta.html"; ?>
<script type="text/javascript">
if (self != top) top.location.href = location.href;
c_order = <?php echo '"c'.$col.'"'?>;
</script>
<script type="text/javascript" src="/script/js.js"></script>
<script type="text/javascript">
function sload(col) {
  var quarterYear = document.getElementById('quarterYear').value;
  var url = '/mrt.php?col=' + col +
    '&t=' + <?php echo $team ?> + '&quarterYear=' + quarterYear;
  location.href = url;
}
function mOver(name) {
	if (name == c_order) return;
   cor_off();
   var node = thNodes[name];
   for (var i = node.length -1; i >= 0; --i) {
      node[i].style.backgroundColor='#ffd800';
      node[i].style.cursor="pointer";
   }
}
function mOut(name) {
   if (name == c_order) return;
   cor_on();
   var node = thNodes[name];
   for (var i = node.length -1; i >= 0; --i) {
      node[i].style.backgroundColor="";
      node[i].style.cursor="";
   }
}
function cor_off() {
   var node = thNodes[c_order];
   for (var i = node.length -1; i >= 0; --i) {
      node[i].style.backgroundColor="";
   }
}
function cor_on() {
   var node = thNodes[c_order];
   for (var i = node.length -1; i >= 0; --i) {
  		node[i].style.backgroundColor='#ffd800';
   }
}
</script>
<LINK REL=StyleSheet HREF="/script/ks2-2.css" TYPE="text/css" MEDIA=screen>
<LINK REL="SHORTCUT ICON" href="/favicon.ico">
<link rel="icon" href="/favicon.ico" type="image/x-icon">
<title>Kakao Stats - Team Members - <?php echo htmlentities($line['name'], ENT_QUOTES);?></title>
<script type="text/javascript" src="/overlib/mini/overlib_mini.js"><!-- overLIB (c) Erik Bosrup --></script>
</head>
<body style="text-align:center;"
   onload="
   thNodes = new Array();
   var elements = document.getElementsByTagName('th');
   for (var i = elements.length -1; i >= 0; --i) {
      var node = elements[i];
      if (node.getAttribute('name') == undefined) continue;
      if (thNodes[node.getAttribute('name').substring(0,2)] == undefined)
      	thNodes[node.getAttribute('name').substring(0,2)] = new Array();
      thNodes[node.getAttribute('name').substring(0,2)][thNodes[node.getAttribute('name').substring(0,2)].length] = node;
   }
   cor_on();
   document.getElementById('ttu').innerHTML = timeToUpdate(time_of_update);
   iid = window.setInterval('document.getElementById(\'ttu\').innerHTML = timeToUpdate(time_of_update)', 1000);
">
<!-- google_ad_section_start(weight=ignore) -->
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>
<?php
$page_name = 'team_monthly';
include './menu.php';
?>
<p style="margin:0.5em auto 0;">&nbsp;</p>
<?php
include './quarter_search.php';

$quarter = substr($quarterYear, 4, 2);
$year = substr($quarterYear, 0, 4);
$query = <<<query
select *
from donor_quarterly_rank_query_count($quarter, $team, $year)
query;
$result = pg_query($link, $query);
$nLines = pg_fetch_result($result, 0, 0);

$query = <<<query
select donor, name, p1, p2, p3, tr1, tr2, tr3, pr1, pr2, pr3, qt
from donor_quarterly_rank_query($quarter, $team, $year)
order by $order
limit $row_count OFFSET $offset;
query;
$result = pg_query($link, $query);
?>
<table class=tabela cellspacing="0" style="margin:15px auto;">
<tr><td style="" class=paginador>Page
<?php pager($nLines, $row_count, $offset, $col, $team, $quarterYear); ?>
</td></tr>
<tr>
<td style="" class=top>
<table cellspacing="0" class="cab top">
<tr>
<td style="text-align:center;font-size:150%;margin-right:0;padding:0.3em 0;line-height:150%;" class=cab>
  <span style="font-weight:bold;"><?php echo htmlentities($team_name,ENT_QUOTES) ?></span>'s
  Monthly Rank at
  <script type='text/javascript'>
    document.write(wDate('<?php echo $date ?>')+' '+timeZone());
  </script>
</td>
</tr>
</table></td></tr>
<tr><td class=top>
<table cellspacing=0 class=corpo>
<thead><?php include 'table_head_mrt.html'; ?></thead>
<tbody id=tdados class=tcorpo>
<?php

$n_linha = $offset;
$n_linha_head = 0;
while ($line = pg_fetch_array($result, NULL, PGSQL_ASSOC)) {
  if (++$n_linha % 2 == 0) $classe = "class=ls";
  else $classe = "";
  if (++$n_linha_head % 18 == 0) include('table_head_mrt.html');

  $cor7 = "c7";
  $line['name'] = preg_replace('/([^\A])_([^\Z])/','$1 $2',
      htmlentities($line['name'], ENT_QUOTES));
  $line['name'] = preg_replace('/([^\s]{10}\B)/', "$1<wbr>&shy;", $line['name']);

  echo "<tr $classe onmouseover='ron(this);' onmouseout='roff(this);'>";
  echo "<td>", format_mil($n_linha), "</td>";

  echo "<td class=\"txt $cor7\">";
  echo "<a class=$cor7 href=\"/usum.php?u=".$line['donor']."\">",
    $line['name'], "</a></td>";

  echo "<td>", format_mil($line['tr1']), "</td>";
  echo "<td>", format_mil($line['pr1']), "</td>";
  echo "<td>", format_mil($line['p1']), "</td>";
  echo "<td>", format_mil($line['tr2']), "</td>";
  echo "<td>", format_mil($line['pr2']), "</td>";
  echo "<td>", format_mil($line['p2']), "</td>";

  echo "<td>", format_mil($line['tr3']), "</td>";
  echo "<td>", format_mil($line['pr3']), "</td>";
  echo "<td>", format_mil($line['p3']), "</td>";
  echo "<td>", format_mil($line['qt']), "</td>";
  echo "</tr>\n";
  }
?>
</tbody>
</table>
</tr>
<tr><td style="" class=paginador>Page
<?php pager($nLines, $row_count, $offset, $col, $team, $quarterYear) ?>
</td></tr>
</table>
<!-- google_ad_section_end -->
<?php include './script/adsense_bottom.js'; ?>
<p>&nbsp;</p>
</body>
</html>
