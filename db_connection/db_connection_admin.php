<?php include("db_connection_info_admin.php"); ?>
<?php 
	$con = mysql_connect($db_host,$db_username,$db_pass);  //burada database'e ba?lan?yor
			
			if (!$con){
					die('Could not connect: ' . mysql_error());
		    }
			
            $db_select = mysql_select_db($db_name, $con);
			mysql_query("SET NAMES 'utf8'");
			mysql_query("SET character_set_connection = 'utf8");
			mysql_query("SET character_set_client = 'utf8'");
			mysql_query("SET character_set_results = 'utf8'");
   				   
		    if(!$db_select){ 
					die("veritabani secim hatasi:".mysql_error());
			}
?>