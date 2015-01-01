<tr><TD style="" class=paginador>Page
<?php

for ($i = 0; $i < ((int)(($nLines/$row_count)+(($nLines%$row_count)>0?1:0))); $i++) {
  if ($i +1 == ((int)(($row_count+$offset)/$row_count))) echo $i + 1, " ";
  else {
    echo '<a class=pghref href="/subteam.php?col=' .$col. '&amp;t=' .$team. '&amp;offset=' .($row_count * $i +1) . '&amp;filter=' . $filter . '">';
    echo $i + 1, "</a> ";
    }
  }
?>
</TD></tr>
