<?php

if (!isset($_GET['t'])) exit;
$team = $_GET['t'];
if (preg_match('/\D/', $team) == 1 || $team == '') exit;
session_start();
$_SESSION['v'] = '1';
include('function_color.php');
require './pgsqlserver.php';
$link = pg_connect($conn_string);
$queryDate = "select to_char(last_date, 'YYYY-MM-DD HH24:MI') as date from last_date;";
$resultDate = pg_query($link, $queryDate);
$lineDate = pg_fetch_array($resultDate, NULL, PGSQL_ASSOC);
$date = $lineDate["date"];

$query = "
select time_nome as team_name
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
</script>
<script type="text/javascript" src="/script/js.js"></script>
<LINK REL=StyleSheet HREF="/script/ks2-2.css" TYPE="text/css" MEDIA=screen>
<LINK REL="SHORTCUT ICON" href="/favicon.ico">
<title>Kakao Stats - Radar Scope -
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
$page_name ='team_radar';
include './menu.php';
?>
<p style="text-align:center;margin:1em auto 0.5em auto;font-size:22px;color:floralWhite;">
<?php
  echo htmlentities($team_name,ENT_QUOTES), <<<html
  at <span style="font-size:18px;">
  <script type="text/javascript">
  document.write(wDate('$date')+' '+timeZone());
  </script></span>
html;
?></p>
<div style="margin: 0 auto 1em;padding:0;">
<img src="/trchart.php?id=<?php echo $team ?>"
  width="740" height="370" alt="Team Radar Screen Chart"
   style="border:1px solid black;padding:0;margin:0;">
</div>

<?php
include './script/entrelacado_ad.html';
?>

<table class="tabela" cellspacing="0" cellpadding="0">
<tr>
<td class="top">
<table cellspacing="0" cellpadding="0" class="cab top">
    <tr>
    <td style="text-align:center;font-size:150%;">Radar Table</td>
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
        (select last_date from last_date) + case
            when
                (t0.pontos_0 - t1.pontos_0) / (t1.pontos_7 - t0.pontos_7) > 421200
                then null else
                (((t0.pontos_0 - t1.pontos_0) / (t1.pontos_7 - t0.pontos_7)
                )::text || ' week')::interval
            end
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
limit 63
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
}
;
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
