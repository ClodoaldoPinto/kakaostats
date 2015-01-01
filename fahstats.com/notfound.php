<html>
<head>
<title>Logfile 404 viewer</title>
</head>
<body>
<pre>
<?
$path=$_SERVER['DOCUMENT_ROOT'].'/../logs';
system ( "grep 404 $path/access_log" );
?>
</pre>
</body>
</html>