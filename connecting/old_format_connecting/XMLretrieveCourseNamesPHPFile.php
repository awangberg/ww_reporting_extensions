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

$query =   'SELECT courseID, courseName, sectionName '
	  .' FROM `courses` ';

$result = mysql_query($query, $con);

$xmlData = "";
$xmlData .= "<ListOfCourses>\n";

$courseNames_array = array();
while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
  $sectionName = $row['sectionName'];
  $courseName = $row['courseName'];
  $courseID = $row['courseID'];

  $name = $courseName . ": " . $sectionName;
  if (array_key_exists($name, $courseNames_array)) {
    //do nothing!
  }
  else {
    //  insert $courseName into the array:
    $courseNames_array[$name] = 1;
    $xmlData .= " <Course>\n";
    $xmlData .= "    <Name>$name</Name>\n";
    $xmlData .= "    <CourseName>$courseName</CourseName>\n";
    $xmlData .= "    <SectionName>$sectionName</SectionName>\n";
    $xmlData .= "    <ID>$courseID</ID>\n";
    $xmlData .= " </Course>\n";
  }
}

$xmlData .= "</ListOfCourses>\n";

print $xmlData;

?>


