<!--
- This project is the AI(Artificial Intelligence) based NLP(Natural Language Processing) project. 
- The project name is Melinda.
- Version 1.2.4
- Developers : Onur Keklik && Fatih Türker
- Contact Addresses : fatihturker35@gmail.com || onurkeklik90@gmail.com
-->

<?php
session_start();
include("db_connection/db_connection_admin.php");
//Creates a unique id
function guid()
{
	if (function_exists('com_create_guid')){
	        return com_create_guid();
	}else{
	    mt_srand((double)microtime()*10000);	//optional for php 4.2.0 and up.
		$charid = strtoupper(md5(uniqid(rand(), true)));
	    $hyphen = chr(45);// "-"
	    $uuid = chr(123)// "{"
	            .substr($charid, 0, 8).$hyphen
	            .substr($charid, 8, 4).$hyphen
	            .substr($charid,12, 4).$hyphen
	            .substr($charid,16, 4).$hyphen
	            .substr($charid,20,12)
	            .chr(125);// "}"
	    return $uuid;
	   }
}

//Detects link in $string as a string
function autolink($string)
{
	$content_array = explode(" ", $string);
	$output = '';

	foreach($content_array as $content)
	{
		//starts with http://
		if(substr($content, 0, 7) == "http://")
		$content = '<a href="' . $content . '" target=\"_blank\"> <img src="images/L.png" /></a>';

		//$sentences[$h]= "<a href=$urlN target=\"_blank\">Tıkla</a>";
		//starts with www.
		if(substr($content, 0, 4) == "www.")
		$content = '<a href="' . $content . '" target=\"_blank\"> <img src="images/L.png" /></a>';

		$output .= " " . $content;
	}

	$output = trim($output);
	return $output;
}	

