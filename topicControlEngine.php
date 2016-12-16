<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="tr" lang="tr">

<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
</head>
<body>
<?php
	include("db_connection/db_connection_admin.php");
	class keywordRanking{
		public $id;
		public $rank;
		public $ratio;
		public $category_id;
	}
	function arrayContainsObject($arr,$o){
		for($i=0;$i<count($arr);$i++){
			if($arr[$i]->id==$o->id){
				return $i;
			}
		}
		return -1;
	}
	$sql=mysql_query("SELECT * FROM topic");
	$num_rows = mysql_num_rows($sql);
	echo $num_rows;
	echo "<br>";
	while($row=mysql_fetch_array($sql)){
		$sampleSentence=$row['name'];
		$sampleID = $row['id'];
		$sampleCategory = $row['category_id'];
		$wordArray = array();
		$wordArrayIndex = 0;
		$abbreviation[0]=array("naber","nbr","napıyon","nabıyon", "naptın");
		$abbreviation[1]=array("kanka","kank","kanqa","panpa");
		$abbreviation[2]=array("selam","slm","mrb","merhaba","meraba","hey");
		$abbreviation[3]=array("evet","evt","eet");
		$words = preg_split('#[\\s.]#', $sampleSentence, -1, PREG_SPLIT_NO_EMPTY);
		$i=0;
		while($i<count($words)){
			if(!preg_match('#[0-9]#',$words[$i])){
				$words[$i] = preg_replace("/(?![.=$'€%-])\p{P}/u", "", $words[$i]);
				if(strlen($words[$i])>2)
				{
					
					for($k=0; $k<count($abbreviation); $k++)
					{
						for($l=0; $l<count($abbreviation[$k]); $l++)
						{
							if(strpos($abbreviation[$k][$l],$words[$i])!==false)
							{
								$words[$i]=$abbreviation[$k][0];
							}
						
						}
						
					}
					if(strlen($words[$i])>2){
						$wordArray[$wordArrayIndex]=$words[$i];
						$wordArrayIndex++;
					}
				}
			}else{
					if(strlen($words[$i])>2){
						$wordArray[$wordArrayIndex]=$words[$i];
						$wordArrayIndex++;
					}
			}

			

			$i++;
		}

		$keywordArray = array();
		$keywordArrayIndex=0;
		$loopIndex=0;
		$rankListArray = array();
		$rankListArrayIndex = 0;

		while($loopIndex<count($wordArray)){
			/*echo $wordArray[$loopIndex];
			echo "<br>";*/
			$rankList = array();
			$rankListIndex=0;
			$rootOfTheWord = $wordArray[$loopIndex];
			$checkSQL = mysql_query("Select * from topic where name LIKE '{$wordArray[$loopIndex]}%' ");
			if(mysql_num_rows($checkSQL)>0){
				while($row=mysql_fetch_array($checkSQL)){
					$nKeyword = new keywordRanking;
					$nKeyword->id=$row['id'];
					$tword = preg_split('#[\\s.]#', $row['name'], -1, PREG_SPLIT_NO_EMPTY);
					$nKeyword->rank += 100/count($tword);
					$nKeyword->ratio = 100/count($tword);
					$nKeyword->category_id = $row['category_id'];
					//echo $nKeyword->rank;
					//echo "<br>";
					$rankList[$rankListIndex]=$row['id'];
					$rankListIndex++;
					$checkIndex=arrayContainsObject($keywordArray,$nKeyword);
					//echo $checkIndex;
					if($checkIndex===-1){
						$keywordArray[$keywordArrayIndex]=$nKeyword;
						$keywordArrayIndex++;
					}else{
						$nKeyword = $keywordArray[$checkIndex];
						$nKeyword->rank += 100/count($tword);
						//echo $nKeyword->rank;
						//echo "<br>";
						$keywordArray[$checkIndex]=$nKeyword;
					}
				}
			}
			
			while(strlen($rootOfTheWord)>4){
				$rootOfTheWord = mb_substr($rootOfTheWord, 0, -1,'UTF-8');
				$checkSQL = mysql_query("Select * from topic where name LIKE '{$rootOfTheWord}%' ");
				if(mysql_num_rows($checkSQL)>0){
					while($row=mysql_fetch_array($checkSQL)){
						$nKeyword = new keywordRanking;
						$nKeyword->id=$row['id'];
						$tword = preg_split('#[\\s.]#', $row['name'], -1, PREG_SPLIT_NO_EMPTY);
						$nKeyword->rank += 100/count($tword);
						$nKeyword->ratio = 100/count($tword);
						$nKeyword->category_id = $row['category_id'];
						//echo $nKeyword->rank;
						//echo "<br>";
						$rankList[$rankListIndex]=$row['id'];
						$rankListIndex++;
						$checkIndex=arrayContainsObject($keywordArray,$nKeyword);
						//echo $checkIndex;
						if($checkIndex===-1){
							$keywordArray[$keywordArrayIndex]=$nKeyword;
							$keywordArrayIndex++;
						}else{
							$nKeyword = $keywordArray[$checkIndex];
							$nKeyword->rank += 100/count($tword);
							//echo $nKeyword->rank;
							//echo "<br>";
							$keywordArray[$checkIndex]=$nKeyword;
						}
					}
				}
			}
			
			$rankListArray[$rankListArrayIndex] = $rankList;
			$rankListArrayIndex++;
			$loopIndex++;
		}

		$checkLoopIndex=0;

		$countIndexArray = array();
		$countIndexArrayIndex = 0;
		$tmpMaxValue = 0;

		while($checkLoopIndex<count($rankListArray)){
			$countIndexArray[$checkLoopIndex] = array();
			for($i=0;$i<count($rankListArray[$checkLoopIndex]);$i++){
				if($countIndexArray[$checkLoopIndex][$rankListArray[$checkLoopIndex][$i]]>0){
					$countIndexArray[$checkLoopIndex][$rankListArray[$checkLoopIndex][$i]]++;
				}else{
					$countIndexArray[$checkLoopIndex][$rankListArray[$checkLoopIndex][$i]]=1;
				}
				//echo $countIndexArray[$rankListArray[$checkLoopIndex][$i]];
				$tmpValue = $rankListArray[$checkLoopIndex][$i];
				
				if($tmpValue>$tmpMaxValue){				
					$tmpMaxValue=$tmpValue;
				}
			}
			for($k=0;$k<=$tmpMaxValue;$k++){
				if($countIndexArray[$checkLoopIndex][$k]>1){
					while($countIndexArray[$checkLoopIndex][$k]!=1){
						for($j=0;$j<count($keywordArray);$j++){
							if($keywordArray[$j]->id==$k){
								$keywordArray[$j]->rank -= $keywordArray[$j]->ratio;
							}
						}
						$countIndexArray[$checkLoopIndex][$k]--;
					}
				}
			}
			$checkLoopIndex++;
		}


		$keywordIndex=0;
		echo "<b style=\"color:red\">Row---> </b>";
		echo $sampleSentence."<br><br>";
		while($keywordIndex<count($keywordArray)){
			if($keywordArray[$keywordIndex]->rank>=50 && $sampleID!=$keywordArray[$keywordIndex]->id && $sampleCategory!=$keywordArray[$keywordIndex]->category_id){
				$result=mysql_query("SELECT * FROM topic where id='".$keywordArray[$keywordIndex]->id."'");
				$confusingRow = mysql_fetch_array($result);
				echo "<b style=\"color:red\">Confusing ROW--> </b>";
				echo $confusingRow['name'];
				echo "<br>";
			}
			$keywordIndex++;
		}
		echo "<br>";
	}
?>
</body>
</html>