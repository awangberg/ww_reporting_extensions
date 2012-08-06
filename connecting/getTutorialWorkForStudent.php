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
$query = "SELECT problem.name, answer_id, student_answer.problem_id FROM `student_answer` LEFT JOIN `problem` ON student_answer.problem_id = problem.problem_id WHERE student_id = $user_id AND answer_id = tutorial_key ORDER BY answer_id ASC, tutorial_key ASC, student_answer.problem_id ASC";
$result = mysql_query($query, $con);
while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
  $xml .= "  <Problem>\n";
  $xml .= "    <tutorial_id>" . $row['problem_id'] . "</tutorial_id>\n";
  $xml .= "    <name>" . $row['name'] . "</name>\n";
  $xml .= "    <answer_id>" .  $row['answer_id'] . "</answer_id>\n";
  $xml .= "  </Problem>\n";
}
$xml .= "</Problems>\n";

mysql_close($con);

print $xml

?>
