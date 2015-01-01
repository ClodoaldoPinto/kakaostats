<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<?php

if (!isset($_GET['t'])) exit;
$team = $_GET['t'];
if (preg_match('/\D/', $team) == 1 or $team == '') exit;
require './pgsqlserver.php';
include 'function_color.php';

$filter = '';
if (isset($_GET['filter'])) $filter = $_GET['filter'];
elseif (isset($_COOKIE['team_filter'])) $filter = $_COOKIE['team_filter'];
$filter = strtolower($filter);
if ($filter != 'active' && $filter != 'inactive' && $filter != 'new' && $filter != 'all') $filter = 'active';
setcookie('team_filter', $filter, time()+60*60*24*365);

$col = isset($_GET['col']) ? $_GET['col'] : "";
if ($col < 0 || $col == "" || preg_match('/\D/', $col) == 1) $col = 12;
$offset = isset($_GET['offset']) ? $_GET['offset'] : 0;
if ($offset < 0 || $offset == "" || preg_match('/\D/', $offset) == 1) $offset = 1;
$offset--;
$row_count = 250;
switch ($col) {
  case 1:
    $order = "name";
    break;
  case 2:
    $order = "member_total desc, points_0 desc, points_7 desc";
    break;
  case 10:
    $order = "points_24 desc, points_0 desc, points_7 desc";
    break;
  case 11:
    $order = "points_7 desc, points_0 desc, points_24 desc";
    break;
  case 13:
    $order = "points_up desc, points_0 desc, points_7 desc, points_24 desc";
    break;
  default:
    $order = "points_0 desc, points_7 desc, points_24 desc";
    break;
  }
$link = pg_connect($conn_string);
$query = "select time_nome as name from times_indice where n_time = $team;";
$result = pg_query($link, $query);
$line = pg_fetch_array($result, NULL, PGSQL_ASSOC);
$team_name = $line ['name'];
?>
<html><head>
<?php require "./meta.html"; ?>
<script type="text/javascript">
if (self != top) top.location.href = location.href;
th_classificado = <?php echo $col?>;

function sload(col) {
  var filter = document.getElementById('filter').value;
  filter = filter == 'active' ? '' : '&filter=' + filter;
  var url = '/subteam.php?col=' + col +
    '&t=' + <?php echo $team ?> + filter;
  location.href = url;
}
</script>
<script type="text/javascript" src="/script/js.js"></script>
<script type="text/javascript">
function mOverCab(cab) {
  tira_cor();
  cab.style.backgroundColor='#ffd800';
  cab.style.cursor="pointer";
  }
function mOutCab(cab) {
  coloca_cor();
  if (cab.getAttribute('name') != 'th' + th_classificado) cab.style.backgroundColor='';
  cab.style.cursor='';
  }
function tira_cor() {
  for (var i=0, len=th_list_class.length; i < len; ++i)
    th_list_class[i].style.backgroundColor='';
  }
