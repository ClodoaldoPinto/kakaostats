<?php
ob_start("ob_gzhandler");
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<?php
if (preg_match('/\D/', $_GET['u']) == 1) exit;
include './mysqlserver.php';
$memberNumber = $_GET['u'];
$link = mysql_connect($mysql_server, 'ksPhpUser', 'phpUser');
mysql_select_db('kakaostats');
$query = 'select team_number as team from donorIndex where donor_number=' .$memberNumber. ' limit 1';
$result = mysql_query($query);
$line = mysql_fetch_array($result, MYSQL_ASSOC);
$team = $line['team'];
$query = 'select d.points_7, d.points_24, d.points_0, d.name as ativo, t.donor_name as name, d.days, d.date, d.challengerNumber, d.challengerTeamRank, d.challengerProjectRank ' . 
	'from donorPages as d inner join donorIndex as t on d.challengerNumber = t.donor_number ' .
	'where d.number=' . $memberNumber . ' order by date, not(d.number=d.challengerNumber);';
$result = mysql_query($query);
$line = mysql_fetch_array($result, MYSQL_ASSOC);
?>
<html><head>
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=ISO-8859-1">
<LINK REL=StyleSheet HREF="./script/ks2.css" TYPE="text/css" MEDIA=screen>
<LINK REL="SHORTCUT ICON" href="./favicon.ico">
<title>Kakao Folding@Home Stats - <?php echo htmlentities($line['name'], ENT_QUOTES);?></title>
<script type="text/javascript" src="./script/js.js"></script>
<script type="text/javascript" src="./overlib/mini/overlib_mini.js"><!-- overLIB (c) Erik Bosrup --></script>
</head><body style="text-align:center;">
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>
<table style="width:100%;margin:0;margin-bottom:1.5em;text-align:left;background:lavender;padding:0;">
<tr style="padding:0;margin:0;"><TD style="padding:0;margin:0;">
<img src="./img/ks.png" width="50" height="45" alt="Kakao Folding Stats" title="Kakao Folding Stats" style="margin:0;vertical-align:top;">
<td style="padding:0;margin:0;">
<?php include "./cab2.html"; ?>
</TD>
<td style="padding:0;margin:0;">
<a class=pwa href="./ad.php">All Donors</a> | 
<a class=pwa href="./"><span style="white-space:nowrap;">All Teams</span></a> | 
<a class=pwa href="./t.php?t=<?php echo $team?>"><span style="white-space:nowrap;">Team Members</span></a> | 
<a class=pwa href="./tp.php?t=<?php echo $team?>"><span style="white-space:nowrap;">Team History</span></a> | 
<a class=pwa href="./tr.php?t=<?php echo $team?>"><span style="white-space:nowrap;">Team Radar Screen</span></a>
</td></tr></table>
<table class=tabela cellspacing=0 cellpadding=0 style="margin:0 auto;padding:0;"><tr>
<td class=top><table cellspacing=0 cellpadding=0 class="cab top"><tr>
<td style="font-size:22px;text-align:center;">
<?php
	$query = "select date from date where 1";
	$resultData = mysql_query($query);
	$lineDate = mysql_fetch_array($resultData,MYSQL_ASSOC);
	$date = $lineDate["date"];
 
	echo htmlentities($line['name'],ENT_QUOTES), " Radar Screen at ",
	"<span style=\"font-size:18px;\">",
	"<script type=text/javascript>",
	"document.write(wDate('$date')+' '+timeZone());",
	"</script></span>"
?>
</td></tr></table></td></tr>
<tr><td class=top>
<table cellspacing=0 class=corpo><thead>
<tr><th colspan=3 style="border-left:0;">Overtake</th>
<th rowspan=3 style=""
	onmouseover="return overlib('Team member overtaking or being overtaked');"
	onmouseout="return nd();"
	>Challenging Member</th>
<th colspan=6>Challenging Member Stats</th></tr>
<tr>
<th rowspan=2 style="border-left:0;" onmouseover="
	return overlib('Future member team ranking<br />Green - conquered position<br />Red - lost position');"
	onmouseout="return nd();"
	>Team<br />ran-<br />king</th>
<th rowspan=2
	onmouseover="return overlib('Days until the ranking change');"
	onmouseout="return nd();"
	>Days</th>
<th rowspan=2
	onmouseover="return overlib('Date of the ranking change');"
	onmouseout="return nd();"
	>Date</th>
<th rowspan=1 colspan=2>Ranking</th>
<th colspan=4>Points</th></tr>
<tr>
<th	onmouseover="return overlib('Current team ranking of the challenging member');"
	onmouseout="return nd();"
	>Team</th>
