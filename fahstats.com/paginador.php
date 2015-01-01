<tr><TD style="" class=paginador>Page
<?php

$search_uri = $search ? "&amp;search=".urlencode($search_text) : "";

$pages = 1 + (int)(($nLines -1) / $row_count);
$page = (int)(($row_count + $offset) / $row_count);
$m = 4;
$sp = array(1, $pages, (int)($page / 2), (int)(($pages - $page) / 2) + $page);
for ($i = $page - $m; $i <= $page + $m; $i++) {
   if ($i > 1 && $i < $pages) {
      $sp[] = $i;
   }
}
$sp = array_diff($sp, array(0));
$sp = array_unique($sp);
sort($sp, SORT_NUMERIC);
foreach ($sp as $value) {
  if ($value == $page) echo $value, " ";
  else {
    echo "<a class=pghref href='./?col=".$col.
      "&amp;offset=".($row_count * ($value -1) +1).
      $search_uri.
      "'>";
    echo $value, "</a> ";
    }
  }
?>
</TD></tr>
