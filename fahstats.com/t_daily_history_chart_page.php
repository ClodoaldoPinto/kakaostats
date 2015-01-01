<?php
if (!isset($_GET['d'])) exit;
$donor = $_GET['d'];
$donorName = $_GET['dn'];
if (preg_match('/\D/', $donor) == 1 || $donor == '') exit;
session_start();
$_SESSION['v'] = '1';
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
<title>Kakao Stats - <?php echo $donorName ?>'s Production History
</title>
<?php include "./meta.html"; ?>
</head>

<body>
<img
  src="./tpchart.php?t=<?php echo $donor ?>"
  width=740 height=370
  alt="Donor Production History Chart"
  style="border:1px solid black;padding:0;margin:0;"
  >

</body>
</html>
