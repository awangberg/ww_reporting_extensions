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
else
{
  	echo "Error selecting database $db: " . mysql_error();
}

$course_id = $_REQUEST['course_id'];

$query = "SELECT course_name FROM `course` WHERE course_id=" . $course_id;
$result = mysql_query($query, $con);
$course_name = "";
while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
  $course_name = $row['course_name'];
}


$xml = "<Students>\n";
$query = "SELECT user.user_id, user.course_id, user.user_name, course.course_name FROM `user` LEFT JOIN `course` ON user.course_id = course.course_id WHERE user.user_id >= 0 ORDER BY course_name ASC, user_name ASC";
$result = mysql_query($query, $con);
while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
  $xml .= "  <Student>\n";
  $xml .= "    <id>" . $row['user_id'] . "</id>\n";
  $xml .= "    <name>" . $row['user_name'] . "</name>\n";
  $xml .= "    <course_name>" .  $row['course_name'] . "</course_name>\n";
  $xml .= "    <course_id>" . $row['course_id'] . "</course_id>\n";
  $xml .= "  </Student>\n";
}
$xml .= "</Students>\n";

mysql_close($con);

print $xml

?>