if( (isset($_POST['username'])) || (isset($_SESSION['user_id'])) )
{
	//Creating a session for user and insert username with unique user id into database
	if(!isset($_SESSION['user_id'])){
		echo "</script>window.location = 'logout.php';</script>";
	}
	if(!empty($_POST['username'])){
		$_SESSION['user_id']=guid(); 
		$username=$_POST['username'];  
			  
		$sql="INSERT INTO users(id, name) VALUES('".$_SESSION['user_id']."', '".mysql_real_escape_string($username)."');";		
		$check = mysql_query($sql);
		if (!$check) 
		{		
			die("Invalid query: " . mysql_error());
		}
		$userResult=mysql_query("SELECT * FROM users WHERE id='".$_SESSION['user_id']."'");
		$user_num_rows = mysql_num_rows($userResult);
		if($user_num_rows>0)
		{
			$rowUser=mysql_fetch_array($userResult);
			$userName = $rowUser['name'];
		}
	}else{ 
		$userResult=mysql_query("SELECT * FROM users WHERE id='".$_SESSION['user_id']."'");
		$user_num_rows = mysql_num_rows($userResult);
		if($user_num_rows>0)
		{
			$rowUser=mysql_fetch_array($userResult);
			$userName = $rowUser['name'];
		}
	}

?>

<!-- The Basic HTML Form -->
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="tr" lang="tr">

<head>
<title>Melinda</title>
<script type="text/javascript" src="http://code.jquery.com/jquery-1.7.2.min.js"></script>
<link rel="stylesheet" type="text/css" href="css/chatStyle.css" />
<!--
	executeAsync(): executing the function 'func' after 1000 milisecond
	
	_request(): requesting requestPage.php, get data, write it in tags whose id is '#response' 
				and make the typing text hidden and text input disabled

	makeTypingVisible(): make the typing text visible and text input not disabled
-->
<script type="text/javascript">
function executeAsync(func) {
	setTimeout(func, 1000);
}

function _request() {	
	executeAsync(function() {
		$.ajax({
			type: 'POST',
			url: 'requestPage.php',
			data: $('form#requestForm').serialize(),
			success: function(req) {
				$('#response').html(req);
				$('#visibilityOfTyping').css('visibility', 'hidden');
				$('#input').attr('disabled', false);
				document.getElementById("chatmessages").scrollTop = document.getElementById("chatmessages").scrollHeight;
				$(document).ready(function(){
						$("#input").focus();
				});
				}
		});
	});
}
function _request2() {	
	executeAsync(function() {
		$.ajax({
			type: 'POST',
			url: 'requestPage2.php',
			data: $('form#requestForm').serialize(),
			success: function(req) {
				$('#response').html(req);
				$('#visibilityOfTyping').css('visibility', 'hidden');
				$('#input').attr('disabled', false);
				document.getElementById("chatmessages").scrollTop = document.getElementById("chatmessages").scrollHeight;
				$(document).ready(function(){
						$("#input").focus();
				});
				}
		});
	});
}
function makeTypingVisible(){
	executeAsync(function() {
		document.getElementById('visibilityOfTyping').style.visibility='visible';
		document.getElementById('input').disabled = true;
	});
}
</script>

<script>
$(document).ready(function(){
$("#input").focus();
});
</script>
<script>
	window.onbeforeunload = function() {
		window.location = 'logout.php';
    }
</script>
<script>
function scrollBottom(){
	document.getElementById("chatmessages").scrollTop = document.getElementById("chatmessages").scrollHeight;
}
</script>

<meta http-equiv="content-type" content="text/html; charset=utf-8" />
</head>

<body>
	<div id="container">
		<div id="header" class="center">
			<p style="margin-top:50px; margin-left:35px;">Kod Adı: Melinda</p>
		</div>
		<div id="mainWrapper" class="center">
			<div id="chatbox">
				<form name="form" id="form" action="" method="post">
					<input type="text" name="input" id="input" class="messageTextBox" placeholder="Mesajınızı buraya yazın...">
					<input type="submit" id="submit" value="Say" style="display:none"/>
				</form>
				
				</br>
				<?php 
				$lastQ=0;
				
				echo "<script>
				var timeout;
				function logout()
				{
					window.location = 'logout.php';
				}

				function start()
				{
					timeout = setTimeout(logout,300000);
					
				}
				
				start();
				</script>";
				
				
				/** Keeps the informations of keywords
				 *	$id as id of topic in database
				 *  $rank as the rank of sentence of the topic (how many words are matched)
				 *  $ratio is weight of one keyword in sentence
				*/
				class keywordRanking{
					public $id;
					public $rank;
					public $ratio;
				}
				
				/** Check the array $arr, if it contains object $o
				 *	If it contains, return index of object
				 *	If it does not, return -1
				*/
				function arrayContainsObject($arr,$o){
					for($i=0;$i<count($arr);$i++){
						if($arr[$i]->id==$o->id){
							return $i;
						}
					}
					return -1;
				}
				
				//Escapes the blank characters
				function escapeBlankCharacters($str){
					$str = str_replace(" ", "", $str);
					return $str;
				}
				
				//Checks the answer of user is yes or not
				function checkTheUserResponseIsYes($userResponse){
					if((stripos($userResponse,"evet")!==false) || (stripos($userResponse,"eet")!==false) ||(stripos($userResponse,"evt")!==false)
							|| (stripos($userResponse,"istiyorum")!==false) || (stripos($userResponse,"ediyorum")!==false) || (stripos($userResponse,"konuşalım")!==false)
							|| (stripos($userResponse,"bahset")!==false)  || (stripos($userResponse,"olur")!==false) || (stripos($userResponse,"tamam")!==false)  
							|| (stripos($userResponse,"tmm")!==false) || (stripos($userResponse,"yes")!==false) || (stripos($userResponse,"yeap")!==false)  
							|| (stripos($userResponse,"yep")!==false) || (stripos($userResponse,"pekiyi")!==false)  || (stripos($userResponse,"peki")!==false)){
								return true;
				         }
							
					return false;
				}
				
				//Checks the answer of user is no or not
				function checkTheUserResponseIsNo($userResponse){
					if((stripos($userResponse,"hayır")!==false) || (stripos($userResponse,"hayr")!==false) ||(stripos($userResponse,"hyr")!==false)
							|| (stripos($userResponse,"istemiyorum")!==false) || (stripos($userResponse,"etmiyorum")!==false) || (stripos($userResponse,"konuşmayalım")!==false)
							|| (stripos($userResponse,"bahsetme")!==false)  || (stripos($userResponse,"olmaz")!==false) || (stripos($userResponse,"yok")!==false) || (stripos($userResponse,"no")!==false)){
								return true;
							}
							
					return false;
				}
				
				//Checks the answer of user is ok or not
				function checkTheUserResponseIsOk($userResponse){
					if((stripos($userResponse,"evet")!==false) || (stripos($userResponse,"eet")!==false) ||(stripos($userResponse,"evt")!==false)
							|| (stripos($userResponse,"istiyorum")!==false) || (stripos($userResponse,"ediyorum")!==false) || (stripos($userResponse,"konuşalım")!==false)
							 || (stripos($userResponse,"olur")!==false) || (stripos($userResponse,"tamam")!==false)  || (stripos($userResponse,"tmm")!==false) 
							 || (stripos($userResponse,"topla")!==false) || (stripos($userResponse,"yes")!==false) || (stripos($userResponse,"yeap")!==false) 
							 || (stripos($userResponse,"yep")!==false) || (stripos($userResponse,"pekiyi")!==false) || (stripos($userResponse,"peki")!==false)){
								 return true;
					     }
					
					return false;
					
				}
				
				//Checks the answer of user is not ok
				function checkTheUserResponseIsNotOk($userResponse){
					if((stripos($userResponse,"hayır")!==false) || (stripos($userResponse,"hayr")!==false) ||(stripos($userResponse,"hyr")!==false)
							|| (stripos($userResponse,"istemiyorum")!==false) || (stripos($userResponse,"etmiyorum")!==false) || (stripos($userResponse,"konuşmayalım")!==false)
							|| (stripos($userResponse,"bahsetme")!==false)  || (stripos($userResponse,"olmaz")!==false) || (stripos($userResponse,"yok")!==false) || (stripos($userResponse,"no")!==false)){
								return true;
							}
					return false;
				}
				
				//Checks the answer of user is do you know
				function checkTheUserResponseIsDoYouKnow($userResponse){
					if((stripos($userResponse,"biliyo")!==false) || (stripos($userResponse,"bilgi")!==false) || (stripos($userResponse,"hakkında")!==false) || (stripos($userResponse,"duydu")!==false)
								|| (stripos($userResponse,"nedir")!==false ) || (stripos($userResponse,"düşünüyorsun")!==false ) || (stripos($userResponse,"kim")!==false )
								|| (stripos($userResponse,"tanıyo")!==false )  || (stripos($userResponse,"anlat")!==false)){
									return true;
								}
								
					return false;
				}
				function greeting($sampleSentence)
				{
					if((stripos($sampleSentence,"süper")!==false) | (stripos($sampleSentence,"bayıldım")!==false) || (stripos($sampleSentence,"harika")!==false)
					|| (stripos($sampleSentence,"tebrik")!==false) || (stripos($sampleSentence,"eyv")!==false) || (stripos($sampleSentence,"eyw")!==false) 
					|| (stripos($sampleSentence,"vay")!==false) || (stripos($sampleSentence,"aferim")!==false)  || (stripos($sampleSentence,"aferin")!==false) 
					|| (stripos($sampleSentence,"afferin")!==false) || (stripos($sampleSentence,"güzel cevap")!==false) || (stripos($sampleSentence,"zekisin")!==false) )
					{
						return true;
					}
					return false;
				}
				
				function badwords($sampleSentence1)
				{
					$sampleSentencePieces=explode(" ",$sampleSentence1);
					//$sampleSentencePieces[1];
					for($i=0; $i<count($sampleSentencePieces); $i++)
					{
						if( (strcmp($sampleSentencePieces[$i],"sik")==0) || (strcmp($sampleSentencePieces[$i],"göt")==0) || (strcmp($sampleSentencePieces[$i],"amk")==0)
						|| (strcmp($sampleSentencePieces[$i],"amq")==0) || (strcmp($sampleSentencePieces[$i],"aq")==0) || (strcmp($sampleSentencePieces[$i],"amına")==0) 
						|| (strcmp($sampleSentencePieces[$i],"amcık")==0) || (strcmp($sampleSentencePieces[$i],"yarak")==0) || (strcmp($sampleSentencePieces[$i],"yavşak")==0)
						|| (strcmp($sampleSentencePieces[$i],"am")==0) || (strcmp($sampleSentencePieces[$i],"pezevenk")==0) || (strcmp($sampleSentencePieces[$i],"orospu")==0) 
						|| (strcmp($sampleSentencePieces[$i],"ibne")==0) )
						{
							return true;
						}
						
					}
					return false;
				}
				//Template method for creating ajax request form
				function templateAjaxRequestForm($Searchword,$userSentence,$userSessionID,$userIP,$onclickFunctions){
					echo "<form method=\"post\" id=\"requestForm\" name=\"requestForm\" onsubmit=\"return false;\">
												<input name=\"searchWord\" type=\"text\" style=\"display:none;\" value=\"".$Searchword."\" />
												<input name=\"sampleSentence\" type=\"text\" style=\"display:none;\" value=\"".$userSentence."\" />
												<input name=\"user_id\" type=\"text\" style=\"display:none;\" value=\"".$userSessionID."\" />
												<input name=\"user_ip\" type=\"text\" style=\"display:none;\" value=\"".$userIP."\" />
												<input type=\"submit\" value=\"Request\" style=\"display:none;\" id=\"Request\" onclick=\"".$onclickFunctions."\" />
											</form>
											<span id=\"response\"></span>
										";
					echo 
					"<script language=\"JavaScript\">
						document.getElementById('input').disabled = true;
						document.getElementById(\"Request\").click();
					</script>";
				}
				
				function ajaxRequestForm($Searchword,$userSentence,$userSessionID,$userIP){
					$onclickFunctions = "_request() & makeTypingVisible() & scrollBottom()";
					templateAjaxRequestForm($Searchword,$userSentence,$userSessionID,$userIP,$onclickFunctions);
				}
				
				function ajaxRequestForm2($Searchword,$userSentence,$userSessionID,$userIP){
					$onclickFunctions = "_request2() & makeTypingVisible() & scrollBottom()";
					templateAjaxRequestForm($Searchword,$userSentence,$userSessionID,$userIP,$onclickFunctions);
				}
				
				//Makes seed and returns it to create varying random numbers
				function make_seed()
				{
				  list($usec, $sec) = explode(' ', microtime());
				  return (float) $sec + ((float) $usec * 100000);
				}
				
				/** Template threading method that simulates threading to make realistic the process of typing of bot, 
				 *	using setTimeout function, and keeps sentences hidden 
				 *	and make visible them in order in time
				 *	and it gets parameters
				 *	$cssClassID as a css id of the div tag
				 *	$typingSleepTime as time that how much time(Milisecond) 'Typing...' string must wait to be visible 
				 *	$answerSleepTime as time how much time(Milisecond) $answerSentence string must wait to be visible
				 *	$answerSentence is a sentence that desired to be printed
				*/
				function templateThreading($cssClassID, $typingSleepTime, $answerSleepTime, $answerSentence){
					echo "
						<style>
						#".$cssClassID."{visibility:hidden;}
						</style>
						<div id=\"".$cssClassID."\">".$answerSentence."</div>";
					echo "<script>document.getElementById('input').disabled = true;
					document.getElementById(\"chatmessages\").scrollTop = document.getElementById(\"chatmessages\").scrollHeight;
					</script>";
					echo "<script>
						function executeAsync(func) {
						setTimeout(func, ".$typingSleepTime.");
						}
				
						executeAsync(function() {
						document.getElementById('visibilityOfTyping').style.visibility='visible';
						document.getElementById('input').disabled = true;
						});
						</script>";
				
					echo "<script>
						function executeAsync(func) {
						setTimeout(func, ".$answerSleepTime.");
						}
				
						executeAsync(function() {
						document.getElementById('visibilityOfTyping').style.visibility='hidden';
						document.getElementById('".$cssClassID."').style.visibility='visible';
						document.getElementById('input').disabled = false;
						document.getElementById(\"chatmessages\").scrollTop = document.getElementById(\"chatmessages\").scrollHeight;
						$(document).ready(function(){
						$(\"#input\").focus();
						});
						});
						</script>";
				}
				
				//Getting user ip
				$ip=$_SERVER['REMOTE_ADDR'];
				
				//Creating unique id with guid() function and set it as user session id
				if(!isset($_SESSION['user_id']))
				{
					$_SESSION['user_id']=guid();
				}
				
				//Creating hidden typing text
				if(isset($_POST['input']))
				{
					echo "
							<style>
								#visibilityOfTyping{visibility:hidden;}
							</style>
							<b id=\"visibilityOfTyping\">Melinda yazıyor..</b>
						";
					echo "<br>";
				}
				?>
				<div id="chatmessages">
					<?php
					$numberOfUserLogs = 0;
					// If session of user_id is set, getting all logs related with that session from database
					if(isset($_SESSION['user_id']))
					{
						$result=mysql_query("SELECT * FROM user_logs WHERE user_id='".$_SESSION['user_id']."' order by(id)");
						$num_rows = mysql_num_rows($result);
						$numberOfUserLogs = $num_rows;
						if($num_rows>0)
						{
							while($row=mysql_fetch_array($result))
							{
								echo $row['log'];
								echo "<br>";
							}
						}
					}
					
					if(isset($_POST['input']))
					{
						$wordArray = array();	//word array of user sentence
						$wordArrayIndex = 0;
					
						/** Abbreviation arrays to catch many typing versions of words
						 *	in first index it keeps the word, and others are the versions
						*/
						$abbreviation[0]=array("naber","nbr","napıyon","nabıyon", "naptın","nasılsın","napiyon","nabiyon","nasilsin");
						$abbreviation[1]=array("kanka","kank","kanqa","panpa");
						$abbreviation[2]=array("selam","slm","mrb","merhaba","meraba","hey");
						$abbreviation[3]=array("evet","evt","eet");
						$abbreviation[4]=array("hayır","hayr","hyr");
						$abbreviation[4]=array("İzmir Ekonomi Üniversitesi","ieü","ieu");
					
						$sampleSentence=$_POST['input'];	//user input
					
						if(strlen(escapeBlankCharacters($sampleSentence))>0)
						{
							$userSentence = $userName.": ".$sampleSentence;
							echo $userSentence;
							echo "<br>";
							$sql="INSERT INTO user_logs(user_id, user_ip, log) VALUES('".$_SESSION['user_id']."', '".$ip."', '".mysql_real_escape_string($userSentence)."');";		
							$check = mysql_query($sql);
							if (!$check) 
							{		
								die("Invalid query: " . mysql_error());
							}
						}
					
						$sampleSentence = mb_convert_case($sampleSentence, MB_CASE_LOWER, "UTF-8");		//Converting characters in the sentence in a lowercase
					
						//Checking the database if input is logged before
						$inputCheckerSQL=mysql_query("SELECT * FROM user_logs WHERE user_id='".$_SESSION['user_id']."' AND log='".mysql_real_escape_string($userSentence)."' ");
						$numberOfFoundRows = mysql_num_rows($inputCheckerSQL);
					
					
						if( ($numberOfFoundRows==2)&&(!isset($_SESSION['question'])))
						{
							//If the input is logged once before and bot did not ask question to user in previous answer
					
							$BotResponse="Melinda: Sanki bunun hakkında daha önce konuşmuştuk";
							
							templateThreading("visibilityOfBotResponse", 500, 4000, $BotResponse);
							
							$sql="INSERT INTO user_logs(user_id, user_ip, log) VALUES('".$_SESSION['user_id']."', '".$ip."', '".mysql_real_escape_string($BotResponse)."');";		
							$check = mysql_query($sql);
							if (!$check) 
							{		
								die("Invalid query: " . mysql_error());
							}
							
							$_SESSION['answer']=$BotResponse;
						}
						
						else if(($numberOfFoundRows==3) &&(!isset($_SESSION['question'])) )
						{
							//If the input is logged twice before and bot did not ask question to user in previous answer
					
							$BotResponse="Melinda: Sana daha fazla nasıl yardımcı olabilirim?";
							
							templateThreading("visibilityOfBotResponse1", 500, 4000, $BotResponse);
							
							$sql="INSERT INTO user_logs(user_id, user_ip, log) VALUES('".$_SESSION['user_id']."', '".$ip."', '".mysql_real_escape_string($BotResponse)."');";		
							$check = mysql_query($sql);
							if (!$check) 
							{		
								die("Invalid query: " . mysql_error());
							}
							
							$_SESSION['answer']=$BotResponse;
							
					
						}
						else if(($numberOfFoundRows>3)&&(!isset($_SESSION['question'])))
						{
					
							//If the input is logged more then two times before and bot did not ask question to user in previous answer
					
							$repeatResponseArray = array();
					
							$repeatResponseArray[0]= "Melinda: Sana hiçbir şey demiyorum!";
							$repeatResponseArray[1]= "Melinda: Burda bot olan benim tamam mı!";
							$repeatResponseArray[2]= "Melinda: Hayat ne kadar tuhaf vapurlar falan..";
							$repeatResponseArray[3]= "Melinda: İnsan bazen hayret ediyor..";
							$repeatResponseArray[4]= "Melinda: Sanıyorum bize nazar değdi";
							
							mt_srand(make_seed());
							$randval = mt_rand();
							$whichOne=$randval%5;
					
							templateThreading("visibilityOfResponse", 500, 4000, $repeatResponseArray[$whichOne]);
							
							$sql="INSERT INTO user_logs(user_id, user_ip, log) VALUES('".$_SESSION['user_id']."', '".$ip."', '".mysql_real_escape_string($repeatResponseArray[$whichOne])."');";		
							$check = mysql_query($sql);
							if (!$check) 
							{		
								die("Invalid query: " . mysql_error());
							}
							
							$_SESSION['answer']=$repeatResponseArray[$whichOne];
							
						}
						
						else if(badwords($sampleSentence))
						{
							$answerTextKArr[0] = "Melinda: Hiç senin gibi birine yakışıyor mu bu laflar?..";
							$answerTextKArr[1] = "Melinda: Senin yaptığını ipragazın kedisi bile yapmaz, ayıp değil mi?..";
							$answerTextKArr[2] = "Melinda: Laflarına dikkat et bence, botların %50 sini evde zor tutuyorum..";
							mt_srand(make_seed());
							$randval = mt_rand();
							$answerIndex=$randval%3;
							$answerText = $answerTextKArr[$answerIndex];
							templateThreading("visibilityOfAnswerT1", 2000, 5000, $answerText);
							echo "<br>";
							$sql="INSERT INTO user_logs(user_id, user_ip, log) VALUES('".$_SESSION['user_id']."', '".$ip."', '".mysql_real_escape_string($answerText)."');";		
							$check = mysql_query($sql);
							if (!$check) 
							{		
								die("Invalid query: " . mysql_error());
							}
										
							$_SESSION['answer']=$answerText;
						}
						
						else if((strlen(escapeBlankCharacters($sampleSentence))<=2) && (strlen(escapeBlankCharacters($sampleSentence))>0))
						{
							//If user input is smaller than or equal to 2 characters
					
							$BotResponse1="Melinda: Çok konuşkan birisisin";
							
							templateThreading("visibilityOfAnswer", 500, 4000, $BotResponse1);
							
							$sql="INSERT INTO user_logs(user_id, user_ip, log) VALUES('".$_SESSION['user_id']."', '".$ip."', '".mysql_real_escape_string($BotResponse1)."');";		
							$check = mysql_query($sql);
							if (!$check) 
							{		
								die("Invalid query: " . mysql_error());
							}
							
							$_SESSION['answer']=$BotResponse1;
							
							if(isset($_SESSION['question']))
							{
								unset($_SESSION['question']);
							}
							
						}
						else if(strlen(escapeBlankCharacters($sampleSentence))<1)
						{	
							if(isset($_SESSION['question']))
							{
								unset($_SESSION['question']);
							}	
						}
						else
						{
							if(isset($_SESSION['question']))
							{
								unset($_SESSION['question']);
							}
							//Checking if the bot ask question to user
							if(isset($_SESSION['lastQuestion']))
							{
								//Checking the answer of user for that question
								if(checkTheUserResponseIsYes($sampleSentence) && (strlen($userResponse)<=14))
								{
									$lastQ=1;
									
									//If the answer is YES searching the answer of that question in database
									$answerOfQuestionSQL=mysql_query("SELECT * FROM result_set WHERE id='".mysql_real_escape_string($_SESSION['lastQuestion'])."'");
									$numberOfFoundRowsForSearch = mysql_num_rows($answerOfQuestionSQL);
									
									if($numberOfFoundRowsForSearch>0)
									{
										while($row3=mysql_fetch_array($answerOfQuestionSQL))
										{
											$h=0;
											$lastValue=0;
											$sentences=array();
											
											//Parsing result set to give the results to user sentence by sentence
											for($u=1; $u<=strlen($row3['txt']); $u++)
											{
												if( ( ($row3['txt']{$u})==".") && (!is_numeric($row3['txt']{$u+1}) ) && (($row3['txt']{$u+1})!=")" ) && (($row3['txt']{$u+1})!="(" ) && ( ($resultTxtArray[$resultInd]{$u+1})!=".") )
												{
													$endP=$u-strlen($row3['txt']);
													$sentences[$h]= substr($row3['txt'], $lastValue, $endP);
													$sentences[$h]=$sentences[$h].".";
													if($sentences[$h]{0}=='.') {$sentences[$h]= substr($sentences[$h],1);}
					
													if(stripos($sentences[$h],"http")!==false)
													{
														$sentences[$h]= substr($row3['txt'], $lastValue);
														if($sentences[$h]{0}=='.') {$sentences[$h]= substr($sentences[$h],1);}
														/*$urlN=$sentences[$h];
														$sentences[$h]= "<a href=$urlN target=\"_blank\">Tıkla</a>";*/
														$u=strlen($row3['txt'])+1;
													}
													if(!empty($sentences[$h]))
													{	
														$sentences[$h]=autolink($sentences[$h]);
														$answerText = "Melinda: ".$sentences[$h];
														templateThreading("visibilityOfAnswerKK".$h."", 3100*($h+1), 4000*($h+1), $answerText);
														$sql="INSERT INTO user_logs(user_id, user_ip, log) VALUES('".$_SESSION['user_id']."', '".$ip."', '".mysql_real_escape_string($answerText)."');";		
														$check = mysql_query($sql);
														if (!$check) 
														{		
															die("Invalid query: " . mysql_error());
														}
														$_SESSION['answer']=$sentences[$h];
													}
													
													$lastValue=$u;
													$h++;
												}
											}
											if($h==0)
											{
												$answerText = "Melinda: ".$row3['txt'];
												templateThreading("visibilityOfAnswerKK".$h."", 3100*($h+1), 4000*($h+1), $answerText);
												$sql="INSERT INTO user_logs(user_id, user_ip, log) VALUES('".$_SESSION['user_id']."', '".$ip."', '".mysql_real_escape_string($answerText)."');";		
												$check = mysql_query($sql);
												if (!$check) 
												{		
													die("Invalid query: " . mysql_error());	
												}
												$_SESSION['answer']=$row3['txt'];
											}
											
										}
									}
									
								}
								
								else if(checkTheUserResponseIsNo($sampleSentence) && (strlen($sampleSentence)<=16))
								{
									$lastQ=2;
									//If the answer is not YES returning random question to user
									$expResponseArray = array();
									$expResponseArray[0]= "Melinda: Üniversitemiz hakkında başka neler öğrenmek istersin?";
									$expResponseArray[1]= "Melinda: Başka ne hakkında konuşmak istersin?";
									mt_srand(make_seed());
									$randval = mt_rand();
									$whichOne=$randval%2;
					
									//Printing the question and inserting it into the database as a log
									templateThreading("visibilityOfExpResponse", 500, 4000, $expResponseArray[$whichOne]);
					
									$sql="INSERT INTO user_logs(user_id, user_ip, log) VALUES('".$_SESSION['user_id']."', '".$ip."', '".$expResponseArray[$whichOne]."');";		
									$check = mysql_query($sql);
									if (!$check) 
									{		
										die("Invalid query: " . mysql_error());
									}
									$_SESSION['answer']=$expResponseArray[$whichOne];
									
								}
					
								unset($_SESSION['lastQuestion']);
							}

							if($lastQ==0)
							{			
								//Parsing the user input and put the words in a $words array
								$words = preg_split('#[\\s.]#', $sampleSentence, -1, PREG_SPLIT_NO_EMPTY);
								$i=0;
					
								/** Iterating $words array and if abbreviations are detected, change them with original words
								 *  and push them into $wordArray 
								*/
								while($i<count($words)){
									if(!preg_match('#[0-9]#',$words[$i])){
										$words[$i] = preg_replace("/(?![.=$'€%-])\p{P}/u", "", $words[$i]);
										if(strlen($words[$i])>2)
										{
											
											for($k=0; $k<count($abbreviation); $k++)
											{
												for($l=0; $l<count($abbreviation[$k]); $l++)
												{
													if(strcmp($abbreviation[$k][$l],$words[$i])==0)
													{
														$words[$i]=$abbreviation[$k][0];
													}
												
												}
												
											}
											
											$wordArray[$wordArrayIndex]=$words[$i];
											$wordArrayIndex++;
										}
									}else{
											$wordArray[$wordArrayIndex]=$words[$i];
											$wordArrayIndex++;
									}
					
									$i++;
								}
					
								$keywordArray = array();
								$keywordArrayIndex=0;
								$loopIndex=0;
								$rankListArray = array();
								$rankListArrayIndex = 0;
					
								/** Iterating $wordArray and searching all words in topics
								 *  if the word is found in topic, then assinging topic row id to keyword->id 
								 *	and increase the rank of the keyword
								*/
								while($loopIndex<count($wordArray)){
									$rankList = array();
									$rankListIndex=0;
									$rootOfTheWord = $wordArray[$loopIndex];
									$checkSQL = mysql_query("Select * from topic where name LIKE '%".mysql_real_escape_string($wordArray[$loopIndex])."%' ");
									if(mysql_num_rows($checkSQL)>0){
										while($row=mysql_fetch_array($checkSQL)){
											$nKeyword = new keywordRanking;
											$nKeyword->id=$row['id'];
											//Parse the sentence of topic row
											$tword = preg_split('#[\\s.]#', $row['name'], -1, PREG_SPLIT_NO_EMPTY);
					
											$tword = array_unique($tword);
											$nKeyword->rank += 100/count($tword);
											$nKeyword->ratio = 100/count($tword);
					
											$rankList[$rankListIndex]=$row['id'];
											$rankListIndex++;
											$checkIndex=arrayContainsObject($keywordArray,$nKeyword);
											
											//If the keyword is not captured before
											if($checkIndex===-1){
												$keywordArray[$keywordArrayIndex]=$nKeyword;
												$keywordArrayIndex++;
											}else{
												$nKeyword = $keywordArray[$checkIndex];
												$nKeyword->rank += 100/count($tword);
					
												$keywordArray[$checkIndex]=$nKeyword;
											}
										}
									}
									
									//Iterate the word while it has 4 character and searching it in topics again
									while(strlen($rootOfTheWord)>4){
										$rootOfTheWord = mb_substr($rootOfTheWord, 0, -1,'UTF-8');
										$checkSQL = mysql_query("Select * from topic where name LIKE '%".mysql_real_escape_string($rootOfTheWord)."%' ");
										if(mysql_num_rows($checkSQL)>0){
											while($row=mysql_fetch_array($checkSQL)){
												$nKeyword = new keywordRanking;
												$nKeyword->id=$row['id'];
												$tword = preg_split('#[\\s.]#', $row['name'], -1, PREG_SPLIT_NO_EMPTY);
					
												$tword = array_unique($tword);
												$nKeyword->rank += 100/count($tword);
												$nKeyword->ratio = 100/count($tword);
					
												$rankList[$rankListIndex]=$row['id'];
												$rankListIndex++;
												$checkIndex=arrayContainsObject($keywordArray,$nKeyword);
					
												if($checkIndex===-1){
													$keywordArray[$keywordArrayIndex]=$nKeyword;
													$keywordArrayIndex++;
												}else{
													$nKeyword = $keywordArray[$checkIndex];
													$nKeyword->rank += 100/count($tword);
					
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
					
								//Iterating $rankListArray and eliminating the duplicate rank increase   
								while($checkLoopIndex<count($rankListArray)){
									$countIndexArray[$checkLoopIndex] = array();
									for($i=0;$i<count($rankListArray[$checkLoopIndex]);$i++){
										if($countIndexArray[$checkLoopIndex][$rankListArray[$checkLoopIndex][$i]]>0){
											$countIndexArray[$checkLoopIndex][$rankListArray[$checkLoopIndex][$i]]++;
										}else{
											$countIndexArray[$checkLoopIndex][$rankListArray[$checkLoopIndex][$i]]=1;
										}
					
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
								$tmpRatio=0;
								$tmpKeywordIndex=0;
					
								//Iterating $keywordArray and finding the keyword that has minimum ratio
								while($keywordIndex<count($keywordArray)){
									$tmpKeyword = $keywordArray[$keywordIndex];
									if($tmpRatio>$tmpKeyword->rank){
										$tmpKeywordIndex=$keywordIndex;
										$tmpRatio = $tmpKeyword->rank;
									}
									$keywordIndex++;
								}
					
								/** Iterating $wordArray and searching all words in result set
								 *  if the word is found in result set, then
								 *	increase the rank of the keyword
								*/
					
								$ind=0;
								$keyInd=0;
								while($ind<count($wordArray)){
									while($keyInd<count($keywordArray)){
										$tmpKey = $keywordArray[$keyInd];
										$checkSQL = mysql_query("Select * from result_set where topic_id=".$tmpKey->id." AND txt LIKE '%".mysql_real_escape_string($wordArray[$ind])."%'");
					
										if(mysql_num_rows($checkSQL)>0){
											$tmpKey->rank += $tmpRatio;
											$keywordArray[$keyInd] = $tmpKey;
										}
										$keyInd++;
									}
									$ind++;
								}
								
								/** Iterating $keywordArray and checking all word combination with topic
								 *  if the combination is found in topic, then
								 *	increase the rank of the keyword
								*/
					
								$ind=0;
								$keyInd=0;
								while($keyInd<count($keywordArray)){
									$tmpKey = $keywordArray[$keyInd];
									$checkSQL = mysql_query("Select * from topic where id=".$tmpKey->id."");
									if(mysql_num_rows($checkSQL)>0){
										$row = mysql_fetch_array($checkSQL);
										$topicName = mb_convert_case($row['name'], MB_CASE_LOWER, "UTF-8");
										$tCharArr = str_split($_POST['input']);
										$tCharArrIndex = 0;
										$tmpCharArr = array();
										
										while($tCharArrIndex<count($tCharArr)){
											array_push($tmpCharArr, $tCharArr[$tCharArrIndex]);
											$tmpStr = implode("",$tmpCharArr);
					
											if(stripos($topicName,$tmpStr)!==false){
												$tmpKey->rank += 1;
												$keywordArray[$keyInd] = $tmpKey;
											}
											$tCharArrIndex++;
										}
									}
									$keyInd++;
								}
								
								$sampleSentence = implode(" ",$words);
								
								//if(count($wordArray)>1){
									$keyInd1=0;
									while($keyInd1<count($keywordArray)){
										$tmpKey = $keywordArray[$keyInd1];
										$checkSQL = mysql_query("Select * from topic where id=".$tmpKey->id."");
										if(mysql_num_rows($checkSQL)>0){
											$row = mysql_fetch_array($checkSQL);
											$keywords = explode(",", $row['keyword']);
											$keywordsInd = 0;
											while($keywordsInd<count($keywords)){
												$keywordsC = explode("=", $keywords[$keywordsInd]);
												$keywordsCInd = 0;
												$keywordFlag=0;
												while($keywordsCInd<count($keywordsC)){
													if(stripos($sampleSentence,$keywordsC[$keywordsCInd])!==false){
														$keywordFlag++;
													}
													$keywordsCInd++;
												}
												if($keywordFlag>0){
													$tmpKey->rank += 40;
													$keywordArray[$keyInd] = $tmpKey;
												}else{
													$tmpKey->rank -= 50;
													$keywordArray[$keyInd] = $tmpKey;
												}
												$keywordsInd++;
											}
										}
										$keyInd1++;
									}
								//}
								
								$keywordIndex=0;
								$tmpRank=0;
								$tmpKeywordIndex=0;
					
								//Iterating $keywordArray and finding the keyword that has maximum rank
								while($keywordIndex<count($keywordArray)){
									$tmpKeyword = $keywordArray[$keywordIndex];
									if($tmpRank<$tmpKeyword->rank){
										$tmpKeywordIndex=$keywordIndex;
										$tmpRank = $tmpKeyword->rank;
									}
									$keywordIndex++;
								}
					
								$nKeyword=$keywordArray[$tmpKeywordIndex];
								
								if($nKeyword->rank>=70)
								{
									//if keyword rank is greater than or equal to 50, getting results of topic that has the same id with the keyword
					
									$result=mysql_query("SELECT * FROM result_set WHERE topic_id='".$nKeyword->id."'");
									$num_rows = mysql_num_rows($result);
									if($num_rows>0)
									{
										mt_srand(make_seed());
										$randval = mt_rand();
										$resultInd=$randval%$num_rows;
										$resultTxtArray=array();
										$resultTxtArrayIndex=0;
										
										while($row = mysql_fetch_array($result)){
											$resultTxtArray[$resultTxtArrayIndex]= $row['txt'];
											$resultTxtArrayIndex++;
										}
										
										$h=0;
										$lastValue=0;
										$sentences=array();
										
										//Parsing result set to give the results to user sentence by sentence
										for($u=1; $u<=strlen($resultTxtArray[$resultInd]); $u++)
										{
											if( ( ($resultTxtArray[$resultInd]{$u})==".") && (!is_numeric($resultTxtArray[$resultInd]{$u+1}) ) && (($resultTxtArray[$resultInd]{$u+1})!=")" ) && (($resultTxtArray[$resultInd]{$u+1})!="(" ) && ( ($resultTxtArray[$resultInd]{$u+1})!=".") )
											{
												$endP=$u-strlen($resultTxtArray[$resultInd]);
												$sentences[$h]= substr($resultTxtArray[$resultInd], $lastValue, $endP);
												$sentences[$h]=$sentences[$h].".";
												if($sentences[$h]{0}=='.') {$sentences[$h]= substr($sentences[$h],1);}
					
												if(stripos($sentences[$h],"http")!==false)
												{
													$sentences[$h]= substr($resultTxtArray[$resultInd], $lastValue);
													if($sentences[$h]{0}=='.') {$sentences[$h]= substr($sentences[$h],1);}
													//$urlN=$sentences[$h];
													//$sentences[$h]= "<a href=$urlN target=\"_blank\">Tıkla</a>";
													$u=strlen($resultTxtArray[$resultInd])+1;
												}
												if(!empty($sentences[$h]))
												{
													$sentences[$h]=autolink($sentences[$h]);
													$answerText = "Melinda: ".$sentences[$h];
													templateThreading("visibilityOfAnswerKK".$h."", 3100*($h+1), 4000*($h+1), $answerText);
													$sql="INSERT INTO user_logs(user_id, user_ip, log) VALUES('".$_SESSION['user_id']."', '".$ip."', '".mysql_real_escape_string($answerText)."');";		
													$check = mysql_query($sql);
													if (!$check) 
													{		
														die("Invalid query: " . mysql_error());
													}
													$_SESSION['answer']=$sentences[$h];
												}
												
												$lastValue=$u;
												$h++;
											}
										}
										if($h==0)
										{
											$answerText = "Melinda: ".$resultTxtArray[$resultInd];
											templateThreading("visibilityOfAnswerKK".$h."", 3100*($h+1), 4000*($h+1), $answerText);
											$sql="INSERT INTO user_logs(user_id, user_ip, log) VALUES('".$_SESSION['user_id']."', '".$ip."', '".mysql_real_escape_string($answerText)."');";		
											$check = mysql_query($sql);
											if (!$check) 
											{		
												die("Invalid query: " . mysql_error());	
											}
											$_SESSION['answer']=$resultTxtArray[$resultInd];
										}
									}
								}
								else if($nKeyword->rank>=30&&$nKeyword->rank<=70)
								{
									
									//If keyword rank is less than 50, returning a question about the category of the maximum ranked topic to the user
									$questionArray = array();
									$questionArrayIndex = 0;
									$result=mysql_query("SELECT * FROM topic WHERE id='".$nKeyword->id."'");
									$num_rows = mysql_num_rows($result);
									if($num_rows>0)
									{
										$row = mysql_fetch_array($result); 	
					
										$wordIndex=0;
										while($wordIndex<count($wordArray)){
											$rootOfTheWord = $wordArray[$wordIndex];
											$result2=mysql_query("SELECT * FROM question WHERE category_id='".$row['category_id']."' ");
											$num_rowsQ = mysql_num_rows($result2);
					
											if($num_rowsQ>0){
												while($rowQ = mysql_fetch_array($result2)){
													$qKey = new keywordRanking;
													$qKey->id = $rowQ['id'];
													$qword = preg_split('#[\\s.]#', $rowQ['txt'], -1, PREG_SPLIT_NO_EMPTY);
													$qword = array_unique($qword);
													$qKey->rank += 100/count($qword);
													$nKeyword->ratio = 100/count($qword);
						
													$rankList[$rankListIndex]=$row['id'];
													$rankListIndex++;
													$checkIndex=arrayContainsObject($questionArray,$qKey);
													
													//If the questionKey is not captured before
													if($checkIndex===-1){
														$questionArray[$questionArrayIndex] = $qKey;
														$questionArrayIndex++;
													}else{
														$qKey = $questionArray[$checkIndex];
														$qKey->rank += 100/count($qword);
														$questionArray[$checkIndex]=$qKey;
													}
						
												}
											}
						
											//Iterating the word while it has 4 character and searching it in topics again
											while(strlen($rootOfTheWord)>4){
												$rootOfTheWord = mb_substr($rootOfTheWord, 0, -1,'UTF-8');
												$checkSQL = mysql_query("Select * from question where txt LIKE '%".mysql_real_escape_string($rootOfTheWord)."%' ");
												if(mysql_num_rows($checkSQL)>0){
													while($rowQ = mysql_fetch_array($result2)){
														$qKey = new keywordRanking;
														$qKey->id = $rowQ['id'];
														$qword = preg_split('#[\\s.]#', $rowQ['txt'], -1, PREG_SPLIT_NO_EMPTY);
														$qword = array_unique($qword);
														$qKey->rank += 100/count($qword);
														$nKeyword->ratio = 100/count($qword);
						
														$rankList[$rankListIndex]=$row['id'];
														$rankListIndex++;
														$checkIndex=arrayContainsObject($questionArray,$qKey);
														
														//If the questionKey is not captured before
														if($checkIndex===-1){
															$questionArray[$questionArrayIndex] = $qKey;
															$questionArrayIndex++;
														}else{
															$qKey = $questionArray[$checkIndex];
															$qKey->rank += 100/count($qword);
															$questionArray[$checkIndex]=$qKey;
														}
						
													}
												}
											}
											$rankListArray[$rankListArrayIndex] = $rankList;
											$rankListArrayIndex++;
											$wordIndex++;
										}
						
										$checkLoopIndex=0;
						
										$countIndexArray = array();
										$countIndexArrayIndex = 0;
										$tmpMaxValue = 0;
										//Iterating $rankListArray and eliminating the duplicate rank increase   
										while($checkLoopIndex<count($rankListArray)){
											$countIndexArray[$checkLoopIndex] = array();
											for($i=0;$i<count($rankListArray[$checkLoopIndex]);$i++){
												if($countIndexArray[$checkLoopIndex][$rankListArray[$checkLoopIndex][$i]]>0){
													$countIndexArray[$checkLoopIndex][$rankListArray[$checkLoopIndex][$i]]++;
												}else{
													$countIndexArray[$checkLoopIndex][$rankListArray[$checkLoopIndex][$i]]=1;
												}
						
												$tmpValue = $rankListArray[$checkLoopIndex][$i];
												
												if($tmpValue>$tmpMaxValue){				
													$tmpMaxValue=$tmpValue;
												}
											}
											for($k=0;$k<=$tmpMaxValue;$k++){
												if($countIndexArray[$checkLoopIndex][$k]>1){
													while($countIndexArray[$checkLoopIndex][$k]!=1){
														for($j=0;$j<count($questionArray);$j++){
															if($questionArray[$j]->id==$k){
																$questionArray[$j]->rank -= $questionArray[$j]->ratio;
															}
														}
														$countIndexArray[$checkLoopIndex][$k]--;
													}
												}
											}
											$checkLoopIndex++;
										}
						
										$qIndex=0;
										$tmpRank=0;
										$tmpQKeywordIndex=0;
										//Iterating $questionArray and finding the question that has maximum rank
										while($qIndex<count($questionArray)){
											$tmpKeyword = $questionArray[$qIndex];
											if($tmpRank<$tmpQKeyword->rank){
												$tmpQKeywordIndex=$qIndex;
												//echo $tmpQKeywordIndex;
												$tmpRank = $tmpQKeyword->rank;
											}
											$qIndex++;
										}
										$maxRankedQuestion = $questionArray[$tmpQKeywordIndex];
										$result2=mysql_query("SELECT * FROM question WHERE id='".$maxRankedQuestion->id."'");
										$num_rows2 = mysql_num_rows($result2);
										if($num_rows2>0)
										{
											mt_srand(make_seed());
											$randval = mt_rand();
											$questionInd=$randval%$num_rows2;
											$questionTxtArray=array();
											$questionTxtArrayIndex=0;
											$questionIdArray=array();
											$questionIdArrayIndex=0;
											while($row2 = mysql_fetch_array($result2))
											{		
												$questionTxtArray[$questionTxtArrayIndex]= $row2['txt'];
												$questionTxtArrayIndex++;
												$questionIdArray[$questionIdArrayIndex]= $row2['result_id'];
												$questionIdArrayIndex++;
											}
											$answerText = "Melinda: ".$questionTxtArray[$questionInd];
											templateThreading("visibilityOfAnswerT2", 500, 4000, $answerText);
											$lastQuestion=$questionIdArray[$questionInd];
											$_SESSION['lastQuestion']=$lastQuestion;
											$_SESSION['question']=0;
					
											echo "<br>";
											
											$sql="INSERT INTO user_logs(user_id, user_ip, log) VALUES('".$_SESSION['user_id']."', '".$ip."', '".mysql_real_escape_string($answerText)."');";		
											$check = mysql_query($sql);
											if (!$check) 
											{		
												die("Invalid query: " . mysql_error());
											}
											$_SESSION['answer']=$questionTxtArray[$questionInd];
										}
										
									}
								}
								else
								{
									if(checkTheUserResponseIsDoYouKnow($sampleSentence))
									{
											$Searchword = explode(" ", $sampleSentence); 
											
											for($c=0; $c<count($Searchword); $c++)
											{
												if(checkTheUserResponseIsDoYouKnow($Searchword[$c]))
												{
													break;
												}
												
												else 
												{	
													$flag=0;
													if($Searchword[$c]{0}=='i')
													{
														$flag=1;
													}
													$Searchword[$c]= ucfirst($Searchword[$c]); 
													if($flag==1)
													{
														$Searchword[$c]=str_replace('I', 'İ', $Searchword[$c]);
													}
										
													$Searchword1=$Searchword1.$Searchword[$c]."_";
												}
											}
											//Ajax request operation in background
											ajaxRequestForm($Searchword1,$sampleSentence,$_SESSION['user_id'],$ip);
											
									}

									else if(greeting($sampleSentence))
									{
										$answerTextM[0]="Melinda: Teşekkür ederim :)"; 
										$answerTextM[1]="Melinda: Teşekkürler :)"; 
										mt_srand(make_seed());
										$randval = mt_rand();
										$answerIndex=$randval%2;
										$answerText = $answerTextM[$answerIndex];
										templateThreading("visibilityOfAnswerT1", 2000, 5000, $answerText);
										echo "<br>";
										$sql="INSERT INTO user_logs(user_id, user_ip, log) VALUES('".$_SESSION['user_id']."', '".$ip."', '".mysql_real_escape_string($answerText)."');";		
										$check = mysql_query($sql);
										if (!$check) 
										{		
											die("Invalid query: " . mysql_error());
										}
										
										$_SESSION['answer']=$answerText;
									}
									else
									{
										$Searchword = explode(" ", $sampleSentence); 
												
										for($c=0; $c<count($Searchword); $c++)
										{
												
											$flag=0;
											if($Searchword[$c]{0}=='i')
											{
												$flag=1;
											}
											$Searchword[$c]= ucfirst($Searchword[$c]); 
											if($flag==1)
											{
												$Searchword[$c]=str_replace('I', 'İ', $Searchword[$c]);
											}
											
											$Searchword1=$Searchword1.$Searchword[$c]."_";
													
										}
										//Ajax request operation in background
										ajaxRequestForm2($Searchword1,$sampleSentence,$_SESSION['user_id'],$ip);			
									}
									
									
								}
					
								$keywordIndex++;
					
							}
					
						}
					
					}
					?>
				</div>
			</div>
			<div id="clearLogs">
				<?php if($numberOfUserLogs>0){
				?>
					<input TYPE="button" class="clearLogsButton" onclick="window.location.href='logout.php'">
				<?php
				}else{	
				?>
					<input TYPE="button" class="clearLogsButton" style="display:none" onclick="window.location.href='logout.php'">
				<?php
					}
				?>
			</div>
		</div>
		<div id="footer" class="center">
			<p>Bu proje, İzmir Ekonomi Üniversitesi Mühendislik ve Bilgisayar Bilimleri Fakültesi, Yazılım Mühendisliği öğrencileri <a href="mailto:fatihturker35@gmail.com">Fatih Türker</a> ve <a href="mailto:onurkeklik90@gmail.com">Onur Keklik</a> tarafından, Yönetim ve Bilgi Sistemleri Müdürlüğü  yönetiminde 2012-2013 zorunlu yaz stajı projesi  “Web Chat Bot  - Bir yapay zeka uygulaması” kapsamında gerçekleştirilmiştir. Henüz beta aşamasında olup, sadece test kullanımına açıktır.</p>
			
			<p>Web Bot uygulamasının kullanımı sırasında doğacak olumsuz durumlardan İzmir Ekonomi Üniversitesi, Yönetim Bilgi Sistemleri  Müdürlüğü ve yazılımcılar sorumlu tutulamazlar.</p>
		</div>
	</div>
</body>
</html>
<?php
}
	
else if(!isset($_SESSION['user_id']))
{
	//if session is empty go back to index page.
	echo '<META HTTP-EQUIV="Refresh" Content="0; URL=index.html">';
}	

mysql_close($con);

?>