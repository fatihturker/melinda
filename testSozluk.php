<?php
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
		function get_partial_eksi_data($link){
			$result = get_data($link);
			if(strpos($result,"Object moved")!==false){
				$pattern = "/<a href=\"(.*?)\">/";
				preg_match_all($pattern, $result, $eksiLink);
				$eksii = "http://eksisozluk.com".$eksiLink[1][0];
				return get_partial_eksi_data($eksii);
			}
			return $result;
		}
		
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
				curl_setopt($curly[$id],CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
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
		
		function get_actual_eksi_data($linkArray){
			$myFirstArray = multiRequest($linkArray);
			$myArray = array();
			echo count($myFirstArray);
			for($i=0;$i<count($myFirstArray);$i++){
				if(strpos($myFirstArray[$i],"Object moved")!==false){
					$pattern = "/<a href=\"(.*?)\">/";
					preg_match_all($pattern, $myFirstArray[$i], $eksiLink);
					$eksii = "http://eksisozluk.com".$eksiLink[1][0];
					$myArray[$i] = get_partial_eksi_data($eksii);
				}
			}
			return $myArray;
		}

		$Searchword1 = "_ibrahim_tatlises_";
		$Searchword1 = substr($Searchword1, 0, -1); 
		$Searchword1Pieces=explode("_",$Searchword1);
		$sozlukData = array();
		$eksiStringArray=array();
		$eksiStringArrayIndex=0;
		$uludagStringArray=array();
		$uludagStringArrayIndex=0;
		$allData = array();
		$allDataIndex = 0;
		for($i = 2; $i<=count($Searchword1Pieces); $i++){
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
				$eksiStringArray[$eksiStringArrayIndex]="http://eksisozluk.com/?q=".$tmpstr;
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
		
		$eksiDataArray = get_actual_eksi_data($eksiStringArray);
		
		for($z=0;$z<count($eksiDataArray);$z++){
			$pattern = "/<meta property=\"og:description\" content=\"(.*?)\" \/>/";
			preg_match_all($pattern, $eksiDataArray[$z], $eksiSozlukData[$z]);
		//	print_r($eksiSozlukData);
		}
		
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
		
		echo $allData[$tmpIndex];
		
?>