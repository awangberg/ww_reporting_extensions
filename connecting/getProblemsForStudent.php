<?php

include("access.php");

header("content-type: text/xml");

$con = mysql_connect($db_host, $db_user, $db_pass);

if (!$con) {
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

$course_id = $_REQUEST['course_id'];
$user_id = $_REQUEST['user_id'];

$query = "SELECT course_name FROM `course` WHERE course_id=" . $course_id;
$result = mysql_query($query, $con);
$course_name = "";
while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
  $course_name = $row['course_name'];
}


$xml = "<Problems>\n";
$query =  "SELECT wwStudentWorkForProblem.ww_set_id, wwStudentWorkForProblem.ww_problem_number, wwStudentWorkForProblem.problem_id, draw.filename FROM `wwStudentWorkForProblem` LEFT JOIN `problem` ON wwStudentWorkForProblem.problem_id = problem.problem_id LEFT JOIN `draw` ON problem.initial_draw_id = draw.draw_id WHERE user_id = $user_id ORDER BY ww_set_id ASC, ww_problem_number ASC"; 

$result = mysql_query($query, $con);
while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
  $xml .= "  <Problem>\n";
  $xml .= "    <id>" . $row['problem_id'] . "</id>\n";
  $xml .= "    <name>" . $row['ww_problem_number'] . "</name>\n";
  $xml .= "    <wwSet>" .  $row['ww_set_id'] . "</wwSet>\n";
  $xml .= "    <size_of_file>" . filesize($row['filename']) . "</size_of_file>\n";
  $xml .= "  </Problem>\n";
}
$xml .= "</Problems>\n";

mysql_close($con);

print $xml

?>
