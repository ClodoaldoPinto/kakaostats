<?php
?>
<form name="search" action="" id="search"
  style="
    border: 0px solid black;
    margin: 5px auto;
    padding: 0;
    display: block;">
<p style="
    margin: 0;
    padding: 0;
    text-align: center;
    color: floralWhite;
    display: inline;
    border: 0;">
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
<?php echo $search_box_text ?> name contains:
<input type="text" id="search_text" size="20" maxlength="30"
  value="<?php echo htmlentities(urldecode($search_text), ENT_QUOTES, 'UTF-8') ?>"
  style="background:floralwhite;margin:0;"
  name="search_text">
<input type="submit" value="Search"
  style="background:gainsboro;margin:0;padding:0;">
<input type="button" value="Clear"
  style="
    background: gainsboro;
    padding: 0;
    margin:0;"
  onclick="
  document.getElementById('search_text').value = '';
  document.getElementById('search_text').focus();
  ">
</p>
</form>
