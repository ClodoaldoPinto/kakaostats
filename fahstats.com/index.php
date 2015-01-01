<?php
include 'function_color.php';
$col = isset($_GET['col']) ? $_GET['col'] : "";
if ($col < 0 || $col == "" || preg_match('/\D/', $col) == 1) $col = 3;
$offset = isset($_GET['offset']) ? $_GET['offset'] : 1;
if ($offset < 0 || $offset == "" || preg_match('/\D/', $offset) == 1) $offset = 1;
$search_text = isset($_GET['search']) ? trim($_GET['search']) : "";

require './pgsqlserver.php';
$link = pg_connect($conn_string);

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

$filename = './html_file_cache/index.html?col='.$col.'&offset='.$offset;
$offset--;
$row_count = 200;
$search = $search_text != '' ? True : False;

if (file_exists($filename) && !$search) {
  if (filemtime($filename) >= $update_unix_timestamp) {
    $file_string = file_get_contents($filename);
    echo(str_replace('var time_to_update = ',
                     'var time_to_update = '.$ttu.';// ', $file_string));
    exit(0);
  }
}


switch ($col) {
  case 1:
    $order = "active_number desc, rank_0";
    break;
  case 2:
    $order = "team_name";
    break;
  case 3:
    $order = "rank_0";
    break;
  case 4:
    $order = "rank_24";
    break;
  case 5:
    $order = "rank_7";
    break;
  case 6:
    $order = "rank_30";
    break;
  case 7:
    $order = "points_24 desc, rank_0";
    break;
  case 8:
    $order = "points_7 desc, rank_0";
    break;
  case 12:
    $order = "points_up desc, rank_0";
    break;
  case 11:
    $order = "new_members desc, rank_0";
    break;
  case 9:
    $order = "(pontos_7) / active_members desc, rank_0";
    break;
  case 10:
    $order = "points_0 desc";
    break;
  default:
    $order = "points_0 desc";
    break;
  }

