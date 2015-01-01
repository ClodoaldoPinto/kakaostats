<?php

session_start();
header("Content-type: image/png");
if (!isset($_SESSION['v'])) {
  readfile('./img/visitfahstats.png');
  exit;
}
$team = $_GET['t'];
$lastDayUpdate = isset($_GET['l']) ? $_GET['l'] : 0;
if (preg_match('/\D/', $team) == 1 or preg_match('/\D/', $lastDayUpdate) == 1) exit;
require_once("/usr/lib/php4/phpchartdir.php");
include './pgsqlserver.php';
$link = pg_connect ($conn_string);
$query = <<<query
  select donor as nm, data::date as day, isodow(data::date) as dow
  from select_new_members($team, 8)
  order by data::date;
query;
#echo $query;
$result = pg_query ($link, $query);
$day = array();
$dayX = array();
$points = array();
$i = 0;
while ($line = pg_fetch_array($result, NULL, PGSQL_ASSOC)) {
  if ($line['dow'] == 0) $day[$i] = substr($line['day'], 5);
  else $day[$i] = "-";
  $dayX[$i] = $i;
  $points[$i] = $line['nm'];
  $i++;
  }
if ($lastDayUpdate == 0) {
  array_pop($points);
  array_pop($day);
  array_pop($dayX);
  }
$c = new XYChart(740, 370, Transparent);
$setPlotAreaObj = $c->setPlotArea(70, 50, 620, 270);
$setPlotAreaObj->setGridColor(0xc0c0c0, 0xc0c0c0, -1, Transparent);
$c->setNumberFormat(',');
$legend = $c->addLegend(70, 20, 0, "vera.ttf", 11);
$legend->setBackground(Transparent);
$layer = $c->addLineLayer();
$dataSetObj = $layer->addDataSet($points, 0x0000FF, "New Members");
#$dataSetObj->setDataSymbol(CircleSymbol, 5, 0x0000ff, 0x0000ff);
$layer->setLineWidth(1);
$curve = new ArrayMath($points);
$curve->lowess();
$splineLayerObj = $c->addSplineLayer($curve->result(), 0x009900, "New Members Lowess");
$splineLayerObj->setLineWidth(2);
$c->xAxis->setLabels($day);
$c->yAxis->setAutoScale(0.01, 0.01, 0.1);
$c->yAxis->setColors(0x000000, 0x0000FF);
$c->xAxis->setTickLength2(5,0);
$labelStyleObj = $c->xAxis->setLabelStyle("normal", 8);
$labelStyleObj->setFontAngle(0);
$labelStyleObj->setPos(0, 5);
$m = new MultiChart(740, 370, 0xFFF8F0);
$m->addChart(0, 0, $c);
$title = $m->addTitle("Team New Members Daily History", "normal", 14);
//$title->setBackground(0xffff00);
print($m->makeChart2(PNG));
?>