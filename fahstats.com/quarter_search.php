<?php
?>
<form name="quarter" action="" id="quarter"
  style="
    border: 0px solid black;
    margin: 0.5em auto 0.5em;
    padding: 0;
    display: block;"
    >
<p style="
    margin: 0;
    padding: 0;
    text-align: center;
    color: floralWhite;
    display: inline;
    border: 0;">
Quarter:
<select size="1" style="margin-right:1.5em;"
  onchange="document.getElementById('quarter').submit()"
  id="quarterYear" name="quarterYear">
<?php
foreach ($aQuarter as $year => $quarters) {
	foreach($quarters as $quarter) {
		echo '<option value="'.$year.$quarter.'"';
		if ($year.$quarter == $quarterYear) echo 'selected';
		echo '>'.$year.' - '.$quarter;
	}
}
?>
</select>
<input type="hidden" value="<?php echo $team ?>" name="t">
</p>
</form>
