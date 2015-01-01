<?php

$col = isset($_GET['col']) ? $_GET['col'] : '';
if ($col == '') $col = 6;
if ($col >= 2 && $col <= 5) $where = "ad.active = '1'";
else $where = "ad.active = '1'";

$offset = isset($_GET['offset']) ? $_GET['offset'] : 0;
if ($offset < 0 || $offset == '' || preg_match('/\D/', $offset) == 1) $offset = 1;

$search_text = isset($_GET['search']) ? $_GET['search'] : '';
$search = trim($search_text) != '' ? True : False;

require './pgsqlserver.php';
$link = pg_connect($conn_string);

include ('function_color.php');

$query = <<<query
select to_char (last_date, 'YYYY-MM-DD HH24:MI:SS') as date,
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
$filename = './html_file_cache/ad.html?col='.$col.'&offset='.$offset;

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
    $order = "di.usuario_nome, ad.rank_0";
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
    $order = "ad.rank_0";
    break;
  case 7:
    $order = "ad.rank_24";
    break;
  case 8:
    $order = "ad.rank_7";
    break;
  case 9:
    $order = "ad.rank_30";
    break;
  case 10:
    $order = "ad.pontos_24 desc, ad.rank_0, ad.pontos_7 desc";
    break;
  case 14:
    $order = "ad.pontos_up desc, ad.rank_0, ad.pontos_7 desc";
    break;
  case 11:
    $order = "ad.pontos_7 desc, ad.rank_0, ad.pontos_24 desc";
    break;
  case 13:
    $order = "ti.time_nome, ad.rank_0";
    break;
  default:
    $order = "ad.rank_0";
    break;
  }
ob_start();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html><head>
<script type="text/javascript">th_classificado = <?php echo $col?>;</script>
<script type="text/javascript" src="/script/js.js"></script>
<LINK REL=StyleSheet HREF="/script/ks2-2.css" TYPE="text/css" MEDIA=screen>
<LINK REL="SHORTCUT ICON" href="/favicon.ico">
<title>Kakao Stats - All Donors</title>
<script type="text/javascript">
if (self != top) top.location.href = location.href;
function sload(col) {
  var search = encodeURIComponent(document.getElementById('search_text').value);
  search = search.replace(/(\%20)+/g, '') != '' ? '&search=' +
    search.replace(/^(\%20)+|(\%20)+$/g, '') : '';
  var url = '/ad.php?col=' + col + search;
  location.href = url;
}
function load(col) {
  var search = encodeURIComponent(document.getElementById('search_text').value);
  search = search.replace(/(\%20)+/g, '') != '' ? '&search=' +
    search.replace(/^(\%20)+|(\%20)+$/g, '') : '';
  var url = '/ad.php?col=' + col + search;
  location.href = url;
}
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
<script type="text/javascript" src="./overlib/mini/overlib_mini.js"><!-- overLIB (c) Erik Bosrup --></script>
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
  //ca();
">
<!-- google_ad_section_start(weight=ignore) -->
<div id="overDiv" style="position:absolute; visibility:hidden;
  z-index:1000;"></div>
<?php
$page_name = 'anchors_all_donors';
include './menu.php';

$search_box_text = "Donor";
require './search.php';

$search_query = $search ?
  " and lower(di.usuario_nome) like '%".
  pg_escape_string(mb_strtolower(mb_convert_encoding(urldecode($search_text), 'ISO-8859-1', 'UTF-8'))).
  "%' " : "";
$team_index_table_join = $search ?
  " inner join usuarios_indice as di on ad.usuario=di.usuario_serial "
  : "";

$query = "select count (*) from donors_production as ad ".
  $team_index_table_join.
  " where active and ad.n_time in (select n_time from times_indice)".
  $search_query.";";
$result = pg_query($link, $query);
$nLines = pg_fetch_row($result);
$nLines = $nLines [0];
$query = <<<query
select
  di.usuario_nome as name,
  di.usuario_serial as number,
  di.n_time as team_number,
  ad.rank_0, ad.rank_24, ad.rank_7, ad.rank_30,
  at.pontos_7 as p7_team,
  rank_0_time as rank_0_team, rank_24_time as rank_24_team,
  rank_7_time as rank_7_team, rank_30_time as rank_30_team,
  ad.pontos_0 as points_0,
  ad.pontos_up as points_up,
  ad.pontos_24 as points_24,
  ad.pontos_7 as points_7,
  ti.time_nome as team_name,
  ad.active
from donors_production as ad
inner join usuarios_indice as di on ad.usuario=di.usuario_serial
inner join times_indice as ti on ti.n_time = ad.n_time
left join teams_production as at on at.n_time = ti.n_time
where ad.active $search_query
order by $order limit $row_count OFFSET $offset;
query;
$result = pg_query ($link, $query);
?>
<table class="tabela" cellspacing="0" style="margin:5px auto 15px;">
<?php include "./allDonorsPager.php"; ?>
<tr>
<td class="top">
<table cellspacing="0" class="cab top">
<tr>
<td style="text-align:center;font-size:150%;margin-right:0;height:2em;border-bottom:1px solid black;"
  class=cab>All Donors Ranking at
  <script type='text/javascript'>
  document.write(wDate('<?php echo $updateDate ?>') + ' ' +timeZone());
  </script>
