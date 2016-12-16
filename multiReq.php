<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
</head>
<?php
 
function multiRequest($data, $options = array()) {
 
  // array of curl handles
  $curly = array();
  // data to be returned
  $result = array();
 
  // multi handle
  $mh = curl_multi_init();
 
  // loop through $data and create curl handles
  // then add them to the multi-handle
  foreach ($data as $id => $d) {
 
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
 
 
$data=array();
$substrArray=array();
$Searchword1="Metal_Müzik_dsgdsg_fdsgd_sd_gsdgsdgsdgs_gsgsdgd_sgsdgds_gdsdgs_dsd_gsdg_sfbfgfgfggggggggggggggggggggggggggggggggggggggggggggggggggggggggghghhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhh_";
for($substrID=0; strlen($Searchword1)>2; $substrID++)
{
	$substrArray[$substrID]=substr($Searchword1, 0, -1);
	$data[$substrID]="http://tr.wikipedia.org/wiki/".$substrArray[$substrID];
	$Searchword1=substr($Searchword1, 0, -1);
	/*echo $substrArray[$substrID];
	echo "<br>";*/
	
}

/*
$data = array(
  'http://tr.wikipedia.org/wiki/Alişan',
  'http://tr.wikipedia.org/wiki/Deep_Purple',
  'http://tr.wikipedia.org/wiki/Opeth',
  'http://tr.wikipedia.org/wiki/Metal'
);*/
$r = multiRequest($data);

//$Searchword1=array("Alişan_","Deep_Purple_","Opeth_","Metal_");
 
		for($i=0; $i<count($r); $i++)
		{
			//echo $r[$i];
			//$VikiSentence=array();
			//$Searchword1="Metal_";
			//$jURL="http://tr.wikipedia.org/wiki/".$Searchword1[$i];
			//$jURL=$r[$i];
			//$context = stream_context_create(array('http'=>array('ignore_errors'=>true)));
			//$result = file_get_contents($jURL, FALSE, $context);
	
			$pattern = "/<p>(.*?).(.*?)<\/p>/";
			//preg_match_all($pattern, $result, $VikiData);
			preg_match_all($pattern, $r[$i], $VikiData);
			$vData = strip_tags($VikiData[0][0]);
			//echo $VikiData[0][0];
			$VikiSentence = explode(".",$vData);
			
			$Searchword2=explode("_", $substrArray[$i]);
			
			for($m=0; $m<5; $m++)
			{
				if((!empty($VikiSentence[$m])) && (!empty($Searchword2[0])))
				{
					if(strpos($VikiSentence[$m],$Searchword2[0])!==false)
					{
						if(strlen($VikiSentence[$m+1])<3)
						{
							$ans =  "Melinda: ".$VikiSentence[$m].".";
							if(strpos($ans,"şu anlamlara gelebilir")!==false)
							{
								$control=0;
								break;
							}
							else
							{
								echo $ans;
								echo "<br>";
								$control=1;
								break;
							}
						}
						
						else
						{
							$ans = "Melinda: ".$VikiSentence[$m].". ".$VikiSentence[$m+1].".";
							
							if(strpos($ans,"şu anlamlara gelebilir")!==false)
							{
								$control=0;
								break;
							}
							else
							{
								echo $ans;	
								echo "<br>";
								$control=1;
								break;
							}
						}
						
						break;
					}
				}
			}
			
		}




?>


<?php/*
if(isset($_POST['searchWord'])){
	include("db_connection/db_connection_admin.php");
	$sampleSentence = $_POST['sampleSentence'];
    $Searchword1 = $_POST['searchWord'];
	$control=0;
	$wikiTime = 30;
	$wikiStartTime = time();
	for($z=0; strlen($Searchword1)>2; $z++)	
	{
		$wiki_life = time();
		//echo ($wiki_life-$wikiStartTime);
		//echo "<br>";
		if($wiki_life-$wikiStartTime>$wikiTime){
			//echo "BREakEvEn";
			break;
		}
		for($t=0; $t<5; $t++)
		{
			$jURL="http://tr.wikipedia.org/wiki/".$Searchword1;

			$context = stream_context_create(array('http'=>array('ignore_errors'=>true)));
			$result = file_get_contents($jURL, FALSE, $context);
	
			$pattern = "/<p>(.*?).(.*?)<\/p>/";
			preg_match_all($pattern, $result, $VikiData);

			$vData = strip_tags($VikiData[0][$t]);
			$VikiSentence = explode(".",$vData);
			
			
			$Searchword2=explode("_",$Searchword1);
						
			for($m=0; $m<5; $m++)
			{
				if(strpos($VikiSentence[$m],$Searchword2[0])!==false)
				{
					if(strlen($VikiSentence[$m+1])<3)
					{
						$ans =  "Melinda: ".$VikiSentence[$m].".";
						if(strpos($ans,"şu anlamlara gelebilir")!==false)
						{
							$control=0;
							$Searchword1="";
							$m=5;
							$t=5;
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
							$_SESSION['answer']=$VikiSentence[$m].".";
							$control=1;
						}
					}
					
					else
					{
						$ans = "Melinda: ".$VikiSentence[$m].". ".$VikiSentence[$m+1].".";
						
						if(strpos($ans,"şu anlamlara gelebilir")!==false)
						{
							$control=0;
							$Searchword1="";
							$m=5;
							$t=5;
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
							$control=1;
						}
					}
					
					break;
				}
			}
			if($control==1) {break;}
	
		}
		if($control==1) {break;}
		$Searchword1=substr($Searchword1, 0, -1); 
	}
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
}
*/
?>