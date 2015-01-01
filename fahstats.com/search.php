<?php
?>

<form name="search" action=""
  style="
    border: 0px solid black;
    margin: 10px auto 5px;
    padding: 0;"
  onsubmit="sload(<?php echo $col ?>);return false;">
<p style="
    margin: 0;
    padding: 0;
    text-align: center;
    color: floralWhite;
    display: inline;
    border: 0;">
<?php echo $search_box_text ?> name contains:
<input type="text" id="search_text" size="20" maxlength="30"
  value="<?php echo htmlentities(urldecode($search_text), ENT_QUOTES, 'UTF-8') ?>"
  style="background:floralwhite;margin:0 0.5em 0 0;">
<input type="submit" name="search_button" value="Search"
  style="background:gainsboro;margin:0 0.5em;padding:0;">
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
