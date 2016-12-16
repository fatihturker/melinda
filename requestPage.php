<?php
session_start();
//this is our multiRequest function for processing multi urls at the same time.
function multiRequest($data, $options = array())
{
	// array of curl handles
	$curly = array();
	// data to be returned
	$result = array();
 
	// multi handle
	$mh = curl_multi_init();
 
	// loop through $data and create curl handles
	// then add them to the multi-handle
	foreach ($data as $id => $d) 
	{
 
		$curly[$id] = curl_init();
 
		$url = (is_array($d) && !empty($d['url'])) ? $d['url'] : $d;
		curl_setopt($curly[$id], CURLOPT_URL,            $url);
		curl_setopt($curly[$id], CURLOPT_HEADER,         0);
		curl_setopt($curly[$id], CURLOPT_RETURNTRANSFER, 1);
 
		// post?
		if (is_array($d)) {
			if (!empty($d['post'])) {
				curl_setopt($curly[$id], CURLOPT_POST,       1);
				curl_setopt($curly[$id], CURLOPT_POSTFIELDS, $d['post']);
			}
		}
 
		// extra options?
		if (!empty($options)) {
			curl_setopt_array($curly[$id], $options);
		}
 
		curl_multi_add_handle($mh, $curly[$id]);
	
	}
 
	// execute the handles
	$running = null;
	do {
		curl_multi_exec($mh, $running);
	} while($running > 0);
 
 
	// get content and remove handles
	foreach($curly as $id => $c) {
		$result[$id] = curl_multi_getcontent($c);
		curl_multi_remove_handle($mh, $c);
	}
 
	// all done
	curl_multi_close($mh);
 
	return $result;
}


if(isset($_POST['searchWord']))
{
	include("db_connection/db_connection_admin.php");
	$sampleSentence = $_POST['sampleSentence'];
    $Searchword1 = $_POST['searchWord'];
	$control=0;
	$wikiTime = 10;
	$wikiStartTime = time();
	$data=array();
	$substrArray=array();
	
	//start from the whole world and remove the last element an each iteration,until its length
	//is smaller than 4, then put them on array for multirequest.
	for($substrID=0; strlen($Searchword1)>3; $substrID++)
	{
		$substrArray[$substrID]=substr($Searchword1, 0, -1);
		$data[$substrID]="http://tr.wikipedia.org/wiki/".$substrArray[$substrID];
		$Searchword1=substr($Searchword1, 0, -1);
		/*echo $Searchword1;
		echo "<br>";*/
	}
	
	//process our links.
	$r = multiRequest($data);

	for($z=0; $z<count($r); $z++)
	{
		//if ceartain time has passed, just break the loop
		$wiki_life = time();
		if($wiki_life-$wikiStartTime>$wikiTime)
		{
			break;
		}
		/*$context = stream_context_create(array('http'=>array('ignore_errors'=>true)));
		$result = file_get_contents($jURL, FALSE, $context);*/

		$pattern = "/<p>(.*?).(.*?)<\/p>/";
		preg_match_all($pattern, $r[$z], $VikiData);
		for($v=0; $v<count($VikiData); $v++)
		{
			$vData = strip_tags($VikiData[0][$v]);

			$VikiSentence = explode(".",$vData);
			//echo $VikiSentence[0];	
			$Searchword2=explode("_", $substrArray[$z]);	
			
			for($m=0; $m<5; $m++)
			{
				/*echo $Searchword2[0];
				echo "<br>";
				echo $VikiSentence[$m];
				echo "<br>";
				echo "<br>";
				echo "<br>";*/
				
				if((!empty($VikiSentence[$m])) && (!empty($Searchword2[0])))
				{
					//if you find a certain keyword in the sentence, insert it into 
					//the database, and print it.
					if(stripos($VikiSentence[$m],$Searchword2[0])!==false)
					{
						if(strlen($VikiSentence[$m+1])<2)
						{
							$ans = "Melinda: ".$VikiSentence[$m].".";
						}
						if(strlen($VikiSentence[$m+1])>2)
						{
							$ans = "Melinda: ".$VikiSentence[$m].". ".$VikiSentence[$m+1].".";
						}
									
						if( (stripos($ans,"anlamlara")!==false) &&( stripos($ans,"gelebilir")!==false))
						{
							$control=0;
							$z=count($r)+1;
							$v=count($VikiData)+1;
							break;
						}
						else
						{
							echo $ans;	
							$sql="INSERT INTO user_logs(user_id, user_ip, log) VALUES('".$_POST['user_id']."', '".$_POST['user_ip']."', '".mysql_real_escape_string($ans)."');";		
							$check = mysql_query($sql);
							if (!$check) 
							{		
								die("Invalid query: " . mysql_error());
							}
							$_SESSION['answer']=$VikiSentence[$m].". ".$VikiSentence[$m+1].".";
							echo "<br>";
							$control=1;
							$z=count($r)+1;
							$v=count($VikiData)+1;
							break;
						}

					}
				}
			}
		}
				
	}
	
	//if wiki search has failed, give user a google link.
	if($control==0) 
	{
		$url1="http://www.google.com.tr/#safe=off&output=search&sclient=psy-ab&q=".urlencode($sampleSentence);
		$link= "<a href=$url1 target=\"_blank\">linkte</a>";
		$ans = "Melinda: Üzgünüm ama aradığın şeyle ilgili uygun bir cevap aklıma gelmedi :( İstersen şu ".$link." aradığın şeyle ilgili daha detaylı cevap bulabilirsin.";
		echo $ans;
		$sql="INSERT INTO user_logs(user_id, user_ip, log) VALUES('".$_POST['user_id']."', '".$_POST['user_ip']."', '".mysql_real_escape_string($ans)."');";		
		$check = mysql_query($sql);
		if (!$check) 
		{		
			die("Invalid query: " . mysql_error());
		}
		$_SESSION['answer']=$ans;
	}
	
	mysql_close($con);
}
?>