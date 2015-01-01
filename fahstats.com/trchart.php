<?php

session_start();
header("Content-type: image/png");
if (!isset($_SESSION['v'])) {
  readfile('./img/visitfahstats.png');
  exit;
}
$team = $_GET['id'];
if (preg_match('/\D/', $team) == 1) exit;
require_once("/usr/lib/php4/phpchartdir.php");
require './pgsqlserver.php';
$link = pg_connect($conn_string);
$query = <<<query
with t0 as (
    select
        n_time,
        pontos_0,
        pontos_7
    from teams_production as at
    where at.n_time = $team
)
select
    time_nome,
    days,
    pontos_0,
    pontos_7
from (
    select
        n_time,
        0 as days,
        pontos_0,
        pontos_7
    from t0
    union
    select
        t1.n_time,
          (t0.pontos_0 - t1.pontos_0) * 7 /
          (t1.pontos_7 - t0.pontos_7)
        as days,
        t1.pontos_0,
        t1.pontos_7
    from teams_production as t1
    inner join t0 on true
    where active
      and
      (
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
order by days, pontos_7, pontos_0
limit 11;
query;
$result = pg_query($link, $query);
#echo $query;
#exit;

$i = 0;
$x = array(1=>0);
$y = array();
$slope = array();
$lowX = 0;
$lowY = 9E50;
$bigY = -9E50;

while ($line = pg_fetch_array($result, NULL, PGSQL_ASSOC))
{
  $days[$i] = $line['days'];
  $name[$i] = utf8_encode($line['time_nome']);
  $points[$i] = $line['pontos_0'];
  $slope[$i] = ($line['pontos_7']) /7;
  if ($i == 0) $bigX = 0;
  else {
    $x[$i] = ($points[$i] -$points[0]) /($slope[0] -$slope[$i]);
    $y[$i] = $points[$i];
#		if ($x[$i] > $bigX) $bigX = $x[$i];
#		if ($x[$i] < $lowX) $lowX = $x[$i];
    }
  $i++;
};
if ($x[1] > $lowX +100) $bigX = $x[1];
else $bigX = $lowX + 100;
$n = $i -1;
for ($i = $i -1; $i > 0; $i--) {
#	echo "i= ", $i, " xi= ", $x[$i], " bigX= ", $bigX;
  if ($x[$i] < $bigX) {
    $n = $i;
    $bigX = $x[$i];
    break;
    }
  }
$y = array();
$x = array();
for ($i = 0; $i <= $n; $i++) {
  $y[$i] = array(0=>0, 1=>1);
  $x[$i] = array(0=>0, 1=>1);
  $x[$i][0] = $lowX;
  $y[$i][0] = $points[$i];
  if ($y[$i][0] < $lowY) $lowY = $y[$i][0];
  $y[$i][1] = $slope[$i] * $bigX + $points[$i];
  if ($y[$i][1] > $bigY) $bigY = $y[$i][1];
  $x[$i][1] = $bigX;
  }
/*
echo "<pre>";
echo number_format($bigX), "<br />", number_format($lowX), "<br>";
echo number_format($bigY), "<br>", number_format($lowY), "<br>";
print_r($slope);
print_r($x);
print_r($y);
print_r($yLabels);
echo "</pre>";
exit;
*/
$c2 = new XYChart(740, 370, 0xFFF8F0, Transparent);
$setPlotAreaObj = $c2->setPlotArea(80, 30, 610, 280, Transparent, Transparent);
$setPlotAreaObj->setGridColor(0xb0b0b0, 0xb0b0b0);
$c2->setNumberFormat(',', '.', '-');
$layer = $c2->addLineLayer2();
$layer->setLineWidth(2);
for ($i=0; $i<=$n; $i++) {
  $layer->addDataSet($y[$i], -1, $name[$i]);
  $layer->setXData($x[$i]);
  }
$xLabels = array();
if ($bigX > 9)
  for($i=0; $i<10; $i++) {
    $xLabels[$i] = number_format($bigX *$i /9);
    }
else {
  if ($bigX == 0) $bigX = 0.001;
  if ($bigX < 1) $div = $bigX;
  else $div = floor($bigX);
  for($i=0; $i<=($bigX>1?$bigX:1); $i+=$bigX/$div) {
    $xLabels[$i] = number_format($i, 1);
    }
  }
for($i=0; $i<10; $i++) {
  $yLabels[$i] = number_format(round(($lowY + (($bigY -$lowY) *$i /9)) /1000));
  }
$c2->yAxis->setLinearScale2($lowY, $bigY, $yLabels);
$c2->xAxis->setLinearScale2($lowX, $bigX, $xLabels);
$legend = $c2->addLegend(95, 40, 1, "normal", 11);
$legend->setBackground(Transparent, Transparent);
$legend->setFontColor(0x202020);
$c2->yAxis->setLabelStyle("normal", 8);
$c2->xAxis->setLabelStyle("normal", 8);
$c2->yAxis->setTitle("Thousand Points", "normal", 11);
$c2->xAxis->setTitle("Days","normal", 11);

$m = new MultiChart (740, 370);
#$m->addChart(0, 0, $c);
$m->addChart(0, 0, $c2);
$m->addTitle("Radar Scope", "normal", 14);
print($m->makeChart2(PNG));
?>