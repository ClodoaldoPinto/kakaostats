<?php
if (!isset($_GET['id'])) exit;
$id = $_GET['id'];
if (preg_match('/\D/', $id) == 1 || $id == '') exit;
session_start();
$_SESSION['v'] = '1';
$name = $_GET['name'];
$chart = $_GET['chart'];
switch ($chart)
{
    case "tradar":
        $page = 'trchart.php';
        $title = 'Radar Scope';
        break;
    case "tdaily":
        $page = 'tpchart.php';
        $title = 'Daily History';
        break;
    case "tsize":
        $page = 'team_size_chart.php';
        $title = 'Size';
        break;
    case "ddaily":
        $page = 'upchart.php';
        $title = 'Daily History';
        break;
    case "dradar":
        $page = 'urchart.php';
        $title = 'Radar Scope';
        break;
    default:
        $page = 'index.php';
        $title = '';
        break;
}
require './pgsqlserver.php';
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
<title>Kakao Stats - <?php echo $name . "'s " . $title ?>
</title>
<LINK REL=StyleSheet HREF="/script/ks2-2.css" TYPE="text/css" MEDIA=screen>
<LINK REL="SHORTCUT ICON" href="/favicon.ico">
<script type=="text/javascript">
if (self != top) top.location.href = location.href;
</script>
</head>

<body>
<div style="margin:20px auto 5px;width:100%;text-align:center;">
<script type="text/javascript"><!--
google_ad_client = "pub-6905571259287773";
/* pop_up_chart */
google_ad_slot = "8126538480";
google_ad_width = 728;
google_ad_height = 90;
//-->
</script>
<script type="text/javascript"
src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
</script>
</div>

<div style="margin:10px auto 5px;width:100%;text-align:center;">
<img
  src="<?php echo "/$page?id=$id" ?>"
  width=740 height=370
  alt="<?php echo $title ?>"
  style="border:1px solid black;padding:0;"
  >
</div>

</body>
</html>
