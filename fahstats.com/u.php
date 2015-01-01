<?php

if (!isset($_GET['u'])) exit;
$donor = $_GET['u'];
if (preg_match('/\D/', $donor) == 1 || $donor == '') exit;
session_start();
$_SESSION['v'] = '1';
include ('function_color.php');
include './pgsqlserver.php';
$link = pg_connect($conn_string);
$query = "
select
    n_time as team,
    usuario_nome as donor_name
from usuarios_indice
where usuario_serial = $donor
;
";
$result = pg_query($link, $query);
$line = pg_fetch_array($result, NULL, PGSQL_ASSOC);
$team = $line ['team'];
$donor_name = utf8_encode($line['donor_name']);

$query = <<<query
select to_char (last_date, 'YYYY-MM-DD HH24:MI') as "date"
from last_date;
query;
$result = pg_query ($link, $query);
$line = pg_fetch_array ($result, NULL, PGSQL_ASSOC);
$date = $line ["date"];
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html><head>
<?php include "./meta.html"; ?>
<LINK REL=StyleSheet HREF="/script/ks2-2.css" TYPE="text/css" MEDIA=screen>
<LINK REL="SHORTCUT ICON" href="/favicon.ico">
<title>Kakao Stats - Radar Scope - <?php echo htmlentities($donor_name, ENT_QUOTES, 'UTF-8');?></title>
<script type="text/javascript">
if (self != top) top.location.href = location.href;
</script>
<script type="text/javascript" src="/script/js.js"></script>
<script type="text/javascript" src="/overlib/mini/overlib_mini.js"><!-- overLIB (c) Erik Bosrup --></script>
<style type="text/css">
.ls { background: gainsboro; }
.ls:hover { background-color:#ffd800; }
.ln { background: floralWhite; }
.ln:hover { background-color:#ffd800; }
</style>
</head>
<body style="text-align:center;"
  onload="
  document.getElementById('ttu').innerHTML = timeToUpdate(time_of_update);
  window.setInterval('document.getElementById(\'ttu\').innerHTML = timeToUpdate(time_of_update)', 1000);
  ">
<!-- google_ad_section_start(weight=ignore) -->
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>
<?php
$page_name = 'donor_radar';
include './menu.php';
?>
<p style="text-align:center;margin:0.5em auto 0.5em auto;font-size:22px;color:floralWhite;">
<?php
echo htmlentities($donor_name, ENT_QUOTES, 'UTF-8'), <<<html
  at
  <span style="font-size:18px;">
  <script type="text/javascript">
  document.write(wDate('$date')+' '+timeZone());
  </script></span>
</p>
<div style="margin-top:0;margin-bottom:0.5em;padding:0;">
<img src="/urchart.php?id=$donor"
  width=740 height=370 alt="Donor's Radar Screen Chart"
  style="border:1px solid black;padding:0;margin:0;">
</div>
html;
include './script/entrelacado_ad.html';
?>

<table class="tabela" cellspacing="0">
<tr>
<td class="top">
<table cellspacing="0" class="cab top">
<tr>
<td style="font-size:22px;text-align:center;"
  >Radar Table
</td>
</tr>
</table>
</td>
</tr>
<tr>
<td class="top">
<table cellspacing="0" class="corpo">
<thead><?php include('table_head_member_radar.html') ?></thead>
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
limit 63
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
  echo "<a href=\"/u.php?u={$line['donor_number']}\" class=\"$cor7\">{$donor_name}</a>";
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
}; ?>
</tbody>
</table>
</td>
</tr>
</table>
<!-- google_ad_section_end -->
<?php include "./footer.html"; ?>
</body></html>
