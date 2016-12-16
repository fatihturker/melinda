<?php
session_start();
//Makes request to $url and get response
function get_data($url) {
  $ch = curl_init();
  $timeout = 5;
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
  $data = curl_exec($ch);
  curl_close($ch);
  return $data;
}

function escapePunctuation($string){
	$string = preg_replace('/[^a-z0-9]+/i', '_', $string);
}

//Gets partial data from eksisozluk
function get_partial_eksi_data($link){
	$result = get_data($link);
	$pattern = "/<a href=\"(.*?)\">/";
	preg_match_all($pattern, $result, $eksiLink);
	$eksii = "https://eksisozluk.com".$eksiLink[1][0];
	if(strpos($result,"Object moved")!==false){
		return get_partial_eksi_data($eksii);
	}
	return $result;
}

//Gets actual data from eksisozluk
function get_actual_eksi_data($linkArray){
	$myArray = array();
	for($i=0;$i<count($linkArray);$i++){
		$myArray[$i] = get_partial_eksi_data($linkArray[$i]);
	}
	return $myArray;
}

//Makes seed and returns it to create varying random numbers
function make_seed()
{
	list($usec, $sec) = explode(' ', microtime());
	return (float) $sec + ((float) $usec * 100000);
}

//kufur filtresi
function checkKufur($sentence){
	if((strcmp($sentence,"sik")==0) | (strcmp($sentence,"göt")==0) || (strcmp($sentence,"amk")==0)
		|| (strcmp($sentence,"amq")==0) || (strcmp($sentence,"aq")==0) || (strcmp($sentence,"amına")==0) 
		|| (strcmp($sentence,"amcık")==0) || (strcmp($sentence,"yarak")==0)  || (strcmp($sentence,"yarağ")==0)
		|| (strcmp($sentence,"yavşak")==0))
		{
			return true;
		}
		return false;
}

