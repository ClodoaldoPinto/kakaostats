<?php

header("Cache-Control: Public");
if (!isset($_GET['t']) || preg_match('/\D/', $_GET['t']) == 1) exit;
$team = $_GET['t'];
$days = isset($_GET['d']) ? $_GET['d'] : 7;
if (preg_match('/\D/', $days) == 1) exit;
$days = $days == "" ? 7 : $days;
$days = $days == 0 ? 1 : $days;
$days = $days > 14 ? 14 : $days;
include './pgsqlserver.php';
$link = pg_connect ($conn_string);
$query = <<<query
  select ui.usuario_nome as name, nm.data, up.rank_0_time as rank
  from donor_first_wu as nm
  inner join usuarios_indice as ui on ui.usuario_serial = nm.donor
  inner join donors_production as up on nm.donor = up.usuario
  where ui.n_time = $team and
    nm.data >= (
      select data from datas order by data desc limit 1) -
        (($days || ' day')::interval)
  order by nm.data desc, ui.usuario_nome
  limit 100;
query;
#echo $query;
#exit;
$veraBold = "./ttf/VeraBd";
$vera = "./ttf/Vera";
$trebucBd = "./ttf/trebucbd";
$result = pg_query ($link, $query);
$rows = pg_num_rows ($result);
$nameColumnWidth = 170;
$dateColumnWidth = 100;
$rankColumnWidth = 40;
$borderWidth = 5;
$boxWidth = $nameColumnWidth + $dateColumnWidth + $rankColumnWidth + $borderWidth * 2;
$utilWidth = $boxWidth - $borderWidth;
$titleHeight = 30;
$headerHeight = 20;
$lineHeight = 18;
$nmi = imagecreate($boxWidth, $rows * $lineHeight + $borderWidth * 2 + $titleHeight + $headerHeight + 1);
$lightSlateGray = imagecolorallocate($nmi, 119,136,153);
$lightSteelBlue = imagecolorallocate($nmi, 176, 196, 222);
$gainsboro = imagecolorallocate($nmi, 220,220,220);
$floralWhite = imagecolorallocate($nmi, 255,250,240);
$black = imagecolorallocate($nmi, 0, 0, 0);
imagefill ($nmi, 0, 0, $lightSlateGray);
imagefilledrectangle($nmi, $borderWidth, $borderWidth, $utilWidth -1, $titleHeight + $borderWidth, $lightSteelBlue);
$text = "New Members by fahstats.com";
$box = imagettfbbox(12, 0, $veraBold, $text);
$x = ($boxWidth - $borderWidth * 2 - ($box[2] - $box[0])) / 2 + $borderWidth;
imagettftext($nmi, 12, 0, $x, 25, $black, $veraBold, $text);
imagefilledrectangle($nmi, $borderWidth, 36, $utilWidth -1, 55, $floralWhite);
imagettftext($nmi, 10, 0, 10, 50, $black, $veraBold, "Member Name");
imagettftext($nmi, 10, 0, 180, 50, $black, $veraBold, "First WU");
imagettftext($nmi, 10, 0, 273, 50, $black, $veraBold, "Rank");
imageline ($nmi, 5, 54, $utilWidth -1, 54, $black);
$y = $lineHeight +50;
$i = 0;
while ($line = pg_fetch_array($result, NULL, PGSQL_ASSOC)) {
  $color = $i % 2 == 0 ? $gainsboro : $floralWhite;
  imagefilledrectangle($nmi, 5, $y -13, $utilWidth -1, $y +5, $color);
  imagettftext($nmi, 10, 0, 10, $y, $black, $vera,
    utf8_encode($line['name']));
  imagefilledrectangle($nmi, 166, $y -13, $utilWidth -1, $y +5, $color);
  imagettftext($nmi, 10, 0, 172, $y, $black, $vera, substr($line['data'], 5, 11));
  $rank = number_format($line['rank'], 0, ".", ",");
  $box = imagettfbbox(10, 0, $vera, $rank);
  $x = $boxWidth - $borderWidth -6 - $box[2] - $box[0];
  imagettftext($nmi, 10, 0, $x, $y, $black, $vera, $rank);
  $y += $lineHeight;
  $i++;
  }
header ("Content-type: image/png");
imagepng ($nmi);
imagedestroy ($nmi);
?>
