<?php

include("access.php");

function check_input($value) {
  //Stripslashes
  if (get_magic_quotes_gpc()) {
	$value = stripslashes($value);
  }

  // Quote if not a number
  if (!is_numeric($value)) {
	$value = "'" . mysql_real_escape_string($value) . "'";
  }
  return $value;
}

header("content-type: text/xml");

$con = mysql_connect($db_host, $db_user, $db_pass);

if(!$con) {
  die('Could not connect: ' . mysql_error());
}

$db = $_POST['userDatabaseName'];


//select the database $db
//create table assignments in $db database:
if (mysql_select_db("$db", $con)) {
	//echo "selected database $db";
}
else {
  	echo "Error selecting database $db: " . mysql_error();
}

$forCourseID = $_POST['courseID'];

$query = 'SELECT homeworkSetID, homeworkSetName '
	.' FROM `homeworkSets` '
	.' WHERE forCourseID = '.$forCourseID;

$result = mysql_query($query, $con);

$xmlData = "";
$xmlData .= "<ListOfHomeworkSets>\n";

while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
  $homeworkSetID = $row['homeworkSetID'];
  $homeworkSetName = $row['homeworkSetName'];

  $xmlData .= "  <HomeworkSet>\n";
  $xmlData .= "    <Name>$homeworkSetName</Name>\n";
  $xmlData .= "    <ID>$homeworkSetID</ID>\n";
  $xmlData .= "  </HomeworkSet>\n";
}


$xmlData .= "</ListOfHomeworkSets>\n";

print $xmlData;

?>