//MultiRequest function for processing multi urls at the same time.
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
		curl_setopt($curly[$id],CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
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
	
	//Query to check the answer of bot is given before came from result_set or not

	$checkSQL5 = mysql_query("Select * from result_set where txt='".mysql_real_escape_string($_SESSION['answer'])."' ");
	$num_rows5 = mysql_num_rows($checkSQL5);
	
	$control=0;
	$wikiTime = 8;
	$time1 = 8;
	$time2 = 8;
	$wikiStartTime = time();
	$startTime1=time();
	$startTime2=time();
	
	$data=array();
	$substrArray=array();

	$Searchword1 = substr($Searchword1, 0, -1); 
	$Searchword1Pieces=explode("_",$Searchword1);

	//put the word combinations on array for multirequest.
	for($substrID=0; $substrID<count($Searchword1Pieces); $substrID++)
	{
		if(count($Searchword1Pieces)==1)
		{
			$substrArray[$substrID]=$Searchword1Pieces[$substrID];
			$data[$substrID]="http://tr.wikipedia.org/wiki/".$substrArray[$substrID];
		}
		else
		{
			if(!empty($Searchword1Pieces[$substrID+1]))
			{
				$substrArray[$substrID]=$Searchword1Pieces[$substrID]."_".$Searchword1Pieces[$substrID+1];
				$data[$substrID]="http://tr.wikipedia.org/wiki/".$substrArray[$substrID];
			}
		}
		

	}
	
	//process links.
	$r = multiRequest($data);


	for($z=0; $z<count($r); $z++)
	{
		//if ceartain time has passed, just break the loop
		$wiki_life = time();
		//echo ($wiki_life-$wikiStartTime);
		//echo "<br>";
		if($wiki_life-$wikiStartTime>$wikiTime)
		{
			//echo "BREakEvEn";
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
	
	if($control==0) 
	{
		$sampleSentence = $_POST['sampleSentence'];
		$Searchword1 = $_POST['searchWord'];
		$Searchword1 = "_".$Searchword1;
		$Searchword1 = substr($Searchword1, 0, -1); 
		$Searchword1Pieces=explode("_",$Searchword1);
		$sozlukData = array();
		$eksiStringArray=array();
		$eksiStringArrayIndex=0;
		$uludagStringArray=array();
		$uludagStringArrayIndex=0;
		$allData = array();
		$allDataIndex = 0;
		
		//create link that consists of word combinations of user sentence
		for($i = 2; $i<=count($Searchword1Pieces); $i++){
			$lifeTime1 = time();
			//echo ($wiki_life-$wikiStartTime);
			//echo "<br>";
			if($lifeTime1-$startTime1>$time1)
			{
				//echo "BREakEvEn";
				break;
			}
			
			for($j=0; $j<$j+1; $j++){
				$sozlukData[$i][$j+1] = array();
				$tmpstr="";
				for($k=0,$l=$j;$k<$i;$k++,$l++){
					$sozlukData[$i][$j+1][$k] = $Searchword1Pieces[$l];
					//echo $sozlukData[$i][$j+1][$k];
					//echo "+";
					if(strlen($tmpstr)>0){
						$tmpstr = $tmpstr."+".$sozlukData[$i][$j+1][$k];
					}else{
						$tmpstr = $sozlukData[$i][$j+1][$k];
					}
				}
				$eksiStringArray[$eksiStringArrayIndex]="https://eksisozluk.com/?q=".$tmpstr;
			//	echo $eksiStringArray[$eksiStringArrayIndex];
			//	echo "<br>";
				$eksiStringArrayIndex++;
				
				$uludagStringArray[$uludagStringArrayIndex]="http://www.uludagsozluk.com/goster.php?k=".$tmpstr;
			//	echo $uludagStringArray[$uludagStringArrayIndex];
			//	echo "<br>";
				$uludagStringArrayIndex++;
				//echo "<br>";
				if($j==(count($Searchword1Pieces)-$i)) break;
			}
		}
		
		//get eksisozluk data and parse
		$eksiDataArray = get_actual_eksi_data($eksiStringArray);
		
		for($z=0;$z<count($eksiDataArray);$z++){
			$pattern = "/<meta property=\"og:description\" content=\"(.*?)\" \/>/";
			preg_match_all($pattern, $eksiDataArray[$z], $eksiSozlukData[$z]);
		//	print_r($eksiSozlukData);
		}
		
		//get uludagsozluk data and parse
		$uludagLinkArray = multiRequest($uludagStringArray);
		
		for($z=0; $z<count($uludagLinkArray); $z++)
		{		
			$pattern = "/<meta name=\"description\" content=\"(.*?)\" \/>/";
			preg_match_all($pattern, $uludagLinkArray[$z], $uludagSozlukData[$z]);
		}
		
		for($k=count($eksiSozlukData);$k>0;$k--){
			$eksiSozlukData[$k][1][0] = iconv("ISO-8859-9","UTF-8",$eksiSozlukData[$k][1][0]);
			$allData[$allDataIndex] =  utf8_decode($eksiSozlukData[$k][1][0]);
			//echo $eksiSozlukData[$k][1][0];
			$allDataIndex++;
		}
		
		for($k=count($uludagSozlukData);$k>0;$k--){
			$uludagSozlukData[$k][1][0] = iconv("ISO-8859-9","UTF-8",$uludagSozlukData[$k][1][0]);
			$allData[$allDataIndex] = $uludagSozlukData[$k][1][0];
			$allDataIndex++;
		}
		$keywordMatrix = array();
		for($p=0;$p<count($Searchword1Pieces);$p++){
		$lifeTime2 = time();
		//echo ($wiki_life-$wikiStartTime);
		//echo "<br>";
		if($lifeTime2-$startTime2>$time2)
		{
			//echo "BREakEvEn";
			break;
		}
			//find most convenient response
			for($r=0;$r<count($allData);$r++){
				if(!empty($allData[$r])&&!empty($Searchword1Pieces[$p])){
					if(strpos($allData[$r], $Searchword1Pieces[$p])!==false){
						$keywordMatrix[$r]++;
					}
				}
			}
		}
		
		$tmpCount=0;
		$tmpIndex=-1;
		for($e=0;$e<count($keywordMatrix);$e++){
			if($tmpCount<$keywordMatrix[$e]){
				$tmpIndex=$e;
				$tmpCount=$keywordMatrix[$e];
			}
		}
		
		if($tmpIndex<0){
			$tmpLength=0;
			for($g=0;$g<count($allData);$g++){
				if(strlen($allData[$g])>$tmpLength){
					$tmpIndex=$g;
					$tmpLength=strlen($allData[$g]);
				}
			}	
		}
		
		/**/
		
		if(strlen($allData[$tmpIndex])>2)
		{
			$dataPieces = explode(".", $allData[$tmpIndex]);
			if(count($dataPieces)>1)
			{
				for($i=0; $i<count($dataPieces)-1; $i++)
				{
					$fixedPiece.=$dataPieces[$i].".";
				}
			}
			else {$fixedPiece=$allData[$tmpIndex];}
			$dataPieces2 = explode("benzeri:", $fixedPiece);
			$dataPieces3=explode(".",$dataPieces2[0]);
			//echo $dataPieces2[0];
			for($i=0; $i<count($dataPieces3)-1; $i++)
			{
				$fixedPiece2.=$dataPieces3[$i].".";
			}

			$dataPieces4=explode(" ",$fixedPiece2);
			
			for($i=0; $i<count($dataPieces4); $i++)
			{
				if(checkKufur($dataPieces4[$i]))
				{
					$fixedPiece3="";
					break;
				}
				else
				{
					$fixedPiece3.=$dataPieces4[$i]." ";
				}
			}
	
			if(strlen($fixedPiece3)>2)
			{
				$ans="Melinda: ".$fixedPiece3;
				echo $ans;

				$sql="INSERT INTO user_logs(user_id, user_ip, log) VALUES('".$_POST['user_id']."', '".$_POST['user_ip']."', '".mysql_real_escape_string($ans)."');";		
				$check = mysql_query($sql);
				if (!$check) 
				{		
					die("Invalid query: " . mysql_error());
				}
				
				$_SESSION['answer']=$allData[$tmpIndex];
				$control=2;
			}
		}
	}
	
	if( ($num_rows5>0) && (stripos($_SESSION['answer'],"?")!==false) && ($control==0) )
	{
		$answerText = "Melinda: Hmmmm. Başka ne hakkında konuşmak istersin?"; 
		echo $answerText;
		echo "<br>";
		$sql="INSERT INTO user_logs(user_id, user_ip, log) VALUES('".$_SESSION['user_id']."', '".$ip."', '".mysql_real_escape_string($answerText)."');";		
		$check = mysql_query($sql);
		if (!$check) 
		{		
			die("Invalid query: " . mysql_error());
		}
		$_SESSION['answer']=$answerText;
		unset($_SESSION['answer']);
		$control=3;
	}
	
	if($control==0)
	{
		$repeatResponseArray = array();
					
		$repeatResponseArray[0]= "Melinda: Sana hiçbir şey demiyorum.";
		$repeatResponseArray[1]= "Melinda: Senin bu yaptığın normal mi?";
		$repeatResponseArray[2]= "Melinda: Hayat ne kadar tuhaf vapurlar falan..";
		$repeatResponseArray[3]= "Melinda: İnsan bazen hayret ediyor..";
		$repeatResponseArray[4]= "Melinda: Sanıyorum bize nazar değdi";
		$repeatResponseArray[5]= "Melinda: Bence artık sen de herkes gibisin.";
		$repeatResponseArray[6]= "Melinda: Sen çok yanlış gelmişsin bence.";	
		$repeatResponseArray[7]= "Melinda: Ah ah ne günlere kaldık...";	
		$repeatResponseArray[8]= "Melinda: Ne desem laf değil.";	
		$repeatResponseArray[9]= "Melinda: Hayat sana güzel valla.";	
		$repeatResponseArray[10]= "Melinda: Buralar eskiden dutluktu.";	
		$repeatResponseArray[11]= "Melinda: Sen sözelciydin galiba..";	
		$repeatResponseArray[12]= "Melinda: Git kendini çok sevdirmeden.";	
		$repeatResponseArray[13]= "Melinda: Ya ameliyatlı yerime gelseydi?";
		$repeatResponseArray[14]= "Melinda: Sen neden böylesin?";
		$repeatResponseArray[15]= "Melinda: Sen bizim üniversiteyle pek ilgilenmiyorsun galiba?";
		
		
		mt_srand(make_seed());
		$randval = mt_rand();
		$whichOne=$randval%16;
					
		echo $repeatResponseArray[$whichOne];
							
		$sql="INSERT INTO user_logs(user_id, user_ip, log) VALUES('".$_POST['user_id']."', '".$_POST['user_ip']."', '".mysql_real_escape_string($repeatResponseArray[$whichOne])."');";		
		$check = mysql_query($sql);
		if (!$check) 
		{		
			die("Invalid query: " . mysql_error());
		}
							
		$_SESSION['answer']=$repeatResponseArray[$whichOne];
		
	}
	
	
	mysql_close($con);
}
?>