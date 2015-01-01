<?php
ob_start("ob_gzhandler");
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<?php
$team = $_GET['t'];
if (preg_match('/\D/', $team) == 1) exit;
$link = mysql_connect('mysql08.powweb.com', 'ksPhpUser', 'phpUser');
mysql_select_db('kakaostats');
$query = 'select * from teamsRadar where team=' .$team. ' order by date, rank_challenger;';
$result = mysql_query($query);
$line = mysql_fetch_array($result,MYSQL_ASSOC);
?>
<html><head>
<?php include "./meta.html"; ?>
<script type="text/javascript" src="./script/js.js"></script>
<LINK REL=StyleSheet HREF="./script/ks2.css" TYPE="text/css" MEDIA=screen>
<LINK REL="SHORTCUT ICON" href="./favicon.ico">
<title>Kakao Folding@Home Stats - <?php echo htmlentities($line['name'], ENT_QUOTES);?></title>
<script type="text/javascript" src="./overlib/mini/overlib_mini.js"><!-- overLIB (c) Erik Bosrup --></script>
</head>
<body style="text-align:center;"
	onload="roll();">
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>
<p style="margin:0;margin-bottom:1.5em;text-align:left;background:lavender;padding:0;">
<img src="./img/ks.png" alt="Kakao Folding Stats" width="50" height="45"
	title="Kakao Folding Stats" style="margin:0;vertical-align:top;">
<?php include "./cab2.html"; ?>
<span style="font-size:120%;">
<a class=pwa href="./">All Teams</a> | 
<a class=pwa href="./t.php?t=<?php echo $team?>">Team Members</a> | 
<a class=pwa href="./tp.php?t=<?php echo $team?>">Team History</a>
</span>
</p>
<p style="text-align:center;margin:0 auto;font-size:22px;color:#E0FFFF;margin-bottom:0.5em;">
<?php
	$queryDate = "select date from date where 1";
	$resultDate = mysql_query($queryDate);
	$lineDate = mysql_fetch_array($resultDate,MYSQL_ASSOC);
	$date = $lineDate["date"];

	echo htmlentities($line['name'],ENT_QUOTES), " at ",
	"<span style=\"font-size:18px;\">",
	"<script type='text/javascript'>",
	"document.write(wDate('$date')+' '+timeZone());",
	"</script></span>";
?> </p>
<div style="margin-top:0;margin-bottom:1em;padding:0;">
<img src="./trchart.php?t=<?php echo $team ?>&amp;l=<?php echo $lastDayUpdate ?>"
	width=720 height=360 alt="Team Production History Chart"
	 style="border:1px solid black;padding:0;margin:0;">
</div>
<table class=tabela cellspacing=0 cellpadding=0 style="margin:0 auto;padding:0;">
<tr>
<td class=top>
<table cellspacing=0 cellpadding=0 class="cab top">
	<tr>
		<td style="text-align:center;font-size:150%;">Team Radar Screen
		</td>
	</tr>
</table>
</td>
</tr>
<tr>
<td class=top>
<table cellspacing=0 class="corpo">
<thead>
<tr><th colspan=3 style="border-left:0;">Overtake</th>
<th rowspan=3 onmouseover="return overlib('Team overtaking or being overtaked');"
	onmouseout="return nd();"
	>Challenging Team</th>
<th rowspan=1 colspan=5 style="border-bottom:0;"
	>Challenging Team Stats</th>
<tr><th rowspan=2 style="border-left:0;" onmouseover="
	return overlib('Future team ranking<br />Green - conquered position<br />Red - lost position');"
	onmouseout="return nd();"
	>Ran-<br />king</th>
<th rowspan=2 onmouseover="
	return overlib('Days until the ranking change');"
	onmouseout="return nd();"
	>Days</th>
<th rowspan=2 onmouseover="
	return overlib('Date of the ranking change');"
	onmouseout="return nd();"
	>Date</th>
<th rowspan=2 style="border-top: 1px solid black;" onmouseover="
	return overlib('Current ranking of the challenging team');"
	onmouseout="return nd();"
	>Ran-<br />king</th>
<th colspan=4 style="border-top: 1px solid black;">Points</th></tr>
<tr>
<th onmouseover="
	return overlib('Points scored by the challenging team in the last 24 hours');"
	onmouseout="return nd();"
	>24 hrs</th>
<th onmouseover="
	return overlib('Points scored by the challenging team in the last 7 days');"
	onmouseout="return nd();"
	>7 days</th>
<th onmouseover="
	return overlib('Total points of the challenging team');"
	onmouseout="return nd();"
	>Total</th>
<th onmouseover="
	return overlib('Difference in points from this team to the challenging team');"
	onmouseout="return nd();"
	>Difference</th></tr>
</thead><tbody>
<?php
$points_0 = $line['points_0'];
$n_linha = 0;
do {
	$n_linha++;
	if ($line['points_7'] >= 75000 * 7) $cor7 = "c1";
	elseif ($line['points_7'] >= 50000 * 7) $cor7 = "c2";
	elseif ($line['points_7'] >= 30000 * 7) $cor7 = "c3";
	elseif ($line['points_7'] >= 15000 * 7) $cor7 = "c4";
	elseif ($line['points_7'] >= 5000 * 7) $cor7 = "c5";
	elseif ($line['points_7'] >= 2500 * 7) $cor7 = "c6";
	elseif ($line['points_7'] >= 1) $cor7 = "c7";
	else $cor7 = "c8";

	if ($line['points_24'] >= 75000) $cor24 = "c1";
	elseif ($line['points_24'] >= 50000) $cor24 = "c2";
	elseif ($line['points_24'] >= 30000) $cor24 = "c3";
	elseif ($line['points_24'] >= 15000) $cor24 = "c4";
	elseif ($line['points_24'] >= 5000) $cor24 = "c5";
	elseif ($line['points_24'] >= 2500) $cor24 = "c6";
	elseif ($line['points_24'] >= 1) $cor24 = "c7";
	else $cor24 = "c8";

	if ($n_linha % 2 == 0) $classe = " class=ls";
	else $classe = "";
	echo "<tr ", $classe, " onmouseover='ron(this);' onmouseout='roff(this);'>";
	echo "<td class=", $line['rg'], ">", number_format($line['rank_f'], 0, ".", ","), "</td>";
	echo "<td class=", $line['rg'], ">", number_format($line['days'], 0, ".", ","), "</td>";
	echo "<td class=txt>",
		"<script type='text/javascript'>",
		"document.write(wDate2('" .$line['date']. "'));",
		"</script>",
		"</td>";
	echo "<td style='' class='txt $cor7'>";
	if ($line['challenger'] != -1)
		echo "<a href=./t.php?t=", $line['challenger'], " class=", $cor7, ">", htmlentities($line['name'], ENT_QUOTES), "</a>";
	else echo htmlentities($line['name'], ENT_QUOTES);
	echo "</td>";
	echo "<td>", number_format($line['rank_challenger'], 0, ".", ","), "</td>";
	echo "<td class=", $cor24, ">", number_format($line['points_24'], 0, ".", ","), "</td>";
	echo "<td class=", $cor7, ">", number_format($line['points_7'], 0, ".", ","), "</td>";
	echo "<td>", number_format($line['points_0'], 0, ".", ","), "</td>";
	echo "<td>", number_format($line['difference'], 0, ".", ","), "</td>";
	echo "</tr>\n";
	} while ($line = mysql_fetch_array($result,MYSQL_ASSOC));
?>
</tbody></table></td></tr></table>
<?php include "./footer.html"; ?>
</body>
</html>
<?php
ob_end_flush();
?>