function coloca_cor() {
  for (var i=0, len=th_list_class.length; i < len; ++i)
    th_list_class[i].style.backgroundColor='#ffd800';
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
  var th_list = document.getElementsByTagName('th');
  th_list_class = [];
  var j = 0;
  for (var i=0, len=th_list.length; i < len; ++i) {
    if (th_list.item(i).getAttribute('name') == 'th<?php echo $col?>') {
      th_list_class[j] = th_list.item(i);
      th_list_class[j].style.background = '#ffd800';
      j++;
      }
    }
  document.getElementById('ttu').innerHTML = timeToUpdate(time_of_update);
  window.setInterval('document.getElementById(\'ttu\').innerHTML = timeToUpdate(time_of_update)', 1000);
">
<!-- google_ad_section_start(weight=ignore) -->
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>
<?php
$page_name = 'team_subteam';
include './menu.php';
echo '<p style="height:15px;margin:0 auto;"></p>';

$query = <<<query
select to_char (last_date, 'YYYY-MM-DD HH24:MI') as date
from last_date;
query;
$result = pg_query ($link, $query);
$line = pg_fetch_array ($result, NULL, PGSQL_ASSOC);
$date = $line ["date"];

$filter_join = '';
if ($filter == 'active') $filter_where = 'active';
elseif ($filter == 'inactive') $filter_where = 'not active';
elseif ($filter == 'all') $filter_where = 'True';
else { # $filter == 'new'
  $filter_join = <<<filter
  inner join donor_first_wu as dfw
  on dfw.donor = t.usuario
filter;
  $filter_where = <<<filter
  dfw.data >= (select data from datas order by data desc limit 1) -
              '14 days'::interval
filter;
}

if ($team == 51)	$case_sensitive = 'false'; // Alliance Francophone
else $case_sensitive = 'true';

if ($team == 92) $subteam_regex = '(^.*?)0'; // Dutch Power Cows
else $subteam_regex = '\\\\[.*\\\\]';

$query = <<<query
select count(*)
from (
select subteam_group_name(d.usuario_nome, E'$subteam_regex', $case_sensitive) as name
from donors_production as t
inner join usuarios_indice as d on t.usuario=d.usuario_serial
$filter_join
where t.n_time = $team and $filter_where
group by name
) as a
query;

$result = pg_query($link, $query);
$nLines = pg_fetch_row($result);
$nLines = $nLines [0];

require './subteam_filter.php';

$query = <<<query
select
  count(*) as member_total,
  subteam_group_name(d.usuario_nome, E'$subteam_regex', $case_sensitive) as name,
  sum(pontos_0) as points_0,
  sum(pontos_24) as points_24,
  sum(pontos_7) as points_7,
  sum(pontos_up) as points_up
from donors_production as t
inner join usuarios_indice as d on t.usuario=d.usuario_serial
$filter_join
where t.n_time = $team and $filter_where
group by name
order by $order limit $row_count OFFSET $offset;
query;
//echo $query;
$result = pg_query($link, $query);
?>
<table class=tabela cellspacing="0" style="margin:15px auto;">
<?php require "./subteam_pager.php"; ?>
<tr>
<td style="" class=top>
<table cellspacing="0" class="cab top">
<tr>
<td style="text-align:center;font-size:150%;margin-right:0;padding:0.3em 0;line-height:150%;" class=cab>
  <span style="font-weight:bold;"><?php echo htmlentities($team_name,ENT_QUOTES) ?></span>'s Subteams View at
  <script type='text/javascript'>
    document.write(wDate('<?php echo $date ?>')+' '+timeZone());
  </script>
</td>
</tr>
</table></td></tr>
<tr><td class=top>
<table cellspacing=0 class=corpo>
<thead><?php include('table_head_subteam.html'); ?></thead>
<tbody id=tdados class=tcorpo>
<?php
$query = <<<query
select
  pontos_0 as points_0,
  pontos_24 as points_24,
  pontos_7 as points_7,
  pontos_up as points_up,
  rank_0,
  rank_7,
  rank_24,
  rank_30
from teams_production
where n_time = $team
query;
$line = pg_fetch_array(pg_query($link, $query), NULL, PGSQL_ASSOC);

$team_name = preg_replace(
      '/([^\s]{18}\B)/S','$1<wbr>&shy;',
      preg_replace('/([^\A])_([^\Z])/S','$1 $2', $team_name)
      );
$team_name = str_replace(
      '&lt;wbr&gt;&amp;shy;','<wbr>&shy;',
      htmlentities($team_name, ENT_QUOTES)
      );

$points_7_team = $line['points_7'];
$points_up_team = $line['points_up'];
$points_24_team = $line['points_24'];
$points_0_team = $line['points_0'];
echo '<tr onmouseover="ron(this);" onmouseout="roff(this);">';
echo '<td></td>';
echo '<td>', number_format($nLines, 0, ".", ","), '</td>';
echo '<td class="txt" style="font-weight:bold;">', $team_name, '</td>';
echo '<td>', number_format($line['points_up'], 0, '.', ','), '</td>';
echo '<td>', number_format($line['points_24'], 0, '.', ','), '</td>';
echo '<td>', number_format($line['points_7'], 0, '.', ','), '</td>';
echo '<td>', number_format($line['points_0'], 0, '.', ','), '</td>';
echo "</tr>\n";
echo '<tr class="ls">';
echo '<td style="line-height:0.1em;">&nbsp;</td>';
echo '<td></td>';
echo '<td></td>';
echo '<td></td>';
echo '<td></td>';
echo '<td></td>';
echo '<td></td>';
echo "</tr>\n";

$n_linha = $offset;
$n_linha_head = 0;
while ($line = pg_fetch_array($result, NULL, PGSQL_ASSOC)) {
  if (++$n_linha % 2 == 0) $classe = "class=ls";
  else $classe = "";
  if (++$n_linha_head % 18 == 0) include('table_head_subteam.html');
  $cor7 = cor_donor($line['points_7']);
  $name = preg_replace('/([^\A])_([^\Z])/','$1 $2',
      htmlentities($line['name'], ENT_QUOTES));
  $name = preg_replace('/([^\s]{10}\B)/', "$1<wbr>&shy;", $name);
  echo "<tr $classe onmouseover='ron(this);' onmouseout='roff(this);'>";
  echo "<td>", number_format($n_linha, 0, ".", ","), "</td>";

  echo "<td>", number_format($line['member_total'], 0, ".", ","), "</td>";

  echo "<td class=\"txt $cor7\">";
  echo '<a class="'.$cor7.'" href="/t.php?t='.$team.'&amp;search_text='.rawurlencode($line['name']).'&amp;filter='.$filter.'">',
    $name, "</a></td>";

  $permil = $points_up_team == 0 ? '' : number_format($line['points_up'] * 1000 / $points_up_team, 3) . ' &permil;';
  echo "<td ",
  	 $points_up_team == 0 ? '' : 'onmouseover="return overlib(\''.$permil.'\', WIDTH, 80)" onmouseout="return nd()"', '>',
    number_format($line['points_up'], 0, ".", ","),
    "</td>";
  $permil = $points_24_team == 0 ? '' : number_format($line['points_24'] * 1000 / $points_24_team, 3) . ' &permil;';
  echo "<td ",
  	 $points_24_team == 0 ? '' : 'onmouseover="return overlib(\''.$permil.'\', WIDTH, 80)" onmouseout="return nd()"', '>',
    number_format($line['points_24'], 0, ".", ","),
    "</td>";
  $permil = $points_7_team == 0 ? '' : number_format($line['points_7'] * 1000 / $points_7_team, 3) . ' &permil;';
  echo '<td class="'.$cor7.'" ',
  	 $points_7_team == 0 ? '' : 'onmouseover="return overlib(\''.$permil.'\', WIDTH, 80)" onmouseout="return nd()"', '>',
    number_format($line['points_7'], 0, ".", ","),
    "</td>";
  $permil = $points_0_team == 0 ? '' : number_format($line['points_0'] * 1000 / $points_0_team, 3) . ' &permil;';
  echo '<td ',
  	 $points_0_team == 0 ? '' : 'onmouseover="return overlib(\''.$permil.'\', WIDTH, 80)" onmouseout="return nd()"', '>',
    number_format($line['points_0'], 0, ".", ","), "</td>";
  echo "</tr>\n";
  }
?>
</tbody>
</table>
</tr>
<?php require "./subteam_pager.php"; ?>
</table>
<!-- google_ad_section_end -->
<?php include './script/adsense_bottom.js'; ?>
<p>&nbsp;</p>
</body>
</html>
