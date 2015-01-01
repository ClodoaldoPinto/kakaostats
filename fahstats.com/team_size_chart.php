<?php

session_start();
header("Content-type: image/png");
if (!isset($_SESSION['v'])) {
  readfile('./img/visitfahstats.png');
  exit;
}
$team = $_GET['id'];
$lastDayUpdate = isset($_GET['l']) ? $_GET['l'] : 0;
if (preg_match('/\D/', $team) == 1 or preg_match('/\D/', $lastDayUpdate) == 1) exit;
require_once("/usr/lib/php4/phpchartdir.php");
include './pgsqlserver.php';
$link = pg_connect ($conn_string);
$query = <<<query
select
    active_members as am,
    new_members as nm,
    am.dia as "day",
    isodow(am.dia) as dow
from (
    select
        active_members,
        data::date as dia
    from team_active_members_history tam
    inner join datas d on d.data_serial = tam.serial_date
    where team_number = $team
) am left outer join (
    select
        count(*) as new_members,
        data::date as dia
    from donor_first_wu dfw
    inner join usuarios_indice ui on ui.usuario_serial = dfw.donor
    where ui.n_time = $team
    group by dia
) nm on am.dia = nm.dia
where
    am.dia > (select max(data) - (15::text || ' week')::interval from datas)::date
order by am.dia
;
query;
#echo $query;
$result = pg_query ($link, $query);
$day = array();
$dayX = array();
$points = array();
$nm = array();
$i = 0;
while ($line = pg_fetch_array($result, NULL, PGSQL_ASSOC)) {
  if ($line['dow'] == 0) $day[$i] = substr($line['day'], 5);
  else $day[$i] = "-";
  $dayX[$i] = $i;
  $points[$i] = $line['am'];
  $nm[$i] = $line['nm'];
  $i++;
  }
if ($lastDayUpdate == 0) {
  array_pop($points);
  array_pop($day);
  array_pop($dayX);
  array_pop($nm);
  }
$c = new XYChart(740, 370, Transparent);
$setPlotAreaObj = $c->setPlotArea(70, 50, 620, 270);
$setPlotAreaObj->setGridColor(0xc0c0c0, 0xc0c0c0, -1, Transparent);
$c->setNumberFormat(',');
$legend = $c->addLegend(70, 20, 0, "vera.ttf", 11);
$legend->setBackground(Transparent);
$layer = $c->addLineLayer();
$dataSetObj = $layer->addDataSet($points, 0x990000, "Active Members");
$layer->setLineWidth(1);
$curve = new ArrayMath($points);
$curve->lowess();
$splineLayerObj = $c->addSplineLayer($curve->result(), 0xFF0000, "");
$splineLayerObj->setLineWidth(2);
$c->xAxis->setLabels($day);
$c->yAxis->setAutoScale(0.05, 0.05, 0.1);
$c->yAxis->setColors(0x000000, 0xAA0000);
$c->xAxis->setTickLength2(6,3);
$labelStyleObj = $c->xAxis->setLabelStyle("normal", 8);
$labelStyleObj->setFontAngle(0);
$labelStyleObj->setPos(0, 5);

$c2 = new XYChart(740, 370, Transparent);
$c2->setPlotArea(70, 50, 620, 270, Transparent, -1, Transparent, Transparent, Transparent);
$c2->setNumberFormat(',');
$legend = $c2->addLegend(595, 23, 0, "normal", 11);
$legend->setBackground(Transparent);
$c2->yAxis->setAutoScale(0.05, 0.05, 0.1);
$c2->yAxis->setColors(0x000000, 0x006600);
$c2->setYAxisOnRight();
$layer = $c2->addLineLayer();
$dataSetObj = $layer->addDataSet($nm, 0x004400, "New Members");
$layer->setLineWidth(1);
$curve2 = new ArrayMath($nm);
$curve2->lowess();
$splineLayerObj2 = $c2->addSplineLayer($curve2->result(), 0x00DD00, "");
$splineLayerObj2->setLineWidth(2);

$m = new MultiChart(740, 370, 0xFFF8F0);
$m->addChart(0, 0, $c);
$m->addChart(0, 0, $c2);
$title = $m->addTitle("Active and New Members Daily History", "normal", 14);
//$title->setBackground(0xffff00);
print($m->makeChart2(PNG));
?>