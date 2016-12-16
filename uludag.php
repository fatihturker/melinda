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

$jURL = "http://www.uludagsozluk.com/goster.php?k=ibrahim+tatlises";
$result = get_data($jURL);

$pattern = "/<ol >(.*?)<\/ol>/";
preg_match_all($pattern, $result, $uluData);

print_r($uluData);
?>