<?php

include("access.php");

$con = mysql_connect($db_host, $db_user, $db_pass);

$db = "goteam";

if(!$con) {
  die('Could not connect: ' . mysql_error());
}

//select the database '$db'
$res = mysql_select_db("$db", $con);

$problemID = $_GET['problemID'];
$promptID = $_GET['promptID'];
$segment = $_GET['segment'];

$drawData = "";


$query = 'SELECT problemName, drawData'
	.' FROM `problems` '
	.' WHERE problemID='.$problemID;
$result = mysql_query($query, $con);
$problemName = "Problem: ";
while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
  $problemName .= $row['problemName'];
  $drawData = $row['drawData'];
}


$query = 'SELECT promptName, promptData'
	.' FROM `prompts` '
	.' WHERE promptID='.$promptID;
$result = mysql_query($query, $con);
$promptData = "Prompt Data: ";
$promptName = "Prompt Name: ";
while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
  $promptData = $row['promptData'];
  $promptName .= $row['promptName'];
}

$tmpPromptData = explode(";~;", $promptData);

$question = array();

for ($j = 0; $j < count($tmpPromptData)-1; $j++) {
  $tmpPromptParts = explode(";_;", $tmpPromptData[$j]);
  $question[$j] = $tmpPromptParts[3];
  $questionType[$j] = $tmpPromptParts[0];
  $questionTime[$j] = $tmpPromptParts[1];
}

//get the array of drawingData
$drawArray = explode(";~;", $drawData);

$maxHeight = 0;

//get the max height:
for ($i=0; $i<count($drawArray)-1; $i++) {
  $lineData = explode(";_;", $drawArray[$i]);
  if ($lineData[6] == "ClearImage") {
    //do nothing.
  }
  else if ($lineData[0] == "author") {
    if ($lineData[6] > $maxHeight) {
      $maxHeight = $lineData[6];
    }  
    else if ($lineData[8] > $maxHeight) {
      $maxHeight = $lineData[8];
    }
  }  
  else {
  
  }
}

$maxHeight = round($maxHeight+1,0);

//Make part of the picture.


$width = 750;
$height = $maxHeight;
$img1 = imagecreate($width, $height);
$background1 = imagecreatetruecolor($width, $height);
imageantialias($img1,false);
imageantialias($background1,false);
$backgroundColor1=imagecolorallocate($img1,255,255,255);

//loop through the lines:
for ($i=0; $i<count($drawArray)-1; $i++) {

  $lineData = explode(";_;", $drawArray[$i]);

  imagesetthickness($img1,$lineData[2]);

  $rColor   = ($lineData[3] & 0x00FF0000) >> 16;
  $gColor   = ($lineData[3] & 0x0000FF00) >> 8;
  $bColor   = ($lineData[3] & 0x000000FF);

  $lineColor1=imagecolorexact($img1, $rColor, $gColor, $bColor);

  if ($lineColor1==(-1)) {
    $lineColor1 = imagecolorallocate($img1, $rColor, $gColor, $bColor);
  }

  if($lineData[0]=="author") {

	if ($segment == 4) {
	  imageline($img1,$lineData[5],$lineData[6],$lineData[7],$lineData[8],$lineColor1);
	}
	else if ($lineData[1]<=$questionTime[$segment-1]) {
	  imageline($img1,$lineData[5],$lineData[6],$lineData[7],$lineData[8],$lineColor1);
	}
  }  
  else {

  }
}



imageantialias($img1,false);

imageantialias($background1,false);

ini_set("memory_limit","64M");

imagecopyresampled($background1,$img1,0,0,0,0,$width,$height,$width,$height);

// save the png to a file:
//imagepng($background1, "png/1.png");
header('Content-Type: image/png');
imagepng($background1);


?>