ob_start();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
<script type="text/javascript">
if (self != top) top.location.href = location.href;
th_classificado = <?php echo $col?>;</script>
<script type="text/javascript" src="/script/js.js"></script>
<script type="text/javascript">
function sload(col) {
  var search = encodeURIComponent(document.getElementById('search_text').value);
  search = search.replace(/(\%20)+/g, '') != '' ? '&search=' +
    search.replace(/^(\%20)+|(\%20)+$/g, '') : '';
  var url = '/?col=' + col + search;
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
<LINK REL=StyleSheet HREF="/script/ks2-2.css" TYPE="text/css" MEDIA=screen>
<LINK REL="SHORTCUT ICON" href="/favicon.ico">
<title>Kakao Stats - Teams Ranking</title>
<script type="text/javascript"
  src="/overlib/mini/overlib_mini.js"><!-- overLIB (c) Erik Bosrup -->
</script>
<style type="text/css">
#tester {display:none;}
</style>
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
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>
<script type="text/javascript" src="/script/advertisement.js"></script>
<?php
$page_name = 'anchors_all_teams';
include './menu.php';

$search_box_text = "Team";
require './search.php';
$search_query = $search ?
  " and lower(ti.time_nome) like '%".
  pg_escape_string(mb_strtolower(mb_convert_encoding(urldecode($search_text), 'ISO-8859-1', 'UTF-8'))).
  "%' " : "";
$team_index_table_join = $search ?
  " inner join times_Indice as ti on at.n_time = ti.n_time "
  : "";
$query = "select count (*) from teams_production as at ".
  $team_index_table_join.
  " where active ".
  $search_query.";";
$result = pg_query($link, $query);
$nLines = pg_fetch_row($result);
$nLines = $nLines [0];
$query = <<<query
select
  active_members as active_number,
  at.n_time as team_number,
  rank_0, rank_24, rank_7, rank_30,
  pontos_0 /1000 as points_0,
  pontos_24 as points_24,
  pontos_7 as points_7,
  pontos_up as points_up,
  ti.time_nome as team_name,
  new_members
from teams_production as at
inner join times_Indice as ti on at.n_time = ti.n_time
where
  active
  and
  active_members > 0
  and
  at.n_time not in (0, -1)
  $search_query
order by $order limit $row_count OFFSET $offset;
query;
$result_co = pg_query($link, $query);

$query = <<<query
select
  active_members as active_number,
  at.n_time as team_number,
  rank_0, rank_24, rank_7, rank_30,
  pontos_0 /1000 as points_0,
  pontos_24 as points_24,
  pontos_7 as points_7,
  pontos_up as points_up,
  ti.time_nome as team_name,
  new_members
from teams_production as at
inner join times_Indice as ti on at.n_time = ti.n_time
where
  at.n_time = -1
;
query;
$result_fah = pg_query($link, $query);

$query = <<<query
select
  active_members as active_number,
  at.n_time as team_number,
  rank_0, rank_24, rank_7, rank_30,
  pontos_0 /1000 as points_0,
  pontos_24 as points_24,
  pontos_7 as points_7,
  pontos_up as points_up,
  ti.time_nome as team_name,
  new_members
from teams_production as at inner join times_Indice as ti on at.n_time = ti.n_time
where
  at.n_time in (0)
  $search_query
order by $order limit $row_count OFFSET $offset;
query;
$result_ag = pg_query($link, $query);

?>
<table class="tabela" cellspacing="0" style="margin:5px auto 15px;">
<?php include "./paginador.php"; ?>
<tr>
<td style="" class=top>
<table cellspacing="0" class="cab top">
<tr>
<td style="text-align:center;font-size:150%;margin-right:0;height:2em;border-bottom:1px solid black;"
    class=cab>Active Teams Rank
<span style="font-size:90%;vertical-align:middle;" class=cab>
<?php
  echo " at <script type='text/javascript'>",
  "document.write(wDate('".$updateDate."')+' '+timeZone());</script>";
?>
</span>
</td>
</tr>

<tr class="xd">
<td style="padding:2px;text-align:center;">
<div class="xd" id="g_div_1">
<?php include './script/tabela_ad.html'; ?>
</div>
<!--<script type="text/javascript">
//    if (document.getElementById("tester") == undefined) {
//      document.getElementById("g_div_1").className = '';
//      document.getElementById("g_div_1").innerHTML = 
//	'<p style="text-align:center;">KakaoStats text ads are <a href="http://adblockplus.org/en/acceptable-ads#criteria">considered acceptable by Adblock Plus</a>.</p>';
//    }
</script>-->
</td>
</tr>

</table>
</td>
</tr>
<tr>
<td class="top">
<table cellspacing="0" class="corpo" style="">
<thead><?php include('table_head_all_teams.html'); ?></thead>
<tbody id="tdados" class="tcorpo">
<?php

$n_linha = $offset;
$line = pg_fetch_array($result_fah, NULL, PGSQL_ASSOC);
$n_linha++;
$line['team_name'] = preg_replace(
  '/([^\s]{18}\B)/S','$1<wbr>&shy;',
  preg_replace('/([^\A])_([^\Z])/S','$1 $2', $line['team_name'])
  );
$line['team_name'] = str_replace(
  '&lt;wbr&gt;&amp;shy;','<wbr>&shy;',
  htmlentities($line['team_name'], ENT_QUOTES)
  );

$points_7_project = $line['points_7'];
$points_up_project = $line['points_up'];
$points_24_project = $line['points_24'];
$points_0_project = $line['points_0'];
$ppa_project =$line['points_7']/$line['active_number'];
echo '<tr class="ls">';
echo "<td></td>";
echo "<td>", number_format($line['active_number'], 0, ".", ","), "</td>";
echo "<td>", number_format($line['new_members'], 0, ".", ","), "</td>";
echo '<td class="txt" style="font-weight:bold;">';
echo $line['team_name'], '</td>';
echo "<td></td>";
echo "<td></td>";
echo "<td></td>";
echo "<td></td>";
echo "<td>", number_format($line['points_up'], 0, ".", ","), "</td>";
echo "<td>", number_format($line['points_24'], 0, ".", ","), "</td>";
echo "<td>", number_format($line['points_7'], 0, ".", ","), "</td>";
echo "<td>",
  number_format($ppa_project, 0, ".", ","),
  "</td>";
echo "<td>", number_format($line['points_0'], 0, ".", ","), "</td>";
echo "</tr>\n";

$n_linha = $offset;
while ($line = pg_fetch_array($result_ag, NULL, PGSQL_ASSOC)) {
  if (++$n_linha % 2 == 0) $classe = 'class="ln"';
  else $classe = 'class="ln"';
  $cor_7 = cor_time($line['points_7']);

  $line['team_name'] = preg_replace(
      '/([^\s]{18}\B)/S','$1<wbr>&shy;',
      preg_replace('/([^\A])_([^\Z])/S','$1 $2', $line['team_name'])
      );
  $line['team_name'] = str_replace(
      '&lt;wbr&gt;&amp;shy;','<wbr>&shy;',
      htmlentities($line['team_name'], ENT_QUOTES)
      );

  echo "<tr $classe>";
  echo "<td></td>";
  echo "<td>", number_format($line['active_number'], 0, ".", ","), "</td>";
  echo "<td>", number_format($line['new_members'], 0, ".", ","), "</td>";
  echo '<td class="txt '.$cor_7.'">';
  echo '<a class="'.$cor_7.'" href="/t.php?t='.$line['team_number'].'">',
    $line['team_name'], "</a>";
  echo "</td>";
  echo "<td></td>";
  echo "<td></td>";
  echo "<td></td>";
  echo "<td></td>";
  $permil = number_format($line['points_up'] * 1000 / $points_up_project, 3) . ' &permil;';
  echo "<td ",
  	 'onmouseover="return overlib(\''.$permil.'\', WIDTH, 80)" onmouseout="return nd()">',
    number_format($line['points_up'], 0, ".", ","), "</td>";
  $permil = number_format($line['points_24'] * 1000 / $points_24_project, 3) . ' &permil;';
  echo "<td ",
  	 'onmouseover="return overlib(\''.$permil.'\', WIDTH, 80)" onmouseout="return nd()">',
    number_format($line['points_24'], 0, ".", ","), "</td>";
  $permil = number_format($line['points_7'] * 1000 / $points_7_project, 3) . ' &permil;';
  echo "<td class=\"$cor_7\" ",
  	 'onmouseover="return overlib(\''.$permil.'\', WIDTH, 80)" onmouseout="return nd()">',
    number_format($line['points_7'], 0, ".", ","), "</td>";
  $permil = number_format($line['points_7'] * 100 / $line['active_number'] / $ppa_project, 2) . ' &#037;';
  echo "<td ",
  	 'onmouseover="return overlib(\''.$permil.'\', WIDTH, 70)" onmouseout="return nd()">',
    number_format($line['points_7']/$line['active_number'], 0, ".", ","),
    "</td>";
  $permil = number_format($line['points_0'] * 1000 / $points_0_project, 3) . ' &permil;';
  echo "<td ",
  	 'onmouseover="return overlib(\''.$permil.'\', WIDTH, 80)" onmouseout="return nd()">',
    number_format($line['points_0'], 0, ".", ","), "</td>";
  echo "</tr>\n";
  }

$n_linha = $offset;
$n_linha_off = 0;
$n_linha_cor = 0;
$j = 1;
while ($line = pg_fetch_array($result_co, NULL, PGSQL_ASSOC)) {
  if ($n_linha_off % 17 == 0 && $n_linha_off > 0) {
    /*
    if ($j % 2 == 0 && $row_count - $n_linha_off >= 15) {
      echo '<tr class="xd"><td colspan="14">';
      echo '<div class="xd" id="ad_div_'.($j).'">';
      echo '</div></td></tr>', "\n";
    }
    $j++;
    */
    if ($n_linha_off > 70 && $n_linha_off < 110 && $j % 2 == 0 && $row_count - $n_linha_off >= 15) {
      echo '<tr class="xd"><td colspan="14" style="text-align:center;">';
      include './script/tabela_ad.html';
      echo '</td></tr>', "\n";
    }
    $j++;
    include('table_head_all_teams.html');
    $n_linha_cor = 0;
  }
  if ($n_linha_cor % 2 == 0) $classe = 'class="ls"';
  else $classe = 'class="ln"';
  $n_linha_off++;
  $n_linha++;
  $n_linha_cor++;

  $cor_7 = cor_time($line['points_7']);

  $line['team_name'] = preg_replace(
      '/([^\s]{18}\B)/S','$1<wbr>&shy;',
      preg_replace('/([^\A])_([^\Z])/S','$1 $2', $line['team_name'])
      );
  $line['team_name'] = str_replace(
      '&lt;wbr&gt;&amp;shy;','<wbr>&shy;',
      htmlentities($line['team_name'], ENT_QUOTES)
      );

  echo "<tr $classe>";
  echo "<td>",
    number_format($n_linha, 0, ".", ","),
    "</td>";
  echo "<td>", number_format($line['active_number'], 0, ".", ","), "</td>";
  echo "<td>", number_format($line['new_members'], 0, ".", ","), "</td>";
  echo '<td class="txt '. $cor_7.'">';
  echo '<a class="'.$cor_7.'" href="/t.php?t='.$line['team_number'].'">',
    $line['team_name'], "</a>";
  echo "</td>";
  echo "<td>",
    $line['rank_0'] == 0 ? '' : number_format($line['rank_0'], 0, ".", ","),
    "</td>";
  echo "<td";
  if ($line['rank_24'] < $line['rank_0']) echo " class=g";
  elseif ($line['rank_24'] > $line['rank_0']) echo " class=r";
  echo ">",
    $line['rank_24'] == 0 ? '' : number_format($line['rank_24'], 0, ".", ","),
    "</td>";
  echo "<td";
  if ($line['rank_7'] < $line['rank_0']) echo " class=g";
  elseif ($line['rank_7'] > $line['rank_0']) echo " class=r";
  echo ">",
    $line['rank_7'] == 0 ? '' : number_format($line['rank_7'], 0, ".", ","),
    "</td>";
  echo "<td";
  if ($line['rank_30'] < $line['rank_0']) echo " class=g";
  elseif ($line['rank_30'] > $line['rank_0']) echo " class=r";
  echo ">",
    $line['rank_30'] == 0 ? '' : number_format($line['rank_30'], 0, ".", ","),
    "</td>";
  $permil = number_format($line['points_up'] * 1000 / $points_up_project, 3) . ' &permil;';
  echo "<td ",
  	 'onmouseover="return overlib(\''.$permil.'\', WIDTH, 80)" onmouseout="return nd()">',
    number_format($line['points_up'], 0, ".", ","), "</td>";
  $permil = number_format($line['points_24'] * 1000 / $points_24_project, 3) . ' &permil;';
  echo "<td ",
  	 'onmouseover="return overlib(\''.$permil.'\', WIDTH, 80)" onmouseout="return nd()">',
    number_format($line['points_24'], 0, ".", ","), "</td>";
  $permil = number_format($line['points_7'] * 1000 / $points_7_project, 3) . ' &permil;';
  echo "<td class=$cor_7 ",
  	 'onmouseover="return overlib(\''.$permil.'\', WIDTH, 80)" onmouseout="return nd()">',
    number_format($line['points_7'], 0, ".", ","), "</td>";
  $permil = $line['active_number'] == 0 ? '' :
    number_format($line['points_7'] * 100 / $line['active_number'] / $ppa_project, 2) . ' &#037';
  echo "<td ",
  	 'onmouseover="return overlib(\''.$permil.'\', WIDTH, 70)" onmouseout="return nd()">',
    number_format($line['points_7']/$line['active_number'], 0, ".", ","), "</td>";
  $permil = number_format($line['points_0'] * 1000 / $points_0_project, 3) . ' &permil;';
  echo "<td ",
  	 'onmouseover="return overlib(\''.$permil.'\', WIDTH, 80)" onmouseout="return nd()">',
    number_format($line['points_0'], 0, ".", ","), "</td>";
  echo "</tr>\n";
  }
?>
</tbody>
</table></td></tr>
<?php include "./paginador.php"; ?>
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