</td>
</tr>

<tr class="ad">
<td style="padding:2px;">
<div class="ad" id="g_div_1">
<?php include './script/tabela_ad.html'; ?>
</div>
</td>
</tr>

</table></td></tr>
<tr><td class="top">
<table cellspacing="0" class="corpo">
<thead><?php include('table_head_ad.html') ?></thead>
<tbody id=tdados class=tcorpo>
<?php
$n_linha = $offset;
$n_linha_off = 0;
$n_linha_cor = 0;
$j = 1;
while ($line = pg_fetch_array($result, NULL, PGSQL_ASSOC)) {
  if ($n_linha_off % 15 == 0 && $n_linha_off > 0) {
    /*
    if ($j % 2 == 0 && $row_count - $n_linha_off >= 15) {
      echo '<tr class="ad"><td colspan="14">';
      echo '<div class="ad" id="ad_div_'.($j).'">';
      echo '</div></td></tr>', "\n";
    }
    $j++;
    */
    if ($n_linha_off > 70 && $n_linha_off < 110 && $j % 2 == 0 && $row_count - $n_linha_off >= 15) {
      echo '<tr class="ad"><td colspan="14" style="text-align:center;">';
      include './script/tabela_ad.html';
      echo '</td></tr>', "\n";
    }
    $j++;
    include('table_head_ad.html');
    $n_linha_cor = 0;
  }
  if ($n_linha_cor % 2 == 0) $classe = 'class="ls"';
  else $classe = 'class="ln"';
  $n_linha_off++;
  $n_linha++;
  $n_linha_cor++;

  $cor7 = cor_donor($line['points_7']);
  $cor7Team = cor_time($line['p7_team']);

   $line['name'] = preg_replace(
     '/([^\s]{10}\B)/S','$1<wbr>&shy;',
     preg_replace('/([^\A])_([^\Z])/S','$1 $2', $line['name'])
     );
   $line['name'] = str_replace(
     '&lt;wbr&gt;&amp;shy;','<wbr>&shy;',
     htmlentities($line['name'], ENT_QUOTES)
     );
   $line['team_name'] = preg_replace(
     '/([^\s]{10}\B)/S','$1<wbr>&shy;',
     preg_replace('/([^\A])_([^\Z])/S','$1 $2', $line['team_name'])
     );
   $line['team_name'] = str_replace(
     '&lt;wbr&gt;&amp;shy;','<wbr>&shy;',
     htmlentities($line['team_name'], ENT_QUOTES)
     );

  echo '<tr '.$classe.'>';
  echo "<td>", number_format($n_linha, 0, ".", ","), "</td>";

  echo '<td class="txt '.$cor7.'">';
  echo '<a class="'.$cor7.'" href="/usum.php?u='.$line['number'].'">',
      $line['name'], "</a>";
  echo "</td>";

  echo '<td class="txt '.$cor7Team.'">';
  echo '<a class="'.$cor7Team.'" href="/tsum.php?t='.$line['team_number'].'">',
      $line['team_name'], "</a>";
  echo "</td>";

  echo "<td>", number_format($line['rank_0'], 0, ".", ","), "</td>";
  echo "<td";
  if ($line['rank_24'] < $line['rank_0']) echo ' class="g"';
  elseif ($line['rank_24'] > $line['rank_0']) echo ' class="r"';
  echo ">", number_format($line['rank_24'], 0, ".", ","), "</td>";
  echo "<td";
  if ($line['rank_7'] < $line['rank_0']) echo ' class="g"';
  elseif ($line['rank_7'] > $line['rank_0']) echo ' class="r"';
  echo ">", number_format($line['rank_7'], 0, ".", ","), "</td>";
  echo "<td";
  if ($line['rank_30'] < $line['rank_0']) echo ' class="g"';
  elseif ($line['rank_30'] > $line['rank_0']) echo ' class="r"';
  echo ">", number_format($line['rank_30'], 0, ".", ","), "</td>";

  if (true) {
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
  }
  else echo "<td></td><td></td><td></td><td></td>";

  echo "<td>", number_format($line['points_up'], 0, ".", ","), "</td>";
  echo "<td>", number_format($line['points_24'], 0, ".", ","), "</td>";
  echo '<td class="'. $cor7. '">',
    number_format($line['points_7'], 0, ".", ","), "</td>";
  echo "<td>", number_format($line['points_0'], 0, ".", ","), "</td>";
  echo "</tr>\n";
  }
?>
</tbody>
</table>
</tr>
<?php include "./allDonorsPager.php"; ?>
</table>
<!-- google_ad_section_end -->
<?php include './script/adsense_bottom.js'; ?>
<p>&nbsp;</p>
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
