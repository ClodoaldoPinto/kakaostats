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
$query = "
select
    points, wus,
    to_char(date_trunc('day', data), 'YYYY-MM-DD') as day,
    isodow(data) as dow
from (
    select
        pontos - lead(pontos, 1, 0::real) over w as points,
        wus - lead(wus, 1, 0) over w as wus,
        d.data
    from times t
    inner join datas d on d.data_serial = t.data
    where
        n_time = $team
        and
        d.data in (
            select sq.data
            from (
                select
                    date_trunc('day', data) as day,
                    max(data) as data
                from datas
                where
                    data > (select max(data) - interval '57 days' from datas)
                group by day
            ) sq
        )
    window w as (order by d.data desc)
) ss
where
    points is not null
    and
    data > (select max(data) - interval '55 days' from datas)
order by data
;";
#echo $query;
$result = pg_query ($link, $query);
$day = array();
$dayX = array();
$points = array();
$pointsPerWu = array();
$i = 0;
while ($line = pg_fetch_array($result, NULL, PGSQL_ASSOC)) {
  if ($line['dow'] == 0) $day[$i] = substr($line['day'], 5);
  else $day[$i] = "-";
  $dayX[$i] = $i;
  $points[$i] = $line['points'];
  $pointsPerWu[$i] = $line ['wus'] == 0 ? 0 : $points[$i] / $line['wus'];
  $i++;
  }
if ($lastDayUpdate == 0) {
  array_pop($points);
  array_pop($day);
  array_pop($dayX);
  array_pop($pointsPerWu);
  }
$c2 = new XYChart(740, 370, Transparent);
$c2->setPlotArea(70, 50, 620, 270, Transparent, -1, Transparent, Transparent, Transparent);
$c2->setNumberFormat(',');
$legend = $c2->addLegend(595, 23, 0, "normal", 11);
$legend->setBackground(Transparent);
$c2->yAxis->setAutoScale(0.01, 0.01, 0.1);
$c2->yAxis->setColors(0x000000, 0x004400);
$c2->setYAxisOnRight();
$layer = $c2->addLineLayer();
$dataSetObj = $layer->addDataSet($pointsPerWu, 0x004400, "Points per WU");
$dataSetObj->setDataSymbol(CircleSymbol, 5, 0x00bb00, 0x00bb00);
$layer->setLineWidth(1);

$c = new XYChart(740, 370, Transparent);
$setPlotAreaObj = $c->setPlotArea(70, 50, 620, 270);
$setPlotAreaObj->setGridColor(0xc0c0c0, 0xc0c0c0, -1, Transparent);
$c->setNumberFormat(',');
$legend = $c->addLegend(70, 23, 0, "vera.ttf", 11);
$legend->setBackground(Transparent);

$layer = $c->addLineLayer();
$dataSetObj = $layer->addDataSet($points,  0xbb0000, "Points");
$dataSetObj->setDataSymbol(CircleSymbol, 5, 0xdd0000, 0xdd0000);
$layer->setLineWidth(1);

$curve = new ArrayMath($points);
$curve->lowess();
$splineLayerObj = $c->addSplineLayer($curve->result(), 0xFF0000, "");
$splineLayerObj->setLineWidth(3);
$c->xAxis->setLabels($day);
$c->yAxis->setAutoScale(0.01, 0.01, 0.1);
$c->yAxis->setColors(0x000000, 0xbb0000);
$c->xAxis->setTickLength2(6,3);
$labelStyleObj = $c->xAxis->setLabelStyle("normal", 8);
$labelStyleObj->setFontAngle(0);
$labelStyleObj->setPos(0, 5);
$trendLayerObj = $c->addTrendLayer($points, $c->dashLineColor(0xbb0000, DashLine), "");
$trendLayerObj->setLineWidth(2);

$m = new MultiChart(740, 370, 0xFFF8F0);
$m->addChart(0, 0, $c2);
$m->addChart(0, 0, $c);
$title = $m->addTitle("Daily Production History", "normal", 14);
//$title->setBackground(0xffff00);
print($m->makeChart2(PNG));
?>
