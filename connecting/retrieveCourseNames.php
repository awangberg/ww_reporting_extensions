<?php

include("access.php");

header("Content-Type: text/xml");

$con = mysql_connect($db_host, $db_user, $db_pass);

if(!$con) {
  die('Could not connect: ' . mysql_error());
}

$db = $_REQUEST['userDatabaseName'];

//select the database $db
//create table assignments in $db database:
if (mysql_select_db("$db", $con)) {
	//echo "selected database $db";
}
else {
  	echo "Error selecting database $db: " . mysql_error();
}

$query = 'SELECT course.course_id, course.course_name, course.section_name, user.first_name, user.last_name '
	.' FROM `course` '
	.' LEFT JOIN `user` '
	.' ON course.instructor_id = user.user_id '
	.' WHERE CURRENT_TIMESTAMP BETWEEN course.initial_date AND course.final_date ';

$result = mysql_query($query, $con);

$xmlData = "";
$xmlData .= "<ListOfCourses>\n";

$courseNames_array = array();
while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
  if ($row['course_id'] != "") {
    $xmlData .= " <Course>\n";
    $xmlData .= "    <Name>" . $row['course_name'] . " " . $row['section_name'] . " (" . $row['last_name'] . ")</Name>\n";
    $xmlData .= "    <Course_id>" . $row['course_id'] . "</Course_id>\n";
    $xmlData .= " </Course>\n";
  }
}

$xmlData .= "</ListOfCourses>\n";

mysql_close($con);

print $xmlData;

?>


