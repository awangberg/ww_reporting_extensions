<?php

include("access.php");

header("content-type: text/xml");

$con = mysql_connect($db_host, $db_user, $db_pass);

if(!$con) {
  die('Could not connect: ' . mysql_error());
}

$db = $_REQUEST['userDatabaseName'];

//select the database $db
if (mysql_select_db("$db", $con)) {
	//echo "selected database $db";
}
else {
  	echo "Error selecting database $db: " . mysql_error();
}

$session_course_id = check_input($_REQUEST['course_id']);

$query = 'SELECT course_name FROM `course` WHERE course_id=' . $session_course_id;

$result = mysql_query($query,$con);
$course_name = "";

while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    $course_name = $row['course_name'];
}
mysql_close($con);



$con = mysql_connect($ww_db_host, $ww_db_user, $ww_db_pass);

if(!$con) {
  die('Could not connect: ' . mysql_error());
}

$db = 'webwork';
if (mysql_select_db("$db", $con)) {
	//echo "selected database $db";
}
else {
  	echo "Error selecting database $db: " . mysql_error();
}

$setTable = $course_name . "_set";

$query = 'SELECT CAST(set_id AS CHAR) AS set_id FROM ' . $setTable;


$return_string = "<Sets>\n";
$result = mysql_query($query,$con);
while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
  $return_string .= "  <Set>\n";
  $return_string .= "    <name>" . $row['set_id'] . "</name>\n";
  $return_string .= "    <id>" . $row['set_id'] . "</id>\n";
  $return_string .= "  </Set>\n";
}
$return_string .= "</Sets>\n";

mysql_close($con);

print $return_string;

?>
