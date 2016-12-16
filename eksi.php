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

function get_actual_eksi_data($link){
	$result = get_data($link);
	$pattern = "/<a href=\"(.*?)\">/";
	preg_match_all($pattern, $result, $eksiLink);
	$eksii = "https://eksisozluk.com".$eksiLink[1][0];
	//echo $eksii;
	if(stripos($result,"object moved")!==false){
		return get_actual_eksi_data($eksii);
	}
	return $result;
}

$jURL = "https://eksisozluk.com/?q=ibrahim+tatlises";
$result = get_actual_eksi_data($jURL);


$pattern = "/<meta property=\"og:description\" content=\"(.*?)\" \/>/";
$eksiDataArray[$z] = str_replace("&#39;","",$result);
preg_match_all($pattern, $result, $eksiData);

echo $eksiData[1][0];
?>