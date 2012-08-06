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

$wwSet_id = $_REQUEST['wwSet_id'];

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

$problemTable = $course_name . "_problem";

$query = 'SELECT problem_id, source_file FROM ' . $problemTable . ' WHERE set_id="' . $wwSet_id . '" ORDER BY problem_id';

$return_string = "<Problems>\n";
$result = mysql_query($query,$con);
while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
  $return_string .= "  <Problem>\n";
  $return_string .= "    <name>" . $row['source_file'] . "</name>\n";
  $return_string .= "    <order>" . $row['problem_id'] . "</order>\n";
  $return_string .= "  </Problem>\n";
}
$return_string .= "</Problems>\n";

mysql_close($con);

print $return_string;

?>