<th	onmouseover="return overlib('Current project ranking of the challenging member');"
	onmouseout="return nd();"
	>Project</th>
<th	onmouseover="return overlib('Points scored by the challenging member in the last 24 hours');"
	onmouseout="return nd();"
	>24 hrs</th>
<th	onmouseover="return overlib('Points scored by the challenging member in the last seven days');"
	onmouseout="return nd();"
	>7 days</th>
<th	onmouseover="return overlib('Total points of the challenging member');"
	onmouseout="return nd();"
	>Total</th>
<th	onmouseover="return overlib('Difference in points from this member to the challenging member');"
	onmouseout="return nd();"
	>Difference</th>
</tr>
</thead><tbody>
<?php
$points_0 = $line['points_0'];
$n_linha = 0;
$rank = $line['challengerTeamRank'];
$rankDonor0 = $rank;
$points7Donor0 = $line['points_7'];
do {
	if ($rankDonor0 == $line['challengerTeamRank']) $rg = 'p';
	elseif ($points7Donor0 > $line['points_7'] and $rankDonor0 > $line['challengerTeamRank']) {
		$rank -= 1;
		$rg = 'g';
		}
	else {
		$rank += 1;
		$rg = 'r';
		}
	$n_linha++;
	if ($line['points_7'] >= 1600 * 7) $cor7 = "c1";
	elseif ($line['points_7'] >= 1200 * 7) $cor7 = "c2";
	elseif ($line['points_7'] >= 800 * 7) $cor7 = "c3";
	elseif ($line['points_7'] >= 400 * 7) $cor7 = "c4";
	elseif ($line['points_7'] >= 200 * 7) $cor7 = "c5";
	elseif ($line['points_7'] >= 100 * 7) $cor7 = "c6";
	elseif ($line['points_7'] >= 1) $cor7 = "c7";
	else $cor7 = "c8";
	
	if ($line['points_24'] >= 1600) $cor24 = "c1";
	elseif ($line['points_24'] >= 1200) $cor24 = "c2";
	elseif ($line['points_24'] >= 800) $cor24 = "c3";
	elseif ($line['points_24'] >= 400) $cor24 = "c4";
	elseif ($line['points_24'] >= 200) $cor24 = "c5";
	elseif ($line['points_24'] >= 100) $cor24 = "c6";
	elseif ($line['points_24'] >= 1) $cor24 = "c7";
	else $cor24 = "c8";
	
	$name = $line['name'];
	if (strlen($name) > 15) {
		$a = explode("_", $name);
		foreach($a as $key => $value) {
			if (strlen($value) > 15) {
				$a[$key] = chunk_split($value, 15, " ");
				$a[$key] = substr($a[$key], 0, strlen($a[$key]) -1);
				}
			}
		$name = implode(" ", $a);
		}
	
	if ($n_linha % 2 == 0) $classe = " class='ls'";
	else $classe = "";
	echo "<tr ", $classe, " onmouseover='ron(this);' onmouseout='roff(this);'>";
	echo "<td class=", $rg, ">", number_format($rank, 0, ".", ","), "</td>";
	echo "<td class=", $rg, ">", number_format($line['days'], 0, ".", ","), "</td>";
	echo "<td style='width:11em;' class=txt>",
		"<script type=text/javascript>",
		"document.write(wDate2('".$line["date"]."'));",
		"</script>",
		"</td>";
	echo "<td style='' class='txt $cor7'>";
	if ($line['ativo'] != '0')
		echo "<a href=./u.php?u=", $line['challengerNumber'], " class=", $cor7, ">", str_replace(" ", "&shy; ", htmlentities($name, ENT_QUOTES)), "</a>";
	else echo str_replace(" ", "&shy; ", htmlentities($name, ENT_QUOTES));
	echo "</td>";
	echo "<td>", number_format($line['challengerTeamRank'], 0, ".", ","), "</td>";
	echo "<td>", number_format($line['challengerProjectRank'], 0, ".", ","), "</td>";
	echo "<td class=", $cor24, ">", number_format($line['points_24'], 0, ".", ","), "</td>";
	echo "<td class=", $cor7, ">", number_format($line['points_7'], 0, ".", ","), "</td>";
	echo "<td>", number_format($line['points_0'], 0, ".", ","), "</td>";
	echo "<td class=br>", number_format($line['points_0'] - $points_0, 0, ".", ","), "</td>";
	echo "</tr>\n";
	} while ($line = mysql_fetch_array($result,MYSQL_ASSOC));
?>
</tbody></table></td></tr></table>
<?php include "./footer.html"; ?>
</body></html>
<?php
ob_end_flush();
?>
