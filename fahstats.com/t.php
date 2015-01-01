<?php
if (!isset($_GET['t'])) exit(0);
$team = $_GET['t'];
if (preg_match('/\D/', $team) == 1 or $team == '') exit(0);

$filter = '';
if (isset($_GET['filter'])) $filter = $_GET['filter'];
elseif (isset($_COOKIE['team_filter'])) $filter = $_COOKIE['team_filter'];
$filter = strtolower($filter);
if ($filter != 'active' && $filter != 'inactive' && $filter != 'new' && $filter != 'all') $filter = 'active';
setcookie('team_filter', $filter, time()+60*60*24*365);

$col = isset($_GET['col']) ? $_GET['col'] : '';
if ($col < 0 || $col == '' || preg_match('/\D/', $col) == 1) $col = 2;
$offset = isset($_GET['offset']) ? $_GET['offset'] : 0;
if ($offset < 0 || $offset == "" || preg_match('/\D/', $offset) == 1) $offset = 1;

$search_text = isset($_GET['search_text']) ? trim($_GET['search_text']) : '';
$search = $search_text != '' ? True : False;

include ('function_color.php');
require './pgsqlserver.php';
$link = pg_connect($conn_string);

$query = <<<query
select
    to_char (last_date, 'YYYY-MM-DD HH24:MI:SS') as "date",
    (select extract(epoch from "datetime") as unixtimestamp
      from processing_end) as unixtimestamp,
    extract(epoch from(select "datetime" from processing_end) +
    '3 hours 3 minutes'::interval - current_timestamp(0)
     )::bigint * 1000 as ttu
from last_date;
query;

$result = pg_query($link, $query);
$row = pg_fetch_row($result);
$updateDate = $row[0];

$update_unix_timestamp = $row[1];
$ttu = $row[2];
$filename = "./html_file_cache/team.html?t=$team&col=$col&offset=$offset&filter=$filter";

if (file_exists($filename) && !$search) {
  if (filemtime($filename) >= $update_unix_timestamp) {
    $file_string = file_get_contents($filename);
    echo(str_replace('var time_to_update = ',
                     'var time_to_update = '.$ttu.';// ', $file_string));
    exit(0);
  }
}

$offset--;
$row_count = 200;
switch ($col) {
  case 1:
    $order = "name";
    break;
  case 2:
    $order = "rank_0_team";
    break;
  case 3:
    $order = "rank_24_team";
    break;
  case 4:
    $order = "rank_7_team";
    break;
  case 5:
    $order = "rank_30_team";
    break;
  case 6:
    $order = "rank_0";
    break;
  case 7:
    $order = "rank_24";
    break;
  case 8:
    $order = "rank_7";
    break;
  case 9:
    $order = "rank_30";
    break;
  case 10:
    $order = "points_24 desc, rank_0_team";
    break;
  case 11:
    $order = "points_7 desc, rank_0_team";
    break;
  case 13:
    $order = "points_up desc, rank_0_team";
    break;
  default:
    $order = "points_0 desc, points_7 desc";
    break;
  }
$query = "
select time_nome as name
from times_indice where n_time = $team
;
";
$result = pg_query($link, $query);
$line = pg_fetch_array($result, NULL, PGSQL_ASSOC);
$team_name = $line ['name'];

ob_start();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN"
   "http://www.w3.org/TR/html4/strict.dtd">
<html><head>
<?php require "./meta.html"; ?>
<script type="text/javascript">
if (self != top) top.location.href = location.href;
th_classificado = <?php echo $col?>;

