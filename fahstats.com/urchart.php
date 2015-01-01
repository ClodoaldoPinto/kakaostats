<?php

session_start();
header("Content-type: image/png");
if (!isset($_SESSION['v'])) {
  readfile('./img/visitfahstats.png');
  exit;
}
$donor_number = $_GET['id'];
if (preg_match('/\D/', $donor_number) == 1) exit;
require_once("/usr/lib/php4/phpchartdir.php");
require './pgsqlserver.php';
$link = pg_connect($conn_string);
#-------------------------------------------------------------------------
$query = <<<query
with u0 as (
    select
        usuario,
        pontos_0,
        pontos_7
    from donors_production as dp
    where dp.usuario = $donor_number
)
select
    usuario_nome,
    days,
    pontos_0,
    pontos_7
from (
    select
        usuario,
        0 as days,
        pontos_0,
        pontos_7
    from u0
    union
    select
        u1.usuario,
          (u0.pontos_0 - u1.pontos_0) * 7 /
          (u1.pontos_7 - u0.pontos_7)
        as days,
        u1.pontos_0,
        u1.pontos_7
    from donors_production as u1
    inner join u0 on true
    where active
        and n_time = (select n_time from usuarios_indice where usuario_serial = $donor_number)
        and
        (
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
inner join usuarios_indice ui on ss.usuario = ui.usuario_serial
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

while ($line = pg_fetch_array($result, NULL, PGSQL_ASSOC)) {
  $days[$i] = $line['days'];
  $name[$i] = utf8_encode($line['usuario_nome']);
  $points[$i] = $line['pontos_0'];
  $slope[$i] = ($line['pontos_7']) /7;
  if ($i == 0) $bigX = 0;
  else {
    $x[$i] = ($points[$i] -$points[0]) /($slope[0] -$slope[$i]);
    $y[$i] = $points[$i];
    }
  $i++;
};
if ($x[1] > $lowX +100) $bigX = $x[1];
else $bigX = $lowX + 100;
$n = $i -1;
for ($i = $i -1; $i > 0; $i--) {
  #echo "i= ", $i, " xi= ", $x[$i], " bigX= ", $bigX;
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
$setPlotAreaObj = $c2->setPlotArea(70, 40, 630, 270, Transparent, Transparent);
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
  if ($bigX < 1) $div = $bigX;
  else $div = floor($bigX);
  for($i=0; $i<=($bigX>1?$bigX:1); $i+=($div == 0 ? 1 : $bigX/$div)) {
    $xLabels[$i] = number_format($i, 1);
    }
  }
for($i=0; $i<10; $i++) {
  $yLabels[$i] = number_format(round(($lowY + (($bigY -$lowY) *$i /9)) /100));
  }
$c2->yAxis->setLinearScale2($lowY, $bigY, $yLabels);
$c2->xAxis->setLinearScale2($lowX, $bigX, $xLabels);
$legend = $c2->addLegend(95, 40, 1, "normal", 11);
$legend->setBackground(Transparent, Transparent);
$legend->setFontColor(0x202020);
$c2->yAxis->setLabelStyle("normal", 8);
$c2->xAxis->setLabelStyle("normal", 8);
$c2->yAxis->setTitle("Hundred Points", "normal", 11);
$c2->xAxis->setTitle("Days","normal", 11);

$m = new MultiChart (740, 370);
$m->addChart(0, 0, $c2);
$m->addTitle("Radar Scope", "normal", 14);
print($m->makeChart2(PNG));
?>