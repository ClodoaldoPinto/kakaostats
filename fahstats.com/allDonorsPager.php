<tr><TD style="" class=paginador>Page
<?php

$search_uri = $search ? "&amp;search=".urlencode($search_text) : "";
$bigPage = (int)(($nLines/$row_count)+(($nLines%$row_count)>0?1:0));
for ($i = 0; $i < $bigPage; $i++) {
  $page = (int)(($row_count+$offset)/$row_count);
  if ($i +1 == $page) echo $i + 1, " ";
  elseif ($i%30 == 0 || abs($i -$page) <= 5 || $i +1 == $bigPage) {
    echo "<a class=pghref href='/ad.php?col=" .$col.
      "&amp;offset=" .($row_count * $i +1).
      $search_uri.
      "'>";
    echo $i + 1, "</a> ";
    }

  }
?>
</TD></tr>
