<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="tr" lang="tr">

<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
</head>

<body>
<?php

include 'simplexlsx.php';
include ("../db_connection/db_connection_admin.php");
$xlsx = new SimpleXLSX('ResultSet2.xlsx');
echo '<h1>$xlsx->rows()</h1>';
echo '<pre>';
for($i=0;$i<count($xlsx->rows());$i++){
	$row = $xlsx->rows();
	echo $row[$i][0];
	$sql="INSERT INTO result_set(txt, topic_id) VALUES('".str_replace("'"," ",$row[$i][0])."', '".$row[$i][1]."');";		
	$check = mysql_query($sql);
	if (!$check) 
	{	
			die("Invalid query: " . mysql_error());
	}
	echo "<br>";

}
echo '</pre>';

?>
</body>
</html>