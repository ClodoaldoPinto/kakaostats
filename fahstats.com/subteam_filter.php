<?php
?>
<form name="search" action="" id="search"
  style="
    border: 0px solid black;
    margin: 0.5em auto 0.5em;
    padding: 0;
    display: block;"
<p style="
    margin: 0;
    padding: 0;
    text-align: center;
    color: floralWhite;
    display: inline;
    border: 0;">
<span style="margin-right:1em;">
<a href="http://forum.fahstats.com/index.php/topic,380.msg564.html#msg564">
About subteams</a></span>
Total:
<span style="padding:0.2em;color:black;background:#ffd800;margin-right:1em;">
<?php echo number_format($nLines, 0, ".", ",") ?></span>
Filter:
<select size="1" style="margin-right:1.5em;"
  onchange="document.getElementById('search').submit()"
  id="filter" name="filter">
  <option value="active" <?php echo $filter == 'active' ? 'selected' : '' ?>>Active
  <option value="new" <?php echo $filter == 'new' ? 'selected' : '' ?>>New
  <option value="all" <?php echo $filter == 'all' ? 'selected' : '' ?>>All
  <option value="inactive" <?php echo $filter == 'inactive' ? 'selected' : '' ?>>Inactive
</select>
<input type="hidden" value="<?php echo $col ?>" name="col">
<input type="hidden" value="<?php echo $team ?>" name="t">
</p>
</form>