function sload(col) {
  var filter = document.getElementById('filter').value;
  filter = filter == 'active' ? '' : '&filter=' + filter;
  var search = encodeURIComponent(document.getElementById('search_text').value);
  search = search.replace(/(\%20)+/g, '') != '' ? '&search_text=' +
    search.replace(/^(\%20)+|(\%20)+$/g, '') : '';
  var url = '/t.php?col=' + col +
    '&t=' + <?php echo $team ?> + search + filter;
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
  //ca();
  document.getElementById('ttu').innerHTML = timeToUpdate(time_of_update);
  window.setInterval('document.getElementById(\'ttu\').innerHTML = timeToUpdate(time_of_update)', 1000);
 ">
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>
<?php
$page_name = 'team_members';
include './menu.php';

$search_query = $search ?
  " and lower(d.usuario_nome) like '%".
  pg_escape_string(mb_strtolower(mb_convert_encoding(urldecode($search_text), 'ISO-8859-1', 'UTF-8'))).
  "%' " : ' ';

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
if ($search)
$query = <<<query
select count (*)
from donors_production t
inner join usuarios_indice d on d.usuario_serial = t.usuario
$filter_join
where t.n_time = $team and $filter_where
$search_query
;
query;
else
$query = <<<query
select count (*)
from donors_production t
$filter_join
where t.n_time = $team and $filter_where
$search_query;
query;

$result = pg_query($link, $query);
$nLines = pg_fetch_row($result);
$nLines = $nLines [0];

$search_box_text = "Member";
require './team_search.php';

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
    pontos_7 as points_7,
    pontos_up as points_up
from donors_production as t
inner join usuarios_indice as d on t.usuario=d.usuario_serial
$filter_join
where t.n_time = $team and $filter_where
  $search_query
order by $order limit $row_count OFFSET $offset
;
query;
$result = pg_query($link, $query);
?>
<table class="tabela" cellspacing="0" style="margin:5px auto 15px;">
<?php require "./teamPager.php"; ?>
<tr>
<td class="top">
<table cellspacing="0" class="cab top">
<tr>
<td style="width=90%;text-align:center;font-size:150%;margin-right:0;
  padding:0.3em 0;line-height:150%;border-bottom:1px solid black;" class="cab">
  <span style="font-weight:bold;"><?php echo htmlentities($team_name,ENT_QUOTES) ?></span>'s
  <?php echo $filter ?> Members at
  <script type="text/javascript">
    document.write(wDate('<?php echo $updateDate ?>')+' '+timeZone());
  </script>
</td>
<td style="text-align:center;border-bottom:1px solid black;" class="cab">
  <a href="/tsum.php?t=<?php echo $team ?>" style="color:blue;background-color:gold;font-size:120%;">
  Team Summary</a>
</td>
</tr>

</table></td></tr>
<tr><td class="top">
<table cellspacing="0" class="corpo">
<thead><?php include('table_head_team_members_list.html'); ?></thead>
<tbody id="tdados" class="tcorpo">
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
echo <<<html
<tr class="ln">
<td></td>
<td class="txt" style="font-weight:bold;">$team_name</td>
<td></td>
<td></td>
<td></td>
<td></td>
html;
echo '<td>', number_format($line['rank_0'], 0, '.', ','), '</td>';
echo '<td>', number_format($line['rank_24'], 0, '.', ','), '</td>';
echo '<td>', number_format($line['rank_7'], 0, '.', ','), '</td>';
echo '<td>', number_format($line['rank_30'], 0, '.', ','), '</td>';
echo '<td>', number_format($line['points_up'], 0, '.', ','), '</td>';
echo '<td>', number_format($line['points_24'], 0, '.', ','), '</td>';
echo '<td>', number_format($line['points_7'], 0, '.', ','), '</td>';
echo '<td>', number_format($line['points_0'], 0, '.', ','), '</td>';
echo "</tr>\n";

$n_linha = $offset;
$n_linha_off = 0;
$n_linha_cor = 0;
$j = 0;
while ($line = pg_fetch_array($result, NULL, PGSQL_ASSOC)) {
  if ($n_linha_off % 17 == 0 && $n_linha_off > 0) {
    /*
    if ($j % 2 == 0 && $row_count - $n_linha_off >= 15) {
      echo '<tr class="ad"><td colspan="14">';
      echo '<div class="ad" id="ad_div_'.($j).'">';
      echo '</div></td></tr>', "\n";
    }
    $j++;*/
    if (($n_linha_off < 20 || $n_linha_off > 70 && $n_linha_off < 110)
        && $j % 2 == 0 && $row_count - $n_linha_off >= 17) {
      echo '<tr class="ad"><td colspan="14" style="text-align:center;">';
      include './script/tabela_ad.html';
      echo '</td></tr>', "\n";
    }
    $j++;

    include('table_head_team_members_list.html');
    $n_linha_cor = 0;
  }
  if ($n_linha_cor % 2 == 0) $classe = 'class="ls"';
  else $classe = 'class="ln"';
  $n_linha_cor++;
  $n_linha_off++;
  $n_linha++;

  $cor7 = cor_donor($line['points_7']);

   $line['name'] = preg_replace(
     '/([^\s]{10}\B)/S','$1<wbr>&shy;',
     preg_replace('/([^\A])_([^\Z])/S','$1 $2', $line['name'])
     );
   $line['name'] = str_replace(
     '&lt;wbr&gt;&amp;shy;','<wbr>&shy;',
     htmlentities($line['name'], ENT_QUOTES)
     );

  echo "<tr $classe>";
  echo "<td>", number_format($n_linha, 0, ".", ","), "</td>";

  echo "<td class=\"txt $cor7\">";
  echo "<a class=\"$cor7\" href=\"/usum.php?u={$line['number']}\">{$line['name']}</a></td>";

  echo "<td>", number_format($line['rank_0_team'], 0, ".", ","), "</td>";
  echo "<td";
  if ($line['rank_24_team'] < $line['rank_0_team']) echo ' class="g"';
  elseif ($line['rank_24_team'] > $line['rank_0_team']) echo ' class="r"';
  echo ">", number_format($line['rank_24_team'], 0, ".", ","), "</td>";
  echo "<td";
  if ($line['rank_7_team'] < $line['rank_0_team']) echo ' class="g"';
  elseif ($line['rank_7_team'] > $line['rank_0_team']) echo ' class="r"';
  echo ">", number_format($line['rank_7_team'], 0, ".", ","), "</td>";
  echo "<td";
  if ($line['rank_30_team'] < $line['rank_0_team']) echo ' class="g"';
  elseif ($line['rank_30_team'] > $line['rank_0_team']) echo ' class="r"';
  echo ">", number_format($line['rank_30_team'], 0, ".", ","), "</td>";

  echo "<td>", number_format($line['rank_0'], 0, ".", ","), "</td>";
  echo "<td";
  if ($line['rank_24'] < $line['rank_0']) echo ' class="g"';
  elseif ($line['rank_24'] > $line['rank_0']) echo ' class="r"';
  echo ">", number_format($line['rank_24'], 0, ".", ","), "</td>";
  echo '<td';
  if ($line['rank_7'] < $line['rank_0']) echo ' class="g"';
  elseif ($line['rank_7'] > $line['rank_0']) echo ' class="r"';
  echo ">", number_format($line['rank_7'], 0, ".", ","), "</td>";
  echo "<td";
  if ($line['rank_30'] < $line['rank_0']) echo ' class="g"';
  elseif ($line['rank_30'] > $line['rank_0']) echo ' class="r"';
  echo ">", number_format($line['rank_30'], 0, ".", ","), "</td>";
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
<?php require "./teamPager.php";
echo '</table>';
include './script/adsense_bottom.js';
?>
<p>&nbsp</p>
</body>
</html>

<?php
$text = ob_get_flush();

if (!$search) {
  $f = fopen($filename, 'wb');
  if (flock($f, LOCK_EX)) {
    fwrite($f, $text);
    flock($f, LOCK_UN);
  }
  fclose($f);
}

?>
